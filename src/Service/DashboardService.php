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
    private EnvironmentalDataRepository $repository;

    public function __construct(EnvironmentalDataRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get chart data for a specific time range.
     *
     * @return array{labels: array<string>, temperature: array<float>, humidity: array<float>, co2: array<float|null>}
     */
    public function getChartData(string $range): array
    {
        $timezone = new DateTimeZone('Europe/Berlin');
        $endDate = new DateTime('now', $timezone);
        $startDate = (clone $endDate)->modify($range);

        $data = $this->repository->findByDateRange($startDate, $endDate);

        if (empty($data)) {
            return [
                'labels' => [],
                'temperature' => [],
                'humidity' => [],
                'co2' => [],
            ];
        }

        return $this->formatChartData($data);
    }

    /**
     * Format data into chart-ready structure.
     *
     * @param array<EnvironmentalData|array{label: string, temperature: float, humidity: float, co2: float|null}> $data
     *
     * @return array{labels: array<string>, temperature: array<float>, humidity: array<float>, co2: array<float|null>}
     */
    private function formatChartData(array $data): array
    {
        $chartData = [
            'labels' => [],
            'temperature' => [],
            'humidity' => [],
            'co2' => [],
        ];

        foreach ($data as $entry) {
            $chartData['labels'][] = $entry->getMeasuredAt()->format('d.m H:i');
            $chartData['temperature'][] = $entry->getTemperature();
            $chartData['humidity'][] = $entry->getHumidity();
            $chartData['co2'][] = $entry->getCarbonDioxide();
        }

        return $chartData;
    }
}
