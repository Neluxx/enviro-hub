<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class EnvironmentalDataRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $doctrineRepository;
    private EnvironmentalDataRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->doctrineRepository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->with(EnvironmentalData::class)
            ->willReturn($this->doctrineRepository);

        $this->repository = new EnvironmentalDataRepository($this->entityManager);
    }

    public function testSaveCallsPersistAndFlush(): void
    {
        $environmentalData = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($environmentalData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($environmentalData);
    }

    public function testSaveCallsPersistBeforeFlush(): void
    {
        $environmentalData = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);
        $callOrder = [];

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'persist';
            });

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'flush';
            });

        $this->repository->save($environmentalData);

        $this->assertSame(['persist', 'flush'], $callOrder);
    }

    public function testGetLastEntryReturnsEnvironmentalData(): void
    {
        $expectedData = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);

        $this->doctrineRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC'])
            )
            ->willReturn($expectedData);

        $result = $this->repository->getLastEntry();

        $this->assertSame($expectedData, $result);
    }

    public function testGetLastEntryReturnsNullWhenNoDataExists(): void
    {
        $this->doctrineRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC'])
            )
            ->willReturn(null);

        $result = $this->repository->getLastEntry();

        $this->assertNull($result);
    }

    public function testGetLastEntryUsesCorrectOrderBy(): void
    {
        $this->doctrineRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->anything(),
                $this->callback(function ($orderBy) {
                    return isset($orderBy['id']) && $orderBy['id'] === 'DESC';
                })
            )
            ->willReturn(null);

        $this->repository->getLastEntry();
    }

    public function testGetLatestEntriesReturnsArrayOfEnvironmentalData(): void
    {
        $data1 = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);
        $data2 = $this->createEnvironmentalData(26.0, 65.0, 1014.00, 500.0);
        $data3 = $this->createEnvironmentalData(24.0, 55.0, 1012.00, 400.0);
        $expectedData = [$data1, $data2, $data3];

        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC']),
                $this->equalTo(1000)
            )
            ->willReturn($expectedData);

        $result = $this->repository->getLatestEntries();

        $this->assertSame($expectedData, $result);
    }

    public function testGetLatestEntriesWithCustomLimit(): void
    {
        $data1 = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);
        $data2 = $this->createEnvironmentalData(26.0, 65.0, 1014.00, 500.0);
        $expectedData = [$data1, $data2];

        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC']),
                $this->equalTo(50)
            )
            ->willReturn($expectedData);

        $result = $this->repository->getLatestEntries(50);

        $this->assertSame($expectedData, $result);
    }

    public function testGetLatestEntriesUsesDefaultLimitWhenNotProvided(): void
    {
        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->equalTo(1000)
            )
            ->willReturn([]);

        $this->repository->getLatestEntries();
    }

    public function testGetLatestEntriesReturnsEmptyArrayWhenNoDataExists(): void
    {
        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC']),
                $this->equalTo(1000)
            )
            ->willReturn([]);

        $result = $this->repository->getLatestEntries();

        $this->assertSame([], $result);
    }

    public function testGetLatestEntriesUsesCorrectOrderBy(): void
    {
        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->anything(),
                $this->callback(function ($orderBy) {
                    return isset($orderBy['id']) && $orderBy['id'] === 'DESC';
                }),
                $this->anything()
            )
            ->willReturn([]);

        $this->repository->getLatestEntries();
    }

    public function testGetLatestEntriesWithLimitOfOne(): void
    {
        $data = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);
        $expectedData = [$data];

        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC']),
                $this->equalTo(1)
            )
            ->willReturn($expectedData);

        $result = $this->repository->getLatestEntries(1);

        $this->assertSame($expectedData, $result);
    }

    public function testGetLatestEntriesWithLargeLimit(): void
    {
        $expectedData = [];

        for ($i = 0; $i < 10; ++$i) {
            $expectedData[] = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);
        }

        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->equalTo([]),
                $this->equalTo(['id' => 'DESC']),
                $this->equalTo(5000)
            )
            ->willReturn($expectedData);

        $result = $this->repository->getLatestEntries(5000);

        $this->assertSame($expectedData, $result);
    }

    public function testGetLatestEntriesPassesEmptyCriteriaArray(): void
    {
        $this->doctrineRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(
                $this->callback(function ($criteria) {
                    return \is_array($criteria) && empty($criteria);
                }),
                $this->anything(),
                $this->anything()
            )
            ->willReturn([]);

        $this->repository->getLatestEntries();
    }

    public function testSaveWithDifferentEnvironmentalDataInstances(): void
    {
        $data1 = $this->createEnvironmentalData(25.5, 60.0, 1013.25, 450.0);
        $data2 = $this->createEnvironmentalData(30.0, 70.0, 1015.00, 600.0);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        $this->repository->save($data1);
        $this->repository->save($data2);
    }

    private function createEnvironmentalData(
        float $temperature,
        float $humidity,
        float $pressure,
        float $co2
    ): EnvironmentalData {
        return new EnvironmentalData(
            $temperature,
            $humidity,
            $pressure,
            $co2,
            new DateTime('2025-01-15 12:00:00')
        );
    }
}
