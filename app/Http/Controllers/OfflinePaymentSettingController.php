<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\OfflinePaymentSetting\StoreRequest;
use App\Http\Requests\OfflinePaymentSetting\UpdateRequest;
use App\Models\OfflinePaymentMethod;

class OfflinePaymentSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.paymentGatewayCredential';
        $this->activeSettingMenu = 'payment_gateway_settings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->offlineMethods = OfflinePaymentMethod::all();
        return view('payment-gateway-credentials.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('payment-gateway-settings.create-offline-payment-modal', $this->data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreRequest $request)
    {
        $method = new OfflinePaymentMethod();
        $method->name = $request->name;
        $method->description = trim_editor($request->description);

        if ($request->hasFile('image')) {
            $method->image = Files::uploadLocalOrS3($request->image, 'offline-method', 300);
        }

        $method->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->method = OfflinePaymentMethod::findOrFail($id);

        return view('payment-gateway-settings.edit-offline-payment-modal', $this->data);
    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array|string[]
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateRequest $request, $id)
    {
        $method = OfflinePaymentMethod::findOrFail($id);
        $method->name = $request->name;
        $method->description = trim_editor($request->description);
        $method->status = $request->status;

        if ($request->image_delete == 'yes') {
            Files::deleteFile($request->image, 'offline-method');
            $method->image = null;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($method->image, 'offline-method');
            $method->image = Files::uploadLocalOrS3($request->image, 'offline-method', 300);
        }

        $method->save();

        return Reply::redirect(route('offline-payment-setting.index'), __('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        OfflinePaymentMethod::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

}
