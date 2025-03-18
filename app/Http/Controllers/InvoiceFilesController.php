<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Traits\IconTrait;
use Illuminate\Http\Request;
use App\Models\InvoiceFiles;

class InvoiceFilesController extends AccountBaseController
{
    use IconTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-people';
        $this->pageTitle = 'app.menu.invoice';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {

            $defaultImage = null;

            foreach ($request->file as $fileData) {
                $file = new InvoiceFiles();
                $file->invoice_id = $request->invoice_id;

                $filename = Files::uploadLocalOrS3($fileData, InvoiceFiles::FILE_PATH);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->save();
            }

        }

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $file = InvoiceFiles::findOrFail($id);
        $this->deletePermission = user()->permission('delete_invoices');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $file->added_by == user()->id)));

        Files::deleteFile($file->hashname, 'invoices/' . $file->invoice_id);

        InvoiceFiles::destroy($id);

        $this->files = InvoiceFiles::where('invoice_id', $file->invoice_id)->orderByDesc('id')->get();
        $view = view('invoices.files.show', $this->data)->render();

        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }

    public function download($id)
    {
        $file = InvoiceFiles::whereRaw('md5(id) = ?', $id)->firstOrFail();

        $this->viewPermission = user()->permission('view_invoices');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $file->added_by == user()->id)));

        return download_local_s3($file, 'invoices/' . $file->hashname);
    }

}
