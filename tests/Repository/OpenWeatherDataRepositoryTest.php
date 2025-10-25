<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\OpenWeatherData;
use App\Repository\OpenWeatherDataRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class OpenWeatherDataRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private OpenWeatherDataRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = new OpenWeatherDataRepository($this->entityManager);
    }

    public function testSaveCallsPersistAndFlush(): void
    {
        $openWeatherData = $this->createOpenWeatherData();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveCallsPersistBeforeFlush(): void
    {
        $openWeatherData = $this->createOpenWeatherData();
        $callOrder = [];

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'persist';
            });

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'flush';
            });

        $this->repository->save($openWeatherData);

        $this->assertSame(['persist', 'flush'], $callOrder);
    }

    public function testSaveWithDifferentOpenWeatherDataInstances(): void
    {
        $data1 = $this->createOpenWeatherData();
        $data2 = $this->createOpenWeatherData(
            'Rain',
            'light rain',
            '10d',
            15.5,
            14.0,
            13.0,
            17.0,
            1010,
            85,
            8000,
            5.5,
            180,
            90
        );

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        $this->repository->save($data1);
        $this->repository->save($data2);
    }

    public function testSavePersistsExactObject(): void
    {
        $openWeatherData = $this->createOpenWeatherData();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($arg) use ($openWeatherData) {
                return $arg === $openWeatherData;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithMinimalWeatherData(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Clear',
            'clear sky',
            '01d',
            0.0,
            0.0,
            0.0,
            0.0,
            0,
            0,
            0,
            0.0,
            0,
            0
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithMaximalWeatherData(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Thunderstorm',
            'thunderstorm with heavy rain',
            '11d',
            40.0,
            45.0,
            35.0,
            50.0,
            1050,
            100,
            50000,
            50.0,
            360,
            100
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithNegativeTemperatures(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Snow',
            'heavy snow',
            '13d',
            -15.5,
            -18.0,
            -20.0,
            -10.0,
            1020,
            90,
            2000,
            10.0,
            270,
            100
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithDifferentWeatherConditions(): void
    {
        $conditions = [
            ['Clouds', 'broken clouds', '04d'],
            ['Drizzle', 'light intensity drizzle', '09d'],
            ['Mist', 'mist', '50d'],
            ['Fog', 'fog', '50n'],
        ];

        $this->entityManager
            ->expects($this->exactly(4))
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(4))
            ->method('flush');

        foreach ($conditions as $condition) {
            $data = $this->createOpenWeatherData(
                $condition[0],
                $condition[1],
                $condition[2]
            );
            $this->repository->save($data);
        }
    }

    public function testSaveWithDifferentWindDirections(): void
    {
        $windDirections = [0, 45, 90, 135, 180, 225, 270, 315, 360];

        $this->entityManager
            ->expects($this->exactly(9))
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(9))
            ->method('flush');

        foreach ($windDirections as $degree) {
            $data = $this->createOpenWeatherData(
                'Clear',
                'clear sky',
                '01d',
                20.0,
                19.0,
                18.0,
                22.0,
                1013,
                50,
                10000,
                5.0,
                $degree,
                0
            );
            $this->repository->save($data);
        }
    }

    public function testSaveWithVariousCloudCoverage(): void
    {
        $cloudCoverages = [0, 25, 50, 75, 100];

        $this->entityManager
            ->expects($this->exactly(5))
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(5))
            ->method('flush');

        foreach ($cloudCoverages as $clouds) {
            $data = $this->createOpenWeatherData(
                'Clouds',
                'few clouds',
                '02d',
                20.0,
                19.0,
                18.0,
                22.0,
                1013,
                50,
                10000,
                3.0,
                90,
                $clouds
            );
            $this->repository->save($data);
        }
    }

    public function testSaveWithDifferentDates(): void
    {
        $dates = [
            '2025-01-15 12:00:00',
            '2025-06-20 15:30:00',
            '2024-12-31 23:59:59',
            '2025-01-01 00:00:00',
        ];

        $this->entityManager
            ->expects($this->exactly(4))
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(4))
            ->method('flush');

        foreach ($dates as $date) {
            $data = new OpenWeatherData(
                'Clear',
                'clear sky',
                '01d',
                20.0,
                19.0,
                18.0,
                22.0,
                1013,
                50,
                10000,
                3.0,
                90,
                0,
                new DateTime($date)
            );
            $this->repository->save($data);
        }
    }

    public function testSaveWithNightIcon(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Clear',
            'clear sky',
            '01n',
            15.0,
            14.0,
            13.0,
            17.0,
            1013,
            60,
            10000,
            2.0,
            45,
            0
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithHighPressure(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Clear',
            'clear sky',
            '01d',
            20.0,
            19.0,
            18.0,
            22.0,
            1050,
            40,
            15000,
            3.0,
            90,
            0
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithLowPressure(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Rain',
            'moderate rain',
            '10d',
            18.0,
            17.0,
            16.0,
            20.0,
            980,
            80,
            5000,
            8.0,
            180,
            100
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithHighWindSpeed(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Thunderstorm',
            'thunderstorm',
            '11d',
            22.0,
            21.0,
            20.0,
            24.0,
            1005,
            75,
            8000,
            25.0,
            270,
            90
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    public function testSaveWithLowVisibility(): void
    {
        $openWeatherData = $this->createOpenWeatherData(
            'Fog',
            'fog',
            '50d',
            12.0,
            11.0,
            10.0,
            14.0,
            1015,
            95,
            500,
            1.0,
            0,
            100
        );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($openWeatherData));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->save($openWeatherData);
    }

    private function createOpenWeatherData(
        string $weatherMain = 'Clear',
        string $weatherDescription = 'clear sky',
        string $weatherIcon = '01d',
        float $temperature = 22.5,
        float $feelsLike = 21.0,
        float $tempMin = 20.0,
        float $tempMax = 25.0,
        int $pressure = 1013,
        int $humidity = 60,
        int $visibility = 10000,
        float $windSpeed = 3.5,
        int $windDeg = 90,
        int $clouds = 20
    ): OpenWeatherData {
        return new OpenWeatherData(
            $weatherMain,
            $weatherDescription,
            $weatherIcon,
            $temperature,
            $feelsLike,
            $tempMin,
            $tempMax,
            $pressure,
            $humidity,
            $visibility,
            $windSpeed,
            $windDeg,
            $clouds,
            new DateTime('2025-01-15 12:00:00')
        );
    }
}
