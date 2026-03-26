<?php

namespace App\Services;

use App\Models\Node;
use App\Models\SensorData;

class SensorDataService
{
    /**
     * Store sensor data and resolve (or create) the associated node.
     *
     * @param array<string, mixed> $validatedData
     */
    public function store(array $validatedData): SensorData
    {
        $node = Node::firstOrCreate(
            ['uuid' => $validatedData['node_uuid']],
            ['title' => 'stub'],
        );

        $validatedData['node_id'] = $node->id;

        return SensorData::create($validatedData);
    }
}
