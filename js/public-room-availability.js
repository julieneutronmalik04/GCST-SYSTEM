(() => {
  'use strict';
  const byId = id => document.getElementById(id);
  const scan = byId('publicRoomScan');
  const search = byId('publicRoomSearch');
  const list = byId('publicRoomList');
  const details = byId('publicRoomDetails');
  const message = byId('roomLookupMessage');
  const updated = byId('availabilityUpdated');
  const refreshButton = byId('availabilityRefresh');
  if (!scan || !search || !list || !details) return;

  const endpoint = new URL('../get-room-status.php', document.currentScript.src);
  let rooms = [];
  let selectedId = null;
  let controller;
  let version = 0;
  let timer;
  let lastChecked = '';
  const normalize = value => String(value ?? '').trim().toUpperCase();
  const label = room => `${room.room_name} (${room.room_code})`;
  const time = value => String(value || '').slice(0, 5);

  function say(text, error = false) {
    message.textContent = text;
    message.dataset.error = String(error);
  }

  function invalidate() {
    clearTimeout(timer);
    controller?.abort();
    version++;
  }

  function hideDetails() {
    selectedId = null;
    details.hidden = true;
    byId('availabilityEmpty').hidden = false;
  }

  function matchRoom(raw) {
    const term = normalize(raw);
    const exact = rooms.find(room => normalize(room.room_code) === term || normalize(label(room)) === term);
    if (exact) return exact;
    if (/^\d+$/.test(term)) {
      const byNumber = rooms.find(room => String(room.id) === term.replace(/^0+(?=\d)/, ''));
      if (byNumber) return byNumber;
    }
    const byName = rooms.filter(room => normalize(room.room_name) === term);
    return byName.length === 1 ? byName[0] : null;
  }

  function render(room, date) {
    selectedId = room.id;
    byId('publicRoomCode').textContent = room.room_code;
    byId('publicRoomName').textContent = room.room_name;
    const badge = byId('publicRoomStatus');
    badge.textContent = room.status;
    badge.dataset.status = room.status;
    byId('publicRoomCapacity').textContent = `${room.capacity} seats`;
    byId('publicRoomLocation').textContent = room.location || 'Not specified';
    byId('publicRoomType').textContent = room.type || 'Not specified';
    byId('publicScheduleDate').textContent = ` · ${date}`;
    const current = room.current_reservation;
    byId('publicRoomCurrent').textContent = room.status === 'Under Maintenance'
      ? 'This room is currently under maintenance.'
      : current ? `An approved reservation is in progress: ${time(current.start)}–${time(current.end)}.`
      : room.status === 'Occupied' ? 'This room is marked occupied. No approved reservation is currently in progress.'
      : 'This room is available now. Check today’s schedule below for upcoming reservations.';
    const schedule = byId('publicRoomSchedule');
    schedule.replaceChildren();
    (room.schedule || []).forEach(slot => {
      const li = document.createElement('li');
      li.dataset.current = String(slot.is_current);
      const range = document.createElement('span');
      range.textContent = `${time(slot.start)}–${time(slot.end)}`;
      const status = document.createElement('span');
      status.textContent = slot.is_current ? 'In progress' : 'Approved';
      li.append(range, status);
      schedule.append(li);
    });
    if (!schedule.children.length) {
      const li = document.createElement('li');
      li.textContent = 'No approved reservations today.';
      schedule.append(li);
    }
    details.hidden = false;
    byId('availabilityEmpty').hidden = true;
  }

  async function refresh(target = null) {
    invalidate();
    const requestVersion = version;
    controller = new AbortController();
    refreshButton.disabled = true;
    if (target) say('Checking live room availability…');
    try {
      const response = await fetch(endpoint, { credentials: 'omit', cache: 'no-store', signal: controller.signal });
      const data = await response.json();
      if (requestVersion !== version) return;
      if (!response.ok || !data.success || !Array.isArray(data.rooms)) throw new Error('Lookup failed');
      rooms = data.rooms;
      list.replaceChildren(...rooms.map(room => new Option(label(room))));
      lastChecked = data.checked_at;
      updated.textContent = `Last checked: ${lastChecked} (school system time). Refreshes every 30 seconds.`;
      if (target) {
        const room = matchRoom(target);
        if (!room) {
          hideDetails();
          say('Room not found. Scan a registered room QR code or choose a room suggestion.', true);
          return;
        }
        search.value = label(room);
        render(room, data.date);
        say(`Showing live availability for ${room.room_code}.`);
      } else if (selectedId !== null) {
        const room = rooms.find(row => row.id === selectedId);
        if (room) { render(room, data.date); say(`Showing live availability for ${room.room_code}.`); }
        else { hideDetails(); say('This room is no longer listed. Please select another room.', true); }
      } else {
        say(rooms.length ? 'Scan a room QR code or choose a suggestion from the searchable room list.' : 'No rooms are currently listed.');
      }
    } catch (error) {
      if (requestVersion !== version || error.name === 'AbortError') return;
      say('Unable to check availability. Please check your connection and try Refresh Now.', true);
      updated.textContent = details.hidden ? 'Live availability is temporarily unavailable.' : `Last checked: ${lastChecked} (school system time). Showing the last successful check; live refresh failed.`;
    } finally {
      if (requestVersion === version) refreshButton.disabled = false;
    }
  }

  function lookupScan() {
    const raw = scan.value.trim();
    if (!raw) { say('Scan or enter a room code or ID first.'); return; }
    hideDetails();
    refresh(raw);
  }
  scan.addEventListener('input', () => {
    invalidate();
    refreshButton.disabled = false;
    hideDetails();
    if (scan.value.trim()) timer = setTimeout(lookupScan, 350);
  });
  scan.addEventListener('change', lookupScan);
  scan.addEventListener('keydown', event => {
    if (event.key === 'Enter') { event.preventDefault(); lookupScan(); }
  });
  byId('publicRoomScanBtn').addEventListener('click', lookupScan);
  function searchRoom() {
    invalidate();
    refreshButton.disabled = false;
    hideDetails();
    scan.value = '';
    if (search.value.trim() && (!rooms.length || matchRoom(search.value))) refresh(search.value.trim());
    else say('Choose a matching suggestion, or enter an exact room code or ID.');
  }
  search.addEventListener('input', searchRoom);
  search.addEventListener('change', searchRoom);
  search.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      hideDetails();
      if (search.value.trim()) refresh(search.value.trim());
    }
  });
  refreshButton.addEventListener('click', () => {
    const target = selectedId === null ? (scan.value.trim() || search.value.trim() || null) : null;
    refresh(target);
  });
  setInterval(() => {
    const choosing = selectedId === null && (document.activeElement === scan || document.activeElement === search);
    if (!document.hidden && !refreshButton.disabled && !choosing) refresh();
  }, 30000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
  refresh();
})();
