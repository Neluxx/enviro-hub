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
        static::assertEmpty($result['pressure']);
        static::assertEmpty($result['co2']);
    }

    /**
     * Test getChartData returns formatted data for today range.
     */
    public function testGetChartDataReturnsTodayData(): void
    {
        $data = [
            new EnvironmentalData(
                temperature: 22.5,
                humidity: 65.0,
                pressure: 1013.25,
                carbonDioxide: 450.0,
                measuredAt: new DateTime('2024-01-15 10:00:00'),
            ),
            new EnvironmentalData(
                temperature: 23.0,
                humidity: 64.5,
                pressure: 1013.50,
                carbonDioxide: 455.0,
                measuredAt: new DateTime('2024-01-15 11:00:00'),
            ),
            new EnvironmentalData(
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
        static::assertEquals(['2024-01-15 10:00', '2024-01-15 11:00', '2024-01-15 12:00'], $result['labels']);
        static::assertEquals([22.5, 23.0, 23.5], $result['temperature']);
        static::assertEquals([65.0, 64.5, 64.0], $result['humidity']);
        static::assertEquals([1013.25, 1013.50, 1013.75], $result['pressure']);
        static::assertEquals([450.0, 455.0, 460.0], $result['co2']);
    }

    /**
     * Test getChartData handles null CO2 values correctly.
     */
    public function testGetChartDataHandlesNullCo2Values(): void
    {
        $data = [
            new EnvironmentalData(
                temperature: 22.0,
                humidity: 60.0,
                pressure: 1012.0,
                carbonDioxide: null,
                measuredAt: new DateTime('2024-01-15 10:00:00'),
            ),
            new EnvironmentalData(
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
     * Test getChartData aggregates data when exceeding max data points.
     */
    public function testGetChartDataAggregatesLargeDataSets(): void
    {
        // Create 300 data points (exceeds MAX_DATA_POINTS of 250)
        $data = [];

        for ($index = 0; $index < 300; ++$index) {
            $data[] = new EnvironmentalData(
                temperature: 20.0 + ($index * 0.01),
                humidity: 60.0 + ($index * 0.01),
                pressure: 1010.0 + ($index * 0.01),
                carbonDioxide: 400.0 + ($index * 0.1),
                measuredAt: new DateTime(\sprintf('2024-01-15 %02d:%02d:00', (int) ($index / 60), $index % 60)),
            );
        }

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->willReturn($data);

        $result = $this->service->getChartData('today');

        // Should be aggregated to around 250 or fewer points
        static::assertLessThanOrEqual(250, \count($result['labels']));
        static::assertGreaterThan(0, \count($result['labels']));
        static::assertCount(\count($result['labels']), $result['temperature']);
        static::assertCount(\count($result['labels']), $result['humidity']);
        static::assertCount(\count($result['labels']), $result['pressure']);
        static::assertCount(\count($result['labels']), $result['co2']);
    }

    /**
     * Test getChartData for week range.
     */
    public function testGetChartDataForWeekRange(): void
    {
        $data = [
            new EnvironmentalData(
                temperature: 20.0,
                humidity: 70.0,
                pressure: 1010.0,
                carbonDioxide: 400.0,
                measuredAt: new DateTime('2024-01-08 10:00:00'),
            ),
            new EnvironmentalData(
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
     * Test getChartData for month range.
     */
    public function testGetChartDataForMonthRange(): void
    {
        $data = [
            new EnvironmentalData(
                temperature: 18.0,
                humidity: 75.0,
                pressure: 1008.0,
                carbonDioxide: 380.0,
                measuredAt: new DateTime('2023-12-20 10:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(function ($date) {
                    // Should be approximately 1 month ago
                    $now = new DateTime();
                    $diff = $now->diff($date);

                    return $diff->days >= 28 && $diff->days <= 32;
                }),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn($data);

        $result = $this->service->getChartData('month');

        static::assertCount(1, $result['labels']);
        static::assertEquals([18.0], $result['temperature']);
    }

    /**
     * Test getChartData for year range.
     */
    public function testGetChartDataForYearRange(): void
    {
        $data = [
            new EnvironmentalData(
                temperature: 15.0,
                humidity: 80.0,
                pressure: 1005.0,
                carbonDioxide: 350.0,
                measuredAt: new DateTime('2023-01-20 10:00:00'),
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->with(
                $this->callback(function ($date) {
                    // Should be approximately 1 year ago
                    $now = new DateTime();
                    $diff = $now->diff($date);

                    return $diff->days >= 360 && $diff->days <= 370;
                }),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn($data);

        $result = $this->service->getChartData('year');

        static::assertCount(1, $result['labels']);
        static::assertEquals([15.0], $result['temperature']);
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
                    $today = (new DateTime())->setTime(0, 0);
                    $diff = $today->diff($date);

                    return $diff->s < 5; // Within 5 seconds
                }),
                $this->callback(fn ($date) => $date instanceof DateTime)
            )
            ->willReturn([]);

        $result = $this->service->getChartData('invalid_range');

        static::assertEmpty($result['labels']);
    }

    /**
     * Test aggregated data is rounded to 2 decimal places.
     */
    public function testAggregatedDataIsRounded(): void
    {
        // Create data that will be aggregated
        $data = [];

        for ($index = 0; $index < 300; ++$index) {
            $data[] = new EnvironmentalData(
                temperature: 20.123456,
                humidity: 60.987654,
                pressure: 1010.555555,
                carbonDioxide: 400.777777,
                measuredAt: new DateTime(\sprintf('2024-01-15 %02d:%02d:00', (int) ($index / 60), $index % 60)),
            );
        }

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->willReturn($data);

        $result = $this->service->getChartData('today');

        // Check that values are rounded to 2 decimal places
        foreach ($result['temperature'] as $temp) {
            static::assertEquals($temp, round($temp, 2));
        }

        foreach ($result['humidity'] as $humidity) {
            static::assertEquals($humidity, round($humidity, 2));
        }

        foreach ($result['pressure'] as $pressure) {
            static::assertEquals($pressure, round($pressure, 2));
        }
    }

    /**
     * Test aggregation maintains data trends.
     */
    public function testAggregationMaintainsDataTrends(): void
    {
        // Create ascending temperature data
        $data = [];

        for ($index = 0; $index < 300; ++$index) {
            $data[] = new EnvironmentalData(
                temperature: 20.0 + $index,
                humidity: 60.0,
                pressure: 1010.0,
                carbonDioxide: 400.0,
                measuredAt: new DateTime(\sprintf('2024-01-15 %02d:%02d:00', (int) ($index / 60), $index % 60)),
            );
        }

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->willReturn($data);

        $result = $this->service->getChartData('today');

        // Verify the trend is maintained (temperatures should generally increase)
        $temperatures = $result['temperature'];
        static::assertGreaterThan($temperatures[0], $temperatures[\count($temperatures) - 1]);
    }

    /**
     * Test that aggregation handles mixed null and non-null CO2 values.
     */
    public function testAggregationHandlesMixedCo2Values(): void
    {
        $data = [];

        for ($index = 0; $index < 300; ++$index) {
            $data[] = new EnvironmentalData(
                temperature: 20.0,
                humidity: 60.0,
                pressure: 1010.0,
                carbonDioxide: $index % 2 === 0 ? 400.0 : null, // Alternate null and non-null
                measuredAt: new DateTime(\sprintf('2024-01-15 %02d:%02d:00', (int) ($index / 60), $index % 60)),
            );
        }

        $this->repository->expects($this->once())
            ->method('findByDateRange')
            ->willReturn($data);

        $result = $this->service->getChartData('today');

        // Should have some non-null CO2 values from aggregation
        $nonNullCo2 = array_filter($result['co2'], fn ($value) => $value !== null);
        static::assertGreaterThan(0, \count($nonNullCo2));
    }
}
