"""Browser checks of the actual faculty form and live validation module."""
from pathlib import Path
import json
import re
import subprocess
import tempfile

root = Path(__file__).resolve().parents[2]
page = (root / 'frontend/faculty-dashboard.html').read_text(encoding='utf-8')
body = re.search(r'<body[^>]*>([\s\S]*?)</body>', page).group(1)
body = re.sub(r'<script\b[^>]*>[\s\S]*?</script>', '', body)
style = '\n'.join(re.findall(r'<style[^>]*>([\s\S]*?)</style>', page))
module = root / 'backend/js/reservation-validation.js'
setup = r"""
let delayed=false, finish, offline=false, requests=[], submits=0;
const get=id=>document.getElementById(id);
const room=get('resRoomSelect');
room.add(new Option('Test room','TEST-A'));room.add(new Option('Maintenance','TEST-M'));
get('resDate').value='2099-01-02';get('resAttendees').value='10';
get('submitResBtn').addEventListener('click',()=>submits++);
get('clearResBtn').addEventListener('click',()=>{['resRoomSelect','resTimeStart','resTimeEnd'].forEach(id=>get(id).value='')});
window.fetch=async(url,options)=>{
 const params=Object.fromEntries(new URL(url).searchParams);requests.push(params);
 if(offline)throw Error('offline');
 const conflict=params.room_id==='TEST-A'&&params.time_start<'10:00'&&(params.time_end===''?params.time_start>='09:00':params.time_end>'09:00');
 const maintenance=params.room_id==='TEST-M';
 const response={ok:true,status:200,json:async()=>({success:true,conflict,checked:!!params.time_start,capacity:30,capacity_exceeded:Number(params.attendees)>30,maintenance,message:conflict?'Warning: This room has a conflict at this time. Your request will be placed on the Waiting List.':'This room is available this time. Your request will be Pending admin approval.'})};
 if(delayed)return new Promise(resolve=>finish=()=>resolve(response));
 return response;
};
"""
tests = r"""
(async()=>{
 const checks=[];const check=(v,label)=>{if(!v)throw Error(label);checks.push(label)};
 const wait=ms=>new Promise(resolve=>setTimeout(resolve,ms));
 const change=async(id,value)=>{get(id).value=value;get(id).dispatchEvent(new Event('change'));await wait(20)};
 await change('resRoomSelect','TEST-A');await change('resTimeStart','09:30');
 check(requests.at(-1).time_end===''&&requests.at(-1).time_start==='09:30','start-only AJAX request made before end or purpose');
 check(get('scheduleValidationError').textContent.includes('Waiting List')&&!get('submitResBtn').disabled,'conflict warns but does not disable request');
 check(get('scheduleValidationError').dataset.state==='waiting'&&getComputedStyle(get('scheduleValidationError')).display!=='none','waiting message visible beside time fields');
 get('submitResBtn').click();check(submits===0,'incomplete request still requires end time');
 await change('resTimeEnd','10:30');get('submitResBtn').click();check(submits===1,'complete conflicting request can proceed to waiting list');
 await change('resTimeStart','10:00');await change('resTimeEnd','11:00');
 check(get('scheduleValidationError').dataset.state==='available'&&!get('submitResBtn').disabled,'free slot clears prior conflict and stays bookable');
 await change('resAttendees','31');check(get('submitResBtn').disabled&&get('capacityValidationError').textContent.includes('30'),'capacity limit still enforced');
 await change('resAttendees','20');check(!get('submitResBtn').disabled,'corrected capacity enables submission');
 await change('resRoomSelect','TEST-M');check(get('submitResBtn').disabled&&get('roomValidationError').textContent.includes('maintenance'),'maintenance blocks requests');
 await change('resRoomSelect','TEST-A');check(!get('submitResBtn').disabled,'choosing available room clears maintenance error');
 delayed=true;get('resTimeStart').value='09:00';get('resTimeStart').dispatchEvent(new Event('change'));await wait(5);
 get('resRoomSelect').value='';get('resRoomSelect').dispatchEvent(new Event('change'));finish();await wait(20);
 check(!get('scheduleValidationError').textContent&&!get('submitResBtn').disabled,'stale AJAX response ignored after room cleared');delayed=false;
 await change('resRoomSelect','TEST-A');offline=true;await change('resTimeStart','10:00');
 check(get('scheduleValidationError').textContent.includes('could not be checked')&&!get('submitResBtn').disabled,'network failure explains server recheck without false conflict block');offline=false;
 await change('resTimeStart','09:00');get('clearResBtn').click();
 check(!get('scheduleValidationError').textContent&&!get('submitResBtn').disabled,'clear removes status and old errors');
 COMPILE.forEach(code=>new Function(code));check(true,'shared, scanner, validation and inline dashboard scripts compile');
 document.body.dataset.result='PASS';get('results').textContent=JSON.stringify(checks);
})().catch(error=>{document.body.dataset.result='FAIL';get('results').textContent=error.stack});
"""
scripts = [(root / 'backend/js/script.js').read_text(encoding='utf-8'), module.read_text(encoding='utf-8'),
           (root / 'backend/js/room-scanner.js').read_text(encoding='utf-8')]
scripts += [s for s in re.findall(r'<script\b[^>]*>([\s\S]*?)</script>', page) if s.strip()]
tests = tests.replace('COMPILE', json.dumps(scripts).replace('</script', '<\\/script'))
html = ('<!doctype html><html><head><meta charset="utf-8"><style>' + style + '</style></head><body>'
        + body + '<pre id="results"></pre><script>' + setup + '</script><script src="'
        + module.as_uri() + '"></script><script>' + tests + '</script></body></html>')
with tempfile.TemporaryDirectory(prefix='gcst-reservation-validation-') as folder:
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
    print('PASS: live reservation validation browser checks')
