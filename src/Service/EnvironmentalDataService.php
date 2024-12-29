<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use DateTime;
use InvalidArgumentException;

class EnvironmentalDataService
{
    private const REQUIRED_FIELDS = ['temperature', 'humidity', 'pressure', 'co2', 'created'];

    public function __construct(
        private readonly EnvironmentalDataRepository $repository,
        private readonly ValidatorInterface $validator
    ) {}

    public function saveEnvironmentalData(array $data): void
    {
        if (!$this->hasAllRequiredFields($data)) {
            throw new InvalidArgumentException('Incomplete data provided.');
        }

        $environmentalData = $this->createEnvironmentalDataFromArray($data);

        $errors = $this->validator->validate($environmentalData);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new ValidatorException(implode('; ', $errorMessages));
        }

        $this->repository->save($environmentalData);
    }

    private function hasAllRequiredFields(array $data): bool
    {
        return !array_diff(self::REQUIRED_FIELDS, array_keys($data));
    }

    private function createEnvironmentalDataFromArray(array $data): EnvironmentalData
    {
        $measuredAt = DateTime::createFromFormat('Y-m-d H:i:s', $data['created']);
        if (!$measuredAt) {
            throw new InvalidArgumentException('Invalid date format for "created" field.');
        }

        $environmentalData = new EnvironmentalData();
        $environmentalData->setTemperature((float)$data['temperature']);
        $environmentalData->setHumidity((float)$data['humidity']);
        $environmentalData->setPressure((float)$data['pressure']);
        $environmentalData->setCo2((float)$data['co2']);
        $environmentalData->setMeasuredAt($measuredAt);
        $environmentalData->setCreatedAt(new DateTime('now'));

        return $environmentalData;
    }
}