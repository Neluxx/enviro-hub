<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OpenWeatherData;
use App\Repository\OpenWeatherDataRepository;
use DateTime;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Open Weather Data Service.
 */
class OpenWeatherDataService
{
    /** The open weather API URL  */
    private const OPEN_WEATHER_API_URL = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly OpenWeatherDataRepository $repository,
        private readonly string $openWeatherApiKey,
    ) {
    }

    /**
     * Fetches weather data from the OpenWeather API for a given city.
     *
     * @param string $cityName the name of the city for which to fetch weather data
     *
     * @throws RuntimeException if the API request fails or returns an unsuccessful response
     * @throws TransportExceptionInterface if there is an issue with the HTTP transport layer during the API request
     * @throws DecodingExceptionInterface if there is an error decoding the JSON response from the API
     * @throws Exception
     *
     * @return OpenWeatherData the weather data object containing temperature, humidity, wind speed, description, and other details
     */
    public function fetchWeatherData(string $cityName): OpenWeatherData
    {
        $response = $this->httpClient->request('GET', self::OPEN_WEATHER_API_URL, [
            'query' => [
                'q' => $cityName,
                'appid' => $this->openWeatherApiKey,
                'units' => 'metric',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException('Failed to fetch weather data from OpenWeather API.');
        }

        $data = $response->toArray();

        return $this->createOpenWeatherDataFromArray($data);
    }

    /**
     * Saves the provided weather data into the repository.
     *
     * @param OpenWeatherData $data the weather data object to be persisted
     *
     * @throws Exception if there is an issue while saving the data into the repository
     */
    public function saveWeatherData(OpenWeatherData $data): void
    {
        $this->repository->save($data);
    }

    /**
     * Creates an OpenWeatherData object from an array of weather data.
     *
     * @param array<string, mixed> $data the associative array containing weather data retrieved from the API
     *
     * @throws InvalidArgumentException if required keys are missing or data is invalid
     * @throws Exception if there is an issue creating the DateTime object for the `createdAt` property
     *
     * @return OpenWeatherData the populated OpenWeatherData object with city name, temperature, humidity, wind speed, description, and timestamp
     */
    public function createOpenWeatherDataFromArray(array $data): OpenWeatherData
    {
        $weatherData = new OpenWeatherData();

        $this->setBasicInformation($weatherData, $data);
        $this->setMainWeatherData($weatherData, $data);
        $this->setWindData($weatherData, $data);
        $this->setAtmosphericData($weatherData, $data);
        $this->setWeatherDescription($weatherData, $data);
        $this->setCoordinates($weatherData, $data);
        $this->setTimestamps($weatherData, $data);

        return $weatherData;
    }

    /**
     * Set basic information.
     *
     * @param array<string, mixed> $data
     */
    private function setBasicInformation(OpenWeatherData $weatherData, array $data): void
    {
        $weatherData->setCityName($data['name'] ?? null);
        $weatherData->setCountry($data['sys']['country'] ?? null);
    }

    /**
     * Set main weather data.
     *
     * @param array<string, mixed> $data
     */
    private function setMainWeatherData(OpenWeatherData $weatherData, array $data): void
    {
        $mainData = $data['main'] ?? [];

        $weatherData->setTemperature($mainData['temp'] ?? null);
        $weatherData->setFeelsLike($mainData['feels_like'] ?? null);
        $weatherData->setTempMin($mainData['temp_min'] ?? null);
        $weatherData->setTempMax($mainData['temp_max'] ?? null);
        $weatherData->setPressure($mainData['pressure'] ?? null);
        $weatherData->setHumidity($mainData['humidity'] ?? null);
    }

    /**
     * Set wind data.
     *
     * @param array<string, mixed> $data
     */
    private function setWindData(OpenWeatherData $weatherData, array $data): void
    {
        $windData = $data['wind'] ?? [];

        $weatherData->setWindSpeed($windData['speed'] ?? null);
        $weatherData->setWindDirection($windData['deg'] ?? null);
    }

    /**
     * Set atmospheric data.
     *
     * @param array<string, mixed> $data
     */
    private function setAtmosphericData(OpenWeatherData $weatherData, array $data): void
    {
        $weatherData->setVisibility($data['visibility'] ?? null);
        $weatherData->setCloudiness($data['clouds']['all'] ?? null);
    }

    /**
     * Set weather description.
     *
     * @param array<string, mixed> $data
     */
    private function setWeatherDescription(OpenWeatherData $weatherData, array $data): void
    {
        $weatherInfo = $data['weather'][0] ?? [];

        $weatherData->setWeatherDescription($weatherInfo['description'] ?? null);
        $weatherData->setWeatherMain($weatherInfo['main'] ?? null);
        $weatherData->setWeatherIcon($weatherInfo['icon'] ?? null);
    }

    /**
     * Set coordinates.
     *
     * @param array<string, mixed> $data
     */
    private function setCoordinates(OpenWeatherData $weatherData, array $data): void
    {
        $coordData = $data['coord'] ?? [];

        $weatherData->setLatitude($coordData['lat'] ?? null);
        $weatherData->setLongitude($coordData['lon'] ?? null);
    }

    /**
     * Set timestamps.
     *
     * @param array<string, mixed> $data
     */
    private function setTimestamps(OpenWeatherData $weatherData, array $data): void
    {
        $sysData = $data['sys'] ?? [];

        $weatherData->setCreatedAt(new DateTime());
        $weatherData->setTimezone($data['timezone'] ?? null);
        $weatherData->setTimestamp(isset($data['dt']) ? (new DateTime())->setTimestamp($data['dt']) : null);
        $weatherData->setSunrise(
            isset($sysData['sunrise']) ? (new DateTime())->setTimestamp($sysData['sunrise']) : null
        );
        $weatherData->setSunset(isset($sysData['sunset']) ? (new DateTime())->setTimestamp($sysData['sunset']) : null);
    }
}
