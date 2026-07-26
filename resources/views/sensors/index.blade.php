@extends('layouts.app')
@section('title', ' - Sensor History')

@section('content')
<h1 class="text-2xl font-bold mb-6">Sensor Reading History</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-blue-400 mb-4">Moisture Readings</h2>
        <canvas id="moistureHistoryChart" height="200"></canvas>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-yellow-400 mb-4">pH Readings</h2>
        <canvas id="phHistoryChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="text-lg font-semibold mb-3 text-blue-400">Recent Moisture Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-300">Time</th>
                        <th class="px-4 py-2 text-right text-gray-300">Value</th>
                        <th class="px-4 py-2 text-right text-gray-300">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($moistureReadings as $reading)
                        <tr class="border-t border-gray-700">
                            <td class="px-4 py-2 text-gray-400">{{ $reading->recorded_at->format('M d, H:i:s') }}</td>
                            <td class="px-4 py-2 text-right">{{ $reading->value }}</td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ $reading->unit }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No moisture readings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h2 class="text-lg font-semibold mb-3 text-yellow-400">Recent pH Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-300">Time</th>
                        <th class="px-4 py-2 text-right text-gray-300">Value</th>
                        <th class="px-4 py-2 text-right text-gray-300">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($phReadings as $reading)
                        <tr class="border-t border-gray-700">
                            <td class="px-4 py-2 text-gray-400">{{ $reading->recorded_at->format('M d, H:i:s') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($reading->value, 1) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ $reading->unit }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No pH readings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const moistureData = @json($moistureReadings);
const phData = @json($phReadings);

function plotChart(canvasId, data, label, color) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = [...data].reverse().map(r => new Date(r.recorded_at).toLocaleString());
    const values = [...data].reverse().map(r => parseFloat(r.value));
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data: values,
                borderColor: color,
                backgroundColor: color + '20',
                fill: true,
                tension: 0.3,
                pointRadius: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#9ca3af' } } },
            scales: {
                x: { ticks: { color: '#6b7280', maxTicksLimit: 10 }, grid: { color: '#374151' } },
                y: { ticks: { color: '#6b7280' }, grid: { color: '#374151' } }
            }
        }
    });
}

if (moistureData.length) plotChart('moistureHistoryChart', moistureData, 'Moisture (%)', '#3b82f6');
if (phData.length) plotChart('phHistoryChart', phData, 'Soil pH', '#eab308');
</script>
@endpush
