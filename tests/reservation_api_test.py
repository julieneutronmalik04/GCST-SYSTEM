"""Exercise real PHP requests using connection-local tables and a private router."""
from pathlib import Path
import json
import os
import secrets
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

root = Path(__file__).resolve().parents[2]
token = secrets.token_hex(16)
with socket.socket() as sock:
    sock.bind(('127.0.0.1', 0))
    port = sock.getsockname()[1]
with tempfile.TemporaryDirectory(prefix='gcst-reservation-api-') as folder:
    env = dict(os.environ, RESERVATION_TEST_TOKEN=token, RESERVATION_TEST_DIR=folder)
    server = subprocess.Popen([
        r'C:\xampp\php\php.exe', '-S', f'127.0.0.1:{port}',
        str(root / 'backend/tests/reservation_api_fixture.php')
    ], cwd=root, env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
       creationflags=subprocess.CREATE_NO_WINDOW)

    def request(path, method='GET', body=None, role='faculty', scenario='', form=False):
        headers = {'X-Test-Token': token, 'X-Test-Role': role, 'X-Test-Scenario': scenario}
        if body is not None:
            headers['Content-Type'] = 'application/x-www-form-urlencoded' if form else 'application/json'
        req = urllib.request.Request(f'http://127.0.0.1:{port}/' + path,
                                     data=(urllib.parse.urlencode(body).encode() if form else json.dumps(body).encode()) if body is not None else None,
                                     headers=headers, method=method)
        try:
            response = urllib.request.urlopen(req, timeout=8)
        except urllib.error.HTTPError as error:
            response = error
        data = json.load(response)
        state = json.loads((Path(folder) / 'state.json').read_text())
        return response.status, data, state

    try:
        for attempt in range(40):
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=.2):
                    break
            except OSError:
                time.sleep(.1)
        params = urllib.parse.urlencode({'room_id': 'TEST-ROOM', 'res_date': '2099-01-02', 'time_start': '09:30'})
        path = 'check_availability.php?' + params
        status, data, _ = request(path)
        assert status == 200 and data['checked'] and not data['conflict'] and not data['complete'], data
        status, data, _ = request(path, scenario='occupied')
        assert status == 200 and data['conflict'] and data['request_status'] == 'Waiting List', data
        payload = {'room_id': 'TEST-ROOM', 'res_date': '2099-01-02', 'time_start': '09:30', 'time_end': '10:30', 'attendees': 10}
        status, data, state = request('reservations.php', 'POST', payload)
        assert status == 200 and data['reservation_status'] == 'Pending', data
        assert state['reservations'][-1]['room_id'] == 7001 and state['reservations'][-1]['room_code'] == 'TEST-ROOM'
        status, data, state = request('reservations.php', 'POST', payload, scenario='occupied')
        assert status == 200 and data['reservation_status'] == 'Waiting List', data
        status, data, state = request('reservations.php', 'PATCH', {'id': 1, 'action': 'approve'}, 'admin')
        assert status == 200 and data['conflicting_requests_updated'] == 1, data
        assert [row['status'] for row in state['reservations']] == ['Approved', 'Rejected']
        assert len(state['faculty_notifications']) == 2
        status, data, _ = request('reservations.php', 'PATCH', {'id': 1, 'action': 'approve'})
        assert status == 403, data
        status, data, _ = request('reservations.php', 'PATCH', {'id': 2, 'action': 'cancel'})
        assert status == 403, data
        status, data, _ = request(path, role='guest')
        assert status == 401, data
        status, data, _ = request('reservations.php', 'POST', dict(payload, time_end='08:30'))
        assert status == 422 and not data['success'], data
        print('PASS: 9 reservation API scenarios, including partial checks, waiting-list submission, automatic rejection, notifications and access control.')
        status, data, _ = request('login.php', 'POST', {'username': 'fixture-20', 'password': 'Fixture-Pass-123'}, role='guest', form=True)
        assert status == 200 and data['role'] == 'vip' and data['redirect'].endswith('/faculty-dashboard.html'), data
        status, data, state = request('users.php', 'POST', {'username': 'new-vip', 'password': 'Fixture-Pass-123', 'full_name': 'Special Requester', 'role': 'vip'}, 'admin', form=True)
        assert status == 200 and data['success'] and state['users'][-1]['role'] == 'vip', data
        status, data, _ = request('users.php', role='vip')
        assert status == 403, data
        status, data, _ = request('reservations.php', 'POST', dict(payload, priority='Regular'), role='vip')
        assert status == 200 and data['priority'] == 'VIP', data
        status, data, _ = request('reservations.php', 'POST', dict(payload, priority='VIP'))
        assert status == 200 and data['priority'] == 'Regular', data
        status, data, _ = request('reservations.php', role='admin', scenario='vip')
        assert status == 200 and data['reservations'][0]['id'] == 3 and data['reservations'][0]['priority'] == 'VIP', data
        assert len(data['reservations'][0]['priority_conflicts']) == 1
        status, data, _ = request('reservations.php', role='vip', scenario='vip')
        assert status == 200 and [row['id'] for row in data['reservations']] == [3], data
        status, data, _ = request('reservations.php?id=1', role='vip', scenario='vip')
        assert status == 403, data
        status, data, state = request('reservations.php', 'PATCH', {'id': 3, 'action': 'approve'}, 'admin', 'vip')
        assert status == 409 and state['reservations'][0]['status'] == 'Approved', data
        status, data, state = request('reservations.php', 'PATCH', {'id': 3, 'action': 'approve', 'priority_override': True, 'expected_conflict_ids': [999]}, 'admin', 'vip')
        assert status == 409 and state['reservations'][0]['status'] == 'Approved', data
        status, data, state = request('reservations.php', 'PATCH', {'id': 3, 'action': 'approve', 'priority_override': True, 'expected_conflict_ids': [1]}, 'admin', 'vip')
        assert status == 200 and data['regular_bookings_waitlisted'] == 1, data
        assert [row['status'] for row in state['reservations']] == ['Waiting List', 'Waiting List', 'Approved']
        assert len(state['faculty_notifications']) == 3 and 'VIP priority' in state['faculty_notifications'][1]['body']
        status, data, state = request('reservations.php', 'PATCH', {'id': 3, 'action': 'approve', 'priority_override': True, 'expected_conflict_ids': [1]}, 'admin', 'vip-blocked')
        assert status == 409 and state['reservations'][0]['status'] == 'Approved', data
        status, data, _ = request('notifications.php', role='vip')
        assert status == 200 and data['success'], data
        status, data, _ = request('analytics.php', role='vip')
        assert status == 200 and data['success'] and sum(data['charts']['my_reservations']['data']) == 0, data
        status, data, state = request('users.php', 'PUT', {'id': 10, 'username': 'fixture-10', 'full_name': 'Promoted User', 'role': 'vip'}, 'admin', form=True)
        assert status == 200 and data['success'] and next(row for row in state['users'] if row['id'] == 10)['role'] == 'vip', data
        status, data, _ = request('auth_check.php', scenario='promoted-user')
        assert status == 200 and data['role'] == 'vip', data
        print('PASS: 16 VIP API scenarios, including login, role assignment and editing, refreshed session roles, server priority tagging, queue order, scoped history, confirmed displacement, notifications and VIP conflict protection.')
    finally:
        server.terminate()
        server.wait(timeout=5)
