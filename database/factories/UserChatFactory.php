<?php

namespace Database\Factories;

use App\Models\UserChat;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserChat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message' => fake()->realText(200),
        ];
    }

}
