<?php

namespace Database\Factories;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Leave::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'duration' => fake()->randomElement(['single']),
            'leave_date' => Carbon::parse(fake()->numberBetween(1, now()->month) . '/' . fake()->numberBetween(1, now()->day) . '/' . now()->year)->format('Y-m-d'),
            'reason' => fake()->realText(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }

}
