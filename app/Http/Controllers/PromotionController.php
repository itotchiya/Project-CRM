<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use App\Models\Promotion;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\Employee\StorePromotionRequest;

class PromotionController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.incrementPromotion.incrementPromotions';

        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_increment_promotion') != 'all');
            return $next($request);
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->pageTitle = __('modules.incrementPromotion.addPromotion');

        $this->userId = request()->user_id ? request()->user_id : null;
        $this->promotion = Promotion::where('employee_id', $this->userId)->latest()->first();
        $this->employeeDetail = EmployeeDetails::select('id', 'user_id', 'department_id', 'designation_id')->where('user_id', $this->userId)->first();

        $this->currentDesignation = $this->employeeDetail->designation;
        $this->currentDepartment = $this->employeeDetail->department;

        // Override with promotion details if available
        if ($this->promotion) {
            $this->currentDesignation = $this->promotion->currentDesignation ?? $this->currentDesignation;
            $this->currentDepartment = $this->promotion->currentDepartment ?? $this->currentDepartment;
        }

        $this->designations = Designation::allDesignations();
        $this->departments = Team::allDepartments();

        return view('employees.ajax.add-promotion', $this->data)->render();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromotionRequest $request)
    {
        DB::beginTransaction();

        $data = [
            'employee_id' => $request->user_id,
            'date' => $request->date ? companyToYmd($request->date) : Carbon::now()->format('Y-m-d'),
            'previous_designation_id' => $request->previous_designation_id,
            'current_designation_id' => $request->current_designation_id,
            'previous_department_id' => $request->previous_department_id,
            'current_department_id' => $request->current_department_id,
            'send_notification' => $request->send_notification == 'yes' ? $request->send_notification : 'no',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        Promotion::create($data);

        DB::commit();

        return Reply::success(__('messages.promotionAddedSuccess'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->pageTitle = __('modules.incrementPromotion.editPromotion');

        $this->promotion = Promotion::findOrFail($id);
        $this->userId = $this->promotion->employee_id ?? null;

        $this->designations = Designation::allDesignations();
        $this->departments = Team::allDepartments();

        return view('employees.ajax.edit-promotion', $this->data)->render();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePromotionRequest $request, string $id)
    {
        DB::beginTransaction();

        $promotion = Promotion::findOrFail($id);

        $promotion->update([
            'date' => $request->date ? companyToYmd($request->date) : Carbon::now()->format('Y-m-d'),
            'current_designation_id' => $request->current_designation_id,
            'current_department_id' => $request->current_department_id,
            'send_notification' => $request->send_notification == 'yes' ? $request->send_notification : 'no'
        ]);

        DB::commit();

        return Reply::success(__('messages.promotionUpdatedSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

}
