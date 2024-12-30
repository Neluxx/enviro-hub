<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "environmental_data")]
class EnvironmentalData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "float")]
    private float $temperature;

    #[ORM\Column(type: "float")]
    private float $humidity;

    #[ORM\Column(type: "float")]
    private float $pressure;

    #[ORM\Column(type: "float")]
    private float $co2;

    #[ORM\Column(type: "datetime")]
    private DateTime $measuredAt;

    #[ORM\Column(type: "datetime")]
    private DateTime $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function getHumidity(): float
    {
        return $this->humidity;
    }

    public function setHumidity(float $humidity): void
    {
        $this->humidity = $humidity;
    }

    public function getPressure(): float
    {
        return $this->pressure;
    }

    public function setPressure(float $pressure): void
    {
        $this->pressure = $pressure;
    }

    public function getCo2(): float
    {
        return $this->co2;
    }

    public function setCo2(float $co2): void
    {
        $this->co2 = $co2;
    }

    public function getMeasuredAt(): DateTime
    {
        return $this->measuredAt;
    }

    public function setMeasuredAt(DateTime $measuredAt): void
    {
        $this->measuredAt = $measuredAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
