<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class SyncUserPermissions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-user-permissions {all?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync User Permissions';

    public function handle()
    {
        $output = new ConsoleOutput();

        $unsyncedUsers = User::with('roles')
            ->where('permission_sync', 0)
            ->when($this->argument('all'), function ($query) {
                return $query->get();
            }, function ($query) {
                return $query->limit(10)->get();
            });

        if ($unsyncedUsers->isEmpty()) {
            $output->writeln('<info>All user permissions are synced</info>');

            return Command::SUCCESS;
        }

        $total = $unsyncedUsers->count();

        $unsyncedUsers->each(function ($user, $key) use ($total) {
            $remaining = $total - $key;

            $this->assignPermissions($user, $remaining);

            $user->permission_sync = 1;
            $user->saveQuietly();
        });

        return Command::SUCCESS;
    }

    private function assignPermissions($user, $remaining)
    {
        $output = new ConsoleOutput();
        //phpcs:ignore
        $output->writeln('<info>Remaining: ' . $remaining . ' Syncing permission started for ' . $user->name . '</info>');
        $rolesCount = $user->roles->count();

        if ($rolesCount > 1) {
            $role = $user->roles->where('name', '!=', 'employee')->first();
        }
        else {
            $role = $user->roles->first();
        }

        if (!$role) {
            //phpcs:ignore
            $output->writeln('<error>Role not found for ' . $user->name . '</error>');

            return false;
        }

        $user->assignUserRolePermission($role->id);

        //phpcs:ignore
        $output->writeln('<info>Remaining: ' . $remaining . ' Syncing permission ended for ' . $user->name . '</info>');
    }

}
