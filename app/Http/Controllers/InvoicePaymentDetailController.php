<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Http\Requests\InvoicePaymentRequest;
use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Models\InvoicePaymentDetail;

class InvoicePaymentDetailController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.financeSettings';
        $this->activeSettingMenu = 'invoice_settings';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('invoice-settings.ajax.payment-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoicePaymentRequest $request)
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $payment = new InvoicePaymentDetail();
        $payment->title = $request->title;
        $payment->payment_details = $request->payment_details;
        $payment->company_id = $this->company->id;
        if ($request->hasFile('image')) {
            $payment->image = Files::uploadLocalOrS3($request->image, 'offline-method', 300);
        }
        $payment->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->payment = InvoicePaymentDetail::findOrFail($id);
        return view('invoice-settings.ajax.payment-edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoicePaymentRequest $request, string $id)
    {
        $payment = InvoicePaymentDetail::findOrFail($id);
        $payment->title = $request->title;
        $payment->payment_details = $request->payment_details;

        if ($request->image_delete == 'yes') {
            Files::deleteFile($request->image, 'offline-method');
            $payment->image = null;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($payment->image, 'offline-method');
            $payment->image = Files::uploadLocalOrS3($request->image, 'offline-method', 300);
        }

        $payment->save();

        return Reply::success(__('messages.updateSuccess'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->deletePermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        $payment = InvoicePaymentDetail::findOrFail($id);

        $payment->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
