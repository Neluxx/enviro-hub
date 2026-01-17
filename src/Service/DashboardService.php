<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SensorData;
use App\Repository\SensorDataRepository;
use DateTime;
use DateTimeZone;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * Dashboard Service.
 *
 * Handles business logic for dashboard data processing and aggregation.
 */
class DashboardService
{
    private const TEMP_OPTIMAL_MIN = 18.0;
    private const TEMP_OPTIMAL_MAX = 24.0;
    private const HUMIDITY_OPTIMAL_MIN = 40.0;
    private const HUMIDITY_OPTIMAL_MAX = 60.0;
    private const CO2_OPTIMAL_MAX = 1000.0;
    private const CO2_WARNING_MAX = 1600.0;

    private SensorDataRepository $repository;
    private ChartBuilderInterface $chartBuilder;

    public function __construct(SensorDataRepository $repository, ChartBuilderInterface $chartBuilder)
    {
        $this->repository = $repository;
        $this->chartBuilder = $chartBuilder;
    }

    /**
     * Get chart data for a specific time range and node UUID.
     *
     * @param string $nodeUuid The node UUID
     * @param string $range Time range string
     *
     * @return array{labels: array<string>, temperature: array<float>, humidity: array<float>, co2: array<float|null>}
     */
    public function getChartDataByNode(string $nodeUuid, string $range): array
    {
        $timezone = new DateTimeZone('Europe/Berlin');
        $endDate = new DateTime('now', $timezone);
        $startDate = (clone $endDate)->modify($range);

        $data = $this->repository->findByNodeUuidAndDateRange($nodeUuid, $startDate, $endDate);

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

    public function createTemperatureChart(array $labels, array $data): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Temperature (°C)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $data,
                    'tension' => 0.4,
                    'cubicInterpolationMode' => 'monotone',
                    'spanGaps' => true,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'annotation' => [
                    'annotations' => [
                        'optimalRange' => [
                            'type' => 'box',
                            'yMin' => self::TEMP_OPTIMAL_MIN,
                            'yMax' => self::TEMP_OPTIMAL_MAX,
                            'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                            'borderColor' => 'rgba(75, 192, 192, 0.3)',
                            'borderWidth' => 1,
                        ],
                        'minLine' => [
                            'type' => 'line',
                            'yMin' => self::TEMP_OPTIMAL_MIN,
                            'yMax' => self::TEMP_OPTIMAL_MIN,
                            'borderColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'display' => true,
                                'content' => 'Min '.self::TEMP_OPTIMAL_MIN.'°C',
                                'position' => 'start',
                                'color' => 'rgba(255, 255, 255, 0.7)',
                                'font' => ['size' => 11],
                            ],
                        ],
                        'maxLine' => [
                            'type' => 'line',
                            'yMin' => self::TEMP_OPTIMAL_MAX,
                            'yMax' => self::TEMP_OPTIMAL_MAX,
                            'borderColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'display' => true,
                                'content' => 'Max '.self::TEMP_OPTIMAL_MAX.'°C',
                                'position' => 'start',
                                'color' => 'rgba(255, 255, 255, 0.7)',
                                'font' => ['size' => 11],
                            ],
                        ],
                    ],
                ],
            ],
            'scales' => [
                'x' => ['grid' => ['color' => 'rgba(255, 255, 255, 0.1)']],
                'y' => [
                    'grid' => ['color' => 'rgba(255, 255, 255, 0.1)'],
                    'beginAtZero' => false,
                ],
            ],
        ]);

        return $chart;
    }

    public function createHumidityChart(array $labels, array $data): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Humidity (%)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'data' => $data,
                    'tension' => 0.4,
                    'cubicInterpolationMode' => 'monotone',
                    'spanGaps' => true,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'annotation' => [
                    'annotations' => [
                        'optimalRange' => [
                            'type' => 'box',
                            'yMin' => self::HUMIDITY_OPTIMAL_MIN,
                            'yMax' => self::HUMIDITY_OPTIMAL_MAX,
                            'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                            'borderColor' => 'rgba(75, 192, 192, 0.3)',
                            'borderWidth' => 1,
                        ],
                        'minLine' => [
                            'type' => 'line',
                            'yMin' => self::HUMIDITY_OPTIMAL_MIN,
                            'yMax' => self::HUMIDITY_OPTIMAL_MIN,
                            'borderColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'display' => true,
                                'content' => 'Min '.self::HUMIDITY_OPTIMAL_MIN.'%',
                                'position' => 'start',
                                'color' => 'rgba(255, 255, 255, 0.7)',
                                'font' => ['size' => 11],
                            ],
                        ],
                        'maxLine' => [
                            'type' => 'line',
                            'yMin' => self::HUMIDITY_OPTIMAL_MAX,
                            'yMax' => self::HUMIDITY_OPTIMAL_MAX,
                            'borderColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'display' => true,
                                'content' => 'Max '.self::HUMIDITY_OPTIMAL_MAX.'%',
                                'position' => 'start',
                                'color' => 'rgba(255, 255, 255, 0.7)',
                                'font' => ['size' => 11],
                            ],
                        ],
                    ],
                ],
            ],
            'scales' => [
                'x' => ['grid' => ['color' => 'rgba(255, 255, 255, 0.1)']],
                'y' => [
                    'grid' => ['color' => 'rgba(255, 255, 255, 0.1)'],
                    'beginAtZero' => false,
                ],
            ],
        ]);

        return $chart;
    }

    public function createCo2Chart(array $labels, array $data): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'CO₂ (ppm)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'data' => $data,
                    'tension' => 0.4,
                    'cubicInterpolationMode' => 'monotone',
                    'spanGaps' => true,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'annotation' => [
                    'annotations' => [
                        'goodRange' => [
                            'type' => 'box',
                            'yMin' => 0,
                            'yMax' => self::CO2_OPTIMAL_MAX,
                            'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                            'borderWidth' => 0,
                        ],
                        'warningRange' => [
                            'type' => 'box',
                            'yMin' => self::CO2_OPTIMAL_MAX,
                            'yMax' => self::CO2_WARNING_MAX,
                            'backgroundColor' => 'rgba(255, 206, 86, 0.1)',
                            'borderWidth' => 0,
                        ],
                        'optimalLine' => [
                            'type' => 'line',
                            'yMin' => self::CO2_OPTIMAL_MAX,
                            'yMax' => self::CO2_OPTIMAL_MAX,
                            'borderColor' => 'rgba(75, 192, 192, 0.5)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'display' => true,
                                'content' => 'Optimal '.self::CO2_OPTIMAL_MAX.' ppm',
                                'position' => 'start',
                                'color' => 'rgba(255, 255, 255, 0.7)',
                                'font' => ['size' => 11],
                            ],
                        ],
                        'warningLine' => [
                            'type' => 'line',
                            'yMin' => self::CO2_WARNING_MAX,
                            'yMax' => self::CO2_WARNING_MAX,
                            'borderColor' => 'rgba(255, 99, 132, 0.5)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'display' => true,
                                'content' => 'Warning '.self::CO2_WARNING_MAX.' ppm',
                                'position' => 'start',
                                'color' => 'rgba(255, 255, 255, 0.7)',
                                'font' => ['size' => 11],
                            ],
                        ],
                    ],
                ],
            ],
            'scales' => [
                'x' => ['grid' => ['color' => 'rgba(255, 255, 255, 0.1)']],
                'y' => [
                    'grid' => ['color' => 'rgba(255, 255, 255, 0.1)'],
                    'beginAtZero' => false,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Format data into chart-ready structure.
     *
     * @param array<SensorData|array{label: string, temperature: float, humidity: float, co2: float|null}> $data
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
