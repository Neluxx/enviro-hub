<?php

namespace Tests\Unit\Services;

use App\Models\Node;
use App\Models\SensorData;
use App\Services\SensorDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SensorDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private SensorDataService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SensorDataService();
    }

    // ---------------------------------------------------------------
    //  Node resolution
    // ---------------------------------------------------------------

    public function test_creates_new_node_when_uuid_does_not_exist(): void
    {
        $uuid = Str::uuid()->toString();

        $this->service->store($this->validData(['node_uuid' => $uuid]));

        $this->assertDatabaseHas('nodes', ['uuid' => $uuid]);
        $this->assertDatabaseCount('nodes', 1);
    }

    public function test_new_node_receives_stub_title(): void
    {
        $uuid = Str::uuid()->toString();

        $this->service->store($this->validData(['node_uuid' => $uuid]));

        $node = Node::where('uuid', $uuid)->first();
        $this->assertSame('stub', $node->title);
    }

    public function test_reuses_existing_node_when_uuid_already_exists(): void
    {
        $node = Node::factory()->create();

        $this->service->store($this->validData(['node_uuid' => $node->uuid]));

        $this->assertDatabaseCount('nodes', 1);
    }

    public function test_assigns_existing_node_id_to_sensor_data(): void
    {
        $node = Node::factory()->create();

        $sensorData = $this->service->store($this->validData(['node_uuid' => $node->uuid]));

        $this->assertSame($node->id, $sensorData->node_id);
    }

    public function test_assigns_newly_created_node_id_to_sensor_data(): void
    {
        $uuid = Str::uuid()->toString();

        $sensorData = $this->service->store($this->validData(['node_uuid' => $uuid]));

        $node = Node::where('uuid', $uuid)->first();
        $this->assertSame($node->id, $sensorData->node_id);
    }

    // ---------------------------------------------------------------
    //  Sensor data persistence
    // ---------------------------------------------------------------

    public function test_stores_sensor_data_in_database(): void
    {
        $data = $this->validData();

        $this->service->store($data);

        $this->assertDatabaseHas('sensor_data', [
            'node_uuid' => $data['node_uuid'],
            'temperature' => $data['temperature'],
            'humidity' => $data['humidity'],
            'pressure' => $data['pressure'],
            'carbon_dioxide' => $data['carbon_dioxide'],
        ]);
    }

    public function test_returns_sensor_data_model(): void
    {
        $result = $this->service->store($this->validData());

        $this->assertInstanceOf(SensorData::class, $result);
        $this->assertTrue($result->exists);
    }

    public function test_stores_multiple_readings_for_same_node(): void
    {
        $uuid = Str::uuid()->toString();

        $this->service->store($this->validData(['node_uuid' => $uuid, 'temperature' => 20.0]));
        $this->service->store($this->validData(['node_uuid' => $uuid, 'temperature' => 25.0]));

        $this->assertDatabaseCount('nodes', 1);
        $this->assertDatabaseCount('sensor_data', 2);
    }

    public function test_stores_sensor_data_with_null_carbon_dioxide(): void
    {
        $data = $this->validData(['carbon_dioxide' => null]);

        $sensorData = $this->service->store($data);

        $this->assertNull($sensorData->carbon_dioxide);
    }

    // ---------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'node_uuid' => Str::uuid()->toString(),
            'temperature' => 22.50,
            'humidity' => 45.30,
            'pressure' => 1013,
            'carbon_dioxide' => 420,
            'measured_at' => now()->toDateTimeString(),
        ], $overrides);
    }
}
