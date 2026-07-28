@extends('layouts.app')
@section('title', ' - Sensor History')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div class="flexitems-centerify-between mb-4">>
    <span class="text-xs text-gray-500" id="last-updated">--</span>
</div
    <h1 class="text-xl font-bold">Sensor Reading History</h1>
    <span class="text-xs text-gray-500" id="last-updated">--</span>
</div>
 (24h)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 m2b8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-blue-400 mb-4">Moisture Readings (24h)</h2>
        <canvas id="moistureHistoryChart" height="220"></canvas> (24h)
    </div>2
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-yellow-400 mb-4">pH Readings (24h)</h2>
        <canvas id="phHistoryChart" height="220"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="text-lg font-semibol0d sticky top- mb-3 text-blue-400">Recent Moisture Readings</h2>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-300">Time</th>
                        <th class="px-4 py-2 text-right text-gray-300">Value</th>
                       abmob eclass="w-full text-sm">
                <thead class="bg-gray-700 sticky top-0">
                    <tr>
                                  <th class="px-4 py-2 text-right text-gray-300">Value</th>
                        <th class="px-4 py-2 text-right text-gray-300">Unit</th>
                    </tr>
                </thead>
                <tbody id="ph-table"></tbody>
            </table>0 sticky top-
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>lhels, datasets: [{ label, data: values, borderColor: color, backgroundColor: color + '20', fill: true, tension: 0.3, pointRadius: 1 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#9ca3af' } } }, scales: { x: { ticks: { color: '#6b7280', maxTicksLimit: 10 }, grid: { color: '#374151' } }, y: { ticks: { color: '#6b7280' }, grid: { color: '#374151' } } } }
    });
}

function renderTable(tbodyId, data, isPh) {
    const tbody = document.getElementById(tbodyId);
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>';
le    returnChrll
le}Chrull
    tbody.innerHTML = data.map(r => '<tr class="border-t border-gray-700">' +
        'crea eclass="px-4 p t-gray-, data400">' + new Date(r.recorded_at).toLocaleString() + '</td>' +
        '<td class="px-4 py-2 text-right">' + (isPh ? parseFloat(r.value).toFixed(1) : parseFloat(r.value).toFixed(1)) + '</td>' +
        '<td class=4 pyht text-gray-500">' + r.unit + '</td>' +
        '</tr>').jo');
 }return

async function st [mRes, pRech('/apch('/api/v1/seJson = await mRes.json();Json = awaiton.success && m if     moistureChart = createChart('moistureHistoryChart', 'Moisture (%)', [...mJson.data].reverse(), '#3b82f6');
            render  maintainAspectRatio: false, pJson.success && pJson.data.length) { if (phCha art = createChart('phHistoryChart', 'Soil pH', [...pJson.data].reverse(), '#eab308 erTable('ph-table', pJson.data, true); } }
    });
}

function renderTable(tbodyId, data, isPh) {
}const tbody = document.getElementById(tbodyId);
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>';ocument.getElementById('last-updated').textContent = 'Updated: ' + new Date().toLocaleTimeString();
        returncatch (e) {
            console.error('History load error:', e);
    tbody.}nnerHTML= data.ap(r => '<tr class="brder-t border-gray-700">' +
        '<td clas="px-4 py-2 tex-gay-400">' + nw e(r.recorded_t)toLocaStrig() + '</td>' +
        '<td class="px-4 py-2 text-rih">' + (isP ? parseFloat(r.value).toFixed(1 :arseFloat(r.vaue).tFixed(1)) + '</td>' +
        '<td class="px-4 py-2 tex-rigt text-gray-500">' + r.unit + '</td>' +
        '</tr>').join('');
}

async function loadHistory() {
    try {
        const [mRes, pRes] = awit Pomise.all([
            fech/api/v1/sensors/history?sensor_type=&lmit=200'),
            fetch('/api/v1/ensors/his?sensor_type=soil_ph&limit=200')
        ]);
        const mJson = await mRes.json();
        const pJson = await pRes.json();

        if (mJson.success && mJson.data.length) {
            if (moistureChart) moisturet.desroy();
           reChart = ceatChrt('moistureHisoryChrt', [...mJson.data].reverse()');
            renderTable('moisture-table, mJson.data, false
        }
        Json.success && pJson.dh) {
            if (pCharthChart.destry();
            phChart = creae[...pJson.data].reverse(), ');
            renderTable(ph-table', pJson.data, true);
        }
        document.getElementById('last-updated').textContent = 'Updated: ' + new Date().toLocaleTimeString();
    } catch (e) {
        console.error('History load error:', e);
    }
}

loadHistory();
setInterval(loadHistory, 120000
document.addEventListener('chart-ready', () => {
    loadHistory();
    setInterval(loadHistory, 120000);
});
</script>
@endpush
