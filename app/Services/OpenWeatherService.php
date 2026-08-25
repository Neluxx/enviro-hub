<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    private const string BASE_URL = 'https://api.openweathermap.org/data/2.5';

    /**
     * Fetch the current weather for the configured location and map it to
     * weather_data model attributes.
     *
     * @return array<string, mixed>
     */
    public function fetchCurrentWeather(): array
    {
        $response = Http::baseUrl(self::BASE_URL)
            ->get('/weather', [
                'lat' => config('services.openweather.latitude'),
                'lon' => config('services.openweather.longitude'),
                'appid' => config('services.openweather.key'),
                'units' => config('services.openweather.units'),
            ])
            ->throw();

        return $this->map($response->json());
    }

    /**
     * Map an OpenWeather current weather response to weather_data attributes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function map(array $data): array
    {
        return [
            'location' => config('services.openweather.location'),
            'temperature' => $data['main']['temp'],
            'feels_like' => $data['main']['feels_like'],
            'humidity' => $data['main']['humidity'],
            'pressure' => $data['main']['pressure'],
            'wind_speed' => $data['wind']['speed'] ?? 0,
            'description' => $data['weather'][0]['description'] ?? '',
            'measured_at' => Carbon::createFromTimestamp($data['dt']),
        ];
    }
}
