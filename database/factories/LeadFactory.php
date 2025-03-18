<?php

namespace Database\Factories;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
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
            'company_name' => fake()->company,
            'address' => fake()->address,
            'client_name' => fake()->name,
            'client_email' => fake()->email,
            'mobile' => fake()->randomNumber(8),
            'value' => fake()->randomNumber(6),
            'note' => fake()->text(),
            'next_follow_up' => 'yes',
        ];
    }

}
