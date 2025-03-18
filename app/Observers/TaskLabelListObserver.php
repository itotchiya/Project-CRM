<?php

namespace App\Observers;

use App\Models\ProjectTemplateTask;
use App\Models\Task;
use App\Models\TaskLabelList;

class TaskLabelListObserver
{

    public function creating(TaskLabelList $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    public function updated($taskLabel)
    {
        if ($taskLabel->isDirty('project_id') && request()->task_id != null) {

            $validLabelIds = TaskLabelList::whereNull('project_id')->pluck('id')->toArray();

            $projectTemplateTasks = ProjectTemplateTask::all();

            foreach ($projectTemplateTasks as $task) {

                $taskLabelsArray = explode(',', $task->task_labels);

                $updatedTaskLabels = array_filter($taskLabelsArray, function($labelId) use ($validLabelIds) {
                    return in_array($labelId, $validLabelIds);
                });

                if (implode(',', $updatedTaskLabels) !== $task->task_labels) {
                    $task->task_labels = implode(',', $updatedTaskLabels);
                    $task->save();
                }
            }

            $task = Task::with('labels')->findOrFail(request()->task_id);

            if ($task->project_id != $taskLabel->project_id) {
                $task->labels()->detach(request()->label_id);
            }

        }
    }

    public function deleted(){

        $validLabelIds = TaskLabelList::whereNull('project_id')->pluck('id')->toArray();

        $projectTemplateTasks = ProjectTemplateTask::all();

        foreach ($projectTemplateTasks as $task) {

            $taskLabelsArray = explode(',', $task->task_labels);

            $updatedTaskLabels = array_filter($taskLabelsArray, function($labelId) use ($validLabelIds) {
                return in_array($labelId, $validLabelIds);
            });

            if (implode(',', $updatedTaskLabels) !== $task->task_labels) {
                $task->task_labels = implode(',', $updatedTaskLabels);
                $task->save();
            }
        }
    }

}


