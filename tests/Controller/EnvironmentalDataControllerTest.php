<?php

namespace App\Tests\Controller;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentalDataControllerTest extends WebTestCase
{
    private const API_ENDPOINT = '/api/environmental-data';

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * Tests valid data is saved successfully, returning HTTP 201.
     */
    public function testSavingValidData(): void
    {
        $postData = [
            'temperature' => 25.5,
            'humidity' => 60,
            'pressure' => 1024,
            'co2' => 450,
            'created' => '2025-01-01 12:00:00',
        ];

        $responseContent = $this->makeRequest('POST', $postData);

        $this->assertArrayHasKey('message', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertStringContainsString('Data saved successfully', $responseContent['message']);
    }

    /**
     * Tests invalid JSON data returns HTTP 400.
     */
    public function testSavingInvalidJsonData(): void
    {
        $postData = 'invalid-json';

        $responseContent = $this->makeRequest('POST', $postData);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertStringContainsString('Invalid JSON data', $responseContent['error']);
    }

    /**
     * Tests missing required fields returns HTTP 400.
     */
    public function testSavingDataWithMissingFields(): void
    {
        $postData = [
            'temperature' => 25.5,
        ];

        $responseContent = $this->makeRequest('POST', $postData);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertStringContainsString('Undefined array key "created"', $responseContent['error']);
    }

    /**
     * Tests validation errors return HTTP 422.
     */
    public function testSavingInvalidFieldData(): void
    {
        $postData = [
            'temperature' => 'invalid',
            'humidity' => 60,
            'pressure' => 1024,
            'co2' => 450,
            'created' => '2025-01-01 12:00:00',
        ];

        $responseContent = $this->makeRequest('POST', $postData);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertStringContainsString('Invalid type for "temperature". Expected a numeric value.', $responseContent['error']);
    }

    /**
     * Reusable method for making requests and decoding responses.
     *
     * @param string $method HTTP method (e.g., 'POST')
     * @param array|string $data Request payload (JSON or raw string)
     *
     * @return array Decoded JSON response
     */
    private function makeRequest(string $method, array|string $data): array
    {
        $client = static::createClient();
        $client->request(
            $method,
            self::API_ENDPOINT,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        return json_decode($client->getResponse()->getContent(), true);
    }
}
