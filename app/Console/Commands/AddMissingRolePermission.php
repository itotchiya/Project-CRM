<?php

namespace App\Console\Commands;

use App\Http\Controllers\RolePermissionController;
use App\Models\Company;
use Illuminate\Console\Command;

class AddMissingRolePermission extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-missing-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add missing permissions';

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle()
    {

        $rolePerm = new RolePermissionController();

        Company::active()->select('id')->chunk(50, function ($companies) use ($rolePerm) {

            foreach ($companies as $company) {
                $this->info('Running for company:' . $company->id);
                $rolePerm->addMissingAdminPermission($company->id);
                $rolePerm->addMissingEmployeePermission($company->id);
            }
        });


        return Command::SUCCESS;
    }

}
