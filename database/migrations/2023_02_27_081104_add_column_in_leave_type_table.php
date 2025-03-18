<?php

use App\Enums\MaritalStatus;
use App\Models\Company;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->integer('effective_after')->nullable()->after('monthly_limit');
            $table->string('effective_type')->nullable()->after('effective_after');
            $table->string('unused_leave')->nullable()->after('effective_type');
            $table->boolean('encashed')->after('unused_leave');
            $table->boolean('allowed_probation')->after('encashed');
            $table->boolean('allowed_notice')->after('allowed_probation');
            $table->string('gender')->nullable()->after('allowed_notice');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('department')->nullable()->after('marital_status');
            $table->string('designation')->nullable()->after('department');
            $table->string('role')->nullable()->after('designation');
        });

        $companies = Company::all();

        if($companies){
            foreach($companies as $company)
            {
                $teams = Team::where('company_id', $company->id)->pluck('id')->toArray();
                $designations = Designation::where('company_id', $company->id)->pluck('id')->toArray();
                $roles = Role::where('name', '<>', 'client')->where('company_id', $company->id)->pluck('id')->toArray();

                LeaveType::where('company_id', $company->id)->update([
                    'gender' => ['male', 'female', 'others'],
                    'marital_status' => MaritalStatus::toArray(),
                    'department' => json_encode($teams),
                    'designation' => json_encode($designations),
                    'role' => json_encode($roles),
                ]);
            }
        }


        User::whereNull('gender')->update([
            'gender' => 'male'
        ]);
        EmployeeDetails::whereNull('marital_status')->update([
            'marital_status' => MaritalStatus::Single
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('effective_after');
            $table->dropColumn('effective_type');
            $table->dropColumn('unused_leave');
            $table->dropColumn('encashed');
            $table->dropColumn('allowed_probation');
            $table->dropColumn('allowed_notice');
            $table->dropColumn('gender');
            $table->dropColumn('marital_status');
            $table->dropColumn('department');
            $table->dropColumn('designation');
            $table->dropColumn('role');
        });
    }

};
