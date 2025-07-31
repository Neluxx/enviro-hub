<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\OpenWeatherData;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Open Weather Data Repository
 */
class OpenWeatherDataRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Save OpenWeatherData entry.
     */
    public function save(OpenWeatherData $openWeatherData): void
    {
        $this->entityManager->persist($openWeatherData);
        $this->entityManager->flush();
    }
}
