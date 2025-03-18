<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\UserInvitation;
use App\Models\UserPermission;
use App\Models\EmployeeDetails;
use App\Models\UniversalSearch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\AcceptInviteRequest;
use App\Http\Requests\User\AccountSetupRequest;
use App\Events\NewUserRegistrationViaInviteEvent;
use App\Models\GlobalSetting;
use Symfony\Component\Mailer\Exception\TransportException;

class RegisterController extends Controller
{

    public function invitation($code)
    {
        if (Auth::check()) {
            return redirect(route('dashboard'));
        }

        $this->invite = UserInvitation::where('invitation_code', $code)
            ->where('status', 'active')
            ->firstOrFail();

        $this->globalSetting = GlobalSetting::first();

        return view('auth.invitation', $this->data);
    }

    public function acceptInvite(AcceptInviteRequest $request)
    {
        $invite = UserInvitation::where('invitation_code', $request->invite)
            ->where('status', 'active')
            ->first();

        $this->company = $invite->company;

        if (is_null($invite) || ($invite->invitation_type == 'email' && $request->email != $invite->email)) {
            return Reply::error('messages.acceptInviteError');
        }

        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->company_id = $invite->company_id;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();
            $user = $user->setAppends([]);

            $lastEmployeeID = EmployeeDetails::where('company_id', $invite->company_id)->count();
            $checkifExistEmployeeId = EmployeeDetails::select('id')->where('employee_id', ($lastEmployeeID + 1))->where('company_id', $invite->company_id)->first();

            if ($user->id) {
                $employee = new EmployeeDetails();
                $employee->user_id = $user->id;
                $employee->company_id = $invite->company_id;
                $employee->employee_id = ((!$checkifExistEmployeeId) ? ($lastEmployeeID + 1) : null);
                $employee->joining_date = now($this->company->timezone)->format('Y-m-d');
                $employee->added_by = $user->id;
                $employee->last_updated_by = $user->id;
                $employee->save();
            }

            $employeeRole = Role::where('name', 'employee')->where('company_id', $invite->company_id)->first();
            $user->attachRole($employeeRole);


            $rolePermissions = PermissionRole::where('role_id', $employeeRole->id)->get();

            foreach ($rolePermissions as $value) {
                $userPermission = UserPermission::where('permission_id', $value->permission_id)
                    ->where('user_id', $user->id)
                    ->firstOrNew();
                $userPermission->permission_id = $value->permission_id;
                $userPermission->user_id = $user->id;
                $userPermission->permission_type_id = $value->permission_type_id;
                $userPermission->save();
            }

            $logSearch = new AccountBaseController();
            $logSearch->logSearchEntry($user->id, $user->name, 'employees.show', 'employee');

            if ($invite->invitation_type == 'email') {
                $invite->status = 'inactive';
                $invite->save();
            }

            // Commit Transaction
            DB::commit();

            // Send Notification to all admins about recently added member
            $admins = User::allAdmins($user->company->id);

            foreach ($admins as $admin) {
                event(new NewUserRegistrationViaInviteEvent($admin, $user));
            }

            session()->forget('user');
            Auth::login($user);

            return Reply::success(__('messages.signupSuccess'));
        } catch (TransportException $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Please configure SMTP details. Visit Settings -> notification setting to set smtp: ' . $e->getMessage(), 'smtp_error');
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support: ' . $e->getMessage());
        }

        return view('auth.invitation', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function setupAccount(AccountSetupRequest $request)
    {
        // Update company name
        $setting = Company::firstOrCreate();
        $setting->company_name = $request->company_name;
        $setting->app_name = $request->company_name;
        $setting->timezone = 'Asia/Kolkata';
        $setting->date_picker_format = 'dd-mm-yyyy';
        $setting->moment_format = 'DD-MM-YYYY';
        $setting->rounded_theme = 1;
        $setting->save();

        // Create admin user
        $user = new User();
        $user->name = $request->full_name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->company_id = $setting->id;
        $user->save();

        $employee = new EmployeeDetails();
        $employee->user_id = $user->id;
        $employee->employee_id = $user->id;
        $employee->company_id = $setting->id;
        $employee->save();

        $search = new UniversalSearch();
        $search->searchable_id = $user->id;
        $search->title = $user->name;
        $search->route_name = 'employees.show';
        $search->save();

        // Attach roles
        $adminRole = Role::where('company_id', $setting->id)->where('name', 'admin')->first();
        $employeeRole = Role::where('company_id', $setting->id)->where('name', 'employee')->first();
        $user->roles()->attach($adminRole->id);
        $user->roles()->attach($employeeRole->id);

        $allPermissions = Permission::orderBy('id')->get()->pluck('id')->toArray();

        foreach ($allPermissions as $permission) {
            $user->permissionTypes()->attach([$permission => ['permission_type_id' => PermissionType::ALL]]);
        }

        Auth::login($user);

        return Reply::success(__('messages.signupSuccess'));
    }

}
