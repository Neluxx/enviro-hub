<?php

namespace App\Repository;

use App\Entity\EnvironmentalData;
use Doctrine\ORM\EntityManagerInterface;

class EnvironmentalDataRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(EnvironmentalData $environmentalData): void
    {
        $this->entityManager->persist($environmentalData);
        $this->entityManager->flush();
    }
}