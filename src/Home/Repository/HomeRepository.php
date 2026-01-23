<?php

declare(strict_types=1);

namespace App\Home\Repository;

use App\Home\Entity\Home;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Home Repository.
 */
class HomeRepository
{
    private EntityManagerInterface $entityManager;

    /** @var EntityRepository<Home> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Home::class);
    }

    /**
     * Find Home by identifier.
     */
    public function findByIdentifier(string $identifier): ?Home
    {
        return $this->repository->findOneBy(['identifier' => $identifier]);
    }

    /**
     * Find all Homes.
     *
     * @return Home[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
