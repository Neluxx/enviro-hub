<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OpenWeatherData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Open Weather Data Repository.
 */
class OpenWeatherDataRepository
{
    /** Default limit */
    private const DEFAULT_LIMIT = 1000;
    private EntityManagerInterface $entityManager;

    /** @var EntityRepository<OpenWeatherData> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(OpenWeatherData::class);
    }

    /**
     * Save OpenWeatherData entry.
     */
    public function save(OpenWeatherData $openWeatherData): void
    {
        $this->entityManager->persist($openWeatherData);
        $this->entityManager->flush();
    }

    /**
     * Get the last OpenWeatherData entry.
     */
    public function getLastEntry(): ?OpenWeatherData
    {
        return $this->repository->findOneBy([], $this->getDescendingOrderById());
    }

    /**
     * Get the latest OpenWeatherData entries.
     *
     * @return OpenWeatherData[]
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
