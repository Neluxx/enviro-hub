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

    public function __construct(
        string $weatherMain,
        string $weatherDescription,
        string $weatherIcon,
        float $temperature,
        float $feelsLike,
        float $tempMin,
        float $tempMax,
        int $pressure,
        int $humidity,
        int $visibility,
        float $windSpeed,
        int $windDirection,
        int $cloudiness,
        DateTime $timestamp,
    ) {
        $this->weatherMain = $weatherMain;
        $this->weatherDescription = $weatherDescription;
        $this->weatherIcon = $weatherIcon;
        $this->temperature = $temperature;
        $this->feelsLike = $feelsLike;
        $this->tempMin = $tempMin;
        $this->tempMax = $tempMax;
        $this->pressure = $pressure;
        $this->humidity = $humidity;
        $this->visibility = $visibility;
        $this->windSpeed = $windSpeed;
        $this->windDirection = $windDirection;
        $this->cloudiness = $cloudiness;
        $this->timestamp = $timestamp;
        $this->createdAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getWeatherMain(): string
    {
        return $this->weatherMain;
    }

    public function getWeatherDescription(): string
    {
        return $this->weatherDescription;
    }

    public function getWeatherIcon(): string
    {
        return $this->weatherIcon;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getFeelsLike(): float
    {
        return $this->feelsLike;
    }

    public function getTempMin(): float
    {
        return $this->tempMin;
    }

    public function getTempMax(): float
    {
        return $this->tempMax;
    }

    public function getPressure(): int
    {
        return $this->pressure;
    }

    public function getHumidity(): int
    {
        return $this->humidity;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function getWindSpeed(): float
    {
        return $this->windSpeed;
    }

    public function getWindDirection(): int
    {
        return $this->windDirection;
    }

    public function getCloudiness(): int
    {
        return $this->cloudiness;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
