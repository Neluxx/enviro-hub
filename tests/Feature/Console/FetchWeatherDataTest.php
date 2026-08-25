<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchWeatherDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.openweather.key' => 'test-key',
            'services.openweather.latitude' => '52.52',
            'services.openweather.longitude' => '13.40',
            'services.openweather.location' => 'Berlin',
            'services.openweather.units' => 'metric',
        ]);
    }

    public function test_fetches_and_stores_weather_data(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response($this->weatherResponse()),
        ]);

        $this->artisan('weather:fetch')->assertSuccessful();

        $this->assertDatabaseHas('weather_data', [
            'location' => 'Berlin',
            'temperature' => 22.5,
            'humidity' => 45,
            'pressure' => 1013,
            'description' => 'broken clouds',
        ]);
    }

    public function test_fails_gracefully_when_openweather_errors(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401),
        ]);

        $this->artisan('weather:fetch')->assertFailed();

        $this->assertDatabaseCount('weather_data', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function weatherResponse(): array
    {
        return [
            'weather' => [
                ['id' => 803, 'main' => 'Clouds', 'description' => 'broken clouds', 'icon' => '04d'],
            ],
            'main' => [
                'temp' => 22.5,
                'feels_like' => 21.8,
                'pressure' => 1013,
                'humidity' => 45,
            ],
            'wind' => ['speed' => 3.6, 'deg' => 200],
            'dt' => 1782820800,
            'name' => 'Berlin',
        ];
    }
}
