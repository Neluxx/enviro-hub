<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OpenWeatherData;
use App\Repository\OpenWeatherDataRepository;
use DateTime;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Open Weather Data Service.
 */
class OpenWeatherDataService
{
    /** The required fields */
    private const REQUIRED_FIELDS = [
        'name',
        'coord.lat',
        'coord.lon',
        'main.temp',
        'main.feels_like',
        'main.temp_min',
        'main.temp_max',
        'main.pressure',
        'main.humidity',
        'wind.speed',
        'wind.deg',
        'visibility',
        'clouds.all',
        'weather.0.main',
        'weather.0.description',
        'weather.0.icon',
        'sys.country',
        'sys.sunrise',
        'sys.sunset',
        'timezone',
        'dt',
    ];

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly OpenWeatherDataRepository $repository,
    ) {
    }

    /**
     * Save open weather data from API response data.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException if required fields are missing or data is invalid
     */
    public function saveOpenWeatherData(array $data): void
    {
        $this->validateRequiredFields($data);

        $openWeatherData = $this->createOpenWeatherDataFromArray($data);

        $this->validateOpenWeatherData($openWeatherData);

        $this->repository->save($openWeatherData);
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
     * Validate that all required fields are present in the data array.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException if required fields are missing
     */
    private function validateRequiredFields(array $data): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!$this->hasNestedKey($data, $field)) {
                throw new InvalidArgumentException("Undefined array key \"$field\"");
            }
        }
    }

    /**
     * Check if a nested key exists in an array using dot notation.
     *
     * @param array<string, mixed> $data
     */
    private function hasNestedKey(array $data, string $path): bool
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            if (!\array_key_exists($key, $data)) {
                return false;
            }
            $data = $data[$key];
        }

        return true;
    }

    /**
     * Validate environmental data object using Symfony validator.
     *
     * @throws InvalidArgumentException if validation fails
     */
    private function validateOpenWeatherData(OpenWeatherData $environmentalData): void
    {
        $errors = $this->validator->validate($environmentalData);

        if (\count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }
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
        $weatherData->setTimestamp((new DateTime())->setTimestamp($data['dt']));
        $weatherData->setSunrise((new DateTime())->setTimestamp($sysData['sunrise']));
        $weatherData->setSunset((new DateTime())->setTimestamp($sysData['sunset']));
    }
}
