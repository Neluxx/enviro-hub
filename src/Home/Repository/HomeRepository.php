<?php

declare(strict_types=1);

namespace App\Home\Repository;

use App\Home\Entity\Home;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Home Repository.
 *
 * @extends ServiceEntityRepository<Home>
 */
class HomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Home::class);
    }

    /**
     * Find Home by identifier.
     */
    public function findByIdentifier(string $identifier): ?Home
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }
}
