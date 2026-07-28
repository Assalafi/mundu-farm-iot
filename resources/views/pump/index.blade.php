@extends('layouts.app')
@section('title', ' - Pump Control')

@section('content')
<h1 class="text-2xl font-bold mb-6">Pump Control</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-8 border border-gray-700 text-center" id="pump-card">
        <div class="text-6xl mb-4" id="pump-icon">⏸️</div>
        <p class="text-xl font-bold mb-2" id="pump-status-text">
            Pump is currently <span class="uppercase" id="pump-status">--</span>
        </p>
        <p class="text-gray-500 text-sm mb-6" id="pump-message">Checking status...</p>
        <div class="flex gap-2">
            <button onclick="togglePump('on')" id="btn-pump-on" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-8 rounded-lg transition disabled:opacity-50">Turn Pump ON</button>
            <button onclick="togglePump('off')" id="btn-pump-off" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition disabled:opacity-50">Turn Pump OFF</button>
        </div>
        <p class="text-xs text-gray-600 mt-3" id="pump-updated">--</p>
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
                <tbody id="pump-log"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function updatePumpUI(isOn) {
    const status = document.getElementById('pump-status');
    const icon = document.getElementById('pump-icon');
    const card = document.getElementById('pump-card');
    const msg = document.getElementById('pump-message');
    const statusText = document.getElementById('pump-status-text');
    const btnOn = document.getElementById('btn-pump-on');
    const btnOff = document.getElementById('btn-pump-off');
    const updated = document.getElementById('pump-updated');

    updated.textContent = 'Updated: ' + new Date().toLocaleTimeString();

    if (isOn) {
        status.textContent = 'ON';
        icon.textContent = '⚡';
        statusText.className = 'text-xl font-bold mb-2 text-emerald-400';
        card.className = 'bg-gray-800 rounded-xl p-8 border border-emerald-700 text-center';
        msg.textContent = 'The water pump is running.';
        btnOn.disabled = true;
        btnOff.disabled = false;
    } else {
        status.textContent = 'OFF';
        icon.textContent = '⏸️';
        statusText.className = 'text-xl font-bold mb-2 text-red-400';
        card.className = 'bg-gray-800 rounded-xl p-8 border border-red-800 text-center';
        msg.textContent = 'The water pump is stopped.';
        btnOn.disabled = false;
        btnOff.disabled = true;
    }
}

function togglePump(action) {
    const btnOn = document.getElementById('btn-pump-on');
    const btnOff = document.getElementById('btn-pump-off');
    btnOn.disabled = true;
    btnOff.disabled = true;

    fetch('/api/v1/pump/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action, triggered_by: 'web' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updatePumpUI(action === 'on');
            fetchPumpLog();
        }
    })
    .catch(e => console.error('Toggle failed:', e));
}

async function fetchPumpState() {
    try {
        const r = await fetch('/api/v1/pump/state');
        const json = await r.json();
        if (json.success) updatePumpUI(json.pump_on);
    } catch (e) { console.error('State fetch failed:', e); }
}

async function fetchPumpLog() {
    try {
        const r = await fetch('/api/v1/pump/history?limit=30');
        const json = await r.json();
        if (!json.success) return;
        const tbody = document.getElementById('pump-log');
        if (!json.data.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No activity yet.</td></tr>';
            return;
        }
        tbody.innerHTML = json.data.map(e => {
            const badge = e.action === 'on'
                ? '<span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-900 text-emerald-400">ON</span>'
                : '<span class="px-2 py-0.5 rounded text-xs font-medium bg-red-900 text-red-400">OFF</span>';
            return '<tr class="border-t border-gray-700">' +
                '<td class="px-4 py-2 text-gray-400">' + new Date(e.triggered_at).toLocaleString() + '</td>' +
                '<td class="px-4 py-2 text-center">' + badge + '</td>' +
                '<td class="px-4 py-2 text-right text-gray-500">' + e.triggered_by + '</td>' +
                '</tr>';
        }).join('');
    } catch (e) { console.error('Log fetch failed:', e); }
}

document.addEventListener('chart-ready', () => {
    fetchPumpState();
    fetchPumpLog();
    setInterval(fetchPumpState, 15000);
    setInterval(fetchPumpLog, 60000);
});
</script>
@endpush
