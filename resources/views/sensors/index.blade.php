@extends('layouts.app')
@section('title', ' - Sensor History')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Sensor Reading History</h1>
    <span class="text-xs text-gray-500" id="last-updated">--</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-blue-400 mb-4">Moisture Readings (24h)</h2>
        <canvas id="moistureHistoryChart" height="220"></canvas>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-yellow-400 mb-4">pH Readings (24h)</h2>
        <canvas id="phHistoryChart" height="220"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="text-lg font-semibold mb-3 text-blue-400">Recent Moisture Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-300">Time</th>
                        <th class="px-4 py-2 text-right text-gray-300">Value</th>
                        <th class="px-4 py-2 text-right text-gray-300">Unit</th>
                    </tr>
                </thead>
                <tbody id="moisture-table"></tbody>
            </table>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-semibold mb-3 text-yellow-400">Recent pH Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-300">Time</th>
                        <th class="px-4 py-2 text-right text-gray-300">Value</th>
                        <th class="px-4 py-2 text-right text-gray-300">Unit</th>
                    </tr>
                </thead>
                <tbody id="ph-table"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let moistureChart = null;
let phChart = null;

function createChart(canvasId, label, data, color) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = data.map(r => new Date(r.recorded_at).toLocaleString());
    const values = data.map(r => parseFloat(r.value));
    return new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{ label, data: values, borderColor: color, backgroundColor: color + '20', fill: true, tension: 0.3, pointRadius: 1 }] },
        options: { responsive: true, maintainAspectRatio: false, animation: { duration: 400 }, plugins: { legend: { labels: { color: '#9ca3af' } } }, scales: { x: { ticks: { color: '#6b7280', maxTicksLimit: 10 }, grid: { color: '#374151' } }, y: { ticks: { color: '#6b7280' }, grid: { color: '#374151' } } } }
    });
}

function updateChart(chart, data, label, color) {
    if (!chart || !data.length) return;
    chart.data.labels = data.map(r => new Date(r.recorded_at).toLocaleString());
    chart.data.datasets[0].data = data.map(r => parseFloat(r.value));
    chart.data.datasets[0].label = label;
    chart.data.datasets[0].borderColor = color;
    chart.data.datasets[0].backgroundColor = color + '20';
    chart.update('none');
}

function renderTable(tbodyId, data, isPh) {
    const tbody = document.getElementById(tbodyId);
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>';
        return;
    }
    tbody.innerHTML = data.map(r => '<tr class="border-t border-gray-700">' +
        '<td class="px-4 py-2 text-gray-400">' + new Date(r.recorded_at).toLocaleString() + '</td>' +
        '<td class="px-4 py-2 text-right">' + (isPh ? parseFloat(r.value).toFixed(1) : parseFloat(r.value).toFixed(1)) + '</td>' +
        '<td class="px-4 py-2 text-right text-gray-500">' + r.unit + '</td>' +
        '</tr>').join('');
}

async function loadHistory() {
    try {
        const [mRes, pRes] = await Promise.all([
            fetch('/api/v1/sensors/history?sensor_type=moisture&limit=200'),
            fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=200')
        ]);
        const mJson = await mRes.json();
        const pJson = await pRes.json();

        if (mJson.success && mJson.data.length) {
            const mData = [...mJson.data].reverse();
            if (moistureChart) {
                updateChart(moistureChart, mData, 'Moisture (%)', '#3b82f6');
            } else {
                moistureChart = createChart('moistureHistoryChart', 'Moisture (%)', mData, '#3b82f6');
            }
            renderTable('moisture-table', mJson.data, false);
        }
        if (pJson.success && pJson.data.length) {
            const pData = [...pJson.data].reverse();
            if (phChart) {
                updateChart(phChart, pData, 'Soil pH', '#eab308');
            } else {
                phChart = createChart('phHistoryChart', 'Soil pH', pData, '#eab308');
            }
            renderTable('ph-table', pJson.data, true);
        }
        document.getElementById('last-updated').textContent = 'Updated: ' + new Date().toLocaleTimeString();
    } catch (e) {
        console.error('History load error:', e);
    }
}

document.addEventListener('chart-ready', () => {
    loadHistory();
    setInterval(loadHistory, 120000);
});
</script>
@endpush
