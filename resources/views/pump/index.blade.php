@extends('layouts.app')
@section('title', ' - Pump Control')

@section('content')
<h1 class="text-2xl font-bold mb-6">Pump Control</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-8 border {{ $pumpOn ? 'border-emerald-700' : 'border-red-800' }} text-center">
        <div class="text-6xl mb-4">{{ $pumpOn ? '⚡' : '⏸️' }}</div>
        <p class="text-xl font-bold mb-2 {{ $pumpOn ? 'text-emerald-400' : 'text-red-400' }}">
            Pump is currently <span class="uppercase">{{ $pumpOn ? 'ON' : 'OFF' }}</span>
        </p>
        <p class="text-gray-500 text-sm mb-6">
            @if($pumpOn)
                The water pump is running. Click below to turn it off.
            @else
                The water pump is stopped. Click below to turn it on.
            @endif
        </p>
        <form method="POST" action="{{ route('pump.toggle') }}">
            @csrf
            @if($pumpOn)
                <input type="hidden" name="action" value="off">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                    Turn Pump OFF
                </button>
            @else
                <input type="hidden" name="action" value="on">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                    Turn Pump ON
                </button>
            @endif
        </form>
    </div>

    <div>
        <h2 class="text-lg font-semibold mb-3">Pump Activity Log</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-300">Time</th>
                        <th class="px-4 py-2 text-center text-gray-300">Action</th>
                        <th class="px-4 py-2 text-right text-gray-300">Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $entry)
                        <tr class="border-t border-gray-700">
                            <td class="px-4 py-2 text-gray-400">{{ $entry->triggered_at->format('M d, H:i:s') }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $entry->action === 'on' ? 'bg-emerald-900 text-emerald-400' : 'bg-red-900 text-red-400' }}">
                                    {{ strtoupper($entry->action) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ $entry->triggered_by }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No pump activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
