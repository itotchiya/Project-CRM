<?php

namespace Database\Factories;

use App\Models\Event;
use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event_name' => fake()->text(20),
            'label_color' => fake()->randomElement(['#1d82f5', '#800080', '#808000', '#008000', '#0000A0', '#000000']),
            'where' => fake()->address,
            'description' => fake()->paragraph,
            'start_date_time' => $start = fake()->randomElement([fake()->dateTimeThisMonth(), fake()->dateTimeThisYear()]),
            'end_date_time' => fake()->dateTimeBetween($start, $start->add(new DateInterval('PT10H30S'))),
            'repeat' => 'no',
        ];
    }

}
