<?php

declare(strict_types=1);

namespace App\Api\SensorData\Service;

use App\Api\SensorData\Entity\SensorData;
use App\Api\SensorData\Repository\SensorDataRepository;
use App\Notification\Service\NotificationService;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Sensor Data API Service.
 */
class SensorDataService
{
    /** The required fields */
    private const array REQUIRED_FIELDS = ['uuid', 'temperature', 'humidity', 'pressure', 'created_at'];

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly SensorDataRepository $repository,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Save sensor data from API response data.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException if required fields are missing or data is invalid
     * @throws InvalidArgumentException if validation fails
     */
    public function saveSensorData(array $data): void
    {
        $this->validateRequiredFields($data);

        $sensorData = $this->createSensorData($data);

        $this->validateSensorData($sensorData);

        $lastEntry = $this->repository->getLastEntryByNodeUuid($sensorData->getNodeUuid());
        $this->notificationService->notifyBasedOnCo2Levels($sensorData, $lastEntry);

        $this->repository->save($sensorData);
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
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Undefined array key \"$field\"");
            }
        }
    }

    /**
     * Validate sensor data object using Symfony validator.
     *
     * @throws InvalidArgumentException if validation fails
     */
    private function validateSensorData(SensorData $sensorData): void
    {
        $errors = $this->validator->validate($sensorData);

        if (\count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }
    }

    /**
     * Create sensor data object from API response data.
     *
     * @param array<string, mixed> $data
     */
    private function createSensorData(array $data): SensorData
    {
        return new SensorData(
            $data['uuid'],
            $data['temperature'],
            $data['humidity'],
            $data['pressure'],
            $data['co2'] ?? null,
            new DateTimeImmutable($data['created_at'])
        );
    }
}
