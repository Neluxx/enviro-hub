<?php

namespace App\Repository;

use App\Entity\EnvironmentalData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Environmental Data Repository.
 */
class EnvironmentalDataRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(EnvironmentalData::class);
    }

    /**
     * Save EnvironmentalData entry.
     */
    public function save(EnvironmentalData $environmentalData): void
    {
        $this->entityManager->persist($environmentalData);
        $this->entityManager->flush();
    }

    /**
     * Get the last EnvironmentalData entry.
     */
    public function getLastEntry(): ?EnvironmentalData
    {
        return $this->repository->findOneBy([], ['id' => 'DESC']);
    }

    /**
     * Get the latest EnvironmentalData entries.
     *
     * @return EnvironmentalData[]
     */
    public function getLatestEntries(): array
    {
        return $this->repository->findBy([], ['id' => 'DESC'], 1000);
    }
}
