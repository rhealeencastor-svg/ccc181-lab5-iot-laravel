<?php

use App\Http\Controllers\IotDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/iot-dashboard');
Route::get('/iot-dashboard', IotDashboardController::class)->name('iot.dashboard');
