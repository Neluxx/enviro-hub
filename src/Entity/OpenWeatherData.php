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

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $weatherMain = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $weatherDescription = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $weatherIcon = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperature = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $feelsLike = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tempMin = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tempMax = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $pressure = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $humidity = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $visibility = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $windSpeed = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $windDirection = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cloudiness = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $timestamp = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $sunrise = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $sunset = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cityName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timezone = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getWeatherMain(): ?string
    {
        return $this->weatherMain;
    }

    public function setWeatherMain(?string $weatherMain): void
    {
        $this->weatherMain = $weatherMain;
    }

    public function getWeatherDescription(): ?string
    {
        return $this->weatherDescription;
    }

    public function setWeatherDescription(?string $weatherDescription): void
    {
        $this->weatherDescription = $weatherDescription;
    }

    public function getWeatherIcon(): ?string
    {
        return $this->weatherIcon;
    }

    public function setWeatherIcon(?string $weatherIcon): void
    {
        $this->weatherIcon = $weatherIcon;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function getFeelsLike(): ?float
    {
        return $this->feelsLike;
    }

    public function setFeelsLike(?float $feelsLike): void
    {
        $this->feelsLike = $feelsLike;
    }

    public function getTempMin(): ?float
    {
        return $this->tempMin;
    }

    public function setTempMin(?float $tempMin): void
    {
        $this->tempMin = $tempMin;
    }

    public function getTempMax(): ?float
    {
        return $this->tempMax;
    }

    public function setTempMax(?float $tempMax): void
    {
        $this->tempMax = $tempMax;
    }

    public function getPressure(): ?int
    {
        return $this->pressure;
    }

    public function setPressure(?int $pressure): void
    {
        $this->pressure = $pressure;
    }

    public function getHumidity(): ?int
    {
        return $this->humidity;
    }

    public function setHumidity(?int $humidity): void
    {
        $this->humidity = $humidity;
    }

    public function getVisibility(): ?int
    {
        return $this->visibility;
    }

    public function setVisibility(?int $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getWindSpeed(): ?float
    {
        return $this->windSpeed;
    }

    public function setWindSpeed(?float $windSpeed): void
    {
        $this->windSpeed = $windSpeed;
    }

    public function getWindDirection(): ?int
    {
        return $this->windDirection;
    }

    public function setWindDirection(?int $windDirection): void
    {
        $this->windDirection = $windDirection;
    }

    public function getCloudiness(): ?int
    {
        return $this->cloudiness;
    }

    public function setCloudiness(?int $cloudiness): void
    {
        $this->cloudiness = $cloudiness;
    }

    public function getTimestamp(): ?DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(?DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getSunrise(): ?DateTime
    {
        return $this->sunrise;
    }

    public function setSunrise(?DateTime $sunrise): void
    {
        $this->sunrise = $sunrise;
    }

    public function getSunset(): ?DateTime
    {
        return $this->sunset;
    }

    public function setSunset(?DateTime $sunset): void
    {
        $this->sunset = $sunset;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getTimezone(): ?int
    {
        return $this->timezone;
    }

    public function setTimezone(?int $timezone): void
    {
        $this->timezone = $timezone;
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
