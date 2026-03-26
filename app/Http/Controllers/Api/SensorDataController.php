<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreSensorDataRequest;
use App\Services\SensorDataService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SensorDataController extends BaseApiController
{
    public function __construct(
        private readonly SensorDataService $sensorDataService,
    ) {}

    /**
     * Store newly received sensor data.
     */
    public function store(StoreSensorDataRequest $request): JsonResponse
    {
        $sensorData = $this->sensorDataService->store($request->validated());

        return response()->json([
            'message' => 'Sensor data stored successfully.',
            'data' => $sensorData,
        ], Response::HTTP_CREATED);
    }
}
