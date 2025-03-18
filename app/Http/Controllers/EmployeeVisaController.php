<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\VisaDetail;
use Doctrine\DBAL\Schema\View;
use App\Http\Requests\StoreVisaRequest;
use App\Http\Requests\UpdateVisaRequest;
use Carbon\Carbon;

class EmployeeVisaController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.employees.visaDetails');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {
        return redirect()->route('employees.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->countries = countries();
        return view('employees.ajax.create-visa-modal', $this->data);
    }

    public function store(StoreVisaRequest $request)
    {
        $visa = new VisaDetail();
        $userId = request()->emp_id;
        $visa->visa_number = $request->visa_number;
        $visa->user_id = $userId;
        $visa->company_id = company()->id;
        $visa->issue_date = Carbon::createFromFormat($this->company->date_format, $request->issue_date);
        $visa->expiry_date = Carbon::createFromFormat($this->company->date_format, $request->expiry_date);
        $visa->added_by = user()->id;
        $visa->country_id = $request->country;

        if($request->has('file')) {
            $visa->file = Files::uploadLocalOrS3($request->file, VisaDetail::FILE_PATH);
        }

        $visa->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        $this->visa = VisaDetail::findOrFail($id);
        $this->view = 'employees.ajax.visa';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('employees.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->countries = countries();
        $this->visa = VisaDetail::findOrFail($id);
        return view('employees.ajax.edit-visa-modal', $this->data);
    }

    public function update(UpdateVisaRequest $request, $id)
    {
        $visa = VisaDetail::findOrFail($id);
        $visa->visa_number = $request->visa_number;
        $visa->issue_date = Carbon::createFromFormat($this->company->date_format, $request->issue_date);
        $visa->expiry_date = Carbon::createFromFormat($this->company->date_format, $request->expiry_date);
        $visa->country_id = $request->country;

        if($request->file_delete == 'yes')
        {
            Files::deleteFile($visa->image, VisaDetail::FILE_PATH);
            $visa->file = null;
        }

        if($request->has('file')) {
            Files::deleteFile($visa->image, VisaDetail::FILE_PATH);
            $visa->file = Files::uploadLocalOrS3($request->file, VisaDetail::FILE_PATH);
        }

        $visa->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $visa = VisaDetail::findOrFail($id);

        Files::deleteFile($visa->file, VisaDetail::FILE_PATH);

        $visa->destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}
