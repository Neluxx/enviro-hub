<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HomeRepository;
use App\Repository\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Node Controller.
 */
class NodeController extends AbstractController
{
    private HomeRepository $homeRepository;
    private NodeRepository $nodeRepository;

    public function __construct(HomeRepository $homeRepository, NodeRepository $nodeRepository)
    {
        $this->homeRepository = $homeRepository;
        $this->nodeRepository = $nodeRepository;
    }

    #[Route('/{identifier}')]
    public function index(string $identifier): Response
    {
        $home = $this->homeRepository->findByIdentifier($identifier);

        if (!$home) {
            throw $this->createNotFoundException('Home not found');
        }

        $nodes = $this->nodeRepository->findByHomeId($home->getId());

        return $this->render('node/index.html.twig', [
            'home' => $home,
            'nodes' => $nodes,
        ]);
    }
}
