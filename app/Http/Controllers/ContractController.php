<?php

namespace App\Http\Controllers;

use App\DataTables\ContractsDataTable;
use App\Events\ContractSignedEvent;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Contract\StoreRequest;
use App\Http\Requests\Admin\Contract\UpdateRequest;
use App\Http\Requests\ClientContracts\SignRequest;
use App\Models\BaseModel;
use App\Models\Contract;
use App\Models\ContractSign;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContractController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contracts';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('contracts', $this->user->modules));

            return $next($request);
        });
    }

    public function index(ContractsDataTable $dataTable)
    {
        abort_403(user()->permission('view_contract') == 'none');

        if (!request()->ajax()) {
            $this->projects = Project::allProjects();

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
            }

            $this->contract = Contract::all();
            $this->contractTypes = ContractType::all();
            $this->contractCounts = Contract::count();
            $this->expiredCounts = Contract::where(DB::raw('DATE(`end_date`)'), '<', now()->format('Y-m-d'))->count();
            $this->aboutToExpireCounts = Contract::where(DB::raw('DATE(`end_date`)'), '>', now()->format('Y-m-d'))
                ->where(DB::raw('DATE(`end_date`)'), '<', now()->timezone($this->company->timezone)->addDays(7)->format('Y-m-d'))
                ->count();
        }

        return $dataTable->render('contracts.index', $this->data);
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
        abort_403(user()->permission('delete_contract') !== 'all');

        Contract::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;

    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $this->deletePermission = user()->permission('delete_contract');

        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && user()->id == $contract->added_by)
            || ($this->deletePermission == 'owned' && user()->id == $contract->client_id)
            || ($this->deletePermission == 'both' && (user()->id == $contract->client_id || user()->id == $contract->added_by)
            )));

        Contract::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));

    }

    public function create()
    {
        $this->addPermission = user()->permission('add_contract');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->contractId = request('id');
        $this->contract = null;

        if ($this->contractId != '') {
            $this->contractTemplate = Contract::findOrFail($this->contractId);
        }

        $this->templates = ContractTemplate::all();
        $this->clients = User::allClients(null, overRidePermission:($this->addPermission == 'all' ? 'all' : null));
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();
        $this->projects = Project::all();


        $this->lastContract = Contract::lastContractNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastContract) < $this->invoiceSetting->contract_digit) {
            $condition = $this->invoiceSetting->contract_digit - strlen($this->lastContract);

            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }


        if (is_null($this->contractId)) {
            $this->contractTemplate = request('template') ? ContractTemplate::findOrFail(request('template')) : null;
        }

        $contract = new Contract();
        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = __('app.menu.addContract');

        $this->view = 'contracts.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('contracts.create', $this->data);

    }

    public function store(StoreRequest $request)
    {
        $contract = new Contract();
        $this->storeUpdate($request, $contract);

        return Reply::redirect(route('contracts.index'), __('messages.recordSaved'));
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_contract');
        $this->contract = Contract::with('signature', 'renewHistory', 'renewHistory.renewedBy')
            ->findOrFail($id)
            ->withCustomFields();

        $this->projects = Project::all();


        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && user()->id == $this->contract->added_by)
            || ($this->editPermission == 'owned' && user()->id == $this->contract->client_id)
            || ($this->editPermission == 'both' && (user()->id == $this->contract->client_id || user()->id == $this->contract->added_by)
            )));

        $this->clients = User::allClients(null, overRidePermission:($this->editPermission == 'all' ? 'all' : null));
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();
        $this->pageTitle = $this->contract->contract_number;

        $contract = new Contract();

        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'contracts.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('contracts.create', $this->data);

    }

    public function update(UpdateRequest $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $this->storeUpdate($request, $contract);

        return Reply::redirect(route('contracts.index'), __('messages.updateSuccess'));
    }

    private function storeUpdate($request, $contract)
    {
        $contract->client_id = $request->client_id;
        $contract->project_id = $request->project_id;
        $contract->subject = $request->subject;
        $contract->amount = $request->amount;
        $contract->currency_id = $request->currency_id;
        $contract->original_amount = $request->amount;
        $contract->contract_name = $request->contract_name;
        $contract->alternate_address = $request->alternate_address;
        $contract->contract_note = $request->note;
        $contract->cell = $request->cell;
        $contract->office = $request->office;
        $contract->city = $request->city;
        $contract->state = $request->state;
        $contract->country = $request->country;
        $contract->postal_code = $request->postal_code;
        $contract->contract_type_id = $request->contract_type;
        $contract->contract_number = $request->contract_number;
        $contract->start_date = companyToYmd($request->start_date);
        $contract->original_start_date = companyToYmd($request->start_date);
        $contract->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $contract->original_end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $contract->description = trim_editor($request->description);
        $contract->contract_detail = trim_editor($request->description);
        $contract->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $contract->updateCustomFieldData($request->custom_fields_data);
        }

        return $contract;
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|mixed|void
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_contract');
        $this->addContractPermission = user()->permission('add_contract');
        $this->editContractPermission = user()->permission('edit_contract');
        $this->deleteContractPermission = user()->permission('delete_contract');
        $this->viewDiscussionPermission = $viewDiscussionPermission = user()->permission('view_contract_discussion');
        $this->viewContractFilesPermission = $viewContractFilesPermission = user()->permission('view_contract_files');

        $this->contract = Contract::with(['signature', 'client', 'client.clientDetails', 'files' => function ($q) use ($viewContractFilesPermission) {
            if ($viewContractFilesPermission == 'added') {
                $q->where('added_by', user()->id);
            }
        }, 'renewHistory', 'renewHistory.renewedBy',
            'discussion' => function ($q) use ($viewDiscussionPermission) {
                if ($viewDiscussionPermission == 'added') {
                    $q->where('contract_discussions.added_by', user()->id);
                }
            }, 'discussion.user'])->findOrFail($id)->withCustomFields();
        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && user()->id == $this->contract->added_by)
            || ($viewPermission == 'owned' && user()->id == $this->contract->client_id)
            || ($viewPermission == 'both' && (user()->id == $this->contract->client_id || user()->id == $this->contract->added_by))
        ));

        $contract = new contract();

        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->contract->contract_number;

        $tab = request('tab');

        $this->view = match ($tab) {
            'discussion' => 'contracts.ajax.discussion',
            'files' => 'contracts.ajax.files',
            'renew' => 'contracts.ajax.renew',
            default => 'contracts.ajax.summary',
        };


        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('contracts.show', $this->data);

    }

    public function download($id)
    {
        $this->contract = Contract::findOrFail($id);
        $viewPermission = user()->permission('view_contract');
        $this->contract = Contract::with('signature', 'client', 'client.clientDetails', 'files')->findOrFail($id)->withCustomFields();

        $getCustomFieldGroupsWithFields = $this->contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && user()->id == $this->contract->added_by)
            || ($viewPermission == 'owned' && user()->id == $this->contract->client_id)
            || ($viewPermission == 'both' && (user()->id == $this->contract->client_id || user()->id == $this->contract->added_by))
        ));


        $pdf = app('dompdf.wrapper');

        $this->company = $this->settings = company();

        $this->invoiceSetting = invoice_setting();

        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        // $pdf->loadView('contracts.contract-pdf', $this->data);
        $customCss = '<style>
        * { text-transform: none !important; }
        </style>';

        $pdf->loadHTML($customCss . view('contracts.contract-pdf', $this->data)->render());
        $filename = 'contract-' . $this->contract->id;

        return $pdf->download($filename . '.pdf');

    }

    public function downloadView($id)
    {
        $this->contract = Contract::findOrFail($id)->withCustomFields();
        $pdf = app('dompdf.wrapper');

        $this->company = $this->settings = Company::findOrFail($this->contract->company_id);

        $this->invoiceSetting = invoice_setting();

        $getCustomFieldGroupsWithFields = $this->contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $pdf->loadView('contracts.contract-pdf', $this->data);

        $filename = 'contract-' . $this->contract->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function sign(SignRequest $request, $id)
    {
        $this->contract = Contract::with('signature')->findOrFail($id);

        if ($this->contract && $this->contract->signature) {
            return Reply::error(__('messages.alreadySigned'));
        }

        $sign = new ContractSign();
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->contract->id;
        $sign->email = $request->email;
        $sign->date = now();
        $sign->place = $request->place;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';
            Files::createDirectoryIfNotExist('contract/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/contract/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'contract/sign', $this->contract->company_id);
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'contract/sign', 300);
            }
        }

        $sign->signature = $imageName;
        $sign->save();

        event(new ContractSignedEvent($this->contract, $sign));

        return Reply::redirect(route('contracts.show', $this->contract->id));
    }

    public function companySign(Request $request)
    {
        $contract = Contract::find($request->id);
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('contract/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/contract/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'contract/sign', $contract->company_id);
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'contract/sign', 300);
            }
        }

        $contract->company_sign = $imageName;
        $contract->sign_date = now();
        $contract->sign_by = user()->id;
        $contract->update();

        return Reply::successWithData(__('messages.signatureAdded'), ['status' => 'success']);


    }

    public function companiesSign(Request $request, $id)
    {
        $this->contract = Contract::find($id);

        return view('contracts.companysign.sign', $this->data);
    }

    public function projectDetail($id)
    {
        if ($id != 0) {
            $projects = Project::where('client_id', $id)->get();

        }
        else {
            $projects = Project::all();
        }

        $options = BaseModel::options($projects, null, 'project_name');

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function companySig($id)
    {
        $this->contract = Contract::find($id);

        return view('contracts.companysign.sign', $this->data);
    }

}
