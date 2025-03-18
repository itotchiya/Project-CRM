<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $productArray = [
            'Women\'s Fashion Handbag',
            'Men\'s Leather Wallet',
            'Unisex Sunglasses',
            'Kids\' Outdoor Play Set',
            'Electric Kitchen Mixer',
            'Memory Foam Mattress',
            'Bluetooth Headphones',
            'Smartphone Case',
            'Digital Camera',
            'Fitness Tracker',
            'Power Bank',
            'Essential Oil Diffuser',
            'Kitchen Knife Set',
            'LED Floor Lamp',
            'Electric Toothbrush',
            'Hair Straightener',
            'Electric Shaver',
            'Handheld Vacuum Cleaner',
            'Coffee Maker',
            'Electric Pressure Cooker',
            'Robot Vacuum Cleaner',
            'Gaming Headset',
            'Electric Kettle',
            'Air Purifier',
            'Portable Air Conditioner',
            'Stand Mixer',
            'Electric Screwdriver',
            'Smart Watch',
            'Smart Thermostat',
            'Smart Lock',
            'Smart Sprinkler',
            'Smart Light Bulb',
            'Smart Plug',
            'Smart Scale',
            'Smart Smoke Detector',
            'Smart Camera',
            'Smart Speakers',
        ];

        return [
            'name' => fake()->randomElement($productArray),
            'price' => fake()->numberBetween(100, 1000),
            'allow_purchase' => 1,
            'description' => fake()->paragraph,
        ];
    }

}
