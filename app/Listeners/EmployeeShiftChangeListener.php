<?php

namespace App\Listeners;

use App\Events\EmployeeShiftChangeEvent;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\User;
use App\Models\UserPermission;
use App\Notifications\ShiftChangeRequest;
use App\Notifications\ShiftChangeStatus;
use Illuminate\Support\Facades\Notification;

class EmployeeShiftChangeListener
{

    /**
     * Handle the event.
     *
     * @param EmployeeShiftChangeEvent $event
     * @return void
     */
    public function handle(EmployeeShiftChangeEvent $event)
    {
        if (!is_null($event->statusChange)) {
            Notification::send($event->changeRequest->shiftSchedule->user, new ShiftChangeStatus($event->changeRequest));

        }
        else {
            $permission = Permission::where('name', 'manage_employee_shifts')->first();
            $allTypePermission = PermissionType::ofType('all')->first();
            $users = UserPermission::where('permission_type_id', $allTypePermission->id)->where('permission_id', $permission->id)->get()->pluck('user_id')->toArray();
            Notification::send(User::select('users.*')->whereIn('id', $users)->get(), new ShiftChangeRequest($event->changeRequest));
        }

    }

}
