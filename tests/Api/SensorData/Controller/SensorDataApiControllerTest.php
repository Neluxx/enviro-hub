<?php

declare(strict_types=1);

namespace App\Tests\Api\SensorData\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SensorDataApiControllerTest extends WebTestCase
{
    public function testSaveDataSuccessfully(): void
    {
        $client = static::createClient();

        $data = [
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 22.5,
                'humidity' => 55.0,
                'pressure' => 1013.25,
                'co2' => 400.0,
                'created_at' => '2025-10-26 12:00:00',
            ],
        ];

        $client->request(
            'POST',
            '/api/sensor-data',
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

        $data = [
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 22.5,
                'humidity' => 55.0,
                'pressure' => 1013.25,
                'co2' => 400.0,
                'created_at' => '2025-10-26 12:00:00',
            ],
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 23.0,
                'humidity' => 56.0,
                'pressure' => 1014.0,
                'co2' => 420.0,
                'created_at' => '2025-10-26 13:00:00',
            ],
        ];

        $client->request(
            'POST',
            '/api/sensor-data',
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

        $data = [
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 22.5,
                'humidity' => 55.0,
                'pressure' => 1013.25,
                'co2' => 400.0,
                'created_at' => '2025-10-26 12:00:00',
            ],
        ];

        $client->request(
            'POST',
            '/api/sensor-data',
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

        $data = [
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 22.5,
                'humidity' => 55.0,
                'pressure' => 1013.25,
                'co2' => 400.0,
                'created_at' => '2025-10-26 12:00:00',
            ],
        ];

        $client->request(
            'POST',
            '/api/sensor-data',
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

        $data = [
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 22.5,
                'humidity' => 55.0,
                'co2' => 400.0,
                'created_at' => '2025-10-26 12:00:00',
            ],
        ];

        $client->request(
            'POST',
            '/api/sensor-data',
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
        $this->assertStringContainsString('pressure', $responseData['error']);
    }

    public function testSaveDataWithInvalidJsonFormat(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/sensor-data',
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

        $data = [
            [
                'uuid' => 'test-node-uuid',
                'temperature' => 22.5,
                'humidity' => 55.0,
                'pressure' => 1013.25,
                'co2' => 400.0,
                'created_at' => 'invalid',
            ],
        ];

        $client->request(
            'POST',
            '/api/sensor-data',
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
            '/api/sensor-data',
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
}
