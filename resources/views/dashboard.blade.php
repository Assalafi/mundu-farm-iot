@extends('layouts.app')
@section('title', ' - Dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Dashboard</h1>
    <div class="flex items-center gap-2 text-xs">
        <span class="w-2 h-2 bg-emerald-400 rounded-full inline-block animate-pulse" id="live-dot"></span>
        <span class="text-gray-500">Live</span>
        <span class="text-gray-600 ml-2" id="last-updated">--</span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Soil Moisture</p>
                <p class="text-3xl font-bold mt-1" id="moisture-value">--</p>
            </div>
            <div class="text-blue-400 text-4xl">💧</div>
        </div>
        <p class="text-xs text-gray-500 mt-2" id="moisture-time">Loading...</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Soil pH Level</p>
                <p class="text-3xl font-bold mt-1" id="ph-value">--</p>
            </div>
            <div class="text-yellow-400 text-4xl">🧪</div>
        </div>
        <p class="text-xs text-gray-500 mt-2" id="ph-time">Loading...</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700" id="pump-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Water Pump</p>
                <p class="text-3xl font-bold mt-1 pump-status-text text-red-400">
                    <span id="pump-status">--</span>
                </p>
            </div>
            <div class="text-4xl" id="pump-icon">⏸️</div>
        </div>
        <div class="mt-4 flex gap-2">
            <button onclick="togglePump('on')" id="btn-pump-on" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition disabled:opacity-50">Turn ON</button>
            <button onclick="togglePump('off')" id="btn-pump-off" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition disabled:opacity-50">Turn OFF</button>
        </div>
        <a href="{{ route('pump.index') }}" class="text-xs text-blue-400 hover:underline mt-3 inline-block">Full history →</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">Recent Moisture Readings</h3>
        <canvas id="moistureChart" height="220"></canvas>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">Recent pH Readings</h3>
        <canvas id="phChart" height="220"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let moistureChart = null;
let phChart = null;

function timeAgo(dateStr) {
    const seconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
    if (seconds < 60) return seconds + 's ago';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
    return Math.floor(seconds / 86400) + 'd ago';
}

function createChart(canvasId, label, data, color, unit) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = data.map(r => new Date(r.recorded_at).toLocaleTimeString());
    const values = data.map(r => parseFloat(r.value));
    return new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{ label: label + ' (' + unit + ')', data: values, borderColor: color, backgroundColor: color + '20', fill: true, tension: 0.3, pointRadius: 2 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#9ca3af' } } }, scales: { x: { ticks: { color: '#6b7280', maxTicksLimit: 8 }, grid: { color: '#374151' } }, y: { ticks: { color: '#6b7280' }, grid: { color: '#374151' } } } }
    });
}

function togglePump(action) {
    const btnOn = document.getElementById('btn-pump-on');
    const btnOff = document.getElementById('btn-pump-off');
    btnOn.disabled = true;
    btnOff.disabled = true;

    fetch('/api/v1/pump/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action: action, triggered_by: 'web' })
    })
    .then(r => r.json())
    .then(data => { if (data.success) updatePumpUI(action === 'on'); })
    .catch(e => console.error('Toggle failed:', e))
    .finally(() => updatePumpUI(action === 'on'));
}

function updatePumpUI(isOn) {
    const status = document.getElementById('pump-status');
    const icon = document.getElementById('pump-icon');
    const card = document.getElementById('pump-card');
    const statusParent = status.parentElement;
    const btnOn = document.getElementById('btn-pump-on');
    const btnOff = document.getElementById('btn-pump-off');

    if (isOn) {
        status.textContent = 'ON';
        icon.textContent = '⚡';
        statusParent.className = 'text-3xl font-bold mt-1 pump-status-text text-emerald-400';
        card.className = 'bg-gray-800 rounded-xl p-6 border border-emerald-700';
        btnOn.disabled = true;
        btnOff.disabled = false;
    } else {
        status.textContent = 'OFF';
        icon.textContent = '⏸️';
        statusParent.className = 'text-3xl font-bold mt-1 pump-status-text text-red-400';
        card.className = 'bg-gray-800 rounded-xl p-6 border border-red-800';
        btnOn.disabled = false;
        btnOff.disabled = true;
    }
}

async function fetchLatest() {
    try {
        const r = await fetch('/api/v1/sensors/latest');
        const json = await r.json();
        if (!json.success) return;

        const m = json.data.moisture;
        const p = json.data.soil_ph;

        if (m) {
            document.getElementById('moisture-value').textContent = parseFloat(m.value).toFixed(1) + '%';
            document.getElementById('moisture-time').textContent = 'Last: ' + timeAgo(m.recorded_at);
        }
        if (p) {
            document.getElementById('ph-value').textContent = parseFloat(p.value).toFixed(1) + ' pH';
            document.getElementById('ph-time').textContent = 'Last: ' + timeAgo(p.recorded_at);
        }
        updatePumpUI(json.pump_state);
        document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
        document.getElementById('live-dot').classList.remove('bg-gray-600');
        document.getElementById('live-dot').classList.add('bg-emerald-400');
    } catch (e) {
        document.getElementById('live-dot').classList.remove('bg-emerald-400');
        document.getElementById('live-dot').classList.add('bg-red-400');
    }
}

async function fetchChartData() {
    try {
        const [mRes, pRes] = await Promise.all([
            fetch('/api/v1/sensors/history?sensor_type=moisture&limit=30'),
            fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=30')
        ]);
        const mJson = await mRes.json();
        const pJson = await pRes.json();

        if (mJson.success && mJson.data.length) {
            if (moistureChart) moistureChart.destroy();
            moistureChart = createChart('moistureChart', 'Moisture', mJson.data.reverse(), '#3b82f6', '%');
        }
        if (pJson.success && pJson.data.length) {
            if (phChart) phChart.destroy();
            phChart = createChart('phChart', 'Soil pH', pJson.data.reverse(), '#eab308', 'pH');
        }
    } catch (e) { console.error('Chart fetch error:', e); }
}

document.addEventListener('chart-ready', () => {
    fetchLatest();
    fetchChartData();
    setInterval(fetchLatest, 10000);
    setInterval(fetchChartData, 60000);
});
</script>
@endpush
