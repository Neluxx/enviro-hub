<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

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

    public function saveEnvironmentalData(array $data): array
    {
        $environmentalData = $this->createEnvironmentalDataFromArray($data);

        $validationErrors = $this->validator->validate($environmentalData);
        if (count($validationErrors) > 0) {
            $validationErrorMessage = $validationErrors[0]->getMessage();
            return ['success' => false, 'message' => $validationErrorMessage];
        }

        $this->repository->save($environmentalData);

        return ['success' => true];
    }

    public function hasAllRequiredFields(array $data): bool
    {
        return !array_diff(self::REQUIRED_FIELDS, array_keys($data));
    }

    private function createEnvironmentalDataFromArray(array $data): EnvironmentalData
    {
        $environmentalData = new EnvironmentalData();
        $environmentalData->setTemperature((float)$data['temperature']);
        $environmentalData->setHumidity((float)$data['humidity']);
        $environmentalData->setPressure((float)$data['pressure']);
        $environmentalData->setCo2((float)$data['co2']);
        $environmentalData->setMeasuredAt(DateTime::createFromFormat('Y-m-d H:i:s', $data['created']));
        $environmentalData->setCreatedAt(new DateTime());

        return $environmentalData;
    }
}