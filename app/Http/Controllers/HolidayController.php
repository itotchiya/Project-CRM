<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Holiday;
use App\Services\Google;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DataTables\HolidayDataTable;
use App\Http\Requests\CommonRequest;
use App\Models\GoogleCalendarModule;
use Illuminate\Support\Facades\View;
use App\Http\Requests\Holiday\CreateRequest;
use App\Http\Requests\Holiday\UpdateRequest;

class HolidayController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.holiday';
    }

    public function index(Request $request)
    {
        $this->viewPermission = user()->permission('view_holiday');
        abort_403(!in_array($this->viewPermission, ['all', 'added', 'owned', 'both']));


        if (request('start') && request('end')) {
            $holidayArray = array();

            $holidays = Holiday::orderBy('date', 'ASC');

            if (request()->searchText != '') {
                $holidays->where('holidays.occassion', 'like', '%' . request()->searchText . '%');
            }

            if (in_array($this->viewPermission, ['owned', 'both'])) {
                $user = user();
                $holidays->where(function ($query) use ($user) {
                    // Common conditions
                    $query->where(function ($q) use ($user) {
                        $q->orWhere('department_id_json', 'like', '%"' . $user->employeeDetails->department_id . '"%')
                            ->orWhereNull('department_id_json');
                    });
                    $query->where(function ($q) use ($user) {
                        $q->orWhere('designation_id_json', 'like', '%"' . $user->employeeDetails->designation_id . '"%')
                            ->orWhereNull('designation_id_json');
                    });
                    $query->where(function ($q) use ($user) {
                        $q->orWhere('employment_type_json', 'like', '%"' . $user->employeeDetails->employment_type . '"%')
                            ->orWhereNull('employment_type_json');
                    });

                    // Additional condition for 'both'
                    if ($this->viewPermission == 'both') {
                        $query->orWhere('added_by', $user->id);
                    }
                });
            }

            $holidays = $holidays->get();

            foreach ($holidays as $key => $holiday) {

                $holidayArray[] = [
                    'id' => $holiday->id,
                    'title' => $holiday->occassion,
                    'start' => $holiday->date->format('Y-m-d'),
                    'end' => $holiday->date->format('Y-m-d'),
                ];
            }

            return $holidayArray;
        }

        return view('holiday.calendar.index', $this->data);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|mixed|void
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_holiday');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->redirectUrl = request()->date ? route('holidays.index') : route('holidays.table_view');
        $this->date = request()->date ? Carbon::parse(request()->date)->timezone(company()->timezone)->translatedFormat(company()->date_format) : '';

        $this->pageTitle = __('app.menu.holiday');

        $this->view = 'holiday.ajax.create';

        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('holiday.create', $this->data);


    }

    /**
     *
     * @param CreateRequest $request
     * @return void
     */
    public function store(CreateRequest $request)
    {

        $this->addPermission = user()->permission('add_holiday');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $occassions = $request->occassion;
        $dates = $request->date;
        $notificationSent = $request->notification_sent;


        foreach ($dates as $index => $value) {
            if ($value != '') {

                $holiday = new Holiday();
                $holiday->date = Carbon::createFromFormat($this->company->date_format, $value);
                $holiday->occassion = $occassions[$index];
                $holiday->notification_sent = $notificationSent;

                if (!empty($request->department)) {
                    $holiday->department_id_json = json_encode($request->department);
                }

                if (!empty($request->designation)) {
                    $holiday->designation_id_json = json_encode($request->designation);
                }

                if (!empty($request->employment_type)) {
                    $holiday->employment_type_json = json_encode($request->employment_type);
                }

                $holiday->save();

                if ($holiday) {
                    $holiday->event_id = $this->googleCalendarEvent($holiday);
                    $holiday->save();
                }


            }
        }

        if (request()->has('type')) {
            return redirect(route('holidays.index'));
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('holidays.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);

    }

    /**
     * Display the specified holiday.
     */
    public function show(Holiday $holiday)
    {
        $this->holiday = $holiday;

        $departmentArray = $this->holiday?->department(json_decode($holiday->department_id_json));
        $this->department = $departmentArray ? implode(', ', $departmentArray) : '--';


        $designationArray = $this->holiday?->designation(json_decode($holiday->designation_id_json));
        $this->designation = $designationArray ? implode(', ', $designationArray) : '--';


        $this->employment_type = !empty($holiday->employment_type_json) ? collect(json_decode($holiday->employment_type_json))
            ->map(function ($employmentType) {
                return __('modules.employees.' . $employmentType);
            })
            ->implode(', ') : '--';


        $this->viewPermission = user()->permission('view_holiday');
        abort_403(!($this->viewPermission == 'all' || $this->viewPermission == 'owned' || $this->viewPermission == 'both' || ($this->viewPermission == 'added' && $this->holiday->added_by == user()->id)));

        $this->pageTitle = __('app.menu.holiday');

        $this->view = 'holiday.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('holiday.create', $this->data);

    }

    /**
     * @param Holiday $holiday
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|mixed|void
     */
    public function edit(Holiday $holiday)
    {
        $this->holiday = $holiday;
        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();


        $this->departmentId = $this->holiday->department_id_json;
        $this->departmentArray = $this->departmentId ? json_decode($this->departmentId, true) : [];
        if (!is_array($this->departmentArray)) {
            $this->departmentArray = [];
        }

        $this->designationId = $this->holiday->designation_id_json;
        $this->designationArray = $this->designationId ? json_decode($this->designationId, true) : [];
        if (!is_array($this->designationArray)) {
            $this->designationArray = [];
        }

        $this->employmentType = $this->holiday->employment_type_json;
        $this->employmentTypeArray = $this->employmentType ? json_decode($this->employmentType, true) : [];
        if (!is_array($this->employmentTypeArray)) {
            $this->employmentTypeArray = [];
        }


        $this->editPermission = user()->permission('edit_holiday');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->holiday->added_by == user()->id)));

        $this->pageTitle = __('app.menu.holiday');

        $this->view = 'holiday.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('holiday.create', $this->data);

    }

    /**
     * @param UpdateRequest $request
     * @param Holiday $holiday
     * @return array|void
     */
    public function update(UpdateRequest $request,  $id)
    {
        $this->editPermission = user()->permission('edit_holiday');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->holiday->added_by == user()->id)));

        $data = $request->all();
        $data['date'] = companyToYmd($request->date);

        $holiday = Holiday::find($id);

        $holiday->department_id_json = $request->has('department') ? json_encode($request->department) : null;
        $holiday->designation_id_json = $request->has('designation') ? json_encode($request->designation) : null;
        $holiday->employment_type_json = $request->has('employment_type') ? json_encode($request->employment_type) : null;

        $holiday->occassion = $request->occassion;
        $holiday->date = Carbon::createFromFormat($this->company->date_format, $request->date);

        $holiday->save();

        if ($holiday) {
            $holiday->event_id = $this->googleCalendarEvent($holiday);
            $holiday->save();
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('holidays.index')]);

    }

    /**
     * @param Holiday $holiday
     * @return array|void
     */
    public function destroy(Holiday $holiday)
    {
        $deletePermission = user()->permission('delete_holiday');
        abort_403(!($deletePermission == 'all' || ($deletePermission == 'added' && $holiday->added_by == user()->id)));

        $holiday->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('holidays.index')]);

    }

    public function tableView(HolidayDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_holiday');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->pageTitle = __('app.menu.listView');
        $this->currentYear = now()->format('Y');
        $this->currentMonth = now()->month;

        /* year range from last 5 year to next year */
        $years = [];

        $latestFifthYear = (int)now()->subYears(5)->format('Y');
        $nextYear = (int)now()->addYear()->format('Y');

        for ($i = $latestFifthYear; $i <= $nextYear; $i++) {
            $years[] = $i;
        }

        $this->years = $years;

        return $dataTable->render('holiday.index', $this->data);
    }

    public function applyQuickAction(Request $request)
    {
        abort_403(!in_array(user()->permission('edit_leave'), ['all', 'added']));

        if ($request->action_type === 'delete') {
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));


    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_holiday') != 'all');

        Holiday::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function markHoliday()
    {
        $this->addPermission = user()->permission('add_holiday');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        return view('holiday.mark-holiday.index', $this->data);
    }

    public function markDayHoliday(CommonRequest $request)
    {
        $this->addPermission = user()->permission('add_holiday');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if (!$request->has('office_holiday_days')) {
            return Reply::error(__('messages.checkDayHoliday'));
        }

        $year = now()->format('Y');

        if ($request->has('year')) {
            $year = $request->has('year');
        }


        if ($request->office_holiday_days != null && count($request->office_holiday_days) > 0) {
            foreach ($request->office_holiday_days as $holiday) {
                $day = $holiday;

                $dateArray = $this->getDateForSpecificDayBetweenDates($year . '-01-01', $year . '-12-31', ($day));

                foreach ($dateArray as $date) {
                    Holiday::firstOrCreate([
                        'date' => $date,
                        'notification_sent' => $request->notification_sent ?: 'no',
                        'occassion' => $request->occassion ? $request->occassion : now()->weekday($day)->translatedFormat('l')
                    ]);
                }

                $this->googleCalendarEventMulti($day, $year);

            }
        }

        $redirectUrl = 'table-view';

        if (url()->previous() == route('holidays.index')) {
            $redirectUrl = route('holidays.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function getDateForSpecificDayBetweenDates($startDate, $endDate, $weekdayNumber)
    {
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);

        $dateArr = [];

        do {
            if (date('w', $startDate) != $weekdayNumber) {
                $startDate += (24 * 3600); // add 1 day
            }
        } while (date('w', $startDate) != $weekdayNumber);


        while ($startDate <= $endDate) {
            $dateArr[] = date('Y-m-d', $startDate);
            $startDate += (7 * 24 * 3600); // add 7 days
        }

        return ($dateArr);
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();
        $googleAccount = company();

        if ($googleAccount->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->holiday_status == 1) {

            $google = new Google();

            if ($event->date) {
                $date = \Carbon\Carbon::parse($event->date)->shiftTimezone($googleAccount->timezone);

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new \Google_Service_Calendar_Event(array(
                    'summary' => $event->occassion,
                    'location' => $googleAccount->address,
                    'colorId' => 1,
                    'start' => array(
                        'dateTime' => $date->copy()->startOfDay(),
                        'timeZone' => $googleAccount->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $date->copy()->endOfDay(),
                        'timeZone' => $googleAccount->timezone,
                    ),
                    'reminders' => array(
                        'useDefault' => false,
                        'overrides' => array(
                            array('method' => 'email', 'minutes' => 24 * 60),
                            array('method' => 'popup', 'minutes' => 10),
                        ),
                    ),
                ));

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (\Google\Service\Exception $error) {
                    if (is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }
        }

        return $event->event_id;
    }

    protected function googleCalendarEventMulti($day, $year)
    {
        $googleAccount = company();
        $module = GoogleCalendarModule::first();

        if ($googleAccount->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->holiday_status == 1) {
            $google = new Google();

            $allDays = $this->getDateForSpecificDayBetweenDates($year . '-01-01', $year . '-12-31', $day);

            $holiday = Holiday::where(DB::raw('DATE(`date`)'), $allDays[0])->first();

            $startDate = Carbon::parse($allDays[0]);

            $frequency = 'WEEKLY';

            $eventData = new \Google_Service_Calendar_Event();
            $eventData->setSummary(now()->startOfWeek($day)->translatedFormat('l'));
            $eventData->setColorId(7);
            $eventData->setLocation('');

            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDateTime($startDate);
            $start->setTimeZone($googleAccount->timezone);

            $eventData->setStart($start);

            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime($startDate);
            $end->setTimeZone($googleAccount->timezone);

            $eventData->setEnd($end);

            $dy = substr(now()->startOfWeek($day)->translatedFormat('l'), 0, 2);

            $eventData->setRecurrence(array('RRULE:FREQ=' . $frequency . ';COUNT=' . count($allDays) . ';BYDAY=' . $dy));

            // Create event
            $google->connectUsing($googleAccount->token);
            // array for multiple

            try {
                if ($holiday->event_id) {
                    $results = $google->service('Calendar')->events->patch('primary', $holiday->event_id, $eventData);
                }
                else {
                    $results = $google->service('Calendar')->events->insert('primary', $eventData);
                }

                $holidays = Holiday::where('occassion', now()->startOfWeek($day)->translatedFormat('l'))->get();

                foreach ($holidays as $holiday) {
                    $holiday->event_id = $results->id;
                    $holiday->save();
                }

                return;
            } catch (\Google\Service\Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }

        }
    }

}
