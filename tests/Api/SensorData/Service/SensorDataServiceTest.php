<?php

declare(strict_types=1);

namespace App\Tests\Api\SensorData\Service;

use App\Api\SensorData\Repository\SensorDataRepository;
use App\Api\SensorData\Service\SensorDataService;
use App\Notification\Service\NotificationService;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeError;

class SensorDataServiceTest extends KernelTestCase
{
    private SensorDataService $service;
    private SensorDataRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $validator = $container->get(ValidatorInterface::class);
        $this->repository = $container->get(SensorDataRepository::class);
        $notificationService = $container->get(NotificationService::class);

        $this->service = new SensorDataService(
            $validator,
            $this->repository,
            $notificationService
        );
    }

    public function testSaveSensorDataSuccess(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 400,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->service->saveSensorData($data);

        $lastEntry = $this->repository->getLastEntryByNodeUuid('test-node-uuid');
        $this->assertSame(22.5, $lastEntry->getTemperature());
        $this->assertSame(45.0, $lastEntry->getHumidity());
        $this->assertSame(1013.25, $lastEntry->getPressure());
        $this->assertSame(400.0, $lastEntry->getCarbonDioxide());
        $this->assertEquals(new DateTimeImmutable('2025-10-26 10:00:00'), $lastEntry->getMeasuredAt());
    }

    public function testSaveMultipleSensorDataEntries(): void
    {
        $data1 = [
            'uuid' => 'test-node-uuid',
            'temperature' => 20.0,
            'humidity' => 40.0,
            'pressure' => 1010.0,
            'co2' => 380,
            'created_at' => '2025-10-26 09:00:00',
        ];

        $data2 = [
            'uuid' => 'test-node-uuid',
            'temperature' => 23.0,
            'humidity' => 50.0,
            'pressure' => 1015.0,
            'co2' => 420,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->service->saveSensorData($data1);
        $this->service->saveSensorData($data2);

        $entries = $this->repository->getLatestEntriesByNodeUuid('test-node-uuid');
        $this->assertCount(2, $entries);

        $this->assertSame(23.0, $entries[0]->getTemperature());
        $this->assertSame(50.0, $entries[0]->getHumidity());
        $this->assertSame(1015.0, $entries[0]->getPressure());
        $this->assertSame(420.0, $entries[0]->getCarbonDioxide());
        $this->assertEquals(new DateTimeImmutable('2025-10-26 10:00:00'), $entries[0]->getMeasuredAt());

        $this->assertSame(20.0, $entries[1]->getTemperature());
        $this->assertSame(40.0, $entries[1]->getHumidity());
        $this->assertSame(1010.0, $entries[1]->getPressure());
        $this->assertSame(380.0, $entries[1]->getCarbonDioxide());
        $this->assertEquals(new DateTimeImmutable('2025-10-26 09:00:00'), $entries[1]->getMeasuredAt());
    }

    public function testSaveSensorDataMissingNodeUuid(): void
    {
        $data = [
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 400,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "uuid"');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataMissingTemperature(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 400,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temperature"');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataMissingHumidity(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'pressure' => 1013.25,
            'co2' => 400,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "humidity"');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataMissingPressure(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'co2' => 400,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "pressure"');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataMissingCo2(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->service->saveSensorData($data);

        $lastEntry = $this->repository->getLastEntryByNodeUuid('test-node-uuid');
        $this->assertNull($lastEntry->getCarbonDioxide());
        $this->assertSame(22.5, $lastEntry->getTemperature());
        $this->assertSame(45.0, $lastEntry->getHumidity());
        $this->assertSame(1013.25, $lastEntry->getPressure());
        $this->assertEquals(new DateTimeImmutable('2025-10-26 10:00:00'), $lastEntry->getMeasuredAt());
    }

    public function testSaveSensorDataMissingCreatedAt(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 400,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "created_at"');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataInvalidTemperatureFormat(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 'invalid',
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 400.0,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #2 ($temperature) must be of type float, string given');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataInvalidHumidityFormat(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 'invalid',
            'pressure' => 1013.25,
            'co2' => 400.0,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #3 ($humidity) must be of type float, string given');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataInvalidPressureFormat(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 'invalid',
            'co2' => 400.0,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #4 ($pressure) must be of type float, string given');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataInvalidCo2Format(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 'invalid',
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #5 ($carbonDioxide) must be of type ?float, string given');

        $this->service->saveSensorData($data);
    }

    public function testSaveSensorDataInvalidDateFormat(): void
    {
        $data = [
            'uuid' => 'test-node-uuid',
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pressure' => 1013.25,
            'co2' => 400,
            'created_at' => 'invalid',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to parse time string (invalid) at position 0 (i)');

        $this->service->saveSensorData($data);
    }
}
