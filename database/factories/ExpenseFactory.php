<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'item_name' => fake()->text(20),
            'purchase_date' => fake()->randomElement([fake()->dateTimeThisMonth(), fake()->dateTimeThisYear()]),
            /** @phpstan-ignore-next-line */
            'purchase_from' => fake()->state,
            'price' => fake()->numberBetween(100, 1000),
            'status' => fake()->randomElement(['approved', 'pending', 'rejected']),
        ];
    }

}
