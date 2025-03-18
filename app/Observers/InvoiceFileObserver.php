<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\InvoiceFiles;

class InvoiceFileObserver
{

    public function saving(InvoiceFiles $invoiceFiles)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $invoiceFiles->last_updated_by = user()->id;
        }
    }

    public function creating(InvoiceFiles $invoiceFiles)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $invoiceFiles->added_by = user()->id;
            $invoiceFiles->created_at = now()->format('Y-m-d H:i:s');
        }
    }

    public function deleting(InvoiceFiles $invoiceFiles)
    {
        $invoiceFiles->load('invoice');

        if (!isRunningInConsoleOrSeeding()) {
            if (isset($invoiceFiles->invoice) && $invoiceFiles->invoice->default_image == $invoiceFiles->hashname) {
                $invoiceFiles->invoice->default_image = null;
                $invoiceFiles->invoice->save();
            }
        }

        Files::deleteFile($invoiceFiles->hashname, InvoiceFiles::FILE_PATH);
    }

}
