<?php

namespace Tests\Unit\Services;

use App\Services\OpenWeatherService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWeatherServiceTest extends TestCase
{
    private OpenWeatherService $service;

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

        $this->service = new OpenWeatherService;
    }

    public function test_maps_openweather_response_to_weather_data_attributes(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response($this->weatherResponse()),
        ]);

        $data = $this->service->fetchCurrentWeather();

        $this->assertSame('Berlin', $data['location']);
        $this->assertSame(22.5, $data['temperature']);
        $this->assertSame(21.8, $data['feels_like']);
        $this->assertSame(45, $data['humidity']);
        $this->assertSame(1013, $data['pressure']);
        $this->assertSame(3.6, $data['wind_speed']);
        $this->assertSame('broken clouds', $data['description']);
        $this->assertSame('2026-06-30 12:00:00', $data['measured_at']->utc()->toDateTimeString());
    }

    public function test_sends_configured_location_and_credentials(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response($this->weatherResponse()),
        ]);

        $this->service->fetchCurrentWeather();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'lat=52.52')
                && str_contains($request->url(), 'lon=13.40')
                && str_contains($request->url(), 'appid=test-key')
                && str_contains($request->url(), 'units=metric');
        });
    }

    public function test_throws_when_openweather_returns_an_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401),
        ]);

        $this->expectException(RequestException::class);

        $this->service->fetchCurrentWeather();
    }

    public function test_defaults_wind_speed_and_description_when_absent(): void
    {
        $response = $this->weatherResponse();
        unset($response['wind'], $response['weather']);

        Http::fake([
            'api.openweathermap.org/*' => Http::response($response),
        ]);

        $data = $this->service->fetchCurrentWeather();

        $this->assertSame(0, $data['wind_speed']);
        $this->assertSame('', $data['description']);
    }

    /**
     * @return array<string, mixed>
     */
    private function weatherResponse(): array
    {
        return [
            'dt' => 1782820800, // 2026-06-30 12:00:00 UTC
            'main' => [
                'temp' => 22.5,
                'feels_like' => 21.8,
                'pressure' => 1013,
                'humidity' => 45,
            ],
            'wind' => [
                'speed' => 3.6,
            ],
            'weather' => [[
                'id' => 803,
                'main' => 'Clouds',
                'description' => 'broken clouds',
                'icon' => '04d',
            ]],
        ];
    }
}
