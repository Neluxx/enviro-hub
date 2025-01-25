<?php

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;

class EnvironmentalDataApiService
{
    private EnvironmentalDataRepository $repository;
    private EnvironmentalDataNotificationService $notificationService;

    public function __construct(
        EnvironmentalDataRepository $repository,
        EnvironmentalDataNotificationService $notificationService,
    ) {
        $this->repository = $repository;
        $this->notificationService = $notificationService;
    }

    public function saveEnvironmentalData(array $data): void
    {
        $this->validate($data);
        $environmentalData = $this->createEnvironmentalData($data);

        $this->notificationService->notifyBasedOnCo2Levels(
            $environmentalData,
            $this->repository->getLastEntry()
        );

        $this->repository->save($environmentalData);
    }

    private function validate(array $data): void
    {
        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $data['created'])) {
            throw new \InvalidArgumentException('Invalid date format for "created" field.');
        }

        if (!is_numeric($data['temperature'])) {
            throw new \InvalidArgumentException('Invalid type for "temperature". Expected a numeric value.');
        }

        if (!is_numeric($data['humidity'])) {
            throw new \InvalidArgumentException('Invalid type for "humidity". Expected a numeric value.');
        }

        if (!is_numeric($data['pressure'])) {
            throw new \InvalidArgumentException('Invalid type for "pressure". Expected a numeric value.');
        }

        if (!is_numeric($data['co2'])) {
            throw new \InvalidArgumentException('Invalid type for "carbon dioxide". Expected a numeric value.');
        }
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
