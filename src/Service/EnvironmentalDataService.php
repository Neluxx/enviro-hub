<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

/**
 * Service responsible for handling environmental data operations such as validation, persistence,
 * and ensuring required fields are provided within the data input.
 */
class EnvironmentalDataService
{
    private EnvironmentalDataRepository $repository;
    private ValidatorInterface $validator;

    private const REQUIRED_FIELDS = ['temperature', 'humidity', 'pressure', 'co2', 'created'];

    public function __construct(
        EnvironmentalDataRepository $repository,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * Saves environmental data after validating its structure and content.
     *
     * This method checks if the provided data is of the correct type and includes all required fields.
     * If the data is invalid or incomplete, an error message is returned.
     * If the data passes validation, it is converted into an entity and saved to the database.
     *
     * Validation errors, if any, are returned as a concatenated message.
     *
     * @param mixed $data The environmental data to be processed.
     *
     * @return array An associative array indicating success or failure and an accompanying message.
     */
    public function saveEnvironmentalData(mixed $data): array
    {
        if (!is_array($data)) {
            return ['success' => false, 'message' => 'Invalid data'];
        }

        if (!$this->hasAllRequiredFields($data)) {
            return ['success' => false, 'message' => 'Some required fields are missing'];
        }

        $environmentalData = $this->createEnvironmentalDataFromArray($data);

        $validationErrors = $this->validator->validate($environmentalData);
        if (count($validationErrors) > 0) {
            $errors = [];
            foreach ($validationErrors as $error) {
                $errors[] = $error->getMessage();
            }

            return ['success' => false, 'message' => implode('; ', $errors)];
        }

        $this->repository->save($environmentalData);

        return ['success' => true];
    }

    /**
     * Verifies if all required fields are present in the given data array.
     *
     * @param array $data The data array to be validated.
     *
     * @return bool True if all required fields are present, false otherwise.
     */
    public function hasAllRequiredFields(array $data): bool
    {
        return !array_diff(self::REQUIRED_FIELDS, array_keys($data));
    }

    /**
     * Creates an EnvironmentalData entity from an associative array of data.
     *
     * @param array $data The input data array used to create the EnvironmentalData entity.
     *
     * @return EnvironmentalData The populated EnvironmentalData object.
     * @throws InvalidArgumentException If the "created" field does not have a valid date format.
     *
     */
    private function createEnvironmentalDataFromArray(array $data): EnvironmentalData
    {
        $environmentalData = new EnvironmentalData();
        $environmentalData->setTemperature((float)$data['temperature']);
        $environmentalData->setHumidity((float)$data['humidity']);
        $environmentalData->setPressure((float)$data['pressure']);
        $environmentalData->setCo2((float)$data['co2']);

        $measuredAt = DateTime::createFromFormat('Y-m-d H:i:s', $data['created']);
        if (!$measuredAt) {
            throw new InvalidArgumentException('Invalid date format for "created" field.');
        }

        $environmentalData->setMeasuredAt($measuredAt);
        $environmentalData->setCreatedAt(new DateTime('now'));

        return $environmentalData;
    }
}