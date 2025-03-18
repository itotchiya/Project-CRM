<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'heading' => fake()->realText(70),
            'description' => fake()->realText(1000),
            'created_at' => fake()->randomElement([date('Y-m-d', strtotime( '+'.mt_rand(0, 7).' days')),fake()->dateTimeThisMonth($max = 'now'), fake()->dateTimeThisYear($max = 'now')]),
        ];
    }

}
