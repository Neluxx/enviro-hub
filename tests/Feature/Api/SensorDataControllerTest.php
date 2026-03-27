<?php

namespace Tests\Feature\Api;

use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SensorDataControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string API_URL = '/api/v1/sensor-data';
    private const string VALID_TOKEN = 'test-api-token';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.api.token' => self::VALID_TOKEN]);
    }

    // ---------------------------------------------------------------
    //  Authentication
    // ---------------------------------------------------------------

    public function test_returns_unauthorized_without_bearer_token(): void
    {
        $response = $this->postJson(self::API_URL, []);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_returns_unauthorized_with_invalid_bearer_token(): void
    {
        $response = $this->postJson(self::API_URL, [], [
            'Authorization' => 'Bearer wrong-token',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_accepts_valid_bearer_token(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(201);
    }

    // ---------------------------------------------------------------
    //  Validation
    // ---------------------------------------------------------------

    public function test_returns_validation_error_when_node_uuid_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['node_uuid']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['node_uuid']);
    }

    public function test_returns_validation_error_when_node_uuid_is_not_a_uuid(): void
    {
        $payload = $this->validPayload(['node_uuid' => 'not-a-uuid']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['node_uuid']);
    }

    public function test_returns_validation_error_when_measured_at_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['measured_at']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['measured_at']);
    }

    public function test_returns_validation_error_when_temperature_is_out_of_range(): void
    {
        $payload = $this->validPayload(['temperature' => 150]);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['temperature']);
    }

    public function test_returns_validation_error_when_temperature_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['temperature']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['temperature']);
    }

    public function test_returns_validation_error_when_humidity_is_out_of_range(): void
    {
        $payload = $this->validPayload(['humidity' => 110]);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['humidity']);
    }

    public function test_returns_validation_error_when_humidity_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['humidity']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['humidity']);
    }

    public function test_returns_validation_error_when_pressure_is_negative(): void
    {
        $payload = $this->validPayload(['pressure' => -1]);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pressure']);
    }

    public function test_returns_validation_error_when_pressure_is_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['pressure']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pressure']);
    }

    public function test_returns_validation_error_when_carbon_dioxide_is_negative(): void
    {
        $payload = $this->validPayload(['carbon_dioxide' => -5]);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['carbon_dioxide']);
    }

    // ---------------------------------------------------------------
    //  Successful storage
    // ---------------------------------------------------------------

    public function test_stores_sensor_data_and_returns_created(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(201)
            ->assertJson(['message' => 'Sensor data stored successfully.']);

        $this->assertDatabaseHas('sensor_data', [
            'node_uuid' => $payload['node_uuid'],
            'temperature' => $payload['temperature'],
            'humidity' => $payload['humidity'],
            'pressure' => $payload['pressure'],
            'carbon_dioxide' => $payload['carbon_dioxide'],
        ]);
    }

    public function test_creates_a_new_node_when_uuid_does_not_exist(): void
    {
        $uuid = Str::uuid()->toString();
        $payload = $this->validPayload(['node_uuid' => $uuid]);

        $this->postJson(self::API_URL, $payload, $this->authHeader())
            ->assertStatus(201);

        $this->assertDatabaseHas('nodes', ['uuid' => $uuid]);

        $node = Node::where('uuid', $uuid)->first();
        $this->assertDatabaseHas('sensor_data', [
            'node_uuid' => $uuid,
            'node_id' => $node->id,
        ]);
    }

    public function test_uses_existing_node_when_uuid_already_exists(): void
    {
        $node = Node::factory()->create();
        $payload = $this->validPayload(['node_uuid' => $node->uuid]);

        $this->postJson(self::API_URL, $payload, $this->authHeader())
            ->assertStatus(201);

        // No additional node should have been created
        $this->assertDatabaseCount('nodes', 1);

        $this->assertDatabaseHas('sensor_data', [
            'node_uuid' => $node->uuid,
            'node_id' => $node->id,
        ]);
    }

    public function test_stores_sensor_data_with_nullable_fields_omitted(): void
    {
        $payload = $this->validPayload();
        unset($payload['carbon_dioxide']);

        $response = $this->postJson(self::API_URL, $payload, $this->authHeader());

        $response->assertStatus(201);

        $this->assertDatabaseHas('sensor_data', [
            'node_uuid' => $payload['node_uuid'],
            'carbon_dioxide' => null,
        ]);
    }

    // ---------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'node_uuid' => Str::uuid()->toString(),
            'temperature' => 22.5,
            'humidity' => 45.3,
            'pressure' => 1013,
            'carbon_dioxide' => 420,
            'measured_at' => now()->toISOString(),
        ], $overrides);
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['Authorization' => 'Bearer ' . self::VALID_TOKEN];
    }
}
