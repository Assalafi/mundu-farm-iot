<?php

namespace App\Http\Controllers;

use App\Models\PumpControl;
use Illuminate\Http\Request;

class PumpWebController extends Controller
{
    public function index()
    {
        $pumpOn = PumpControl::currentState();
        $history = PumpControl::latest('triggered_at')->limit(50)->get();

        return view('pump.index', compact('pumpOn', 'history'));
    }

    public function toggle(Request $request)
    {
        $action = $request->input('action', 'off');

        PumpControl::create([
            'action' => $action,
            'triggered_by' => 'web',
            'triggered_at' => now(),
        ]);

        return redirect()->route('pump.index')->with('success', "Pump turned {$action}.");
    }
}
