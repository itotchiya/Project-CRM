<?php

namespace Database\Seeders;

use App\Enums\MaritalStatus;
use App\Models\ClientDetails;
use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\UniversalSearch;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run($companyId)
    {

        $count = config('app.seed_record_count');

        $adminRole = Role::where('name', 'admin')->where('company_id', $companyId)->first();
        $employeeRole = Role::where('name', 'employee')->where('company_id', $companyId)->first();
        $clientRole = Role::where('name', 'client')->where('company_id', $companyId)->first();


        $faker = \Faker\Factory::create();

        $user = new User();
        $user->name = $faker->name;
        $user->company_id = $companyId;

        if ($companyId === 1) {
            $user->email = 'admin@example.com';
            $user->password = Hash::make('123456');
            $user->gender = 'male';
            $user->save();

            $this->addEmployeeDetails($user, $employeeRole, $companyId);
            $user->roles()->attach($adminRole->id); // id only

            $user = new User();
            $user->name = $faker->name;
            $user->company_id = $companyId;
            $user->email = 'employee@example.com';
            $user->password = Hash::make('123456');
            $user->gender = 'male';
            $user->save();

            $this->addEmployeeDetails($user, $employeeRole, $companyId);

            // Client details
            $user = new User();
            $user->name = $faker->name;
            $user->company_id = $companyId;
            $user->email = 'client@example.com';
        }
        else {
            $user->email = 'admin' . $companyId . '@example.com';
            $user->password = Hash::make('123456');
            $user->gender = 'male';
            $user->save();

            $this->addEmployeeDetails($user, $employeeRole, $companyId);
            $user->roles()->attach($adminRole->id); // id only

            $user = new User();
            $user->name = $faker->name;
            $user->company_id = $companyId;
            $user->email = 'employee' . $companyId . '@example.com';
            $user->password = Hash::make('123456');
            $user->gender = 'male';
            $user->save();

            $this->addEmployeeDetails($user, $employeeRole, $companyId);

            // Client details
            $user = new User();
            $user->name = $faker->name;
            $user->company_id = $companyId;
            $user->email = 'client' . $companyId . '@example.com';

        }

        $user->password = Hash::make('123456');
        $user->save();
        $this->addClientDetails($user, $clientRole, $companyId);


        // Multiple client create
        User::factory()->count((int)$count)->make()
            ->each(function (User $user) use ($clientRole, $companyId) {

                $user->company_id = $companyId;
                $user->save();
                $this->command->info('Seeding client: ' . ($user->id) );
                $this->addClientDetails($user, $clientRole, $companyId);
            });

        // Multiple employee create
        User::factory((int)$count)->make()
            ->each(function (User $user) use ($employeeRole, $companyId) {

                $user->company_id = $companyId;
                $user->save();
                $this->command->info('employee employee: ' . ($user->id) );
                $this->addEmployeeDetails($user, $employeeRole, $companyId);
            });
    }

    private function addEmployeeDetails($user, $employeeRole, $companyId)
    {
        $faker = \Faker\Factory::create();
        $employee = new EmployeeDetails();
        $employee->user_id = $user->id;
        $employee->company_id = $companyId;
        /* @phpstan-ignore-line */
        $employee->employee_id = 'EMP-' . (EmployeeDetails::where('company_id', $companyId)->count() + 1);
        /* @phpstan-ignore-line */
        $employee->address = $faker->address;
        $employee->about_me = 'I am super human';
        $employee->hourly_rate = $faker->numberBetween(15, 100);
        $employee->department_id = rand(1, 6);
        $employee->designation_id = rand(1, 5);
        $employee->joining_date = now()->subMonths(9)->toDateTimeString();
        $employee->calendar_view = 'task,events,holiday,tickets,leaves,follow_ups';
        $employee->marital_status = MaritalStatus::Single;
        $employee->save();

        $search = new UniversalSearch();
        $search->searchable_id = $user->id;
        $search->company_id = $companyId;
        $search->title = $user->name;
        $search->route_name = 'employees.show';
        $search->module_type = 'employee';
        $search->save();

        // Assign Role
        $user->roles()->attach($employeeRole->id);
    }

    private function addClientDetails($user, $clientRole, $companyId)
    {
        $faker = \Faker\Factory::create();
        $search = new UniversalSearch();
        $search->searchable_id = $user->id;
        $search->company_id = $companyId;
        /* @phpstan-ignore-line */
        $search->title = $user->name;
        /* @phpstan-ignore-line */
        $search->route_name = 'clients.show';
        $search->module_type = 'client';
        $search->save();

        $client = new ClientDetails();
        $client->user_id = $user->id;
        $client->company_id = $companyId;
        /* @phpstan-ignore-line */
        $client->company_name = $faker->company;
        $client->address = $faker->address;
        $client->website = 'https://worksuite.biz';
        $client->save();

        // Assign Role
        $user->roles()->attach($clientRole->id);
        /* @phpstan-ignore-line */
    }

}
