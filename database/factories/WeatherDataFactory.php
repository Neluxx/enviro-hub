<?php

namespace Database\Factories;

use App\Models\WeatherData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeatherData>
 */
class WeatherDataFactory extends Factory
{
    protected $model = WeatherData::class;

    /**
     * {@inheritdoc}
     */
    public function definition(): array
    {
        return [
            'location' => fake()->city(),
            'temperature' => fake()->randomFloat(2, -20, 45),
            'feels_like' => fake()->randomFloat(2, -20, 45),
            'humidity' => fake()->numberBetween(0, 100),
            'pressure' => fake()->numberBetween(900, 1100),
            'wind_speed' => fake()->randomFloat(2, 0, 30),
            'description' => fake()->words(2, true),
            'measured_at' => now(),
        ];
    }
}
