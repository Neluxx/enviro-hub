<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use App\Service\DashboardService;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for DashboardService.
 */
class DashboardServiceTest extends TestCase
{
    private EnvironmentalDataRepository&MockObject $repository;
    private DashboardService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EnvironmentalDataRepository::class);
        $this->service = new DashboardService($this->repository);
    }

    /**
     * Test getChartData returns empty arrays when no data exists.
     */
    public function testGetChartDataReturnsEmptyArraysWhenNoData(): void
    {
        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->willReturn([]);

        $result = $this->service->getChartData('today');

        static::assertEmpty($result['labels']);
        static::assertEmpty($result['temperature']);
        static::assertEmpty($result['humidity']);
        static::assertEmpty($result['co2']);
    }

    /**
     * Test getChartData returns formatted data for today range.
     */
    public function testGetChartDataReturnsTodayData(): void
    {
        $data = [
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 22.5,
                humidity: 65.0,
                pressure: 1013.25,
                carbonDioxide: 450.0,
                measuredAt: new DateTime('2024-01-15 10:00:00'),
            ),
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 23.0,
                humidity: 64.5,
                pressure: 1013.50,
                carbonDioxide: 455.0,
                measuredAt: new DateTime('2024-01-15 11:00:00'),
            ),
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 23.5,
                humidity: 64.0,
                pressure: 1013.75,
                carbonDioxide: 460.0,
                measuredAt: new DateTime('2024-01-15 12:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(fn ($date) => $date instanceof DateTime),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn($data);

        $result = $this->service->getChartData('today');

        static::assertCount(3, $result['labels']);
        static::assertEquals(['15.01 10:00', '15.01 11:00', '15.01 12:00'], $result['labels']);
        static::assertEquals([22.5, 23.0, 23.5], $result['temperature']);
        static::assertEquals([65.0, 64.5, 64.0], $result['humidity']);
        static::assertEquals([450.0, 455.0, 460.0], $result['co2']);
    }

    /**
     * Test getChartData handles null CO2 values correctly.
     */
    public function testGetChartDataHandlesNullCo2Values(): void
    {
        $data = [
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 22.0,
                humidity: 60.0,
                pressure: 1012.0,
                carbonDioxide: null,
                measuredAt: new DateTime('2024-01-15 10:00:00'),
            ),
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 22.5,
                humidity: 61.0,
                pressure: 1012.5,
                carbonDioxide: 450.0,
                measuredAt: new DateTime('2024-01-15 11:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->willReturn($data);

        $result = $this->service->getChartData('today');

        static::assertNull($result['co2'][0]);
        static::assertEquals(450.0, $result['co2'][1]);
    }

    /**
     * Test getChartData for week range.
     */
    public function testGetChartDataForWeekRange(): void
    {
        $data = [
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 20.0,
                humidity: 70.0,
                pressure: 1010.0,
                carbonDioxide: 400.0,
                measuredAt: new DateTime('2024-01-08 10:00:00'),
            ),
            new EnvironmentalData(
                nodeUuid: 'test-node-uuid',
                temperature: 21.0,
                humidity: 68.0,
                pressure: 1012.0,
                carbonDioxide: 420.0,
                measuredAt: new DateTime('2024-01-10 10:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(function ($date) {
                    // Should be approximately 7 days ago
                    $now = new DateTime();
                    $diff = $now->diff($date);

                    return $diff->days >= 6 && $diff->days <= 8;
                }),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn($data);

        $result = $this->service->getChartData('week');

        static::assertCount(2, $result['labels']);
        static::assertEquals([20.0, 21.0], $result['temperature']);
    }

    /**
     * Test getChartData defaults to today for unknown range.
     */
    public function testGetChartDataDefaultForUnknownRange(): void
    {
        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(function ($date) {
                    // Should be start of today
                    $today = (new DateTime())->modify('-24 hours');
                    $diff = $today->diff($date);

                    return $diff->s < 5; // Within 5 seconds
                }),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn([]);

        $result = $this->service->getChartData('invalid_range');

        static::assertEmpty($result['labels']);
    }
}
