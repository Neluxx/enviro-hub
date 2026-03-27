<?php

namespace Database\Factories;

use App\Models\Node;
use App\Models\SensorData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SensorData>
 */
class SensorDataFactory extends Factory
{
    protected $model = SensorData::class;

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        return [
            'node_id' => Node::factory(),
            'node_uuid' => Str::uuid()->toString(),
            'temperature' => fake()->randomFloat(2, -20, 45),
            'humidity' => fake()->randomFloat(2, 0, 100),
            'pressure' => fake()->numberBetween(900, 1100),
            'carbon_dioxide' => fake()->numberBetween(300, 2000),
            'measured_at' => now(),
        ];
    }
}
