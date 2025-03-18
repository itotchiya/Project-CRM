<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\ShiftRotation;
use App\Models\EmployeeShift;
use App\Models\AutomateShift;
use Illuminate\Support\Facades\DB;
use App\Models\ShiftRotationSequence;
use App\Http\Requests\EmployeeShift\StoreAutomateShift;
use App\Http\Requests\EmployeeShift\StoreShiftRotationRequest;
use Illuminate\Support\Facades\Artisan;


class ShiftRotationController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.shiftRotation';
        $this->activeSettingMenu = 'attendance_settings';

        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_attendance_setting') == 'all' && in_array('attendance', user_modules())));
            return $next($request);
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->pageTitle = __('app.menu.shiftRotation');
        $this->view = 'attendance-settings.ajax.create';
        $this->dates = range(1, 30);

        $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('attendance-settings.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShiftRotationRequest $request)
    {
        if (!$request->has('shifts')) {
            return Reply::error(__('messages.addShift'));
        }

        DB::beginTransaction();

        $shiftRotation = ShiftRotation::create([
            'company_id' => company()->id,
            'rotation_name' => $request->rotation_name,
            'rotation_frequency' => $request->rotation_frequency,
            'color_code' => $request->color_code,
            'override_shift' => $request->override_shift ?? 'no',
            'send_mail' => $request->send_mail ?? 'no',
            'schedule_on' => $request->rotation_frequency != 'monthly' ? $request->schedule_on : null,
            'rotation_date' => $request->rotation_frequency == 'monthly' ? $request->rotation_date : null,
        ]);
        if ($request->has('shifts')) {
            foreach ($request->shifts as $key => $shift) {
                ShiftRotationSequence::create([
                    'employee_shift_rotation_id' => $shiftRotation->id,
                    'employee_shift_id' => $shift,
                    'sequence' => $request->sort_order[$key] ?? null,
                ]);
            }
        }

        DB::commit();
        return Reply::success(__('messages.recordSaved'));
    }

    public function changeStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $rotation = ShiftRotation::find($id);

        if (!is_null($rotation)) {
            $rotation->status = $status;
            $rotation->save();
        }

        return Reply::success(__('messages.rotationStatusChanged'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->pageTitle = __('app.menu.shiftRotation');
        $this->view = 'attendance-settings.ajax.edit';
        $this->dates = range(1, 30);
        DB::enableQueryLog();

        $this->shiftRotation = ShiftRotation::with([
            'sequences' => function ($q) {
                $q->with('rotation', 'shift');
            }
        ])->findOrFail($id);

        $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('attendance-settings.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreShiftRotationRequest $request, $id)
    {
        if (!$request->has('shifts')) {
            return Reply::error(__('messages.addShift'));
        }

        DB::beginTransaction();

        $shiftRotation = ShiftRotation::findOrFail($id);
        $shiftRotation->update([
            'rotation_name' => $request->rotation_name,
            'rotation_frequency' => $request->rotation_frequency,
            'color_code' => $request->color_code,
            'override_shift' => $request->override_shift ?? 'no',
            'send_mail' => $request->send_mail ?? 'no',
            'schedule_on' => $request->rotation_frequency != 'monthly' ? $request->schedule_on : null,
            'rotation_date' => $request->rotation_frequency == 'monthly' ? $request->rotation_date : null,
        ]);
        ShiftRotationSequence::where('employee_shift_rotation_id', $shiftRotation->id)->delete();

        foreach ($request->shifts as $key => $shift) {
            ShiftRotationSequence::create([
                'employee_shift_rotation_id' => $shiftRotation->id,
                'employee_shift_id' => $shift,
                'sequence' => $request->sort_order[$key] ?? null,
            ]);
        }

        DB::commit();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rotation = ShiftRotation::findOrFail($id);

        if (!$rotation) {
            return Reply::error(__('messages.rotationNotFound'));
        }

        // Delete rotation and associated sequences
        $rotation->sequences()->delete();
        $rotation->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function manageEmployees($id)
    {
        $this->rotaionId = $id;
        $this->rotations = ShiftRotation::active()->get();
        $rotationEmployees = AutomateShift::where('employee_shift_rotation_id', $id)->pluck('user_id');
        $this->employees = User::whereIn('id', $rotationEmployees)->lazy();

        return view('attendance-settings.ajax.manage-employees', $this->data);
    }

    public function removeEmployee(Request $request)
    {
        AutomateShift::where('user_id', $request->empId)->where('employee_shift_rotation_id', $request->rotationId)->delete();

        return Reply::success(__('messages.employeeRemoveSuccess'));
    }

    public function changeEmployeeRotation(Request $request)
    {
        $shift = AutomateShift::where('user_id', $request->empId)->first();

        if ($shift) {
            $shift->employee_shift_rotation_id = $request->newRotationId;
            $shift->save();
        }

        return Reply::success(__('messages.employeeRotationChanged'));
    }

    public function automateShift()
    {
        $this->pageTitle = __('modules.attendance.automateShifts');

        $otherEmployees = AutomateShift::pluck('user_id')->unique();

        $this->employees = User::allEmployees(null, true, 'all')->whereNotIn('id', $otherEmployees);
        $this->departments = Team::all();
        $this->shiftRotation = ShiftRotation::active()->get();

        $this->view = 'attendance-settings.ajax.automate-shifts';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('attendance-settings.create', $this->data);
    }

    public function storeAutomateShift(StoreAutomateShift $request)
    {
        if ($request->has('user_id') && $request->has('rotation')) {
            foreach ($request->user_id as $user) {
                AutomateShift::updateOrCreate([
                    'user_id' => $user,
                    'employee_shift_rotation_id' => $request->rotation,
                ]);
            }
        }

        return Reply::success(__('messages.automateShiftAdded'));
    }

    public function runRotation(Request $request)
    {
        if ($request->isMethod('post')) {
            $rotationIds = $request->input('rotation_ids');
            if (!$rotationIds) {
                return Reply::error(__('messages.noShiftRotation'));
            }

                Artisan::call('assign-employee-shift-rotation', [
                   '--rotation_ids' => $rotationIds
                ]);
            return Reply::success(__('messages.rotationRunSuccessfully'));
        }

        $this->rotations = ShiftRotation::active()->with('automateShifts')->get();

        foreach ($this->rotations as $rotation) {
            $rotation->employees_count = $rotation->automateShifts->count();
        }

        return view('attendance-settings.ajax.run-rotation', $this->data);
    }

}
