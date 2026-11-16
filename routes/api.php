<?php

use App\Http\Controllers\Api\SensorReadingController;
use App\Http\Middleware\AuthenticateDevice;
use Illuminate\Support\Facades\Route;

Route::post('/iot/readings', [SensorReadingController::class, 'store'])
    ->middleware(AuthenticateDevice::class);

// Open read-only endpoints for the local classroom demonstration.
Route::get('/iot/readings', [SensorReadingController::class, 'index']);
Route::get('/iot/readings/latest', [SensorReadingController::class, 'latest']);
