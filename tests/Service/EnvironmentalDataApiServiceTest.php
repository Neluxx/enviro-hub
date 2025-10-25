<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use App\Service\EnvironmentalDataApiService;
use App\Service\EnvironmentalDataNotificationService;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EnvironmentalDataApiServiceTest extends TestCase
{
    private ValidatorInterface $validator;
    private EnvironmentalDataRepository $repository;
    private EnvironmentalDataNotificationService $notificationService;
    private EnvironmentalDataApiService $service;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->repository = $this->createMock(EnvironmentalDataRepository::class);
        $this->notificationService = $this->createMock(EnvironmentalDataNotificationService::class);

        $this->service = new EnvironmentalDataApiService(
            $this->validator,
            $this->repository,
            $this->notificationService
        );
    }

    public function testSaveEnvironmentalDataWithValidDataSucceeds(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->notificationService
            ->expects($this->once())
            ->method('notifyBasedOnCo2Levels');

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(EnvironmentalData::class));

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithMissingTemperatureThrowsException(): void
    {
        $data = [
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temperature"');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithMissingHumidityThrowsException(): void
    {
        $data = [
            'temperature' => 25.5,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "humidity"');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithMissingPressureThrowsException(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "pressure"');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithMissingCo2ThrowsException(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "co2"');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithMissingCreatedAtThrowsException(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "created_at"');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithValidationErrorsThrowsException(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $violationList = $this->createMock(ConstraintViolationList::class);
        $violationList
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $violationList
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('Validation error message');

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation error message');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataCallsNotificationService(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $previousData = new EnvironmentalData(
            24.0,
            55.0,
            1012.0,
            400.0,
            new DateTime('2025-10-25 09:00:00')
        );

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn($previousData);

        $this->notificationService
            ->expects($this->once())
            ->method('notifyBasedOnCo2Levels')
            ->with(
                $this->isInstanceOf(EnvironmentalData::class),
                $this->equalTo($previousData)
            );

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithEmptyArrayThrowsException(): void
    {
        $data = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temperature"');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataCreatesCorrectEntityValues(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (EnvironmentalData $entity) use ($data) {
                return $entity->getTemperature() === $data['temperature']
                    && $entity->getHumidity() === $data['humidity']
                    && $entity->getPressure() === $data['pressure']
                    && $entity->getCarbonDioxide() === $data['co2']
                    && $entity->getMeasuredAt()->format('Y-m-d H:i:s') === $data['created_at'];
            }));

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithNullLastEntrySucceeds(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->notificationService
            ->expects($this->once())
            ->method('notifyBasedOnCo2Levels')
            ->with(
                $this->isInstanceOf(EnvironmentalData::class),
                $this->isNull()
            );

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithDifferentDateFormatsSucceeds(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25T10:00:00+00:00',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataValidationExecutesBeforeNotification(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $violationList = $this->createMock(ConstraintViolationList::class);
        $violationList
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $violationList
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('Validation error');

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->notificationService
            ->expects($this->never())
            ->method('notifyBasedOnCo2Levels');

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->expectException(InvalidArgumentException::class);

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithNegativeValuesSucceeds(): void
    {
        $data = [
            'temperature' => -15.0,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithZeroValuesSucceeds(): void
    {
        $data = [
            'temperature' => 0.0,
            'humidity' => 0.0,
            'pressure' => 0.0,
            'co2' => 0.0,
            'created_at' => '2025-10-25 10:00:00',
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveEnvironmentalData($data);
    }

    public function testSaveEnvironmentalDataWithExtraFieldsSucceeds(): void
    {
        $data = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'pressure' => 1013.25,
            'co2' => 450.0,
            'created_at' => '2025-10-25 10:00:00',
            'extra_field' => 'extra_value',
            'another_field' => 123,
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('getLastEntry')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveEnvironmentalData($data);
    }
}
