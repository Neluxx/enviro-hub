<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EnvironmentalDataRepository;
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
    private EnvironmentalDataRepository $repository;

    public function __construct(EnvironmentalDataRepository $repository, DashboardService $service)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    #[Route('/')]
    public function index(): Response
    {
        $data = $this->repository->getLastEntry();

        $versionFile = $this->getParameter('kernel.project_dir') . '/VERSION.txt';
        $version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : 'N/A';

        return $this->render('dashboard/index.html.twig', [
            'data' => $data,
            'version' => $version,
        ]);
    }

    #[Route('/api/environmental-data/chart/{range}', methods: ['GET'])]
    public function getChartData(string $range): JsonResponse
    {
        $chartData = $this->service->getChartData($range);

        return new JsonResponse($chartData);
    }
}
