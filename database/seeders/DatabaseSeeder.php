<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        // Set Seeding to true check if data is seeding.
        // This is required to stop notification in installation
        config(['app.seeding' => true]);

        Artisan::call('key:generate');

        $this->call(CountriesTableSeeder::class);
        $this->call(SmtpSettingsSeeder::class);
        $this->call(CoreDatabaseSeeder::class);
        $this->call(ModulePermissionSeeder::class);

        $this->call(OrganisationSettingsTableSeeder::class);

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            if (!App::environment('codecanyon')) {
                $this->call(DepartmentTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(UsersTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(BankAccountSeeder::class, false, ['companyId' => $company->id]);
                $this->call(ProjectCategorySeeder::class, false, ['companyId' => $company->id]);
                $this->call(ProjectSeeder::class, false, ['companyId' => $company->id]);
                $this->call(EstimateSeeder::class, false, ['companyId' => $company->id]);
                $this->call(ExpenseSeeder::class, false, ['companyId' => $company->id]);
                $this->call(TicketSeeder::class, false, ['companyId' => $company->id]);
                $this->call(TicketSettingSeeder::class, false, ['companyId' => $company->id]);
                $this->call(RoleSeeder::class, false, ['companyId' => $company->id]);
                $this->call(LeaveSeeder::class, false, ['companyId' => $company->id]);
                $this->call(NoticesTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(EventTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(LeadSeeder::class, false, ['companyId' => $company->id]);
                $this->call(TaxTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(ProductTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(ContractTypeTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(ContractTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(LeadsTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(MessageSeeder::class, false, ['companyId' => $company->id]);
                $this->call(ShiftSeeder::class, false, ['companyId' => $company->id]);
                $this->call(AttendanceTableSeeder::class, false, ['companyId' => $company->id]);
                $this->call(AppreciationSeeder::class, false, ['companyId' => $company->id]);
            }

            $this->call(EmployeePermissionSeeder::class, false, ['companyId' => $company->id]);
        }

        if (!App::environment('codecanyon')) {
            Artisan::call('sync-user-permissions all');
        }

        config(['app.seeding' => false]);

        cache()->flush();
    }

}
