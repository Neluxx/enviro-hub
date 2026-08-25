<?php

namespace App\Console\Commands;

use App\Models\WeatherData;
use App\Services\OpenWeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchWeatherData extends Command
{
    /**
     * @var string
     */
    protected $signature = 'weather:fetch';

    /**
     * @var string
     */
    protected $description = 'Fetch the current weather from OpenWeather and store it.';

    /**
     * Execute the console command.
     */
    public function handle(OpenWeatherService $openWeatherService): int
    {
        try {
            $weatherData = WeatherData::create($openWeatherService->fetchCurrentWeather());
        } catch (Throwable $e) {
            Log::error('Failed to fetch weather data from OpenWeather.', ['exception' => $e]);
            $this->error('Failed to fetch weather data: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Stored weather for {$weatherData->location} measured at {$weatherData->measured_at}.");

        return self::SUCCESS;
    }
}
