<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Environmental Data API Service.
 */
class EnvironmentalDataApiService
{
    private ValidatorInterface $validator;
    private EnvironmentalDataRepository $repository;
    private EnvironmentalDataNotificationService $notificationService;

    public function __construct(
        ValidatorInterface $validator,
        EnvironmentalDataRepository $repository,
        EnvironmentalDataNotificationService $notificationService,
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->notificationService = $notificationService;
    }

    public function saveEnvironmentalData(array $data): void
    {
        $environmentalData = $this->createEnvironmentalData($data);

        $errors = $this->validator->validate($environmentalData);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->notificationService->notifyBasedOnCo2Levels(
            $environmentalData,
            $this->repository->getLastEntry()
        );

        $this->repository->save($environmentalData);
    }

    private function createEnvironmentalData(array $data): EnvironmentalData
    {
        return new EnvironmentalData(
            (float) $data['temperature'],
            (float) $data['humidity'],
            (float) $data['pressure'],
            (float) $data['co2'],
            \DateTime::createFromFormat('Y-m-d H:i:s', $data['created']),
            new \DateTime('now')
        );
    }
}
