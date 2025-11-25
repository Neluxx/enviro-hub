<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use DateTime;
use DateTimeZone;

/**
 * Dashboard Service.
 *
 * Handles business logic for dashboard data processing and aggregation.
 */
class DashboardService
{
    /** The maximum number of data points to display on the chart */
    private const MAX_DATA_POINTS = 250;

    private EnvironmentalDataRepository $repository;

    public function __construct(EnvironmentalDataRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get chart data for a specific time range.
     *
     * @return array{labels: array<string>, temperature: array<float>, humidity: array<float>, pressure: array<float>, co2: array<float|null>}
     */
    public function getChartData(string $range): array
    {
        $timezone = new DateTimeZone('Europe/Berlin');
        $endDate = new DateTime('now', $timezone);
        $startDate = $this->calculateStartDate($range, $endDate);

        $data = $this->repository->findByDateRange($startDate, $endDate);

        if (empty($data)) {
            return [
                'labels' => [],
                'temperature' => [],
                'humidity' => [],
                'pressure' => [],
                'co2' => [],
            ];
        }

        if (\count($data) > self::MAX_DATA_POINTS) {
            $data = $this->aggregateData($data, $range);
        }

        return $this->formatChartData($data);
    }

    /**
     * Calculate start date based on the range.
     */
    private function calculateStartDate(string $range, DateTime $endDate): DateTime
    {
        return match ($range) {
            'today' => (clone $endDate)->setTime(0, 0),
            'week' => (clone $endDate)->modify('-7 days'),
            'month' => (clone $endDate)->modify('-1 month'),
            'year' => (clone $endDate)->modify('-1 year'),
            default => (clone $endDate)->setTime(0, 0),
        };
    }

    /**
     * Format data into chart-ready structure.
     *
     * @param array<EnvironmentalData|array{label: string, temperature: float, humidity: float, pressure: float, co2: float|null}> $data
     *
     * @return array{labels: array<string>, temperature: array<float>, humidity: array<float>, pressure: array<float>, co2: array<float|null>}
     */
    private function formatChartData(array $data): array
    {
        $chartData = [
            'labels' => [],
            'temperature' => [],
            'humidity' => [],
            'pressure' => [],
            'co2' => [],
        ];

        foreach ($data as $entry) {
            if (\is_array($entry)) {
                // Aggregated data
                $chartData['labels'][] = $entry['label'];
                $chartData['temperature'][] = round($entry['temperature'], 2);
                $chartData['humidity'][] = round($entry['humidity'], 2);
                $chartData['pressure'][] = round($entry['pressure'], 2);
                $chartData['co2'][] = $entry['co2'] !== null ? round($entry['co2'], 2) : null;
            } else {
                // Original data point
                $chartData['labels'][] = $entry->getMeasuredAt()->format('Y-m-d H:i');
                $chartData['temperature'][] = $entry->getTemperature();
                $chartData['humidity'][] = $entry->getHumidity();
                $chartData['pressure'][] = $entry->getPressure();
                $chartData['co2'][] = $entry->getCarbonDioxide();
            }
        }

        return $chartData;
    }

    /**
     * Aggregate data points to reduce the number of points while maintaining trends.
     *
     * @param array<EnvironmentalData> $data
     *
     * @return array<array{label: string, temperature: float, humidity: float, pressure: float, co2: float|null}>
     */
    private function aggregateData(array $data, string $range): array
    {
        $dataCount = \count($data);
        $interval = (int) ceil($dataCount / self::MAX_DATA_POINTS);

        $aggregated = [];
        $bucket = [];

        // Determine label format based on range
        $labelFormat = match ($range) {
            'today' => 'H:i',
            'week' => 'D H:i',
            'month' => 'M d',
            'year' => 'M Y',
            default => 'Y-m-d H:i',
        };

        foreach ($data as $index => $entry) {
            $bucket[] = $entry;

            // When bucket is full or we're at the last item, aggregate and add to result
            if (\count($bucket) === $interval || $index === $dataCount - 1) {
                $aggregated[] = $this->aggregateBucket($bucket, $labelFormat);
                $bucket = [];
            }
        }

        return $aggregated;
    }

    /**
     * Aggregate a bucket of data points into a single averaged point.
     *
     * @param array<EnvironmentalData> $bucket
     *
     * @return array{label: string, temperature: float, humidity: float, pressure: float, co2: float|null}
     */
    private function aggregateBucket(array $bucket, string $labelFormat): array
    {
        $count = \count($bucket);

        $sumTemp = 0;
        $sumHumidity = 0;
        $sumPressure = 0;
        $sumCo2 = 0;
        $co2Count = 0;

        // Use the middle timestamp for the label
        $middleEntry = $bucket[(int) floor($count / 2)];

        foreach ($bucket as $entry) {
            $sumTemp += $entry->getTemperature();
            $sumHumidity += $entry->getHumidity();
            $sumPressure += $entry->getPressure();

            $co2 = $entry->getCarbonDioxide();

            if ($co2 !== null) {
                $sumCo2 += $co2;
                ++$co2Count;
            }
        }

        return [
            'label' => $middleEntry->getMeasuredAt()->format($labelFormat),
            'temperature' => $sumTemp / $count,
            'humidity' => $sumHumidity / $count,
            'pressure' => $sumPressure / $count,
            'co2' => $co2Count > 0 ? $sumCo2 / $co2Count : null,
        ];
    }
}
