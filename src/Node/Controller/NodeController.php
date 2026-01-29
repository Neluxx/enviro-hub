<?php

declare(strict_types=1);

namespace App\Node\Controller;

use App\Api\SensorData\Repository\SensorDataRepository;
use App\Home\Repository\HomeRepository;
use App\Node\Repository\NodeRepository;
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
    private SensorDataRepository $sensorDataRepository;

    public function __construct(
        HomeRepository $homeRepository,
        NodeRepository $nodeRepository,
        SensorDataRepository $sensorDataRepository
    ) {
        $this->homeRepository = $homeRepository;
        $this->nodeRepository = $nodeRepository;
        $this->sensorDataRepository = $sensorDataRepository;
    }

    #[Route('/{identifier}')]
    public function index(string $identifier): Response
    {
        $home = $this->homeRepository->findByIdentifier($identifier);

        if (!$home) {
            throw $this->createNotFoundException('Home not found');
        }

        $nodes = $this->nodeRepository->findByHomeId($home->getId());

        // Get last sensor data for all nodes
        $nodeUuids = array_map(static fn ($node) => $node->getUuid(), $nodes);
        $sensorData = $this->sensorDataRepository->getLastEntriesByNodeUuids($nodeUuids);

        return $this->render('@Node/index.html.twig', [
            'home' => $home,
            'nodes' => $nodes,
            'sensorData' => $sensorData,
        ]);
    }
}
