<?php

declare(strict_types=1);

namespace App\Tests\Dashboard\Service;

use App\Api\SensorData\Entity\SensorData;
use App\Api\SensorData\Repository\SensorDataRepository;
use App\Dashboard\Service\DashboardService;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;

/**
 * Test class for DashboardService.
 */
class DashboardServiceTest extends TestCase
{
    private SensorDataRepository&MockObject $repository;
    private ChartBuilderInterface&MockObject $chartBuilder;
    private DashboardService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SensorDataRepository::class);
        $this->chartBuilder = $this->createMock(ChartBuilderInterface::class);
        $this->service = new DashboardService($this->repository, $this->chartBuilder);
    }

    /**
     * Test getChartData returns empty arrays when no data exists.
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testGetChartDataReturnsEmptyArraysWhenNoData(): void
    {
        $this->repository->expects($this->once())
            ->method('findByNodeUuidAndDateRange')
            ->willReturn([]);

        $result = $this->service->getChartDataByNodeUuid('test-node-uuid', '-24 hours');

        static::assertEmpty($result['labels']);
        static::assertEmpty($result['temperature']);
        static::assertEmpty($result['humidity']);
        static::assertEmpty($result['co2']);
    }

    /**
     * Test getChartData returns formatted data for today range.
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testGetChartDataReturnsTodayData(): void
    {
        $data = [
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 22.5,
                humidity: 65.0,
                pressure: 1013.25,
                carbonDioxide: 450.0,
                measuredAt: new DateTimeImmutable('2024-01-15 10:00:00'),
            ),
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 23.0,
                humidity: 64.5,
                pressure: 1013.50,
                carbonDioxide: 455.0,
                measuredAt: new DateTimeImmutable('2024-01-15 11:00:00'),
            ),
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 23.5,
                humidity: 64.0,
                pressure: 1013.75,
                carbonDioxide: 460.0,
                measuredAt: new DateTimeImmutable('2024-01-15 12:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByNodeUuidAndDateRange')
            ->with(
                'test-node-uuid',
                $this->callback(fn ($date) => $date instanceof DateTime),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn($data);

        $result = $this->service->getChartDataByNodeUuid('test-node-uuid', '-24 hours');

        static::assertCount(3, $result['labels']);
        static::assertEquals(['15.01 10:00', '15.01 11:00', '15.01 12:00'], $result['labels']);
        static::assertEquals([23, 23, 24], $result['temperature']);
        static::assertEquals([65, 65, 64], $result['humidity']);
        static::assertEquals([450, 455, 460], $result['co2']);
    }

    /**
     * Test getChartData handles null CO2 values correctly.
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testGetChartDataHandlesNullCo2Values(): void
    {
        $data = [
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 22.0,
                humidity: 60.0,
                pressure: 1012.0,
                carbonDioxide: null,
                measuredAt: new DateTimeImmutable('2024-01-15 10:00:00'),
            ),
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 22.5,
                humidity: 61.0,
                pressure: 1012.5,
                carbonDioxide: 450.0,
                measuredAt: new DateTimeImmutable('2024-01-15 11:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByNodeUuidAndDateRange')
            ->willReturn($data);

        $result = $this->service->getChartDataByNodeUuid('test-node-uuid', '-24 hours');

        static::assertNull($result['co2'][0]);
        static::assertEquals(450.0, $result['co2'][1]);
    }

    /**
     * Test getChartData for week range.
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testGetChartDataForWeekRange(): void
    {
        $data = [
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 20.0,
                humidity: 70.0,
                pressure: 1010.0,
                carbonDioxide: 400.0,
                measuredAt: new DateTimeImmutable('2024-01-08 10:00:00'),
            ),
            new SensorData(
                nodeUuid: 'test-node-uuid',
                temperature: 21.0,
                humidity: 68.0,
                pressure: 1012.0,
                carbonDioxide: 420.0,
                measuredAt: new DateTimeImmutable('2024-01-10 10:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByNodeUuidAndDateRange')
            ->with(
                'test-node-uuid',
                $this->callback(function ($date) {
                    // Should be approximately 7 days ago
                    $now = new DateTime();
                    $diff = $now->diff($date);

                    return $diff->days >= 6 && $diff->days <= 8;
                }),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn($data);

        $result = $this->service->getChartDataByNodeUuid('test-node-uuid', '-7 days');

        static::assertCount(2, $result['labels']);
        static::assertEquals([20.0, 21.0], $result['temperature']);
    }
}
