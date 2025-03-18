<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class RemoveSeenNotification extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-seen-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete seen notifications';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        Notification::whereNotNull('read_at')->delete();

        return Command::SUCCESS;
    }

}
