<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Environmental Data API Service.
 */
class EnvironmentalDataApiService
{
    /** The required fields */
    private const REQUIRED_FIELDS = ['temperature', 'humidity', 'pressure', 'co2', 'created'];

    /** The date format */
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EnvironmentalDataRepository $repository,
        private readonly EnvironmentalDataNotificationService $notificationService,
    ) {
    }

    /**
     * Save environmental data from API response data.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException if required fields are missing or data is invalid
     * @throws InvalidArgumentException if validation fails
     */
    public function saveEnvironmentalData(array $data): void
    {
        $this->validateRequiredFields($data);

        $environmentalData = $this->createEnvironmentalData($data);

        $this->validateEnvironmentalData($environmentalData);

        $this->notificationService->notifyBasedOnCo2Levels(
            $environmentalData,
            $this->repository->getLastEntry()
        );

        $this->repository->save($environmentalData);
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
     * Validate environmental data object using Symfony validator.
     *
     * @throws InvalidArgumentException if validation fails
     */
    private function validateEnvironmentalData(EnvironmentalData $environmentalData): void
    {
        $errors = $this->validator->validate($environmentalData);

        if (\count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }
    }

    /**
     * Create environmental data object from API response data.
     *
     * @param array<string, mixed> $data
     */
    private function createEnvironmentalData(array $data): EnvironmentalData
    {
        $created = DateTime::createFromFormat(self::DATE_FORMAT, $data['created']);

        if (false === $created) {
            throw new InvalidArgumentException('Invalid date format for "created". Expected format: '.self::DATE_FORMAT);
        }

        return new EnvironmentalData(
            (float) $data['temperature'],
            (float) $data['humidity'],
            (float) $data['pressure'],
            (float) $data['co2'],
            $created,
            new DateTime('now')
        );
    }
}
