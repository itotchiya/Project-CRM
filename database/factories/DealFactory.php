<?php

namespace Database\Factories;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => fake()->randomNumber(5),
            'name' => fake()->sentence(3),
            'note' => fake()->realText(),
            'next_follow_up' => 'yes',
        ];
    }

}
