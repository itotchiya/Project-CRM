<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Passport;
use Illuminate\Http\Request;
use App\Http\Requests\StorePassportRequest;
use App\Http\Requests\UpdatePassportRequest;

class PassportController extends Controller
{

    public function create()
    {
        $this->countries = countries();
        return view('employees.ajax.create-passport-modal', $this->data);
    }

    public function store(StorePassportRequest $request)
    {
        $userId = request()->emp_id;
        $passport = new Passport();
        $passport->passport_number = $request->passport_number;
        $passport->user_id = $userId;
        $passport->company_id = company()->id;
        $passport->issue_date = companyToYmd($request->issue_date);
        $passport->expiry_date = companyToYmd($request->expiry_date);
        $passport->added_by = user()->id;
        $passport->country_id = $request->nationality;

        if ($request->hasFile('file')) {
            $passport->file = Files::uploadLocalOrS3($request->file, Passport::FILE_PATH);
        }

        $passport->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        $this->countries = countries();
        $this->passport = Passport::findOrFail($id);
        return view('employees.ajax.edit-passport-modal', $this->data);
    }

    public function update(UpdatePassportRequest $request, $id)
    {
        $passport = Passport::findOrFail($id);
        $passport->passport_number = $request->passport_number;
        $passport->issue_date = companyToYmd($request->issue_date);
        $passport->expiry_date = companyToYmd($request->expiry_date);
        $passport->country_id = $request->nationality;

        if($request->file_delete == 'yes')
        {
            Files::deleteFile($passport->file, Passport::FILE_PATH);
            $passport->file = null;
        }

        if ($request->hasFile('file')) {
            Files::deleteFile($passport->file, Passport::FILE_PATH);
            $passport->file = Files::uploadLocalOrS3($request->file, Passport::FILE_PATH);
        }

        $passport->save();

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
        $passport = Passport::findOrFail($id);

        Files::deleteFile($passport->file, Passport::FILE_PATH);

        $passport->destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}
