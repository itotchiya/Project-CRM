<?php

namespace App\Http\Controllers;

use App\DataTables\ContractTemplatesDataTable;
use App\Helper\Reply;
use App\Http\Requests\StoreContractTemplate;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;

class ContractTemplateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contractTemplate';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(ContractTemplatesDataTable $dataTable)
    {
        abort_403(user()->permission('manage_contract_template') == 'none');

        $this->contractTypes = ContractType::all();
        $this->contractCounts = Contract::count();

        return $dataTable->render('contract-template.index', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->contractId = request('id');
        $this->contract = null;

        if ($this->contractId != '') {
            $this->contract = ContractTemplate::findOrFail($this->contractId);
        }

        $this->clients = User::allClients();
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();

        $this->pageTitle = __('app.menu.addContractTemplate');
        $this->view = 'contract-template.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('contract-template.create', $this->data);
    }

    public function store(StoreContractTemplate $request)
    {
        $contract = new ContractTemplate();
        $contract->subject = $request->subject;
        $contract->amount = $request->amount;
        $contract->currency_id = $request->currency_id;
        $contract->contract_type_id = $request->contract_type;
        $contract->description = trim_editor($request->description);
        $contract->contract_detail = trim_editor($request->description);
        $contract->added_by = user()->id;
        $contract->save();

        return Reply::redirect(route('contract-template.index'), __('messages.recordSaved'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->contract = ContractTemplate::findOrFail($id);
        $this->manageContractTemplatePermission = user()->permission('manage_contract_template');
        abort_403(!in_array($this->manageContractTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.menu.contractTemplate');
        $this->view = 'contract-template.ajax.overview';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('contract-template.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $this->contract = ContractTemplate::findOrFail($id);
        $this->manageContractTemplatePermission = user()->permission('manage_contract_template');
        abort_403(!in_array($this->manageContractTemplatePermission, ['all', 'added']));

        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();

        $this->pageTitle = __('app.update') . ' ' . __('app.menu.contractTemplate');

        $this->view = 'contract-template.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('contract-template.create', $this->data);
    }

    public function update(StoreContractTemplate $request, $id)
    {
        $contract = ContractTemplate::findOrFail($id);
        $contract->subject = $request->subject;
        $contract->amount = $request->amount;
        $contract->currency_id = $request->currency_id;
        $contract->contract_type_id = $request->contract_type;
        $contract->description = trim_editor($request->description);
        $contract->contract_detail = trim_editor($request->description);

        $contract->save();

        return Reply::redirect(route('contract-template.index'), __('messages.updateSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('manage_contract_template') != 'all' && user()->permission('manage_contract_template') != 'added');

        ContractTemplate::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contract = ContractTemplate::findOrFail($id);

        $contract->destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}
