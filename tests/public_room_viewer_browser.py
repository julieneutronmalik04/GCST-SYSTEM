"""Isolated Edge browser checks using the actual viewer markup and JavaScript."""
from pathlib import Path
import json
import re
import subprocess
import tempfile

root = Path(__file__).resolve().parents[2]
page = (root / 'frontend/room-availability.html').read_text(encoding='utf-8')
body = re.search(r'<body[^>]*>([\s\S]*?)<script src=', page).group(1)
script = root / 'backend/js/public-room-availability.js'
setup = r"""
const demo=[
 {id:7,room_code:'GA-309',room_name:'Classroom',type:'Classroom',capacity:30,location:'Main Building',status:'Available',schedule:[],current_reservation:null},
 {id:8,room_code:'LAB-1',room_name:'Lab <img src=x onerror=alert(1)>',type:'Laboratory',capacity:20,location:null,status:'Under Maintenance',schedule:[{start:'09:00:00',end:'10:00:00',is_current:false}],current_reservation:null}
];
let offline=false, tick, delayed=false, finish;
window.setInterval=callback=>{tick=callback;return 1};
window.fetch=async(url,opts)=>{
 if(opts.credentials!=='omit'||opts.cache!=='no-store')throw Error('Public fetch options');
 if(offline)throw Error('offline');
 const response={ok:true,json:async()=>({success:true,rooms:demo,date:'2026-10-22',checked_at:'2026-10-22 12:00:00'})};
 if(delayed)return new Promise(resolve=>finish=()=>resolve(response));
 return response;
};
"""
tests = r"""
(async()=>{
 const checks=[];
 const check=(v,s)=>{if(!v)throw Error(s);checks.push(s)};
 const wait=ms=>new Promise(resolve=>setTimeout(resolve,ms));
 const id=s=>document.getElementById(s);
 await wait(20);
 check(id('publicRoomList').options.length===2,'public room suggestions loaded');
 const scan=async text=>{
  id('publicRoomScan').value=text;
  const e=new KeyboardEvent('keydown',{key:'Enter',cancelable:true});
  id('publicRoomScan').dispatchEvent(e);
  check(e.defaultPrevented,'scanner Enter consumed');await wait(20);
 };
 await scan('  ga-309\r\n');
 check(!id('publicRoomDetails').hidden&&id('publicRoomCode').textContent==='GA-309','USB room code lookup');
 check(id('publicRoomCapacity').textContent==='30 seats'&&id('publicRoomLocation').textContent==='Main Building','room facts displayed');
 check(id('publicRoomStatus').textContent==='Available'&&id('publicRoomSchedule').textContent.includes('No approved'),'available room and empty schedule');
 await scan('008');
 check(id('publicRoomCode').textContent==='LAB-1'&&id('publicRoomStatus').textContent==='Under Maintenance','numeric ID lookup and maintenance status');
 check(!document.querySelector('img')&&id('publicRoomName').textContent.includes('<img'),'labels render as text without injection');
 check(id('publicRoomSchedule').textContent.includes('09:00\u201310:00'),'approved schedule times shown');
 await scan('GA-3');
 check(id('publicRoomDetails').hidden&&id('roomLookupMessage').dataset.error==='true','unknown or partial code hides previous room');
 id('publicRoomSearch').value='Classroom (GA-309)';id('publicRoomSearch').dispatchEvent(new Event('input'));await wait(20);
 check(id('publicRoomCode').textContent==='GA-309'&&!id('publicRoomDetails').hidden,'searchable dropdown selection');
 id('publicRoomScan').focus();demo[0].status='Occupied';demo[0].current_reservation={start:'11:00:00',end:'13:00:00',is_current:true};demo[0].schedule=[demo[0].current_reservation];tick();await wait(20);
 check(id('publicRoomStatus').textContent==='Occupied'&&id('publicRoomCurrent').textContent.includes('11:00\u201313:00'),'automatic refresh updates status while scanner focused');
 offline=true;id('availabilityRefresh').click();await wait(20);
 check(id('availabilityUpdated').textContent.includes('last successful check')&&id('roomLookupMessage').dataset.error==='true','failed refresh explicitly marks stale status');
 await scan('8');check(id('publicRoomDetails').hidden,'failed new lookup never shows previous room');offline=false;
 id('publicRoomScan').value='GA-309';id('publicRoomScan').dispatchEvent(new Event('input'));await wait(400);
 check(!id('publicRoomDetails').hidden,'USB input without Enter is debounced');
 delayed=true;id('publicRoomScan').value='LAB-1';id('publicRoomScanBtn').click();await wait(5);
 id('publicRoomSearch').value='par';id('publicRoomSearch').dispatchEvent(new Event('input'));finish();await wait(20);
 check(id('publicRoomDetails').hidden,'stale response cannot overwrite newer search');
 document.body.dataset.result='PASS';id('results').textContent=JSON.stringify(checks);
})().catch(e=>{document.body.dataset.result='FAIL';document.getElementById('results').textContent=e.stack});
"""
html = ('<!doctype html><html><head><meta charset="utf-8"></head><body>' + body
        + '<pre id="results"></pre><script>' + setup + '</script><script src="'
        + script.as_uri() + '"></script><script>' + tests + '</script></body></html>')
with tempfile.TemporaryDirectory(prefix='gcst-public-rooms-') as folder:
    path = Path(folder) / 'test.html'
    path.write_text(html, encoding='utf-8')
    result = subprocess.run([
        r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
        '--headless', '--no-sandbox', '--disable-gpu', '--disable-software-rasterizer',
        '--in-process-gpu', '--no-first-run', '--user-data-dir=' + folder + '/profile',
        '--dump-dom', '--virtual-time-budget=4000', path.as_uri()
    ], capture_output=True, text=True, encoding='utf-8', errors='replace', timeout=45)
    match = re.search(r'<pre id="results">([\s\S]*?)</pre>', result.stdout)
    print(match.group(1) if match else result.stderr[-1200:])
    if 'data-result="PASS"' not in result.stdout:
        raise SystemExit(1)
    print('PASS: public room viewer browser checks')
