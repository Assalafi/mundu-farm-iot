<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PumpControl;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sensor_type' => 'required|string|in:soil_ph,moisture',
            'value' => 'required|numeric|min:0',
        ]);

        $units = [
            'soil_ph' => 'pH',
            'moisture' => '%',
        ];

        $reading = SensorReading::create([
            'sensor_type' => $validated['sensor_type'],
            'value' => $validated['value'],
            'unit' => $units[$validated['sensor_type']],
            'recorded_at' => $request->input('recorded_at', now()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sensor reading recorded.',
            'data' => $reading,
        ], 201);
    }

    public function latest(): JsonResponse
    {
        $moisture = SensorReading::where('sensor_type', 'moisture')
            ->latest('recorded_at')
            ->first();

        $ph = SensorReading::where('sensor_type', 'soil_ph')
            ->latest('recorded_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'moisture' => $moisture,
                'soil_ph' => $ph,
            ],
            'pump_state' => PumpControl::currentState(),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'sensor_type' => 'required|string|in:soil_ph,moisture',
            'limit' => 'integer|min:1|max:500',
        ]);

        $readings = SensorReading::where('sensor_type', $request->sensor_type)
            ->latest('recorded_at')
            ->limit($request->input('limit', 100))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $readings,
        ]);
    }
}
