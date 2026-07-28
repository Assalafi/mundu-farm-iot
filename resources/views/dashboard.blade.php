@extends('layouts.app')
@section('title', ' - Dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Dashboard</h1>
    <div class="flex items-center gap-2 text-xs">
        <span class="w-2 h-2 rounded-full inline-block animate-pulse bg-emerald-400" id="live-dot"></span>
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
        <div class="mt-3 bg-gray-700 rounded-full h-2 overflow-hidden">
            <div class="bg-blue-500 h-full rounded-full transition-all duration-500" id="moisture-bar" style="width:0%"></div>
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
        <div class="mt-3 bg-gray-700 rounded-full h-2 overflow-hidden">
            <div class="bg-yellow-500 h-full rounded-full transition-all duration-500" id="ph-bar" style="width:0%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-2" id="ph-time">Loading...</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700" id="pump-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Water Pump</p>
                <p class="text-3xl font-bold mt-1 text-red-400">
                    <span id="pump-status">--</span>
                </p>
            </div>
            <div class="text-4xl" id="pump-icon">⏸️</div>
        </div>
        <div class="mt-4 flex gap-2">
            <button onclick="togglePump('on')" id="btn-pump-on" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition disabled:opacity-50">Turn ON</button>
            <button onclick="togglePump('off')" id="btn-pump-off" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition disabled:opacity-50">Turn OFF</button>
        </div>
        <a href="{{ route('pump.index') }}" class="text-xs text-blue-400 hover:underline mt-3 inline-block">Full history</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">Moisture History</h3>
        <div class="flex items-end gap-1 h-32" id="moisture-bars"></div>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">pH History</h3>
        <div class="flex items-end gap-1 h-32" id="ph-bars"></div>
    </div>
</div>

<div class="mt-6 bg-gray-800 rounded-xl p-6 border border-gray-700">
    <h3 class="text-lg font-semibold mb-4">Latest Readings</h3>
    <div class="max-h-64 overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-700 sticky top-0">
                <tr>
                    <th class="px-3 py-2 text-left text-gray-300">Sensor</th>
                    <th class="px-3 py-2 text-right text-gray-300">Value</th>
                    <th class="px-3 py-2 text-right text-gray-300">Time</th>
                </tr>
            </thead>
            <tbody id="combined-log"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function ago(d){
  var s = Math.floor((Date.now() - new Date(d).getTime())/1000);
  if(s<60) return s+'s';
  if(s<3600) return Math.floor(s/60)+'m';
  if(s<86400) return Math.floor(s/3600)+'h';
  return Math.floor(s/86400)+'d';
}

function togglePump(a){
  document.getElementById('btn-pump-on').disabled = true;
  document.getElementById('btn-pump-off').disabled = true;
  fetch('/api/v1/pump/toggle', {
    method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
    body:JSON.stringify({action:a,triggered_by:'web'})
  }).then(function(r){return r.json()}).then(function(d){if(d.success)pumpUI(a==='on')}).catch(function(){});
}

function pumpUI(on){
  var s=document.getElementById('pump-status');
  var i=document.getElementById('pump-icon');
  var c=document.getElementById('pump-card');
  var b1=document.getElementById('btn-pump-on');
  var b2=document.getElementById('btn-pump-off');
  if(on){
    s.textContent='ON'; i.textContent='⚡';
    c.className='bg-gray-800 rounded-xl p-6 border border-emerald-700';
    b1.disabled=true; b2.disabled=false;
  } else {
    s.textContent='OFF'; i.textContent='⏸️';
    c.className='bg-gray-800 rounded-xl p-6 border border-red-800';
    b1.disabled=false; b2.disabled=true;
  }
}

function bars(id,data,max,color){
  var h='',items=data.slice(0,30).reverse();
  for(var i=0;i<items.length;i++){
    var pct=Math.max(3,(parseFloat(items[i].value)/max)*100);
    var t=new Date(items[i].recorded_at);
    var ts=t.getHours()+':'+String(t.getMinutes()).padStart(2,'0');
    h+='<div class="flex-1 flex flex-col items-end min-w-[10px]" style="height:100%">'+
      '<div class="w-full '+color+' rounded-t transition-all duration-500" style="height:'+pct+'%;min-height:3px"></div>'+
      '<span class="text-[9px] text-gray-600 leading-none mt-0.5 whitespace-nowrap">'+ts+'</span></div>';
  }
  document.getElementById(id).innerHTML=h;
}

function log(data){
  var t=document.getElementById('combined-log');
  if(!data.length){t.innerHTML='<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>';return;}
  t.innerHTML=data.map(function(r){
    var c=r.sensor_type==='moisture'?'text-blue-400':'text-yellow-400';
    var icon=r.sensor_type==='moisture'?'💧':'🧪';
    return '<tr class="border-t border-gray-700">'+
      '<td class="px-3 py-1.5 text-gray-300">'+icon+' <span class="'+c+'">'+r.sensor_type.replace('_',' ')+'</span></td>'+
      '<td class="px-3 py-1.5 text-right">'+parseFloat(r.value).toFixed(1)+' '+r.unit+'</td>'+
      '<td class="px-3 py-1.5 text-right text-gray-500">'+new Date(r.recorded_at).toLocaleString()+'</td></tr>';
  }).join('');
}

async function refresh(){
  try{
    var r=await fetch('/api/v1/sensors/latest');
    var j=await r.json();
    if(!j.success)return;
    var m=j.data.moisture,p=j.data.soil_ph;
    if(m){
      document.getElementById('moisture-value').textContent=parseFloat(m.value).toFixed(1)+'%';
      document.getElementById('moisture-bar').style.width=Math.min(100,parseFloat(m.value))+'%';
      document.getElementById('moisture-time').textContent='Last: '+ago(m.recorded_at)+' ago';
    }
    if(p){
      document.getElementById('ph-value').textContent=parseFloat(p.value).toFixed(1)+' pH';
      document.getElementById('ph-bar').style.width=((parseFloat(p.value)/14)*100)+'%';
      document.getElementById('ph-time').textContent='Last: '+ago(p.recorded_at)+' ago';
    }
    pumpUI(j.pump_state);
    document.getElementById('last-updated').textContent=new Date().toLocaleTimeString();
  }catch(e){}
}

async function refreshHistory(){
  try{
    var a=await fetch('/api/v1/sensors/history?sensor_type=moisture&limit=200');
    var b=await fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=200');
    var m=await a.json(),p=await b.json();
    if(m.success)bars('moisture-bars',m.data,100,'bg-blue-500/70');
    if(p.success)bars('ph-bars',p.data,14,'bg-yellow-500/70');
  }catch(e){}
}

async function refreshLog(){
  try{
    var a=await fetch('/api/v1/sensors/history?sensor_type=moisture&limit=10');
    var b=await fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=10');
    var m=await a.json(),p=await b.json();
    var all=[];
    if(m.success)all=all.concat(m.data);
    if(p.success)all=all.concat(p.data);
    all.sort(function(x,y){return new Date(y.recorded_at)-new Date(x.recorded_at)});
    log(all.slice(0,20));
  }catch(e){}
}

refresh();refreshHistory();refreshLog();
setInterval(refresh,10000);
setInterval(refreshHistory,120000);
setInterval(refreshLog,60000);
</script>
@endpush
