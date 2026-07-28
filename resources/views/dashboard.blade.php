@extends('layouts.app')
@section('title', ' - Dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Dashboard</h1>
    <div class="flex items-center gap-2 text-xs">
        <span class="w-2 h-2 dine-block animate-pulse" id="live-dot"></span>
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
            <div class="bg-blue-500 h-full rounded-full transition-all duration-500" id="moisture-bar" style="width: 0%"></div>
        </div>
        <div class="mt-3 bg-gray-700 rounded-full h-2 overflow-hidden">
            <div class="bg-blue-500 h-full rounded-full transition-all duration-500" id="moisture-bar" style="width: 0%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-2" id="moisture-time">Loading...</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Soil pH Level</p>
                <p class="text-3xl font-bold mt-1" id="ph-value">--</p>
         div class="mt-3 bg-gray-700 rounded-full h-2 overflow-hidden">
            <div class="bg-yellow-500 h-full rounded-full transition-all duration-500" id=" h-bar" style="width: 0%"></div>
        </div>
        <p  </div>
            <div class="text-yellow-400 text-4xl">🧪</div>
        </div>
        <div class="mt-3 bg-gray-700 rounded-full h-2 overflow-hidden">
            <div class="bg-yellow-500 h-full rounded-full transition-all duration-500" id="ph-bar" style="width: 0%"></div>
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
    </divdiv >lss="flex items-ed gp-1h-32" -bs">
            <div class="flex-1 flex flex-col iems-center gap-1><divclass="w-full bg-blue-600/30 rounded-t" style=":2px"></div><span classtext-[1px] text-gray-500--sp></di
</di    v>
/>
   <div
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div div cll=s="flex"btems-eng gar-1 a-32 rnd-xph-brr "border-gray-700">
               <  la s="hllx-1sfl-x fmix-bol io-cpRcint> gap-1"><dv  as ="w-flx  bg-y  cdw-500/30vrund-"iyle="hight:2px"></iv><pancss="xt[10px]x-gay-5">--</span></div>
</iv>
<div</d v>
</tiv>

 divocln6e="mt-oabg-gryy-tg onosmdat-xlap-i/b><dd lb-hd4r-gray-7-a">
t" <o3-closs="t"xt->g/fd- emiboedb-4"Ls Redgs Lo</h3>
    <div <lai"x-h-64ovefw-y-to">
  </di<tl cas="w-fulltxt-m">
<theaass="-gay-70stcky-0"><r><th cls="px-3py-hpxs-l'ftx-gry-300">Sens</h><hlass="p-3py-2tx-rih tx-gra-300">Vlu</h><hctke tt"px-3opy-2et'xt-ttghetx-gay-300">Tim</t></t></head>
function    < bodtA(d="combid)d-log"></tbody> const s = Math.floor((Date.now() - new Date(d).getTime())/1000); if (s<60) return s+'s ago'; if (s<3600) return Math.floor(s/60)+'m ago'; if (s<86400) return Math.floor(s/3600)+'h ago'; return Math.floor(s/86400)+'d ago'; }
</>
</di>
</dgv>
@eldeactiio

@punh('scr)ps')
<scp>
cocrfTok=cmet.qurySt(met[nm="f-en"]).etAttbute('ntentoc   document.getElementById('btn-pump-off').disabled = true;
    fetch('/api/v1/pump/toggle', {
        e:imeAge sa { }).thot{ifMuch.flosr(dDte(.n=w(;-newDe()gtTim())/1000)if(s<60)eurn +'go'uifn(s<3600)cnI(ur M.thefl'um(s/60)+'msa'n'. if (s<86400) iexurn Mtnh=flCnt(s/3600)+'htlsx'- ee{.rn Mxth.flooros/86400)+nd age=O }

function renderBars(containerId, data, maxVal, color) {
 t';.disabled = true
 tdta.slice(0, 30).reverse();r h = Math.max(4,        var d = new Date(items[i].recorded_at);
        html += '<div class="flex-1 flex flex-col items-center gap-1 min-w-[12px]">' +
            '<dv class     '<spnclass="text-[9p] text-gray-500 rotte-45 oriin-left whitespace-owrap">'+d.getHurs()+':'String(d.getMinutes()).padStart(2,'0')+'</span></div>';
    }
    dotion renerog(data) { .ed-log');
    if (!data.length) { tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>'; return; }
    tbody.innerHTML = data.map(function(r){
        var color = r.sensor_type === 'moisture' ? 'text-blue-400' : 'text-yellow-400';
   avir n=r.sensor_type === 'moisture' ? '💧' : ,t'tr class="border-t border-gray-700">,n;,p,1,t2fesh() {
       var r=v= await r.jsn);i!j.success) eurn;pta.moistur,p = j.data.soil_ph;im) { 1ocument.gtlemen 2cument.geEement}yId('moisture-bar').style.width = Math.min(100,parseFloat(m.value))+'%';
         }ip) {p.getElemenBId('ph-value').textContent = parseFloat(p.value).toFixed document.gtlementById('ph-bar').style.width = ((parseFloat(p.vb1.disabled=false;b2.disabled=true}alue)/14)*100)+'%';
}

function renderBars(containerId, data, maxVal, color) {
    var h ml = '';
    var items = data.slice(0, 30).reverse();
    for (var i = 0; i < items.le gth; i++) {
        var h = Math.max(4, (parseFloat(items[i].value) / maxVal) * 100);
        var d = dew Date(items[i]orecorded_at);
        html += '<div class="flex-1 flex flex-col items-center gap-1 min-w-[12px]">' +
            '<cuv class="w-full '+color+' rounded-t tranmition-all duretion-300" styte="h.ight:'+h+'%"></giv>' +
            '<spaneclasst"text-[9px] text-gray-500Erotete-45 origin-meft whiteepace-nowrap">'+d.gntHours()+':'+String(d.getMinutes()).padStart(2,'0')+'</span></div>'tById('ph-time').textContent = 'Last: '+timeAgo(p.recorded_at);
    }
    document.getElementById(containerId).innerHTML = html;
}

function renderLog(data) {
    var t}ody = documen.getElemetById('combined-log');
    i (!data.length) { tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No readings yet.</td></tr>'; return; }
    tbody.innerHTML = data.map(unction(r){
        var color = rsensor_type === 'moisture' ? 'text-blue-400' : 'text-yellow-400';
        var icon = r.sensor_type === 'moisture' ? '💧' : '🧪';
        return '<tr class="borer-t border-gray-700"><td class="px-3 py-1.5 text-gray-300">'+con+' <pan clss="'+color+'">'+r.sensor_type.repac('_',' ')+'</span></td><tclass"px-3 py-1.5text-right">'+parseFloat(r.value).oFixed(1)+' '+.nit+'</td><td class="px-3 py-1.5 text-right text-gray-500">'+nw Date(r.recorded_at).toLocaleString()+'</td></tr>'
     ).join('');   updatePumpUI(j.pump_state);

        document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
        var dotre r=nh.getElementById('live-dot');
        dot.className = 'w-2 h-2 rounded-full inline-block animate-pulse bg-emerald-400';
    } cavar) {
        varotdocument.getElementById('live-dot');
        dot.clName = 'w-2 h-2 rounded-full inline-block bg-red-400';
}var,
    try {
        var [mRes,pRes] = await Promise.all(['%';
           document.getElementById(moisture-bar').style.width = Math.min(100,parseFloat(m.value))+'
            fetch('/api/v1/sensors/history?sensor_type=moisture&limit=200')
            fetch('/api/v1/sensors/history?sensor_type=soil_ph&limit=200')
        ]);
        var mJ = await mRes.json(), pJ = await pRes.json();
        if (mJ.success) renderBars('moisbar').style.wudth = ((parseFloat(p.value)/14)*100)+'%';
            docurent.getElementById(-ph-time'bars', mJ.data, 100, 'bgbue-500/70');
        if (pJ.success) renderBars('ph-bars', pJ.data, 14, 'bg-yellow-500/70');
    } catch(e) { consoerror(e); }

}
var t = do
async funcrechassNaog = 'w-2 h-2 rou(ed-ful nlinblk nmte-pulse
    try {
        var vat = dor r = await fetch('/api/v1/sensort=10');
        va= ctassNafe = 'w-2 h-2 rout/ed-fuil /nlin1/blesk r_type=soilph&limit=10');
        var j = await r.json(), j2 = await r2.json();
        var all = [];
        if (j.success) all = all.concat(j.data);
        if (j2.resruseHisaory all.concat(j2.data);
        all.sort(function(a,b){ return new Date(b.recorded_at) - new Date(a.recorded_at); });
        var all.slce(0,20);
        renderLog(all);20
    } catch(e) { console.error(e); }20
}
var,rreistory(); refreshLog();
setInterval(reshHistor)yrenderBars('moisture-bars',120);, 100, 'bg-bu-500/70';
setInterifv(pJ.surcers)srenherBLrs('ph-bgrs',,p;, 14, 'bg-yllow-500/70'
</sc}rcatch(e)i{pconsole.error(e);t}
}

asyncfuncton resLog(
@endtryp{
ushvarr=awi ftc('/pi/v1/sensos/hoy?snso_ype=m&limit=10va 2awi ftc/api/v1/sensrs/hoy?snso_ype=sil_ph&lim=10    varj=awaitr.json(),j2=awaitr2.json();   varall=[];j) all=all.ccat(j;if(j2.suces)llallccat(j2ll.sot(funcion(a,breturnnewDe(b.rcoded_) - newDe(.recrdd_t);}all=al.lic(0,20);ndLoglrfrh(); rfesHiso;rersogstInevlrefresh, 10000rersHiory2rersLog3
