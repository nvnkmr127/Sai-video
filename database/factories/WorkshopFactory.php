<?php

namespace Database\Factories;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workshop>
 */
class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'location' => $this->faker->city,
            'max_seats' => $this->faker->numberBetween(10, 100),
            'is_active' => true,
        ];
    }
}
