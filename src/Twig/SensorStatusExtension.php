<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for determining sensor status colors.
 */
class SensorStatusExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sensor_status_class', [$this, 'getSensorStatusClass']),
        ];
    }

    /**
     * Get the Bootstrap background class based on sensor type and value.
     */
    public function getSensorStatusClass(string $type, ?float $value): string
    {
        if ($value === null) {
            return 'text-bg-secondary';
        }

        return match ($type) {
            'temperature' => $this->getTemperatureStatus($value),
            'humidity' => $this->getHumidityStatus($value),
            'co2' => $this->getCo2Status($value),
            default => 'text-bg-primary',
        };
    }

    /**
     * Get temperature status class.
     * Success: 18-22°C
     * Warning: 16-18°C or 22-24°C
     * Danger: <16°C or >24°C.
     */
    private function getTemperatureStatus(float $value): string
    {
        if ($value >= 18 && $value <= 22) {
            return 'text-bg-success';
        }

        if (($value >= 16 && $value < 18) || ($value > 22 && $value <= 24)) {
            return 'text-bg-warning';
        }

        return 'text-bg-danger';
    }

    /**
     * Get humidity status class.
     * Success: 40-60%
     * Warning: 30-40% or 60-70%
     * Danger: <30% or >70%.
     */
    private function getHumidityStatus(float $value): string
    {
        if ($value >= 40 && $value <= 60) {
            return 'text-bg-success';
        }

        if (($value >= 30 && $value < 40) || ($value > 60 && $value <= 70)) {
            return 'text-bg-warning';
        }

        return 'text-bg-danger';
    }

    /**
     * Get CO2 status class.
     * Success: 0-999 ppm
     * Warning: 1000-1599 ppm
     * Danger: >=1600 ppm.
     */
    private function getCo2Status(float $value): string
    {
        if ($value >= 0 && $value < 1000) {
            return 'text-bg-success';
        }

        if ($value >= 1000 && $value < 1600) {
            return 'text-bg-warning';
        }

        return 'text-bg-danger';
    }
}
