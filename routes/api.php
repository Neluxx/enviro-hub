<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticateApiToken::class)->prefix('v1')->group(function () {
    Route::post('/sensor-data', [SensorDataController::class, 'store']);
});
