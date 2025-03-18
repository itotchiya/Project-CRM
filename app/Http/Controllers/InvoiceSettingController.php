<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\UpdateInvoiceSetting;
use App\Http\Requests\UpdatePrefixSetting;
use App\Http\Requests\UpdateTemplateSetting;
use App\Models\GlobalSetting;
use App\Models\InvoicePaymentDetail;
use App\Models\InvoiceSetting;
use App\Models\QuickBooksSetting;
use App\Models\UnitType;

class InvoiceSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'app.menu.financeSettings';
        $this->activeSettingMenu = 'invoice_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_finance_setting') == 'all' && (in_array('invoices', user_modules()) ||
                in_array('estimates', user_modules()) || in_array('orders', user_modules()) || in_array('leads', user_modules()) || in_array('payments', user_modules()))));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tab = request('tab');

        if (is_null($tab)) {
            if (in_array('invoices', user_modules())) {
                $tab = 'general';
            } elseif (in_array('invoices', user_modules()) || in_array('estimates', user_modules()) || in_array('orders', user_modules()) || in_array('leads', user_modules())) {
                $tab = 'template';
            } elseif (in_array('invoices', user_modules()) || in_array('payments', user_modules())) {
                $tab = 'quickbooks';
            }
        }

        $this->unitTypes = UnitType::all();

        switch ($tab) {
            case 'quickbooks':
                $this->quickbookSetting = QuickBooksSetting::first();
                $this->view = 'invoice-settings.ajax.quickbooks';
                break;
            case 'units':
                $this->view = 'invoice-settings.ajax.units';
                break;
            case 'prefix':
                $this->view = 'invoice-settings.ajax.prefix';
                break;
            case 'template':
                $this->view = 'invoice-settings.ajax.template';
                break;
            case 'payment':
                $this->payments = InvoicePaymentDetail::get();
                $this->view = 'invoice-settings.ajax.payment';
                break;
            default:
                $this->view = 'invoice-settings.ajax.general';
                break;
        }

        $this->invoiceSetting = InvoiceSetting::first();
        $this->activeTab = $tab ?: 'general';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('invoice-settings.index', $this->data);
    }

    /**
     * @param UpdateInvoiceSetting $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function update(UpdateInvoiceSetting $request)
    {
        $setting = InvoiceSetting::first();
        $setting->due_after             = $request->due_after;
        $setting->invoice_terms         = $request->invoice_terms;
        $setting->show_gst              = $request->has('show_gst') ? 'yes' : 'no';
        $setting->hsn_sac_code_show     = $request->has('hsn_sac_code_show') ? 1 : 0;
        $setting->tax_calculation_msg   = $request->has('show_tax_calculation_msg') ? 1 : 0;
        $setting->authorised_signatory   = $request->has('show_authorised_signatory') ? 1 : 0;
        $setting->show_status   = $request->has('show_status') ? 1 : 0;
        $setting->show_project          = $request->has('show_project') ? 1 : 0;
        $setting->send_reminder         = $request->send_reminder;
        $setting->reminder              = $request->reminder;
        $setting->send_reminder_after   = $request->send_reminder_after;
        $setting->locale                = $request->locale;
        $setting->show_client_name      = $request->has('show_client_name') ? 'yes' : 'no';
        $setting->show_client_email     = $request->has('show_client_email') ? 'yes' : 'no';
        $setting->show_client_phone     = $request->has('show_client_phone') ? 'yes' : 'no';
        $setting->show_client_company_name = $request->has('show_client_company_name') ? 'yes' : 'no';
        $setting->show_client_company_address   = $request->has('show_client_company_address') ? 'yes' : 'no';
        $setting->other_info = $request->other_info;

        if ($request->hasFile('logo')) {
            Files::deleteFile($setting->logo, GlobalSetting::APP_LOGO_PATH);
            $setting->logo = Files::uploadLocalOrS3($request->logo, GlobalSetting::APP_LOGO_PATH, width: 400);
        }

        if ($request->hasFile('authorised_signatory_signature')) {
            Files::deleteFile($setting->authorised_signatory_signature, GlobalSetting::APP_LOGO_PATH);
            $setting->authorised_signatory_signature = Files::uploadLocalOrS3($request->authorised_signatory_signature, GlobalSetting::APP_LOGO_PATH, width: 400);
        }

        $setting->save();

        session()->forget('invoice_setting');
        session()->forget('company');

        return Reply::success(__('messages.updateSuccess'));
    }

    public function updatePrefix(UpdatePrefixSetting $request, $id)
    {
        $setting = InvoiceSetting::findOrFail($id);

        if (in_array('invoices', user_modules())) {
            $setting->invoice_prefix               = $request->invoice_prefix;
            $setting->invoice_number_separator     = $request->invoice_number_separator;
            $setting->invoice_digit                = $request->invoice_digit;
            $setting->credit_note_prefix           = $request->credit_note_prefix;
            $setting->credit_note_number_separator = $request->credit_note_number_separator;
            $setting->credit_note_digit            = $request->credit_note_digit;
        }

        if (in_array('estimates', user_modules())) {
            $setting->estimate_prefix              = $request->estimate_prefix;
            $setting->estimate_number_separator    = $request->estimate_number_separator;
            $setting->estimate_digit               = $request->estimate_digit;
            $setting->estimate_request_prefix      = $request->estimate_request_prefix;
            $setting->estimate_request_number_separator = $request->estimate_request_number_separator;
            $setting->estimate_request_digit       = $request->estimate_request_digit;
        }

        if (in_array('orders', user_modules())) {
            $setting->order_prefix                 = $request->order_prefix;
            $setting->order_number_separator       = $request->order_number_separator;
            $setting->order_digit                  = $request->order_digit;
        }

        $setting->proposal_prefix               = $request->proposal_prefix;
        $setting->proposal_number_separator     = $request->proposal_number_separator;
        $setting->proposal_digit                = $request->proposal_digit;

        $setting->save();

        session()->forget('invoice_setting');
        session()->forget('company');

        return Reply::success(__('messages.updateSuccess'));
    }

    public function updateTemplate(UpdateTemplateSetting $request, $id)
    {
        $setting = InvoiceSetting::findOrFail($id);
        $setting->template = $request->template;
        $setting->save();

        session()->forget('invoice_setting');
        session()->forget('company');

        return Reply::success(__('messages.updateSuccess'));
    }
}
