<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;

class SensorHistoryController extends Controller
{
    public function index()
    {
        $moistureReadings = SensorReading::where('sensor_type', 'moisture')
            ->latest('recorded_at')
            ->limit(100)
            ->get();

        $phReadings = SensorReading::where('sensor_type', 'soil_ph')
            ->latest('recorded_at')
            ->limit(100)
            ->get();

        return view('sensors.index', compact('moistureReadings', 'phReadings'));
    }
}
