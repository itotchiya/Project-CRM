<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Milestone\StoreMilestone;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;


class ProjectMilestoneController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id = request('id');

        $this->project = Project::findOrFail($id);
        $addProjectMilestonePermission = user()->permission('add_project_milestones');
        $project = Project::findOrFail($id);

        abort_403(!($addProjectMilestonePermission == 'all' || $project->project_admin == user()->id));

        return view('projects.milestone.create', $this->data);
    }

    /**
     * @param StoreMilestone $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreMilestone $request)
    {
        $milestone = new ProjectMilestone();
        $milestone->project_id = $request->project_id;
        $milestone->milestone_title = $request->milestone_title;
        $milestone->summary = $request->summary;
        $milestone->cost = ($request->cost == '') ? '0' : $request->cost;
        $milestone->currency_id = $request->currency_id;
        $milestone->status = $request->status;
        $milestone->add_to_budget = $request->add_to_budget;
        $milestone->start_date = $request->start_date == null ? $request->start_date : companyToYmd($request->start_date);
        $milestone->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $milestone->save();

        $project = Project::findOrFail($request->project_id);

        if ($request->add_to_budget == 'yes') {
            $project->project_budget = (!is_null($project->project_budget) ? ($project->project_budget + $milestone->cost) : $milestone->cost);
            $project->currency_id = $request->currency_id;
            $project->save();
        }

        $this->logProjectActivity($project->id, 'messages.newMilestoneCreated');

        return Reply::success(__('messages.milestoneSuccess'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->milestone = ProjectMilestone::findOrFail($id);
        $this->currencies = Currency::all();

        return view('projects.milestone.edit', $this->data);
    }

    /**
     * @param StoreMilestone $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreMilestone $request, $id)
    {
        $milestone = ProjectMilestone::findOrFail($id);

        // find the current cost of milestone
        $oldCost = $milestone->getOriginal('cost');

        $milestone->project_id = $request->project_id;
        $milestone->milestone_title = $request->milestone_title;
        $milestone->summary = $request->summary;
        $milestone->cost = ($request->cost == '') ? '0' : $request->cost;
        $milestone->currency_id = $request->currency_id;
        $milestone->status = $request->status;
        $milestone->start_date = $request->start_date == null ? $request->start_date : companyToYmd($request->start_date);
        $milestone->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $milestone->save();

        $project = Project::findOrFail($milestone->project_id);

        // get the latest cost of milestone
        $newCost = $milestone->cost;
        $costDifference = $newCost - $oldCost;

        // Update the project budget if the add_to_budget flag is set to 'yes'
        if ($milestone->add_to_budget == 'yes') {

            // Update project budget
            $project->project_budget += $costDifference;
            $project->save();
        }

        $this->logProjectActivity($milestone->project_id, 'messages.milestoneUpdated');

        return Reply::success(__('messages.milestoneSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $milestone = ProjectMilestone::findOrFail($id);

        // Retrieve the associated project
        $project = Project::findOrFail($milestone->project_id);

        // Update project budget by subtracting the cost of the milestone
        if ($milestone->add_to_budget == 'yes') {
            $project->project_budget -= $milestone->cost;
            $project->save();
        }

        ProjectMilestone::destroy($id);
        $this->logProjectActivity($milestone->project_id, 'messages.milestoneDeleted');

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function show($id)
    {
        $viewMilestonePermission = user()->permission('view_project_milestones');

        $this->milestone = ProjectMilestone::with('tasks', 'tasks.users', 'tasks.boardColumn', 'tasks.createBy', 'tasks.timeLogged', 'project')->findOrFail($id);

        $project = Project::withTrashed()->findOrFail($this->milestone->project_id);

        abort_403(!(
            $viewMilestonePermission == 'all'
            || ($viewMilestonePermission == 'added' && $this->milestone->added_by == user()->id)
            || ($viewMilestonePermission == 'owned' && $this->milestone->project->client_id == user()->id && in_array('client', user_roles()))
            || ($viewMilestonePermission == 'owned' && in_array('employee', user_roles()))
            || ($project->project_admin == user()->id)
        ));

        $totalTaskTime = 0;

        foreach ($this->milestone->tasks as $totalTime) {
            $totalMinutes = $totalTime->timeLogged->sum('total_minutes');
            $breakMinutes = $totalTime->breakMinutes();
            $totalMinutes = $totalMinutes - $breakMinutes;
            $totalTaskTime += $totalMinutes;
        }

        /** @phpstan-ignore-next-line */
        $this->timeLog = CarbonInterval::formatHuman($totalTaskTime);

        return view('projects.milestone.show', $this->data);
    }

    public function byProject($id)
    {
        if ($id == 0) {
            $options = '<option value="">--</option>';
        }
        else {
            $projects = ProjectMilestone::where('project_id', $id)->whereNot('status', 'complete')->get();
            $options = BaseModel::options($projects, null, 'milestone_title');
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function updateStatus(Request $request, $id)
    {
        $milestone = ProjectMilestone::findOrFail($id);
        $milestone->status = $request->input('status');
        $milestone->save();

        return response()->json(['status' => 'success', 'message' =>  __('messages.updateSuccess')]);
    }

}
