<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\StoreBusinessAddress;
use App\Models\CompanyAddress;
use App\Models\EmployeeDetails;
use App\View\Components\Employee;

class BusinessAddressController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.businessAddresses';
        $this->activeSettingMenu = 'business_address';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_company_setting') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $this->companyAddresses = CompanyAddress::all();

        return view('company-address.index', $this->data);
    }

    public function create()
    {
        $this->countries = countries();

        return view('company-address.create', $this->data);
    }

    public function store(StoreBusinessAddress $request)
    {
        CompanyAddress::create([
            'country_id' => $request->country,
            'address' => $request->address,
            'location' => $request->location,
            'tax_number' => $request->tax_number,
            'tax_name' => $request->tax_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        $this->countries = countries();
        $this->companyAddress = CompanyAddress::findOrfail($id);

        return view('company-address.edit', $this->data);
    }

    public function update(StoreBusinessAddress $request, $id)
    {
        $companyAddress = CompanyAddress::findOrfail($id);
        $companyAddress->country_id = $request->country;
        $companyAddress->address = $request->address;
        $companyAddress->location = $request->location;
        $companyAddress->tax_number = $request->tax_number;
        $companyAddress->tax_name = $request->tax_name;
        $companyAddress->latitude = $request->latitude;
        $companyAddress->longitude = $request->longitude;
        $companyAddress->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function setDefaultAddress()
    {
        CompanyAddress::where('is_default', 1)->update(['is_default' => 0]);

        $companyAddress = CompanyAddress::findOrfail(request()->addressId);
        $companyAddress->is_default = 1;
        $companyAddress->save();

        session()->forget(['default_address', 'company']);

        return Reply::success(__('messages.recordSaved'));
    }

    public function destroy($id)
    {
        CompanyAddress::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}
