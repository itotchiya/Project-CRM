<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketChannel;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\TicketReply;
use App\Models\TicketAgentGroups;
use App\Models\TicketGroup;

class TicketSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        // Save agent

        $faker = \Faker\Factory::create();

        $count = config('app.seed_record_count');
        $ticketGroups = [
            [
                'group_name' => 'Legal',
                'company_id' => $companyId
            ],
            [
                'group_name' => 'Installation',
                'company_id' => $companyId
            ],
            [
                'group_name' => 'Spam',
                'company_id' => $companyId
            ],
            [
                'group_name' => 'Very Important',
                'company_id' => $companyId
            ],
            [
                'group_name' => 'Technical',
                'company_id' => $companyId
            ],
        ];

        TicketGroup::insert($ticketGroups);

        $agents = $this->getEmployees($companyId);
        $groups = $this->getGroups($companyId);

        for ($i = 1; $i <= 5; $i++) {
            $agent = new TicketAgentGroups();
            $agent->company_id = $companyId;
            $agent->agent_id = $faker->randomElement($agents);
            $agent->group_id = $faker->randomElement($groups);
            $agent->save();
        }

        $types = TicketType::where('company_id', $companyId)->get()->pluck('id')->toArray();
        $users = User::where('company_id', $companyId)->get()->pluck('id')->toArray();
        $channels = TicketChannel::where('company_id', $companyId)->get()->pluck('id')->toArray();
        $agents = TicketAgentGroups::where('company_id', $companyId)->get()->pluck('agent_id')->toArray();
        $groups = TicketGroup::where('company_id', $companyId)->get()->pluck('id')->toArray();

        $type = $types[array_rand($types)];
        $user = $users[array_rand($users)];
        $channel = $channels[array_rand($channels)];
        $agent = $agents[array_rand($agents)];
        $group = $groups[array_rand($groups)];

        Ticket::factory($companyId)->count((int)$count)
            ->make()
            ->each(function (Ticket $ticket) use ($faker, $companyId, $type, $user, $channel, $agent, $group) {

                $ticket->company_id = $companyId;
                $ticket->ticket_number = Ticket::where('company_id', $companyId)->max('ticket_number') + 1;
                $ticket->user_id = $user;
                $ticket->agent_id = $agent;
                $ticket->group_id = $group;
                $ticket->channel_id = $channel;
                $ticket->type_id = $type;
                $ticket->save();

                $usersArray = [$ticket->user_id, $ticket->agent_id];
                /* @phpstan-ignore-line */
                $admins = $this->getAdmins($companyId);
                $usersData = array_merge($usersArray, $admins);

                for ($i = 1; $i <= 5; $i++) {
                    // Save  message
                    $reply = new TicketReply();
                    $reply->message = $faker->realText(50);
                    $reply->ticket_id = $ticket->id;
                    /* @phpstan-ignore-line */
                    $reply->user_id = $faker->randomElement($usersData); // Current logged in user
                    $reply->save();

                    // Log search
                    $search = new \App\Models\UniversalSearch();
                    $search->searchable_id = $ticket->ticket_number;
                    $search->company_id = $companyId;
                    /* @phpstan-ignore-line */
                    $search->title = 'Ticket: ' . $ticket->subject;
                    /* @phpstan-ignore-line */
                    $search->route_name = 'tickets.show';
                    $search->save();
                }
            });
    }

    private function getEmployees($companyId)
    {
        return User::select('users.id as id')
            ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'employee')
            ->where('users.company_id', $companyId)
            ->inRandomOrder()
            ->get()->pluck('id')
            ->toArray();
    }

    private function getAdmins($companyId)
    {
        return User::select('users.id as id')
            ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->where('users.company_id', $companyId)
            ->inRandomOrder()
            ->get()->pluck('id')
            ->toArray();
    }

    private function getGroups($companyId)
    {
        return TicketGroup::inRandomOrder()
            ->where('company_id', $companyId)
            ->get()->pluck('id')
            ->toArray();
    }

}
