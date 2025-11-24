<?php

declare(strict_types=1);

namespace App\Controller;

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
            'today' => (new DateTime())->setTime(0, 0, 0),
            'week' => (new DateTime())->modify('-7 days'),
            'month' => (new DateTime())->modify('-1 month'),
            'year' => (new DateTime())->modify('-1 year'),
            default => (new DateTime())->setTime(0, 0, 0),
        };

        $data = $this->repository->findByDateRange($startDate, $endDate);

        $chartData = [
            'labels' => [],
            'temperature' => [],
            'humidity' => [],
            'pressure' => [],
            'co2' => [],
        ];

        foreach ($data as $entry) {
            $chartData['labels'][] = $entry->getMeasuredAt()->format('Y-m-d H:i');
            $chartData['temperature'][] = $entry->getTemperature();
            $chartData['humidity'][] = $entry->getHumidity();
            $chartData['pressure'][] = $entry->getPressure();
            $chartData['co2'][] = $entry->getCarbonDioxide();
        }

        return new JsonResponse($chartData);
    }
}
