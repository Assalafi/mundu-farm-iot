<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PumpControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PumpController extends Controller
{
    public function state(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'pump_on' => PumpControl::currentState(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:on,off',
        ]);

        $control = PumpControl::create([
            'action' => $request->action,
            'triggered_by' => $request->input('triggered_by', 'api'),
            'triggered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pump turned {$request->action}.",
            'data' => $control,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $history = PumpControl::history($request->input('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
