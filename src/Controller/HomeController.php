<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HomeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Home Controller.
 */
class HomeController extends AbstractController
{
    private HomeRepository $homeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        $this->homeRepository = $homeRepository;
    }

    #[Route('/')]
    public function index(): Response
    {
        $homes = $this->homeRepository->findAll();

        return $this->render('home/index.html.twig', [
            'homes' => $homes,
        ]);
    }
}
