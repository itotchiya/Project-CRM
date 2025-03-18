<?php

namespace App\Http\Controllers;

use App\DataTables\SalesReportDataTable;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class SalesReportController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.salesReport';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(SalesReportDataTable $dataTable) /** @phpstan-ignore-line */
    {
        if (!request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);
        }

        $this->clients = User::allClients();

        return $dataTable->render('reports.sales.index', $this->data); /** @phpstan-ignore-line */
    }

}
