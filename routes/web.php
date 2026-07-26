<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PumpWebController;
use App\Http\Controllers\SensorHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/sensors', [SensorHistoryController::class, 'index'])->name('sensors.index');

Route::get('/pump', [PumpWebController::class, 'index'])->name('pump.index');
Route::post('/pump/toggle', [PumpWebController::class, 'toggle'])->name('pump.toggle');
