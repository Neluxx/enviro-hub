<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HomeRepository;
use App\Repository\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Home Controller.
 */
class HomeController extends AbstractController
{
    private HomeRepository $homeRepository;
    private NodeRepository $nodeRepository;

    public function __construct(HomeRepository $homeRepository, NodeRepository $nodeRepository)
    {
        $this->homeRepository = $homeRepository;
        $this->nodeRepository = $nodeRepository;
    }

    #[Route('/')]
    public function index(): Response
    {
        $homes = $this->homeRepository->findAll();

        // Build node counts for each home
        $nodeCounts = [];
        foreach ($homes as $home) {
            $homeId = $home->getId();
            $nodeCounts[$homeId] = $this->nodeRepository->countByHomeId($homeId);
        }

        return $this->render('home/index.html.twig', [
            'homes' => $homes,
            'nodeCounts' => $nodeCounts,
        ]);
    }
}
