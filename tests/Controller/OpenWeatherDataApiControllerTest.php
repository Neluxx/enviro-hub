<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OpenWeatherDataApiControllerTest extends WebTestCase
{
    public function testSaveDataSuccessfully(): void
    {
        $client = static::createClient();

        $data = $this->getValidOpenWeatherData();

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->getBearerToken(),
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Data saved successfully', $responseData['message']);
    }

    public function testSaveMultipleDataEntriesSuccessfully(): void
    {
        $client = static::createClient();

        $data = $this->getMultipleOpenWeatherData();

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->getBearerToken(),
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Data saved successfully', $responseData['message']);
    }

    public function testSaveDataWithMissingAuthorizationHeader(): void
    {
        $client = static::createClient();

        $data = $this->getValidOpenWeatherData();

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testSaveDataWithInvalidToken(): void
    {
        $client = static::createClient();

        $data = $this->getValidOpenWeatherData();

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token',
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testSaveDataWithMissingRequiredField(): void
    {
        $client = static::createClient();

        $data = $this->getValidOpenWeatherData();
        unset($data[0]['weather_main']);

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->getBearerToken(),
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('weather_main', $responseData['error']);
    }

    public function testSaveDataWithInvalidJsonFormat(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->getBearerToken(),
            ],
            'invalid json'
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testSaveDataWithInvalidDateFormat(): void
    {
        $client = static::createClient();

        $data = $this->getValidOpenWeatherData();
        $data[0]['created_at'] = 'invalid';

        $client->request(
            'POST',
            '/api/open-weather-data',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$this->getBearerToken(),
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testOnlyPostMethodIsAllowed(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/open-weather-data',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$this->getBearerToken()]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    private function getBearerToken(): string
    {
        return self::getContainer()->getParameter('bearer_token');
    }

    /**
     * Get valid open weather data for testing.
     *
     * @return array<array<string, mixed>>
     */
    private function getValidOpenWeatherData(): array
    {
        return [
            [
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
            ],
        ];
    }

    /**
     * Get multiple open weather data for testing.
     *
     * @return array<array<string, mixed>>
     */
    private function getMultipleOpenWeatherData(): array
    {
        return [
            [
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
            ],
            [
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
            ],
        ];
    }
}
