<?php

declare(strict_types=1);

namespace App\Api\SensorData\Repository;

use App\Api\SensorData\Entity\SensorData;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Sensor Data Repository.
 *
 * @extends ServiceEntityRepository<SensorData>
 */
class SensorDataRepository extends ServiceEntityRepository
{
    private const int DEFAULT_LIMIT = 1000;
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, SensorData::class);
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
     * Get the last SensorData entry for a specific node UUID.
     *
     * @param string $nodeUuid The node UUID to filter by
     */
    public function getLastEntryByNodeUuid(string $nodeUuid): ?SensorData
    {
        return $this->findOneBy(['nodeUuid' => $nodeUuid], ['id' => 'DESC']);
    }

    /**
     * Get the latest SensorData entries for a specific node UUID.
     *
     * @param string $nodeUuid The node UUID to filter by
     * @param int $limit The maximum number of entries to return
     *
     * @return SensorData[]
     */
    public function getLatestEntriesByNodeUuid(string $nodeUuid, int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->findBy(['nodeUuid' => $nodeUuid], ['id' => 'DESC'], $limit);
    }

    /**
     * Get the latest SensorData entries by date range for a specific node UUID.
     *
     * @param string $nodeUuid The node UUID to filter by
     * @param DateTime $startDate The start date of entries to return
     * @param DateTime $endDate The end date of entries to return
     *
     * @return SensorData[]
     */
    public function findByNodeUuidAndDateRange(string $nodeUuid, DateTime $startDate, DateTime $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.nodeUuid = (:nodeUuid)')
            ->andWhere('e.measuredAt >= :startDate')
            ->andWhere('e.measuredAt <= :endDate')
            ->setParameter('nodeUuid', $nodeUuid)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.measuredAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the last SensorData entry for multiple node UUIDs.
     *
     * @param string[] $nodeUuids Array of node UUIDs
     *
     * @return array<string, SensorData|null> Array indexed by node UUID
     */
    public function getLastEntriesByNodeUuids(array $nodeUuids): array
    {
        $lastEntries = [];

        foreach ($nodeUuids as $uuid) {
            $lastEntries[$uuid] = $this->getLastEntryByNodeUuid($uuid);
        }

        return $lastEntries;
    }
}
