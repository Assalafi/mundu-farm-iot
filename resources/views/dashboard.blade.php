@extends('layouts.app')
@section('title', ' - Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Soil Moisture</p>
                <p class="text-3xl font-bold mt-1" id="moisture-value">{{ $moisture ? $moisture->value . '%' : '--' }}</p>
            </div>
            <div class="text-blue-400 text-4xl">💧</div>
        </div>
        <p class="text-xs text-gray-500 mt-2" id="moisture-time">{{ $moisture ? 'Last: ' . $moisture->recorded_at->diffForHumans() : 'No data yet' }}</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Soil pH Level</p>
                <p class="text-3xl font-bold mt-1" id="ph-value">{{ $ph ? number_format($ph->value, 1) . ' pH' : '--' }}</p>
            </div>
            <div class="text-yellow-400 text-4xl">🧪</div>
        </div>
        <p class="text-xs text-gray-500 mt-2" id="ph-time">{{ $ph ? 'Last: ' . $ph->recorded_at->diffForHumans() : 'No data yet' }}</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700" id="pump-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Water Pump</p>
                <p class="text-3xl font-bold mt-1 pump-status-text {{ $pumpOn ? 'text-emerald-400' : 'text-red-400' }}">
                    <span id="pump-status">{{ $pumpOn ? 'ON' : 'OFF' }}</span>
                </p>
            </div>
            <div class="text-4xl" id="pump-icon">{{ $pumpOn ? '⚡' : '⏸️' }}</div>
        </div>
        <div class="mt-4 flex gap-2">
            <button onclick="togglePump('on')" id="btn-pump-on" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition disabled:opacity-50" {{ $pumpOn ? 'disabled' : '' }}>Turn ON</button>
            <button onclick="togglePump('off')" id="btn-pump-off" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition disabled:opacity-50" {{ !$pumpOn ? 'disabled' : '' }}>Turn OFF</button>
        </div>
        <a href="{{ route('pump.index') }}" class="text-xs text-blue-400 hover:underline mt-3 inline-block">Full history →</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">Recent Moisture Readings</h3>
        <canvas id="moistureChart" height="200"></canvas>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">Recent pH Readings</h3>
        <canvas id="phChart" height="200"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let moistureChart = null;
let phChart = null;

function createChart(canvasId, label, data, color, unit) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const labels = [...data].reverse().map(r => new Date(r.recorded_at).toLocaleTimeString());
    const values = [...data].reverse().map(r => parseFloat(r.value));
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label + ' (' + unit + ')',
                data: values,
                borderColor: color,
                backgroundColor: color + '20',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#9ca3af' } } },
            scales: {
                x: { ticks: { color: '#6b7280' }, grid: { color: '#374151' } },
                y: { ticks: { color: '#6b7280' }, grid: { color: '#374151' } }
            }
        }
    });
}

function togglePump(action) {
    const btnOn = document.getElementById('btn-pump-on');
    const btnOff = document.getElementById('btn-pump-off');
    btnOn.disabled = true;
    btnOff.disabled = true;

    fetch('/api/v1/pump/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ action: action, triggered_by: 'web' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updatePumpUI(action === 'on');
        }
    })
    .catch(err => console.error('Pump toggle failed:', err))
    .finally(() => {
        if (action === 'on') {
            btnOn.disabled = true;
            btnOff.disabled = false;
        } else {
            btnOn.disabled = false;
            btnOff.disabled = true;
        }
    });
}

function updatePumpUI(isOn) {
    const status = document.getElementById('pump-status');
    const icon = document.getElementById('pump-icon');
    const card = document.getElementById('pump-card');
    const statusText = status.parentElement;
    const btnOn = document.getElementById('btn-pump-on');
    const btnOff = document.getElementById('btn-pump-off');

    if (isOn) {
        status.textContent = 'ON';
        icon.textContent = '⚡';
        statusText.className = 'text-3xl font-bold mt-1 pump-status-text text-emerald-400';
        card.className = 'bg-gray-800 rounded-xl p-6 border border-emerald-700';
        btnOn.disabled = true;
        btnOff.disabled = false;
    } else {
        status.textContent = 'OFF';
        icon.textContent = '⏸️';
        statusText.className = 'text-3xl font-bold mt-1 pump-status-text text-red-400';
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
            document.getElementById('moisture-value').textContent = m.value + '%';
            document.getElementById('moisture-time').textContent = 'Last: ' + new Date(m.recorded_at).toLocaleTimeString();
        }
        if (p) {
            document.getElementById('ph-value').textContent = parseFloat(p.value).toFixed(1) + ' pH';
            document.getElementById('ph-time').textContent = 'Last: ' + new Date(p.recorded_at).toLocaleTimeString();
        }

        updatePumpUI(json.pump_state);
    } catch (e) {
        console.error('Live fetch failed:', e);
    }
}

async function fetchChartData() {
    try {
        const [mRes, pRes] = await Promise.all([
            fetch('/api/v1/sensors/history?sensor_type=moisture&limit=20'),
            fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=20')
        ]);
        const mJson = await mRes.json();
        const pJson = await pRes.json();

        if (mJson.success && mJson.data.length) {
            if (moistureChart) moistureChart.destroy();
            moistureChart = createChart('moistureChart', 'Moisture', mJson.data, '#3b82f6', '%');
        }
        if (pJson.success && pJson.data.length) {
            if (phChart) phChart.destroy();
            phChart = createChart('phChart', 'Soil pH', pJson.data, '#eab308', 'pH');
        }
    } catch (e) {
        console.error('Chart update failed:', e);
    }
}

const initialMoisture = @json(optional($recentHistory)['moisture'] ?? collect());
const initialPh = @json(optional($recentHistory)['soil_ph'] ?? collect());

if (initialMoisture.length) moistureChart = createChart('moistureChart', 'Moisture', initialMoisture, '#3b82f6', '%');
if (initialPh.length) phChart = createChart('phChart', 'Soil pH', initialPh, '#eab308', 'pH');

setInterval(fetchLatest, 5000);
setInterval(fetchChartData, 30000);
</script>
@endpush
