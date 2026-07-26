<?php

namespace App\Http\Controllers;

use App\Models\PumpControl;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $moisture = SensorReading::where('sensor_type', 'moisture')
            ->latest('recorded_at')
            ->first();

        $ph = SensorReading::where('sensor_type', 'soil_ph')
            ->latest('recorded_at')
            ->first();

        $pumpOn = PumpControl::currentState();

        $recentHistory = SensorReading::whereIn('sensor_type', ['moisture', 'soil_ph'])
            ->latest('recorded_at')
            ->limit(20)
            ->get()
            ->groupBy('sensor_type');

        return view('dashboard', compact('moisture', 'ph', 'pumpOn', 'recentHistory'));
    }
}
