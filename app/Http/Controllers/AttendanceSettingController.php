<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Carbon;

use App\DataTables\ShiftRotationDataTable;
use Carbon\Carbon;

use App\Models\Role;
use App\Helper\Reply;

use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Models\EmployeeShift;
// use Endroid\QrCode\QrCode;
use App\Models\AttendanceSetting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\EmployeeShiftSchedule;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\ErrorCorrectionLevel;
use App\Http\Requests\AttendanceSetting\UpdateAttendanceSetting;

class AttendanceSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.attendanceSettings';
        $this->activeSettingMenu = 'attendance_settings';
        $this->middleware(function ($request, $next) {

            abort_403(!(user()->permission('manage_attendance_setting') == 'all' && in_array('attendance', user_modules())));

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->ipAddresses = [];
        $this->attendanceSetting = AttendanceSetting::first();
        $this->monthlyReportRoles = json_decode($this->attendanceSetting->monthly_report_roles);
        $this->roles = Role::where('name', '<>', 'client')->get();

        if (json_decode($this->attendanceSetting->ip_address)) {
            $this->ipAddresses = json_decode($this->attendanceSetting->ip_address, true);
        }

        $tab = request('tab');
        switch ($tab) {
        case 'shift':
            $this->weekMap = Holiday::weekMap();
            $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();
            $this->view = 'attendance-settings.ajax.shift';
            break;

        case 'qrcode':


            $this->qr = Builder::create()
                ->writer(new PngWriter())
                ->encoding(new Encoding('UTF-8'))
                ->data((route('settings.qr-login', ['hash' => company()->hash])))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(300)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->validateResult(false)
                ->build();



            $this->view = 'attendance-settings.ajax.qrcode';
        break;
        case 'shift-rotation':
            return $this->shiftRotation();
            break;
        default:
            $this->view = 'attendance-settings.ajax.attendance';
            break;
        }

        $this->activeTab = $tab ?: 'attendance';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('attendance-settings.index', $this->data);
    }

    public function shiftRotation()
    {
        $this->pageTitle = 'app.menu.shiftRotation';
        $this->activeTab = request('tab') ?: 'overview';
        $this->view = 'attendance-settings.ajax.shift-rotation';
        $dataTable = new ShiftRotationDataTable(true);

        return $dataTable->render('attendance-settings.index', $this->data);
    }

    /**
     * @param UpdateAttendanceSetting $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    //phpcs:ignore
    public function update(UpdateAttendanceSetting $request, $id)
    {
        $setting = company()->attendanceSetting;

        $attendanceSetting = AttendanceSetting::find(company()->id);

        if (request()->auto_clock_in == 'yes' && $attendanceSetting->qr_enable == 1) {
            return Reply::error(__('messages.fristSignAndQrCodeError'));
        }

        $setting->employee_clock_in_out = ($request->employee_clock_in_out == 'yes') ? 'yes' : 'no';
        $setting->radius_check = ($request->radius_check == 'yes') ? 'yes' : 'no';
        $setting->ip_check = ($request->ip_check == 'yes') ? 'yes' : 'no';
        $setting->radius = $request->radius;
        $setting->ip_address = json_encode($request->ip);
        $setting->alert_after = $request->alert_after;
        $setting->week_start_from = $request->week_start_from;
        $setting->alert_after_status = ($request->alert_after_status == 'on') ? 1 : 0;
        $setting->save_current_location = ($request->save_current_location) ? 1 : 0;
        $setting->allow_shift_change = ($request->allow_shift_change) ? 1 : 0;
        $setting->auto_clock_in = ($request->auto_clock_in) ? 'yes' : 'no';
        $setting->show_clock_in_button = ($request->show_clock_in_button == 'yes') ? 'yes' : 'no';
        $setting->auto_clock_in_location = $request->auto_clock_in_location;
        $setting->monthly_report = ($request->monthly_report) ? 1 : 0;
        $setting->monthly_report_roles = json_encode($request->monthly_report_roles);
        $setting->save();

        session()->forget(['attendance_setting','company']);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function attendanceShift($defaultAttendanceSettings)
    {
        $checkPreviousDayShift = EmployeeShiftSchedule::with('shift')->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->subDay()->toDateString())
            ->first();

        $checkTodayShift = EmployeeShiftSchedule::with('shift')->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->toDateString())
            ->first();

        $backDayFromDefault = Carbon::parse(now(company()->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time);

        $backDayToDefault = Carbon::parse(now(company()->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time);

        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', now(company()->timezone)->toDateTimeString(), 'UTC');

        if ($checkPreviousDayShift && $nowTime->betweenIncluded($checkPreviousDayShift->shift_start_time, $checkPreviousDayShift->shift_end_time)) {
            $attendanceSettings = $checkPreviousDayShift;

        }
        else if ($nowTime->betweenIncluded($backDayFromDefault, $backDayToDefault)) {
            $attendanceSettings = $defaultAttendanceSettings;

        }
        else if ($checkTodayShift &&
            ($nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time)
                || $nowTime->gt($checkTodayShift->shift_end_time)
                || (!$nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) && $defaultAttendanceSettings->show_clock_in_button == 'no'))
        ) {
            $attendanceSettings = $checkTodayShift;
        }
        else if ($checkTodayShift && !is_null($checkTodayShift->shift->early_clock_in)) {
            $attendanceSettings = $checkTodayShift;
        }
        else {
            $attendanceSettings = $defaultAttendanceSettings;
        }

        return $attendanceSettings->shift;

    }

}
