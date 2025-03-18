<?php

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\File;

class BaseDataTable extends DataTable
{

    protected $company;
    public $user;
    public $domHtml;

    public function __construct()
    {
        $this->company = company();
        $this->user = user();
        $this->domHtml = "<'row'<'col-sm-12'tr>><'d-flex'<'flex-grow-1'l><i><p>>";
    }

    public function setBuilder($table, $orderBy = 1)
    {
        $intl = File::exists(public_path('i18n/' . user()->locale . '.json')) ? asset('i18n/' . user()->locale . '.json') : __('app.datatable');

        return parent::builder()
            ->setTableId($table)
            ->columns($this->getColumns()) /** @phpstan-ignore-line */
            ->minifiedAjax()
            ->orderBy($orderBy)
            ->destroy(true)
            ->responsive()
            ->serverSide()
            ->stateSave(true)
            ->pageLength(companyOrGlobalSetting()->datatable_row_limit ?? 10)
            ->processing()
            ->dom($this->domHtml)
            ->language($intl);
    }

    protected function filename(): string
    {
        // Remove DataTable from name
        $filename = str()->snake(class_basename($this), '-');

        return str_replace('data-table', '', $filename)  . now()->format('Y-m-d-H-i-s');
    }

    public function checkBox($row, $hideCheckbox = false): string
    {
        if ($hideCheckbox) {
            return '';
        }

        return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';

    }

}
