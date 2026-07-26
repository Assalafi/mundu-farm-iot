<?php

use App\Http\Controllers\Api\PumpController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('sensors/latest', [SensorController::class, 'latest']);
    Route::post('sensors/reading', [SensorController::class, 'store']);
    Route::get('sensors/history', [SensorController::class, 'history']);

    Route::get('pump/state', [PumpController::class, 'state']);
    Route::post('pump/toggle', [PumpController::class, 'toggle']);
    Route::get('pump/history', [PumpController::class, 'history']);
});
