<?php

namespace App\Repository;

use App\Entity\OpenWeatherData;
use Doctrine\ORM\EntityManagerInterface;

class OpenWeatherDataRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(OpenWeatherData $environmentalData): void
    {
        $this->entityManager->persist($environmentalData);
        $this->entityManager->flush();
    }
}