<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\OpenWeatherDataRepository;
use App\Service\OpenWeatherDataService;
use DateTime;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeError;

class OpenWeatherDataServiceTest extends KernelTestCase
{
    private OpenWeatherDataService $service;
    private OpenWeatherDataRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $validator = $container->get(ValidatorInterface::class);
        $this->repository = $container->get(OpenWeatherDataRepository::class);

        $this->service = new OpenWeatherDataService(
            $validator,
            $this->repository
        );
    }

    public function testSaveOpenWeatherDataSuccess(): void
    {
        $data = $this->getValidOpenWeatherData();

        $this->service->saveOpenWeatherData($data);

        $lastEntry = $this->repository->getLastEntry();
        $this->assertSame('Clear', $lastEntry->getWeatherMain());
        $this->assertSame('clear sky', $lastEntry->getWeatherDescription());
        $this->assertSame('01d', $lastEntry->getWeatherIcon());
        $this->assertSame(22.5, $lastEntry->getTemperature());
        $this->assertSame(21.0, $lastEntry->getFeelsLike());
        $this->assertSame(20.0, $lastEntry->getTempMin());
        $this->assertSame(25.0, $lastEntry->getTempMax());
        $this->assertSame(1013, $lastEntry->getPressure());
        $this->assertSame(65, $lastEntry->getHumidity());
        $this->assertSame(10000, $lastEntry->getVisibility());
        $this->assertSame(5.5, $lastEntry->getWindSpeed());
        $this->assertSame(180, $lastEntry->getWindDirection());
        $this->assertSame(0, $lastEntry->getCloudiness());
        $this->assertEquals(new DateTime('2025-10-26 10:00:00'), $lastEntry->getTimestamp());
    }

    public function testSaveMultipleOpenWeatherDataEntries(): void
    {
        $data1 = [
            'weather_main' => 'Clouds',
            'weather_description' => 'broken clouds',
            'weather_icon' => '04d',
            'temperature' => 20.0,
            'feels_like' => 19.0,
            'temp_min' => 18.0,
            'temp_max' => 22.0,
            'pressure' => 1010,
            'humidity' => 60,
            'visibility' => 8000,
            'wind_speed' => 4.0,
            'wind_deg' => 90,
            'clouds' => 75,
            'created_at' => '2025-10-26 09:00:00',
        ];

        $data2 = [
            'weather_main' => 'Rain',
            'weather_description' => 'light rain',
            'weather_icon' => '10d',
            'temperature' => 23.0,
            'feels_like' => 22.5,
            'temp_min' => 21.0,
            'temp_max' => 25.0,
            'pressure' => 1015,
            'humidity' => 70,
            'visibility' => 5000,
            'wind_speed' => 6.5,
            'wind_deg' => 270,
            'clouds' => 90,
            'created_at' => '2025-10-26 10:00:00',
        ];

        $this->service->saveOpenWeatherData($data1);
        $this->service->saveOpenWeatherData($data2);

        $entries = $this->repository->getLatestEntries();
        $this->assertCount(2, $entries);

        $this->assertSame('Rain', $entries[0]->getWeatherMain());
        $this->assertSame('light rain', $entries[0]->getWeatherDescription());
        $this->assertSame('10d', $entries[0]->getWeatherIcon());
        $this->assertSame(23.0, $entries[0]->getTemperature());
        $this->assertSame(22.5, $entries[0]->getFeelsLike());
        $this->assertSame(21.0, $entries[0]->getTempMin());
        $this->assertSame(25.0, $entries[0]->getTempMax());
        $this->assertSame(1015, $entries[0]->getPressure());
        $this->assertSame(70, $entries[0]->getHumidity());
        $this->assertSame(5000, $entries[0]->getVisibility());
        $this->assertSame(6.5, $entries[0]->getWindSpeed());
        $this->assertSame(270, $entries[0]->getWindDirection());
        $this->assertSame(90, $entries[0]->getCloudiness());
        $this->assertEquals(new DateTime('2025-10-26 10:00:00'), $entries[0]->getTimestamp());

        $this->assertSame('Clouds', $entries[1]->getWeatherMain());
        $this->assertSame('broken clouds', $entries[1]->getWeatherDescription());
        $this->assertSame('04d', $entries[1]->getWeatherIcon());
        $this->assertSame(20.0, $entries[1]->getTemperature());
        $this->assertSame(19.0, $entries[1]->getFeelsLike());
        $this->assertSame(18.0, $entries[1]->getTempMin());
        $this->assertSame(22.0, $entries[1]->getTempMax());
        $this->assertSame(1010, $entries[1]->getPressure());
        $this->assertSame(60, $entries[1]->getHumidity());
        $this->assertSame(8000, $entries[1]->getVisibility());
        $this->assertSame(4.0, $entries[1]->getWindSpeed());
        $this->assertSame(90, $entries[1]->getWindDirection());
        $this->assertSame(75, $entries[1]->getCloudiness());
        $this->assertEquals(new DateTime('2025-10-26 09:00:00'), $entries[1]->getTimestamp());
    }

    public function testSaveOpenWeatherDataMissingWeatherMain(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['weather_main']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "weather_main"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingWeatherDescription(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['weather_description']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "weather_description"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingWeatherIcon(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['weather_icon']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "weather_icon"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingTemperature(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['temperature']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temperature"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingFeelsLike(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['feels_like']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "feels_like"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingTempMin(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['temp_min']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temp_min"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingTempMax(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['temp_max']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "temp_max"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingPressure(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['pressure']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "pressure"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingHumidity(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['humidity']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "humidity"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingVisibility(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['visibility']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "visibility"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingWindSpeed(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['wind_speed']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "wind_speed"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingWindDirection(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['wind_deg']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "wind_deg"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingClouds(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['clouds']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "clouds"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataMissingCreatedAt(): void
    {
        $data = $this->getValidOpenWeatherData();
        unset($data['created_at']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined array key "created_at"');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidWeatherMainFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['weather_main'] = 99;

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #1 ($weatherMain) must be of type string, int given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidWeatherDescriptionFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['weather_description'] = 99;

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #2 ($weatherDescription) must be of type string, int given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidWeatherIconFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['weather_icon'] = 99;

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #3 ($weatherIcon) must be of type string, int given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidTemperatureFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['temperature'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #4 ($temperature) must be of type float, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidFeelsLikeFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['feels_like'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #5 ($feelsLike) must be of type float, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidTempMinFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['temp_min'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #6 ($tempMin) must be of type float, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidTempMaxFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['temp_max'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #7 ($tempMax) must be of type float, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidPressureFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['pressure'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #8 ($pressure) must be of type int, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidHumidityFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['humidity'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #9 ($humidity) must be of type int, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidVisibilityFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['visibility'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #10 ($visibility) must be of type int, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidWindSpeedFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['wind_speed'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #11 ($windSpeed) must be of type float, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidWindDirectionFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['wind_deg'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #12 ($windDirection) must be of type int, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidCloudinessFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['clouds'] = 'invalid';

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #13 ($cloudiness) must be of type int, string given');

        $this->service->saveOpenWeatherData($data);
    }

    public function testSaveOpenWeatherDataInvalidDateFormat(): void
    {
        $data = $this->getValidOpenWeatherData();
        $data['created_at'] = 'invalid';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to parse time string (invalid) at position 0 (i)');

        $this->service->saveOpenWeatherData($data);
    }

    /**
     * Get valid open weather data for testing.
     *
     * @return array<string, mixed>
     */
    public function getValidOpenWeatherData(): array
    {
        return [
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
            'created_at' => '2025-10-26 10:00:00',
        ];
    }
}
