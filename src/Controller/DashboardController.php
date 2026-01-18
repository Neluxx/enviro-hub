<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SensorDataRepository;
use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Dashboard Controller.
 */
class DashboardController extends AbstractController
{
    private DashboardService $dashboardService;
    private SensorDataRepository $sensorDataRepository;

    public function __construct(
        DashboardService $dashboardService,
        SensorDataRepository $sensorDataRepository,
    ) {
        $this->dashboardService = $dashboardService;
        $this->sensorDataRepository = $sensorDataRepository;
    }

    #[Route('/{homeIdentifier}/{nodeUuid}', requirements: ['homeIdentifier' => '^(?!api).*'])]
    public function index(string $nodeUuid): Response
    {
        $data = $this->sensorDataRepository->getLastEntryByNodeUuid($nodeUuid);

        $chartData = $this->dashboardService->getChartDataByNodeUuid($nodeUuid, '-12 hours');

        $tempChart = $this->dashboardService->createTemperatureChart(
            $chartData['labels'],
            $chartData['temperature']
        );

        $humidityChart = $this->dashboardService->createHumidityChart(
            $chartData['labels'],
            $chartData['humidity']
        );

        $co2Chart = $this->dashboardService->createCo2Chart(
            $chartData['labels'],
            $chartData['co2']
        );

        $versionFile = $this->getParameter('kernel.project_dir').'/VERSION.txt';
        $version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : 'N/A';

        return $this->render('dashboard/index.html.twig', [
            'nodeUuid' => $nodeUuid,
            'data' => $data,
            'version' => $version,
            'tempChart' => $tempChart,
            'humidityChart' => $humidityChart,
            'co2Chart' => $co2Chart,
        ]);
    }

    #[Route('/{homeIdentifier}/{nodeUuid}/api/sensor-data/chart/{range}', methods: ['GET'], requirements: ['homeIdentifier' => '^(?!api).*'])]
    public function getChartData(string $nodeUuid, string $range): JsonResponse
    {
        $chartData = $this->dashboardService->getChartDataByNodeUuid($nodeUuid, $range);

        return new JsonResponse($chartData);
    }
}
