<?php

declare(strict_types=1);

namespace App\Api\SensorData\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sensor_data')]
class SensorData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $nodeUuid;

    #[ORM\Column(type: 'float')]
    private float $temperature;

    #[ORM\Column(type: 'float')]
    private float $humidity;

    #[ORM\Column(type: 'float')]
    private float $pressure;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $carbonDioxide = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $measuredAt;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    public function __construct(
        string $nodeUuid,
        float $temperature,
        float $humidity,
        float $pressure,
        ?float $carbonDioxide,
        DateTime $measuredAt,
    ) {
        $this->nodeUuid = $nodeUuid;
        $this->temperature = $temperature;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
        $this->carbonDioxide = $carbonDioxide;
        $this->measuredAt = $measuredAt;
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNodeUuid(): string
    {
        return $this->nodeUuid;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getHumidity(): float
    {
        return $this->humidity;
    }

    public function getPressure(): float
    {
        return $this->pressure;
    }

    public function getCarbonDioxide(): ?float
    {
        return $this->carbonDioxide;
    }

    public function getMeasuredAt(): DateTime
    {
        return $this->measuredAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
