<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\EstimateTemplate;
use App\Models\EstimateTemplateItem;
use App\Models\EstimateTemplateItemImage;
use App\Models\Product;
use App\Models\ProductFiles;

class EstimateTemplateObserver
{

    public function creating(EstimateTemplate $estimate)
    {
        if (company()) {
            $estimate->company_id = company()->id;
        }
    }

    public function created(EstimateTemplate $estimate)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (!empty(request()->item_name)) {
                $itemsSummary = request()->item_summary;
                $cost_per_item = request()->cost_per_item;
                $hsn_sac_code = request()->hsn_sac_code;
                $quantity = request()->quantity;
                $unitId = request()->unit_id;
                $productId = request()->product_id;
                $amount = request()->amount;
                $tax = request()->taxes;
                $invoice_item_image = request()->invoice_item_image;
                $invoice_item_image_url = request()->invoice_item_image_url;

                foreach (request()->item_name as $key => $item) {
                    if (!is_null($item)) {
                        $estimateTemplateItem = EstimateTemplateItem::create(
                            [
                                'estimate_template_id' => $estimate->id,
                                'company_id' => $estimate->company_id,
                                'item_name' => $item,
                                'item_summary' => $itemsSummary[$key],
                                'type' => 'item',
                                'unit_id' => (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null,
                                'product_id' => (isset($productId[$key]) && !is_null($productId[$key])) ? $productId[$key] : null,
                                'hsn_sac_code' => (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                                'quantity' => $quantity[$key],
                                'unit_price' => round($cost_per_item[$key], 2),
                                'amount' => round($amount[$key], 2),
                                'taxes' => ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null)
                            ]
                        );
                    }

                    /* Invoice file save here */
                    if (isset($estimateTemplateItem) && (isset($invoice_item_image[$key]) || isset($invoice_item_image_url[$key]))) {

                        $estimateTemplateItemImage = new EstimateTemplateItemImage();
                        $estimateTemplateItemImage->estimate_template_item_id = $estimateTemplateItem->id;
                        $estimateTemplateItemImage->company_id = $estimateTemplateItem->company_id;

                        if (isset($invoice_item_image_url[$key])) {
                            $product = Product::findOrFail(request()->product_id[$key]);

                            $fileOrgName = ProductFiles::where('product_id', request()->product_id[$key])->where('hashname', $product->default_image)->first();

                            $fileName = Files::generateNewFileName($fileOrgName->filename);

                            Files::copy(Product::FILE_PATH . '/' . $fileOrgName->hashname, EstimateTemplateItemImage::FILE_PATH . '/' . $estimateTemplateItem->id . '/' . $fileName);

                            $estimateTemplateItemImage->filename = $fileOrgName->filename;
                            $estimateTemplateItemImage->hashname = $fileName;
                        }

                        if (isset($invoice_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3($invoice_item_image[$key], EstimateTemplateItemImage::FILE_PATH . '/' . $estimateTemplateItem->id . '/');
                            $estimateTemplateItemImage->filename = $invoice_item_image[$key]->getClientOriginalName();
                            $estimateTemplateItemImage->hashname = $filename;
                            $estimateTemplateItemImage->size = $invoice_item_image[$key]->getSize();
                        }

                        $estimateTemplateItemImage->external_link = isset($invoice_item_image[$key]) ? null : (isset($invoice_item_image_url[$key]) ? $invoice_item_image_url[$key] : null);
                        $estimateTemplateItemImage->save();
                    }

                }
            }

        }
    }

    /**
     * @throws RelatedResourceNotFoundException
     */
    public function updated(EstimateTemplate $estimate)
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
            $tax = $request->taxes;
            $quantity = $request->quantity;
            $cost_per_item = $request->cost_per_item;
            $amount = $request->amount;
            $estimate_item_image = $request->invoice_item_image;
            $estimate_item_image_url = $request->invoice_item_image_url;
            $item_ids = $request->item_ids;
            $unitId = request()->unit_id;
            $productId = request()->product_id;

            if (!empty($request->item_name) && is_array($request->item_name)) {
                // Step1 - Delete all invoice items which are not avaialable
                if (!empty($item_ids)) {
                    EstimateTemplateItem::whereNotIn('id', $item_ids)->where('estimate_template_id', $estimate->id)->delete();
                }

                // Step2&3 - Find old invoices items, update it and check if images are newer or older
                foreach ($items as $key => $item) {
                    $invoice_item_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                    $estimateTemplateItem = EstimateTemplateItem::find($invoice_item_id);

                    if ($estimateTemplateItem === null) {
                        $estimateTemplateItem = new EstimateTemplateItem();
                    }

                    $estimateTemplateItem->estimate_template_id = $estimate->id;
                    $estimateTemplateItem->company_id = $estimate->company_id;
                    $estimateTemplateItem->item_name = $item;
                    $estimateTemplateItem->item_summary = $itemsSummary[$key];
                    $estimateTemplateItem->type = 'item';
                    $estimateTemplateItem->unit_id = (isset($unitId[$key]) && !is_null($unitId[$key])) ? $unitId[$key] : null;
                    $estimateTemplateItem->product_id = (isset($productId[$key]) && !is_null($productId[$key])) ? $productId[$key] : null;
                    $estimateTemplateItem->hsn_sac_code = (isset($hsn_sac_code[$key]) && !is_null($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null;
                    $estimateTemplateItem->quantity = $quantity[$key];
                    $estimateTemplateItem->unit_price = round($cost_per_item[$key], 2);
                    $estimateTemplateItem->amount = round($amount[$key], 2);
                    $estimateTemplateItem->taxes = ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null);
                    $estimateTemplateItem->save();


                    /* Invoice file save here */
                    // phpcs:ignore
                    if ((isset($estimate_item_image[$key]) && $request->hasFile('invoice_item_image.' . $key)) || isset($estimate_item_image_url[$key])) {

                        $estimateTemplateItemImage = EstimateTemplateItemImage::where('estimate_template_item_id', $estimateTemplateItem->id)->firstOrNew();

                        if ($estimateTemplateItemImage) {
                            $estimateTemplateItemImage->estimate_template_item_id = $estimateTemplateItem->id;
                            $estimateTemplateItemImage->company_id = $estimateTemplateItem->company_id;
                        }

                        /* Delete previous uploaded file if it not a product (because product images cannot be deleted) */
                        if (!isset($estimate_item_image_url[$key]) && $estimateTemplateItem && $estimateTemplateItem->estimateTemplateItemImage) {
                            Files::deleteFile($estimateTemplateItem->estimateTemplateItemImage->hashname, EstimateTemplateItemImage::FILE_PATH . '/' . $estimateTemplateItem->id . '/');
                        }

                        if (isset($estimate_item_image[$key])) {
                            $filename = Files::uploadLocalOrS3($estimate_item_image[$key], EstimateTemplateItemImage::FILE_PATH . '/' . $estimateTemplateItem->id . '/');
                            $estimateTemplateItemImage->filename = $estimate_item_image[$key]->getClientOriginalName();
                            $estimateTemplateItemImage->hashname = $filename;
                            $estimateTemplateItemImage->size = $estimate_item_image[$key]->getSize();
                        }

                        $estimateTemplateItemImage->external_link = isset($estimate_item_image[$key]) ? null : ($estimate_item_image_url[$key] ?? null);
                        $estimateTemplateItemImage->save();

                    }
                }
            }
        }

    }

}
