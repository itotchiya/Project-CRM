<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subject' => fake()->text(20),
            'status' => fake()->randomElement(['open', 'pending', 'resolved', 'closed']),
            'priority' => fake()->randomElement(['low', 'high', 'medium', 'urgent']),
            'created_at' => fake()->randomElement([date('Y-m-d', strtotime( '+'.mt_rand(0, 7).' days')), fake()->dateTimeThisYear($max = 'now')]),
            'updated_at' => fake()->randomElement([date('Y-m-d', strtotime( '+'.mt_rand(0, 7).' days')), fake()->dateTimeThisYear($max = 'now')]),
        ];
    }

}
