<?php

namespace App\Observers;

use Carbon\Carbon;
use Exception;
use App\Models\User;
use App\Helper\Files;
use App\Models\Invoice;
use App\Models\Estimate;
use App\Services\Google;
use App\Scopes\ActiveScope;
use Google_Service_Calendar_Event;
use Illuminate\Support\Str;
use App\Models\InvoiceItems;
use App\Models\Notification;
use App\Models\CompanyAddress;
use App\Events\NewInvoiceEvent;
use App\Models\UniversalSearch;
use App\Models\InvoiceItemImage;
use App\Models\EstimateItemImage;
use App\Traits\UnitTypeSaveTrait;
use App\Events\InvoiceUpdatedEvent;
use App\Models\GoogleCalendarModule;
use App\Http\Controllers\QuickbookController;
use App\Models\Payment;
use App\Models\ProposalItemImage;
use App\Traits\EmployeeActivityTrait;

class InvoiceObserver
{
    use EmployeeActivityTrait;

    use UnitTypeSaveTrait;

    public function saving(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $invoice->last_updated_by = user()->id;

                if (request()->has('calculate_tax')) {
                    $invoice->calculate_tax = request()->calculate_tax;
                }
            }
        }
    }

    public function creating(Invoice $invoice)
    {

        $invoice->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {

            if ((request()->type && request()->type == 'send') || !is_null($invoice->invoice_recurring_id) || request()->type == 'mark_as_send') {
                $invoice->send_status = 1;
            }
            else {
                $invoice->send_status = 0;
            }

            if (request()->type && request()->type == 'draft') {
                $invoice->status = 'draft';
            }

            if (!is_null($invoice->estimate_id)) {
                $estimate = Estimate::findOrFail($invoice->estimate_id);

                if ($estimate->status == 'accepted') {
                    $invoice->send_status = 1;
                }
            }

            /* If it is a order invoice, then send_status will be always 1 so that it could be visible to clients */
            if (isset($invoice->order_id)) {
                $invoice->send_status = 1;
            }

            $invoice->added_by = user() ? user()->id : null;
        }

        if (company()) {
            $invoice->company_id = company()->id;
        }

        if (is_numeric($invoice->invoice_number)) {
            $invoice->invoice_number = $invoice->formatInvoiceNumber();
        }

        $invoiceSettings = company() ? company()->invoiceSetting : $invoice->company->invoiceSetting;
        $invoice->original_invoice_number = str($invoice->invoice_number)->replace($invoiceSettings->invoice_prefix . $invoiceSettings->invoice_number_separator, '');
    }

    public function created(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (!empty(request()->item_name) && is_array(request()->item_name)) {

                $itemsSummary = request()->item_summary;
                $cost_per_item = request()->cost_per_item;
                $hsn_sac_code = request()->hsn_sac_code;
                $quantity = request()->quantity;
                $unitId = request()->unit_id;
                $product = request()->product_id;
                $amount = request()->amount;
                $tax = request()->taxes;
                $invoice_item_image = request()->invoice_item_image;
                $invoice_item_image_delete = request()->invoice_item_image_delete;
                $invoice_item_image_url = request()->invoice_item_image_url;
                $invoiceOldImage = request()->image_id;

                foreach (request()->item_name as $key => $item) :
                    if (!is_null($item)) {
                        $invoiceItem = InvoiceItems::create(
                            [
                                'invoice_id' => $invoice->id,
                                'item_name' => $item,
                                'item_summary' => $itemsSummary[$key] ?: '',
                                'type' => 'item',
                                'unit_id' => (isset($unitId[$key])) ? $unitId[$key] : null,
                                'product_id' => (isset($product[$key])) ? $product[$key] : null,
                                'hsn_sac_code' => (isset($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                                'quantity' => $quantity[$key],
                                'unit_price' => round($cost_per_item[$key], 2),
                                'amount' => round($amount[$key], 2),
                                'taxes' => ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null),
                                'field_order' => $key + 1
                            ]
                        );
                    }

                    /* Invoice file save here */
                    if (isset($invoiceItem) && (isset($invoice_item_image[$key]) || isset($invoice_item_image_url[$key]))) {

                        $filename = '';

                        if (isset($invoice_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3($invoice_item_image[$key], InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/');
                        }

                        InvoiceItemImage::create(
                            [
                                'invoice_item_id' => $invoiceItem->id,
                                'filename' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : '',
                                'hashname' => isset($invoice_item_image[$key]) ? $filename : null,
                                'size' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getSize() : null,
                                'external_link' => isset($invoice_item_image[$key]) ? null : (isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : null),
                            ]
                        );

                    }

                    $image = true;

                    if (isset($invoice_item_image_delete[$key])) {
                        $image = false;
                    }

                    if ($image && (isset(request()->image_id[$key]) && $invoiceOldImage[$key] != '') && isset($invoiceItem) && request()->has('estimate_id')) {
                        $estimateOldImg = EstimateItemImage::with('item')->where('id', request()->image_id[$key])->first();

                        $this->duplicateImageStore($estimateOldImg, $invoiceItem);
                    }

                    if ($image && (isset(request()->image_id[$key]) && $invoiceOldImage[$key] != '') && isset($invoiceItem) && request()->has('proposal_id')) {
                        $estimateOldImg = ProposalItemImage::with('item')->where('id', request()->image_id[$key])->first();

                        $this->duplicateImageStore($estimateOldImg, $invoiceItem, true);
                    }

                endforeach;
            }

            if (($invoice->project && $invoice->project->client_id != null) || $invoice->client_id != null) {
                $clientId = ($invoice->project && $invoice->project->client_id != null) ? $invoice->project->client_id : $invoice->client_id;
                // Notify client
                $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);

                if ($notifyUser && request()->type && request()->type == 'send') {
                    event(new NewInvoiceEvent($invoice, $notifyUser));
                }
            }

            // Add event to google calendar
            if (request()->type && (request()->type == 'send' || request()->type == 'mark_as_send')) {
                if (!is_null($invoice->due_date)) {
                    $invoice->event_id = $this->googleCalendarEvent($invoice);
                }

                if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                    $quickBooks = new QuickbookController();
                    $qbInvoiceId = $quickBooks->createInvoice($invoice);

                    $invoice->quickbooks_invoice_id = $qbInvoiceId;
                    $invoice->saveQuietly();
                }
            }

            if (is_null($invoice->company_address_id)) {
                $defaultCompanyAddress = CompanyAddress::where('is_default', 1)->where('company_id', $invoice->company_id)->first();
                $invoice->company_address_id = $defaultCompanyAddress->id;
            }
        }

        $paymentStatus = request()->payment_status;

        $invoice->custom_invoice_number = $invoice->invoice_number;

        if ($paymentStatus == '1') {
            $invoice->gateway = request()->gateway;
            $invoice->transaction_id = request()->transaction_id;
            $invoice->offline_method_id = request()->offline_methods;
            $invoice->status = 'paid';
        }

        $invoice->saveQuietly();

        if ($paymentStatus == '1') {
            $clientPayment = new Payment();
            $clientPayment->currency_id = $invoice->currency_id;
            $clientPayment->invoice_id = $invoice->id;
            $clientPayment->project_id = $invoice->project_id;
            $clientPayment->amount = $invoice->total;
            $clientPayment->exchange_rate = $invoice->exchange_rate;
            $clientPayment->transaction_id = $invoice->transaction_id;
            $clientPayment->bank_account_id = $invoice->bank_account_id;
            $clientPayment->offline_method_id = request()->offline_methods;
            $clientPayment->default_currency_id = request()->default_currency_id;
            $clientPayment->gateway = $invoice->gateway;
            $clientPayment->status = 'complete';
            $clientPayment->paid_on = now();
            $clientPayment->save();
        }
    }

    public function updating(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (request()->type && request()->type == 'send' || request()->type == 'mark_as_send') {
                $invoice->send_status = 1;

                if ($invoice->status == 'draft') {
                    $invoice->status = 'unpaid';
                }
            }

            // Update event to google calendar
            if ($invoice && !is_null($invoice->due_date)) {
                $invoice->event_id = $this->googleCalendarEvent($invoice);
            }

        }

    }

    public function updated(Invoice $invoice)
    {
        if (!isRunningInConsoleOrSeeding()) {
            /*
                Step1 - Delete all invoice items which are not avaialable
                Step2 - Find old invoices items, update it and check if images are newer or older
                Step3 - Insert new invoices items with images
            */


            $request = request();

            $items = $request->item_name;
            $itemsSummary = $request->item_summary;
            $hsn_sac_code = $request->hsn_sac_code;
            $unitId = $request->unit_id;
            $product = $request->product_id;
            $tax = $request->taxes;
            $quantity = $request->quantity;
            $cost_per_item = $request->cost_per_item;
            $amount = $request->amount;
            $invoice_item_image = $request->invoice_item_image;
            $invoice_item_image_url = $request->invoice_item_image_url;
            $item_ids = $request->item_ids;
            
            if (empty($item_ids)) {
                $item_ids = InvoiceItems::where('invoice_id', $invoice->id)->pluck('id')->toArray();
            }

            if (!empty($request->item_name) && is_array($request->item_name) && !request()->has('cn_number')) {
                // Step1 - Delete all invoice items which are not avaialable
                if (!empty($item_ids)) {
                    InvoiceItems::whereNotIn('id', $item_ids)->where('invoice_id', $invoice->id)->delete();
                }

                // Step2&3 - Find old invoices items, update it and check if images are newer or older
                foreach ($items as $key => $item) {
                    $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                    try {
                        $invoiceItem = InvoiceItems::findOrFail($invoice_item_id);
                    } catch (Exception) {
                        $invoiceItem = new InvoiceItems();
                    }

                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->item_name = $item;
                    $invoiceItem->item_summary = $itemsSummary[$key];
                    $invoiceItem->type = 'item';
                    $invoiceItem->unit_id = (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null;
                    $invoiceItem->product_id = (isset($product[$key]) && !is_null($product[$key])) ? $product[$key] : null;
                    $invoiceItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                    $invoiceItem->quantity = $quantity[$key];
                    $invoiceItem->unit_price = round($cost_per_item[$key], 2);
                    $invoiceItem->amount = round($amount[$key], 2);
                    $invoiceItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                    $invoiceItem->field_order = $key + 1;
                    $invoiceItem->saveQuietly();

                    /* Invoice file save here */
                    if ((isset($invoice_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) || isset($invoice_item_image_url[$key])) {

                        /* Delete previous uploaded file if it not a product (because product images cannot be deleted) */
                        if (!isset($invoice_item_image_url[$key]) && $invoiceItem && $invoiceItem->invoiceItemImage) {
                            Files::deleteFile($invoiceItem->invoiceItemImage->hashname, InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/');
                        }

                        $filename = '';

                        if (isset($invoice_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3($invoice_item_image[$key], InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/');
                        }

                        InvoiceItemImage::updateOrCreate(
                            [
                                'invoice_item_id' => $invoiceItem->id,
                            ],
                            [
                                'filename' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : '',
                                'hashname' => isset($invoice_item_image[$key]) ? $filename : null,
                                'size' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getSize() : null,
                                'external_link' => isset($invoice_item_image[$key]) ? null : ($invoice_item_image_url[$key] ?? null),
                            ]
                        );
                    }
                }
            }
        }

        // Send notification
        if (($invoice->isDirty('issue_date') || $invoice->isDirty('due_date') || $invoice->isDirty('sub_total') || $invoice->isDirty('discount') || $invoice->isDirty('discount_type') || $invoice->isDirty('total') || $invoice->isDirty('recurring') || $invoice->isDirty('note') || $invoice->isDirty('calculate_tax') || $invoice->isDirty('due_amount')) && (($invoice->project && $invoice->project->client_id != null) || $invoice->client_id != null)) {
            $clientId = ($invoice->project && $invoice->project->client_id != null) ? $invoice->project->client_id : $invoice->client_id;

            // Notify client
            $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);

            if ($notifyUser && request()->type != 'mark_as_send') {
                event(new InvoiceUpdatedEvent($invoice, $notifyUser));
            }

        }

        $paymentStatus = request()->payment_status;

        $invoice->custom_invoice_number = $invoice->invoice_number;

        if ($paymentStatus == '1') {
            $invoice->gateway = request()->gateway;
            $invoice->transaction_id = request()->transaction_id;
            $invoice->offline_method_id = request()->offline_methods;
            $invoice->status = 'paid';
        }

        if ($invoice->amountDue() == 0) {
            $invoice->status = 'paid';
        }

        $invoice->saveQuietly();

        // To add payment if received
        if ($paymentStatus == '1' && $invoice->amountDue() != 0) {
            $clientPayment = new Payment();
            $clientPayment->currency_id = $invoice->currency_id;
            $clientPayment->invoice_id = $invoice->id;
            $clientPayment->project_id = $invoice->project_id;
            $clientPayment->amount = $invoice->amountDue();
            $clientPayment->exchange_rate = $invoice->exchange_rate;
            $clientPayment->transaction_id = $invoice->transaction_id;
            $clientPayment->bank_account_id = $invoice->bank_account_id;
            $clientPayment->offline_method_id = request()->offline_methods;
            $clientPayment->default_currency_id = $invoice->default_currency_id;
            $clientPayment->gateway = $invoice->gateway;
            $clientPayment->status = 'complete';
            $clientPayment->paid_on = now();
            $clientPayment->save();
        }

        if (!is_null($invoice->quickbooks_invoice_id)) {
            if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                $quickBooks = new QuickbookController();
                $quickBooks->updateInvoice($invoice);
            }
        }

    }

    public function deleting(Invoice $invoice)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $invoice->id)->where('module_type', 'invoice')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = ['App\Notifications\InvoicePaymentReceived', 'App\Notifications\InvoiceReminder', 'App\Notifications\NewInvoice', 'App\Notifications\NewPayment'];
        Notification::deleteNotification($notifyData, $invoice->id);

        /* Delete invoice item files */
        $invoiceItems = InvoiceItems::where('invoice_id', $invoice->id)->get();

        if ($invoiceItems) {
            foreach ($invoiceItems as $invoiceItem) {
                Files::deleteDirectory(InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id);
            }
        }

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = $invoice->company;

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($invoice->event_id) {
                    $google->service('Calendar')->events->delete('primary', $invoice->event_id);
                }
            } catch (\Google\Service\Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        /* End of deleting event from google calendar */

        if (!is_null($invoice->quickbooks_invoice_id)) {
            if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                $quickBooks = new QuickbookController();
                $quickBooks->deleteInvoice($invoice);
            }
        }
    }

    public function deleted(Invoice $invoice)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'invoice-deleted');

        }
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();

        $company = $module->company;

        if (!$company) {
            return true;
        }

        $googleAccount = $company;

        if ($company->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->invoice_status == 1) {

            $google = new Google();
            $attendiesData = [];


            $attendees = User::where('id', $event->client_id)->first();

            if (!is_null($event->due_date) && !is_null($attendees) && $attendees->google_calendar_status) {
                $attendiesData[] = ['email' => $attendees->email];
            }

            $description = __('messages.invoiceDueOn');

            if ($event->issue_date && $event->due_date) {
                $start_date = Carbon::parse($event->issue_date)->shiftTimezone($googleAccount->timezone);
                $due_date = Carbon::parse($event->due_date)->shiftTimezone($googleAccount->timezone);

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new Google_Service_Calendar_Event(array(
                    'summary' => $event->invoice_number . ' ' . $description,
                    'location' => $googleAccount->address,
                    'description' => $description,
                    'colorId' => 4,
                    'start' => array(
                        'dateTime' => $start_date,
                        'timeZone' => $googleAccount->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $due_date,
                        'timeZone' => $googleAccount->timezone,
                    ),
                    'attendees' => $attendiesData,
                    'reminders' => array(
                        'useDefault' => false,
                        'overrides' => array(
                            array('method' => 'email', 'minutes' => 24 * 60),
                            array('method' => 'popup', 'minutes' => 10),
                        ),
                    ),
                ));

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (\Google\Service\Exception $error) {
                    if (is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }

        }

        return $event->event_id;
    }

    public function duplicateImageStore($estimateOldImg, $invoiceItem, $proposal = false)
    {

        if (!is_null($estimateOldImg)) {

            $file = new InvoiceItemImage();

            $file->invoice_item_id = $invoiceItem->id;

            $fileName = Files::generateNewFileName($estimateOldImg->filename);

            if (!$proposal) {
                Files::copy(EstimateItemImage::FILE_PATH . '/' . $estimateOldImg->item->id . '/' . $estimateOldImg->hashname, InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/' . $fileName);

            }
            else {
                Files::copy(ProposalItemImage::FILE_PATH . '/' . $estimateOldImg->item->id . '/' . $estimateOldImg->hashname, InvoiceItemImage::FILE_PATH . '/' . $invoiceItem->id . '/' . $fileName);
            }

            $file->filename = $estimateOldImg->filename;
            $file->hashname = $fileName;
            $file->size = $estimateOldImg->size;
            $file->save();

        }
    }

}
