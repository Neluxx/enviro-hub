<?php

namespace Database\Seeders;

use App\Models\Home;
use App\Models\Node;
use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    /**
     * Seed homes, nodes, and realistic sensor data for the dashboard.
     */
    public function run(): void
    {
        $homes = [
            ['title' => 'Mountain Cabin', 'nodes' => ['Living Room', 'Bedroom', 'Kitchen']],
            ['title' => 'City Apartment', 'nodes' => ['Office', 'Balcony Sensor']],
            ['title' => 'Lake House', 'nodes' => ['Garage', 'Basement', 'Attic', 'Porch']],
        ];

        foreach ($homes as $homeData) {
            $home = Home::factory()->create(['title' => $homeData['title']]);

            foreach ($homeData['nodes'] as $nodeTitle) {
                $node = Node::factory()->create([
                    'title' => $nodeTitle,
                    'home_id' => $home->id,
                ]);

                $this->seedSensorData($node);
            }
        }
    }

    /**
     * Generate 72 hours of sensor readings every 15 minutes for a node.
     * Values follow a realistic daily cycle with gentle noise.
     */
    private function seedSensorData(Node $node): void
    {
        $start = Carbon::now()->subHours(72);
        $intervalMinutes = 15;
        $points = (int) (72 * 60 / $intervalMinutes); // 288 data points

        // Base values per node give variety between sensors
        $baseTemp = fake()->randomFloat(1, 18, 26);
        $baseHumidity = fake()->randomFloat(1, 35, 65);
        $baseCo2 = fake()->numberBetween(400, 800);

        $records = [];

        for ($i = 0; $i < $points; $i++) {
            $measuredAt = $start->copy()->addMinutes($i * $intervalMinutes);
            $hour = (float) $measuredAt->format('G') + $measuredAt->minute / 60;

            // Simulate daily temperature cycle: cooler at night, warmer during the day
            $tempCycle = sin(($hour - 6) * M_PI / 12) * 4; // ±4 °C swing
            $temperature = round($baseTemp + $tempCycle + fake()->randomFloat(2, -0.5, 0.5), 2);

            // Humidity inversely related to temperature
            $humidityCycle = -sin(($hour - 6) * M_PI / 12) * 10; // ±10 % swing
            $humidity = round(
                max(10, min(99, $baseHumidity + $humidityCycle + fake()->randomFloat(2, -1, 1))),
                2
            );

            // CO₂ rises during "occupied" hours (8–22), dips at night
            $co2Occupied = ($hour >= 8 && $hour <= 22) ? 200 : 0;
            $co2 = max(300, $baseCo2 + $co2Occupied + fake()->numberBetween(-30, 30));

            $records[] = [
                'node_id' => $node->id,
                'node_uuid' => $node->uuid,
                'temperature' => $temperature,
                'humidity' => $humidity,
                'pressure' => fake()->numberBetween(1005, 1025),
                'carbon_dioxide' => $co2,
                'measured_at' => $measuredAt,
                'created_at' => $measuredAt,
                'updated_at' => $measuredAt,
            ];
        }

        // Insert in chunks for performance
        foreach (array_chunk($records, 100) as $chunk) {
            SensorData::insert($chunk);
        }
    }
}
