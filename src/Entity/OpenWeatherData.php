<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'open_weather_data')]
class OpenWeatherData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $weatherMain;

    #[ORM\Column(type: 'string', length: 255)]
    private string $weatherDescription;

    #[ORM\Column(type: 'string', length: 50)]
    private string $weatherIcon;

    #[ORM\Column(type: 'float')]
    private float $temperature;

    #[ORM\Column(type: 'float')]
    private float $feelsLike;

    #[ORM\Column(type: 'float')]
    private float $tempMin;

    #[ORM\Column(type: 'float')]
    private float $tempMax;

    #[ORM\Column(type: 'integer')]
    private int $pressure;

    #[ORM\Column(type: 'integer')]
    private int $humidity;

    #[ORM\Column(type: 'integer')]
    private int $visibility;

    #[ORM\Column(type: 'float')]
    private float $windSpeed;

    #[ORM\Column(type: 'integer')]
    private int $windDirection;

    #[ORM\Column(type: 'integer')]
    private int $cloudiness;

    #[ORM\Column(type: 'datetime')]
    private DateTime $timestamp;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setWeatherMain(string $weatherMain): void
    {
        $this->weatherMain = $weatherMain;
    }

    public function setWeatherDescription(string $weatherDescription): void
    {
        $this->weatherDescription = $weatherDescription;
    }

    public function setWeatherIcon(string $weatherIcon): void
    {
        $this->weatherIcon = $weatherIcon;
    }

    public function setTemperature(float $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function setFeelsLike(float $feelsLike): void
    {
        $this->feelsLike = $feelsLike;
    }

    public function setTempMin(float $tempMin): void
    {
        $this->tempMin = $tempMin;
    }

    public function setTempMax(float $tempMax): void
    {
        $this->tempMax = $tempMax;
    }

    public function setPressure(int $pressure): void
    {
        $this->pressure = $pressure;
    }

    public function setHumidity(int $humidity): void
    {
        $this->humidity = $humidity;
    }

    public function setVisibility(int $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function setWindSpeed(float $windSpeed): void
    {
        $this->windSpeed = $windSpeed;
    }

    public function setWindDirection(int $windDirection): void
    {
        $this->windDirection = $windDirection;
    }

    public function setCloudiness(int $cloudiness): void
    {
        $this->cloudiness = $cloudiness;
    }

    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
