<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EnvironmentalDataService
{
    private EnvironmentalDataRepository $repository;
    private ValidatorInterface $validator;

    public function __construct(
        EnvironmentalDataRepository $repository,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function saveEnvironmentalData(array $data): array
    {
        $environmentalData = new EnvironmentalData();
        $environmentalData->setTemperature((float) $data['temperature']);
        $environmentalData->setHumidity((float) $data['humidity']);
        $environmentalData->setPressure((float) $data['pressure']);
        $environmentalData->setCo2((float) $data['co2']);
        $environmentalData->setMeasuredAt(new DateTime($data['created']));
        $environmentalData->setCreatedAt(new DateTime());

        $errors = $this->validator->validate($environmentalData);
        if (count($errors) > 0) {
            return ['success' => false, 'message' => $errors[0]->getMessage()];
        }

        $this->repository->save($environmentalData);

        return ['success' => true];
    }
}