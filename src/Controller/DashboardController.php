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
    private DashboardService $service;
    private SensorDataRepository $repository;

    public function __construct(SensorDataRepository $repository, DashboardService $service)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    #[Route('/')]
    public function index(): Response
    {
        $data = $this->repository->getLastEntry();

        $chartData = $this->service->getChartData('-12 hours');

        $tempChart = $this->service->createTemperatureChart(
            $chartData['labels'],
            $chartData['temperature']
        );

        $humidityChart = $this->service->createHumidityChart(
            $chartData['labels'],
            $chartData['humidity']
        );

        $co2Chart = $this->service->createCo2Chart(
            $chartData['labels'],
            $chartData['co2']
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

    #[Route('/api/sensor-data/chart/{range}', methods: ['GET'])]
    public function getChartData(string $range): JsonResponse
    {
        $chartData = $this->service->getChartData($range);

        return new JsonResponse($chartData);
    }
}
