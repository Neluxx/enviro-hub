<?php

namespace App\Repository;

use App\Entity\EnvironmentalData;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Environmental Data Repository
 */
class EnvironmentalDataRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Save EnvironmentalData entry.
     *
     * @param EnvironmentalData $environmentalData
     * @return void
     */
    public function save(EnvironmentalData $environmentalData): void
    {
        $this->entityManager->persist($environmentalData);
        $this->entityManager->flush();
    }

    /**
     * Get the last EnvironmentalData entry.
     *
     * @return EnvironmentalData|null
     */
    public function getLastEntry(): ?EnvironmentalData
    {
        return $this->entityManager->getRepository(EnvironmentalData::class)->findOneBy([], ['id' => 'DESC']);
    }

    /**
     * Get the latest EnvironmentalData entries.
     *
     * @return EnvironmentalData[]
     */
    public function getLatestEntries(): array
    {
        return $this->entityManager->getRepository(EnvironmentalData::class)->findBy([], ['id' => 'DESC'], 1000);
    }
}
