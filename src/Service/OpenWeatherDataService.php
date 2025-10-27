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
        'weather_main',
        'weather_description',
        'weather_icon',
        'temperature',
        'feels_like',
        'temp_min',
        'temp_max',
        'pressure',
        'humidity',
        'visibility',
        'wind_speed',
        'wind_deg',
        'clouds',
        'created_at',
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
        return new OpenWeatherData(
            $data['weather_main'],
            $data['weather_description'],
            $data['weather_icon'],
            $data['temperature'],
            $data['feels_like'],
            $data['temp_min'],
            $data['temp_max'],
            $data['pressure'],
            $data['humidity'],
            $data['visibility'],
            $data['wind_speed'],
            $data['wind_deg'],
            $data['clouds'],
            new DateTime($data['created_at'])
        );
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
}
