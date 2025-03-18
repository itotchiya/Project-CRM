<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectStatusSetting;

class ProjectCalendarController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projectCalendar';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $viewPermission = user()->permission('view_projects');

        if (in_array('client', user_roles())) {
            $this->clients = User::client();
        }
        else {
            $this->clients = User::allClients();
            $this->allEmployees = User::allEmployees(null, true, ($viewPermission == 'all' ? 'all' : null));
        }

        $this->categories = ProjectCategory::all();
        $this->departments = Team::all();
        $this->projectStatus = ProjectStatusSetting::where('status', 'active')->get();

        if ($request->start && $request->end) {
            $startDate = Carbon::parse($request->start)->format('Y-m-d');
            $endDate = Carbon::parse($request->end)->format('Y-m-d');


            if ($startDate !== null && $endDate !== null) {
                $model = Project::where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween(DB::raw('DATE(projects.`deadline`)'), [$startDate, $endDate]);
                        $q->orWhereBetween(DB::raw('DATE(projects.`start_date`)'), [$startDate, $endDate]);
                        $q->orWhere(function ($q1) use ($startDate, $endDate) {
                            $q1->where('projects.start_date', '<=', $startDate)
                                ->where('projects.deadline', '>=', $endDate);
                            return $q1;
                        });
                });
            }

            if (!is_null($request->categoryId) && $request->categoryId != 'all') {
                $model->where('category_id', $request->categoryId);
            }

            if ($request->pinned == 'pinned') {
                $model->join('pinned', 'pinned.project_id', 'projects.id');
                $model->where('pinned.user_id', user()->id);
            }

            if (!is_null($request->employeeId) && $request->employeeId != 'all') {
                $model->leftJoin('project_members', 'project_members.project_id', 'projects.id')
                    ->selectRaw('projects.id, projects.project_short_code, projects.hash, projects.added_by,
                    projects.project_name, projects.start_date, projects.deadline, projects.client_id,
                    projects.completion_percent, projects.project_budget, projects.currency_id,
                    projects.status');
                $model->where('project_members.user_id', $request->employeeId);
            }

            if (!is_null($request->teamId) && $request->teamId != 'all') {
                $model->where('team_id', $request->teamId);
            }

            if (!is_null($request->clientID) && $request->clientID != 'all') {
                $model->where('projects.client_id', $request->clientID);
            }

            if (!is_null($request->status) && $request->status != 'all') {

                if ($request->status == 'overdue') {
                    $model->where('projects.completion_percent', '!=', 100);

                    if ($request->deadLineStartDate == '' && $request->deadLineEndDate == '') {
                        $model->whereDate('projects.deadline', '<', now(company()->timezone)->toDateString());
                    }
                }
                else {
                    $model->where('projects.status', $request->status);
                }
            }

            if ($request->progress) {
                $progressData = explode(',', $request->progress);
                $model->where(function ($q) use ($progressData) {
                    foreach ($progressData as $progress) {
                        $completionPercent = explode('-', $progress);
                        $q->orWhereBetween('projects.completion_percent', [$completionPercent[0], $completionPercent[1]]);
                    }
                });
            }

            if ($request->searchText != '') {
                $model->where(function ($query) use($request) {
                    $query->where('projects.project_name', 'like', '%' . $request->searchText . '%')
                        ->orWhere('projects.project_short_code', 'like', '%' . $request->searchText . '%'); // project short code
                });
            }

            $model = $model->get();
            $projectData = [];

            foreach ($model as $key => $value) {
                $projectStatus = ProjectStatusSetting::where('status_name', $value->status)->first();
                $projectData[] = [
                    'id' => $value->id,
                    'title' => $value->project_name,
                    'start' => $value->start_date->format('Y-m-d'),
                    'end' => (!is_null($value->deadline) ? $value->deadline->format('Y-m-d') : $value->start_date->format('Y-m-d')),
                    'color' => isset($projectStatus->color) ? $projectStatus->color : '#00b5ff'
                ];
            }

            return $projectData;
        }

        return view('projects.calendar', $this->data);
    }

}
