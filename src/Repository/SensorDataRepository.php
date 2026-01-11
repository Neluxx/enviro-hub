<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SensorData;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Sensor Data Repository.
 */
class SensorDataRepository
{
    /** Default limit */
    private const DEFAULT_LIMIT = 1000;

    private EntityManagerInterface $entityManager;

    /** @var EntityRepository<SensorData> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(SensorData::class);
    }

    /**
     * Save SensorData entry.
     */
    public function save(SensorData $sensorData): void
    {
        $this->entityManager->persist($sensorData);
        $this->entityManager->flush();
    }

    /**
     * Get the last SensorData entry.
     */
    public function getLastEntry(): ?SensorData
    {
        return $this->repository->findOneBy([], $this->getDescendingOrderById());
    }

    /**
     * Get the latest SensorData entries.
     *
     * @param int $limit The maximum number of entries to return
     *
     * @return SensorData[]
     */
    public function getLatestEntries(int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->repository->findBy([], $this->getDescendingOrderById(), $limit);
    }

    /**
     * Get the latest SensorData entries by date range.
     *
     * @param DateTime $startDate The start date of entries to return
     * @param DateTime $endDate The end date of entries to return
     *
     * @return SensorData[]
     */
    public function findByDateRange(DateTime $startDate, DateTime $endDate): array
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.measuredAt >= :startDate')
            ->andWhere('e.measuredAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.measuredAt', 'ASC')
            ->getQuery()
            ->getResult();
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
