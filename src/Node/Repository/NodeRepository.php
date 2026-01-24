<?php

declare(strict_types=1);

namespace App\Node\Repository;

use App\Node\Entity\Node;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Node Repository.
 *
 * @extends ServiceEntityRepository<Node>
 */
class NodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Node::class);
    }

    /**
     * Find all Nodes by Home ID.
     *
     * @return Node[]
     */
    public function findByHomeId(int $homeId): array
    {
        return $this->findBy(['homeId' => $homeId]);
    }

    /**
     * Count Nodes by Home ID.
     */
    public function countByHomeId(int $homeId): int
    {
        return $this->count(['homeId' => $homeId]);
    }
}
