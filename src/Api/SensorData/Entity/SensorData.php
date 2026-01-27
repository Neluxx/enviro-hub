<?php

declare(strict_types=1);

namespace App\Api\SensorData\Entity;

use App\Api\SensorData\Repository\SensorDataRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SensorDataRepository::class)]
#[ORM\Table(name: 'sensor_data')]
#[ORM\Index(name: 'idx_node_uuid', columns: ['node_uuid'])]
class SensorData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $nodeUuid;

    #[ORM\Column(type: 'integer')]
    private int $temperature;

    #[ORM\Column(type: 'integer')]
    private int $humidity;

    #[ORM\Column(type: 'integer')]
    private int $pressure;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $carbonDioxide;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $measuredAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $nodeUuid,
        int|float $temperature,
        int|float $humidity,
        int|float $pressure,
        int|float|null $carbonDioxide,
        DateTimeImmutable $measuredAt,
    ) {
        $this->nodeUuid = $nodeUuid;
        $this->temperature = (int) round($temperature);
        $this->humidity = (int) round($humidity);
        $this->pressure = (int) round($pressure);
        $this->carbonDioxide = $carbonDioxide ? (int) round($carbonDioxide) : null;
        $this->measuredAt = $measuredAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNodeUuid(): string
    {
        return $this->nodeUuid;
    }

    public function getTemperature(): int
    {
        return $this->temperature;
    }

    public function getHumidity(): int
    {
        return $this->humidity;
    }

    public function getPressure(): int
    {
        return $this->pressure;
    }

    public function getCarbonDioxide(): ?int
    {
        return $this->carbonDioxide;
    }

    public function getMeasuredAt(): DateTimeImmutable
    {
        return $this->measuredAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
