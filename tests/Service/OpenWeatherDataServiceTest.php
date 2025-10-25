<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\OpenWeatherData;
use App\Repository\OpenWeatherDataRepository;
use App\Service\OpenWeatherDataService;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OpenWeatherDataServiceTest extends TestCase
{
    private ValidatorInterface $validator;
    private OpenWeatherDataRepository $repository;
    private OpenWeatherDataService $service;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->repository = $this->createMock(OpenWeatherDataRepository::class);

        $this->service = new OpenWeatherDataService(
            $this->validator,
            $this->repository
        );
    }

    public function testSaveOpenWeatherDataWithValidDataSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(OpenWeatherData::class));

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingTemperatureThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['temperature']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temperature"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingFeelsLikeThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['feels_like']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "feels_like"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingTempMinThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['temp_min']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temp_min"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingTempMaxThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['temp_max']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temp_max"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingHumidityThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['humidity']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "humidity"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingPressureThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['pressure']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "pressure"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingWeatherMainThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['weather_main']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "weather_main"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingWeatherDescriptionThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['weather_description']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "weather_description"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingWeatherIconThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['weather_icon']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "weather_icon"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingVisibilityThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['visibility']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "visibility"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingWindSpeedThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['wind_speed']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "wind_speed"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingWindDegThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['wind_deg']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "wind_deg"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingCloudsThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['clouds']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "clouds"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMissingCreatedAtThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        unset($data['created_at']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "created_at"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithValidationErrorsThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();

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

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithEmptyArrayThrowsException(): void
    {
        $data = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temperature"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataCreatesCorrectEntityValues(): void
    {
        $data = $this->getCompleteWeatherData();

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (OpenWeatherData $entity) use ($data) {
                return $entity->getWeatherMain() === $data['weather_main']
                    && $entity->getWeatherDescription() === $data['weather_description']
                    && $entity->getWeatherIcon() === $data['weather_icon']
                    && $entity->getTemperature() === $data['temperature']
                    && $entity->getFeelsLike() === $data['feels_like']
                    && $entity->getTempMin() === $data['temp_min']
                    && $entity->getTempMax() === $data['temp_max']
                    && $entity->getPressure() === $data['pressure']
                    && $entity->getHumidity() === $data['humidity']
                    && $entity->getVisibility() === $data['visibility']
                    && $entity->getWindSpeed() === $data['wind_speed']
                    && $entity->getWindDirection() === $data['wind_deg']
                    && $entity->getCloudiness() === $data['clouds']
                    && $entity->getTimestamp()->format('Y-m-d H:i:s') === $data['created_at'];
            }));

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithExtraFieldsSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['extra_field'] = 'extra_value';
        $data['another_field'] = 123;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithNegativeTemperatureSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['temperature'] = -15.0;
        $data['feels_like'] = -18.0;
        $data['temp_min'] = -20.0;
        $data['temp_max'] = -10.0;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithZeroValuesSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['temperature'] = 0.0;
        $data['feels_like'] = 0.0;
        $data['temp_min'] = 0.0;
        $data['temp_max'] = 0.0;
        $data['wind_speed'] = 0.0;
        $data['wind_deg'] = 0;
        $data['clouds'] = 0;
        $data['visibility'] = 0;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataValidationExecutesBeforeSave(): void
    {
        $data = $this->getCompleteWeatherData();

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

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->expectException(InvalidArgumentException::class);

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithDifferentDateFormatsSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['created_at'] = '2025-10-25T10:00:00+00:00';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testCreateOpenWeatherDataFromArrayWithValidDataSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();

        $result = $this->service->createOpenWeatherDataFromArray($data);

        $this->assertInstanceOf(OpenWeatherData::class, $result);
        $this->assertEquals($data['weather_main'], $result->getWeatherMain());
        $this->assertEquals($data['weather_description'], $result->getWeatherDescription());
        $this->assertEquals($data['weather_icon'], $result->getWeatherIcon());
        $this->assertEquals($data['temperature'], $result->getTemperature());
        $this->assertEquals($data['feels_like'], $result->getFeelsLike());
        $this->assertEquals($data['temp_min'], $result->getTempMin());
        $this->assertEquals($data['temp_max'], $result->getTempMax());
        $this->assertEquals($data['pressure'], $result->getPressure());
        $this->assertEquals($data['humidity'], $result->getHumidity());
        $this->assertEquals($data['visibility'], $result->getVisibility());
        $this->assertEquals($data['wind_speed'], $result->getWindSpeed());
        $this->assertEquals($data['wind_deg'], $result->getWindDirection());
        $this->assertEquals($data['clouds'], $result->getCloudiness());
        $this->assertEquals($data['created_at'], $result->getTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testCreateOpenWeatherDataFromArrayWithInvalidDateFormatThrowsException(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['created_at'] = 'invalid-date-format';

        $this->expectException(Exception::class);

        $this->service->createOpenWeatherDataFromArray($data);
    }

    public function testCreateOpenWeatherDataFromArrayWithMissingKeyThrowsInvalidArgumentException(): void
    {
        $data = [
            'weather_main' => 'Clear',
            'weather_description' => 'clear sky',
            'weather_icon' => '01d',
            'temperature' => 22.5,
            'feels_like' => 21.0,
            'temp_min' => 20.0,
            'temp_max' => 25.0,
            'pressure' => 1013,
            'humidity' => 65,
            'visibility' => 10000,
            'wind_speed' => 5.5,
            'wind_deg' => 180,
            'clouds' => 0,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "created_at"');

        $this->service->createOpenWeatherDataFromArray($data);
    }

    public function testSaveOpenWeatherDataWithHighWindSpeedSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['wind_speed'] = 150.5;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithMaxVisibilitySucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['visibility'] = 10000;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithFullCloudCoverSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['clouds'] = 100;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithNorthWindDirectionSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['wind_deg'] = 0;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithSouthWindDirectionSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['wind_deg'] = 180;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithEastWindDirectionSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['wind_deg'] = 90;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithWestWindDirectionSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['wind_deg'] = 270;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataWithFullCircleWindDirectionSucceeds(): void
    {
        $data = $this->getCompleteWeatherData();
        $data['wind_deg'] = 360;

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->service->saveOpenWeatherData($data);
    }

    private function getCompleteWeatherData(): array
    {
        return [
            'temperature' => 22.5,
            'feels_like' => 21.0,
            'temp_min' => 20.0,
            'temp_max' => 25.0,
            'humidity' => 65,
            'pressure' => 1013,
            'weather_main' => 'Clear',
            'weather_description' => 'clear sky',
            'weather_icon' => '01d',
            'visibility' => 10000,
            'wind_speed' => 5.5,
            'wind_deg' => 180,
            'clouds' => 20,
            'created_at' => '2025-10-25 10:00:00',
        ];
    }
}
