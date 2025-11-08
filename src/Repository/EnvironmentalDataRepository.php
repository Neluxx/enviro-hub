<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EnvironmentalData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Environmental Data Repository.
 */
class EnvironmentalDataRepository
{
    /** Default limit */
    private const DEFAULT_LIMIT = 1000;

    private EntityManagerInterface $entityManager;

    /** @var EntityRepository<EnvironmentalData> */
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
        return $this->repository->findOneBy([], $this->getDescendingOrderById());
    }

    /**
     * Get the latest EnvironmentalData entries.
     *
     * @param int $limit The maximum number of entries to return
     *
     * @return EnvironmentalData[]
     */
    public function getLatestEntries(int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->repository->findBy([], $this->getDescendingOrderById(), $limit);
    }

    /**
     * Get descending order by ID.
     *
     * @return array<string, string>
     */
    private function getDescendingOrderById(): array
    {
        return ['id' => 'DESC'];
    }
}
