<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EnvironmentalData;
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
        $lastEntry = $this->repository->getLastEntry();
        $latestEntries = $this->repository->getLatestEntries();
        $chart = $this->createEnvironmentalDataChart($chartBuilder, $latestEntries);

        return $this->render('dashboard/index.html.twig', [
            'lastEntry' => $lastEntry,
            'chart' => $chart,
        ]);
    }

    /**
     * Creates a chart for environmental data.
     *
     * @param ChartBuilderInterface $chartBuilder The chart builder
     * @param EnvironmentalData[] $latestEntries The latest environmental data
     */
    private function createEnvironmentalDataChart(ChartBuilderInterface $chartBuilder, array $latestEntries): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $temperatures = array_map(fn ($data) => $data->getTemperature(), $latestEntries);

        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            'datasets' => [
                [
                    'label' => 'Environmental Data',
                    'backgroundColor' => 'rgb(54, 162, 235)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'data' => $temperatures,
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 15,
                    'suggestedMax' => 30,
                ],
            ],
        ]);

        return $chart;
    }
}
