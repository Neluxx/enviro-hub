<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EnvironmentalDataRepository;
use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * Dashboard Controller.
 */
class DashboardController extends AbstractController
{
    private DashboardService $service;
    private EnvironmentalDataRepository $repository;
    private ChartBuilderInterface $chartBuilder;

    public function __construct(
        EnvironmentalDataRepository $repository,
        DashboardService $service,
        ChartBuilderInterface $chartBuilder
    ) {
        $this->service = $service;
        $this->repository = $repository;
        $this->chartBuilder = $chartBuilder;
    }

    #[Route('/')]
    public function index(): Response
    {
        $data = $this->repository->getLastEntry();

        $chartData = $this->service->getChartData('-12 hours');

        $tempChart = $this->createEnvironmentalChart(
            'Temperature (°C)',
            $chartData['labels'],
            $chartData['temperature'],
            'rgb(255, 99, 132)'
        );

        $humidityChart = $this->createEnvironmentalChart(
            'Humidity (%)',
            $chartData['labels'],
            $chartData['humidity'],
            'rgb(54, 162, 235)'
        );

        $co2Chart = $this->createEnvironmentalChart(
            'CO₂ (ppm)',
            $chartData['labels'],
            $chartData['co2'],
            'rgb(75, 192, 192)'
        );

        $versionFile = $this->getParameter('kernel.project_dir').'/VERSION.txt';
        $version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : 'N/A';

        return $this->render('dashboard/index.html.twig', [
            'data' => $data,
            'version' => $version,
            'tempChart' => $tempChart,
            'humidityChart' => $humidityChart,
            'co2Chart' => $co2Chart,
        ]);
    }

    #[Route('/api/environmental-data/chart/{range}', methods: ['GET'])]
    public function getChartData(string $range): JsonResponse
    {
        $chartData = $this->service->getChartData($range);

        return new JsonResponse($chartData);
    }

    private function createEnvironmentalChart(string $label, array $labels, array $data, string $color): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => str_replace('rgb', 'rgba', $color).', 0.1)',
                    'borderColor' => $color,
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
}
