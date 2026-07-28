@extends('layouts.app')
@section('title', ' - Sensor History')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Sensor Reading History</h1>
    <span class="text-xs text-gray-500" id="last-updated">--</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-blue-400 mb-4">Moisture History (24h)</h2>
        <div class="flex items-end gap-1 h-40" id="moisture-bars">
            <div class="flex-1 flex items-end justify-center text-gray-500 text-xs">Loading...</div>
        </div>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-yellow-400 mb-4">pH History (24h)</h2>
        <div class="flex items-end gap-1 h-40" id="ph-bars">
            <div class="flex-1 flex items-end justify-center text-gray-500 text-xs">Loading...</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="text-lg font-semibold mb-3 text-blue-400">Moisture Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 sticky top-0"><tr><th class="px-4 py-2 text-left text-gray-300">Time</th><th class="px-4 py-2 text-right text-gray-300">Value</th><th class="px-4 py-2 text-right text-gray-300">Unit</th></tr></thead>
                <tbody id="moisture-table"></tbody>
            </table>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-semibold mb-3 text-yellow-400">pH Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 sticky top-0"><tr><th class="px-4 py-2 text-left text-gray-300">Time</th><th class="px-4 py-2 text-right text-gray-300">Value</th><th class="px-4 py-2 text-right text-gray-300">Unit</th></tr></thead>
                <tbody id="ph-table"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function renderBars(containerId, data, maxVal, color) {
    var html = '';
    var items = data.slice(0, 100).reverse();
    for (var i = 0; i < items.length; i++) {
        var h = Math.max(2, (parseFloat(items[i].value) / maxVal) * 100);
        var d = new Date(items[i].recorded_at);
        html += '<div class="flex-1 flex flex-col items-center gap-1 min-w-[10px]" title="'+parseFloat(items[i].value).toFixed(1)+' at '+d.toLocaleString()+'">' +
            '<div class="w-full '+color+' rounded-t" style="height:'+h+'%;min-height:2px"></div>' +
            '<span class="text-[9px] text-gray-600">'+(i%10===0?d.getHours()+':'+String(d.getMinutes()).padStart(2,'0'):'')+'</span></div>';
    }
    document.getElementById(containerId).innerHTML = html;
}

function renderTable(tbodyId, data, isPh) {
    var tbody = document.getElementById(tbodyId);
    if (!data.length) { tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>'; return; }
    tbody.innerHTML = data.map(function(r){return'<tr class="border-t border-gray-700"><td class="px-4 py-2 text-gray-400">'+new Date(r.recorded_at).toLocaleString()+'</td><td class="px-4 py-2 text-right">'+parseFloat(r.value).toFixed(1)+'</td><td class="px-4 py-2 text-right text-gray-500">'+r.unit+'</td></tr>';}).join('');
}

async function loadHistory() {
    try {
        var [mRes,pRes] = await Promise.all([
            fetch('/api/v1/sensors/history?sensor_type=moisture&limit=500'),
            fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=500')
        ]);
        var mJson = await mRes.json(), pJson = await pRes.json();
        if (mJson.success) { renderBars('moisture-bars', mJson.data, 100, 'bg-blue-500/70'); renderTable('moisture-table', mJson.data, false); }
        if (pJson.success) { renderBars('ph-bars', pJson.data, 14, 'bg-yellow-500/70'); renderTable('ph-table', pJson.data, true); }
        document.getElementById('last-updated').textContent = 'Updated: '+new Date().toLocaleTimeString();
    } catch(e) { console.error(e); }
}

loadHistory();
setInterval(loadHistory, 120000);
</script>
@endpush
