<?php

declare(strict_types=1);

namespace App\Node\Repository;

use App\Node\Entity\Node;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Node Repository.
 */
class NodeRepository
{
    private EntityManagerInterface $entityManager;

    /** @var EntityRepository<Node> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Node::class);
    }

    /**
     * Find all Nodes by Home ID.
     *
     * @return Node[]
     */
    public function findByHomeId(int $homeId): array
    {
        return $this->repository->findBy(['homeId' => $homeId]);
    }

    /**
     * Find Node by UUID.
     */
    public function findByUuid(string $uuid): ?Node
    {
        return $this->repository->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Count Nodes by Home ID.
     */
    public function countByHomeId(int $homeId): int
    {
        return $this->repository->count(['homeId' => $homeId]);
    }
}
