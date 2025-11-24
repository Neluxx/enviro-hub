<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Dashboard Controller.
 */
class DashboardController extends AbstractController
{
    /** The maximum number of data points to display on the chart */
    public const MAX_DATA_POINTS = 250;

    private EnvironmentalDataRepository $repository;

    public function __construct(EnvironmentalDataRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/')]
    public function index(): Response
    {
        $data = $this->repository->getLastEntry();

        return $this->render('dashboard/index.html.twig', ['data' => $data]);
    }

    #[Route('/api/environmental-data/chart/{range}', methods: ['GET'])]
    public function getChartData(string $range): JsonResponse
    {
        $endDate = new DateTime();
        $startDate = match ($range) {
            'today' => (new DateTime())->setTime(0, 0),
            'week' => (new DateTime())->modify('-7 days'),
            'month' => (new DateTime())->modify('-1 month'),
            'year' => (new DateTime())->modify('-1 year'),
            default => (new DateTime())->setTime(0, 0),
        };

        $data = $this->repository->findByDateRange($startDate, $endDate);

        if (empty($data)) {
            return new JsonResponse([
                'labels' => [],
                'temperature' => [],
                'humidity' => [],
                'pressure' => [],
                'co2' => [],
            ]);
        }

        if (\count($data) > self::MAX_DATA_POINTS) {
            $data = $this->aggregateData($data, $range);
        }

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

        return new JsonResponse($chartData);
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
