<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EnvironmentalDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

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
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $data = $this->repository->getLatestEntries();
        $chart = $this->createEnvironmentalDataChart($chartBuilder);

        return $this->render('dashboard/index.html.twig', [
            'data' => $data,
            'chart' => $chart,
        ]);
    }

    private function createEnvironmentalDataChart(ChartBuilderInterface $chartBuilder): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'Environmental Data',
                    'backgroundColor' => 'rgb(54, 162, 235)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'data' => [0, 10, 5, 2, 20, 30, 45], // Fake data for testing purposes only
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $chart;
    }
}
