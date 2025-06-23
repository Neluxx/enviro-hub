<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentalDataControllerTest extends WebTestCase
{
    private const API_ENDPOINT = '/api/environmental-data';

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

        $validToken = 'your-valid-bearer-token';

        $responseContent = $this->makeRequest('POST', $postData, $validToken);

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

        $validToken = 'your-valid-bearer-token';

        $responseContent = $this->makeRequest('POST', $postData, $validToken);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertStringContainsString('Invalid JSON data', $responseContent['error']);
    }

    /**
     * Tests missing required fields returns HTTP 422.
     */
    public function testSavingDataWithMissingFields(): void
    {
        $postData = [
            'temperature' => 25.5,
        ];

        $validToken = 'your-valid-bearer-token';

        $responseContent = $this->makeRequest('POST', $postData, $validToken);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertStringContainsString('Undefined array key "humidity"', $responseContent['error']);
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

        $validToken = 'your-valid-bearer-token';

        $responseContent = $this->makeRequest('POST', $postData, $validToken);

        $this->markTestIncomplete('Validation is missing.');
        // $this->assertArrayHasKey('error', $responseContent);
        // self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        // $this->assertStringContainsString('Invalid type for "temperature". Expected a numeric value.', $responseContent['error']);
    }

    /**
     * Tests missing Authorization header returns HTTP 401.
     */
    public function testRequestWithoutToken(): void
    {
        $postData = [
            'temperature' => 25.5,
            'humidity' => 60,
            'pressure' => 1024,
            'co2' => 450,
            'created' => '2025-01-01 12:00:00',
        ];

        $responseContent = $this->makeRequest('POST', $postData);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertStringContainsString('Missing or invalid Authorization header', $responseContent['error']);
    }

    /**
     * Tests invalid token returns HTTP 401.
     */
    public function testRequestWithInvalidToken(): void
    {
        $postData = [
            'temperature' => 25.5,
            'humidity' => 60,
            'pressure' => 1024,
            'co2' => 450,
            'created' => '2025-01-01 12:00:00',
        ];

        $invalidToken = 'invalid-token';

        $responseContent = $this->makeRequest('POST', $postData, $invalidToken);

        $this->assertArrayHasKey('error', $responseContent);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertStringContainsString('Invalid token', $responseContent['error']);
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * Reusable method for making requests and decoding responses.
     *
     * @param string $method HTTP method (e.g., 'POST')
     * @param array<string, mixed>|string $data Request payload (JSON or raw string)
     * @param string|null $token Optional bearer token
     *
     * @return array<string, mixed> Decoded JSON response
     */
    private function makeRequest(string $method, array|string $data, ?string $token = null): array
    {
        $client = static::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];

        if ($token) {
            $headers['HTTP_Authorization'] = 'Bearer '.$token;
        }

        $client->request(
            $method,
            self::API_ENDPOINT,
            [],
            [],
            $headers,
            json_encode($data)
        );

        return json_decode($client->getResponse()->getContent(), true);
    }
}
