<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\Proposal;
use App\Models\Notification;
use App\Models\ProposalItem;
use App\Events\NewProposalEvent;
use App\Models\ProposalItemImage;
use App\Traits\UnitTypeSaveTrait;
use App\Models\ProposalTemplateItemImage;
use App\Traits\DealHistoryTrait;
use App\Traits\EmployeeActivityTrait;

class ProposalObserver
{
    use EmployeeActivityTrait;


    use UnitTypeSaveTrait, DealHistoryTrait;

    public function saving(Proposal $proposal)
    {

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $proposal->last_updated_by = user()->id;
            }
        }

        if (request()->has('calculate_tax')) {
            $proposal->calculate_tax = request()->calculate_tax;
        }
    }

    public function creating(Proposal $proposal)
    {
        $proposal->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $proposal->added_by = user()->id;
            }
        }

        if (company()) {
            $proposal->company_id = company()->id;
        }

        if ((request()->type && request()->type == 'send' || request()->type == 'mark_as_send')) {
            $proposal->send_status = 1;
        }
        else {
            $proposal->send_status = 0;
        }

    }

    public function created(Proposal $proposal)
    {

        if (!isRunningInConsoleOrSeeding()) {

            self::createDealHistory($proposal->deal_id, 'proposal-created', proposalId: $proposal->id);

            if (user()) {
                self::createEmployeeActivity(user()->id, 'proposal-created', $proposal->id, 'proposal');
            }


            if (!empty(request()->item_name)) {
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
                        $proposalItem = ProposalItem::create(
                            [
                                'proposal_id' => $proposal->id,
                                'item_name' => $item,
                                'item_summary' => $itemsSummary[$key],
                                'type' => 'item',
                                'unit_id' => (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null,
                                'product_id' => (isset($product[$key]) && !is_null($product[$key])) ? $product[$key] : null,
                                'hsn_sac_code' => (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                                'quantity' => $quantity[$key],
                                'unit_price' => round($cost_per_item[$key], 2),
                                'amount' => round($amount[$key], 2),
                                'taxes' => ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null),
                                'field_order' => $key + 1
                            ]
                        );
                    }

                    /* Invoice file save here */
                    if (isset($proposalItem) && (isset($invoice_item_image[$key]) || isset($invoice_item_image_url[$key]))) {

                        $filename = '';

                        if (isset($invoice_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3($invoice_item_image[$key], ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');
                        }

                        ProposalItemImage::create(
                            [
                                'proposal_item_id' => $proposalItem->id,
                                'filename' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getClientOriginalName() : '',
                                'hashname' => isset($invoice_item_image[$key]) ? $filename : '',
                                'size' => isset($invoice_item_image[$key]) ? $invoice_item_image[$key]->getSize() : '',
                                'external_link' => isset($invoice_item_image[$key]) ? null : (isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : null)
                            ]
                        );
                    }

                    $image = true;

                    if (isset($invoice_item_image_delete[$key])) {
                        $image = false;
                    }

                    if ($image && (isset(request()->image_id[$key]) && $invoiceOldImage[$key] != '')) {
                        $estimateOldImg = ProposalTemplateItemImage::where('id', request()->image_id[$key])->first();

                        if (isset($proposalItem)) {
                            $this->duplicateImageStore($estimateOldImg, $proposalItem);
                        }
                    }

                endforeach;
            }

            if (request()->type == 'send') {
                $type = 'new';
                event(new NewProposalEvent($proposal, $type));
            }


        }
    }

    /**
     * @throws RelatedResourceNotFoundException
     */
    public function updating(Proposal $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (request()->type && request()->type == 'send' || request()->type == 'mark_as_send') {
                $proposal->send_status = 1;
            }
        }
    }

    public function updated(Proposal $proposal)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createEmployeeActivity(user()->id, 'proposal-updated', $proposal->id, 'proposal');
            }

            if ($proposal->isDirty('status')) {
                $type = 'signed';
                event(new NewProposalEvent($proposal, $type));
            }

            /*
                Step1 - Delete all invoice items which are not avaialable
                Step2 - Find old invoices items, update it and check if images are newer or older
                Step3 - Insert new invoices items with images
            */

            $request = request();

            $items = $request->item_name;
            $itemsSummary = $request->item_summary;
            $hsn_sac_code = $request->hsn_sac_code;
            $tax = $request->taxes;
            $quantity = $request->quantity;
            $unitId = $request->unit_id;
            $productId = $request->product_id;
            $cost_per_item = $request->cost_per_item;
            $amount = $request->amount;
            $proposal_item_image = $request->invoice_item_image;
            $proposal_item_image_url = $request->invoice_item_image_url;
            $item_ids = $request->item_ids;

            if (!empty($request->item_name) && is_array($request->item_name)) {
                // Step1 - Delete all invoice items which are not avaialable
                if (!empty($item_ids)) {
                    ProposalItem::whereNotIn('id', $item_ids)->where('proposal_id', $proposal->id)->delete();
                }

                // Step2&3 - Find old invoices items, update it and check if images are newer or older
                foreach ($items as $key => $item) {
                    $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                    $proposalItem = ProposalItem::find($invoice_item_id);

                    if ($proposalItem === null) {
                        $proposalItem = new ProposalItem();
                    }

                    $proposalItem->proposal_id = $proposal->id;
                    $proposalItem->item_name = $item;
                    $proposalItem->item_summary = $itemsSummary[$key];
                    $proposalItem->type = 'item';
                    $proposalItem->unit_id = (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null;
                    $proposalItem->product_id = (isset($productId[$key]) && !is_null($productId[$key])) ? $productId[$key] : null;
                    $proposalItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                    $proposalItem->quantity = $quantity[$key];
                    $proposalItem->unit_price = round($cost_per_item[$key], 2);
                    $proposalItem->amount = round($amount[$key], 2);
                    $proposalItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                    $proposalItem->field_order = $key + 1;
                    $proposalItem->save();


                    /* Invoice file save here */
                    // phpcs:ignore
                    if ((isset($proposal_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) || isset($proposal_item_image_url[$key])) {

                        $filename = '';
                        $proposalFileSize = null;

                        /* Delete previous uploaded file if it not a product (because product images cannot be deleted) */
                        if (!isset($proposal_item_image_url[$key]) && $proposalItem && $proposalItem->proposalItemImage) {
                            Files::deleteFile($proposalItem->proposalItemImage->hashname, ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');

                            $filename = Files::uploadLocalOrS3($proposal_item_image[$key], ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');
                            $proposalFileSize = $proposal_item_image[$key]->getSize();
                        }

                        if ($filename == '' && isset($proposal_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3($proposal_item_image[$key], ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/');
                            $proposalFileSize = $proposal_item_image[$key]->getSize();
                        }

                        ProposalItemImage::updateOrCreate(
                            [
                                'proposal_item_id' => $proposalItem->id,
                            ],
                            [
                                'filename' => isset($proposal_item_image[$key]) ? $proposal_item_image[$key]->getClientOriginalName() : '',
                                'hashname' => isset($proposal_item_image[$key]) ? $filename : '',
                                'size' => isset($proposal_item_image[$key]) ? $proposalFileSize : '',
                                'external_link' => isset($proposal_item_image[$key]) ? null : ($proposal_item_image_url[$key] ?? '')
                            ]
                        );
                    }
                }
            }
        }


    }

    public function deleted(Proposal $proposal)
    {
        if (user()) {
            self::createDealHistory($proposal->deal_id, 'proposal-deleted');
            self::createEmployeeActivity(user()->id, 'proposal-deleted');

        }


    }

    public function deleting(Proposal $proposal)
    {
        $notifyData = ['App\Notifications\NewProposal', 'App\Notifications\ProposalSigned'];

        Notification::deleteNotification($notifyData, $proposal->id);

    }

    public function duplicateImageStore($estimateOldImg, $proposalItem)
    {
        if (!is_null($estimateOldImg)) {

            $file = new ProposalItemImage();

            $file->proposal_item_id = $proposalItem->id;

            $fileName = Files::generateNewFileName($estimateOldImg->filename);

            Files::copy(ProposalTemplateItemImage::FILE_PATH . '/' . $estimateOldImg->id . '/' . $estimateOldImg->hashname, ProposalItemImage::FILE_PATH . '/' . $proposalItem->id . '/' . $fileName);

            $file->filename = $estimateOldImg->filename;
            $file->hashname = $fileName;
            $file->size = $estimateOldImg->size;
            $file->save();

        }
    }

}
