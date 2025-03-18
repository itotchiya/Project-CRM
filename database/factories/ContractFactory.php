<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subject' => fake()->realText(20),
            'amount' => $amount = fake()->numberBetween(100, 1000),
            'original_amount' => $amount,
            'start_date' => $start = fake()->dateTimeThisMonth(now()),
            'original_start_date' => $start,
            'end_date' => $end = now()->addMonths(fake()->numberBetween(1, 5))->format('Y-m-d'),
            'original_end_date' => $end,
            'description' => fake()->paragraph,
            'contract_detail' => fake()->realText(300),
        ];
    }

}
