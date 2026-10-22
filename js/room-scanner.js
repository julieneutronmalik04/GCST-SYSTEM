/* USB QR scanners act as keyboards. Room QR payloads contain the room code. */
(() => {
  'use strict';
  const input = document.getElementById('roomQrScanInput');
  const select = document.getElementById('resRoomSelect');
  const status = document.getElementById('roomQrScanStatus');
  const grid = document.getElementById('facultyReserveRoomsGrid');
  if (!input || !select || !status) return;

  const endpoint = new URL('../modules/rooms.php', document.currentScript.src);
  let timer;
  let controller;
  let version = 0;
  let applyingScan = false;
  const normalize = value => String(value ?? '').trim().toUpperCase();
  const hint = 'Connect a USB QR scanner, then scan here. You can also type a room code or ID.';

  function message(text, error = false) {
    status.textContent = text;
    status.dataset.error = String(error);
  }

  function cancelLookup() {
    clearTimeout(timer);
    controller?.abort();
    version++;
  }

  function highlightRoom() {
    grid?.querySelectorAll('[data-room-pick], [data-reserve-room-id]').forEach(tile => {
      const selected = Boolean(select.value) &&
        (tile.dataset.roomPick || tile.dataset.reserveRoomId) === select.value;
      tile.classList.toggle('room-qr-selected', selected);
      tile.setAttribute('aria-current', String(selected));
    });
  }

  async function scanRoom() {
    cancelLookup();
    const term = normalize(input.value);
    if (!term) { message(hint); return; }
    const requestVersion = version;
    controller = new AbortController();
    message('Looking up room…');
    try {
      // Read all rooms so IDs and codes work independently of dashboard filters.
      const response = await fetch(endpoint, { credentials: 'include', cache: 'no-store', signal: controller.signal });
      const data = await response.json();
      if (requestVersion !== version) return;
      if (!response.ok || !data.success || !Array.isArray(data.rooms)) {
        throw new Error(data.message || 'Room lookup failed. Please try again.');
      }
      // Codes take priority, including room codes composed entirely of digits.
      const room = data.rooms.find(row => normalize(row.room_code || row.room_id) === term) ||
        (/^\d+$/.test(term) ? data.rooms.find(row => String(row.id) === term.replace(/^0+(?=\d)/, '')) : null);
      if (!room) { message('Room not found. Scan a registered room QR code or select manually below.', true); return; }
      if (room.status === 'Under Maintenance') {
        message(`${room.room_code || room.room_id} is under maintenance and cannot be reserved. Select another room.`, true);
        return;
      }

      const code = String(room.room_code || room.room_id);
      let option = [...select.options].find(item => item.value === code);
      if (!option) { option = new Option('', code); select.add(option); }
      option.textContent = `${code} — ${room.room_name} (${room.status})`;
      option.disabled = false;
      select.value = code;
      applyingScan = true;
      try { select.dispatchEvent(new Event('change', { bubbles: true })); }
      finally { applyingScan = false; }
      message(`Selected ${code} — ${room.room_name}. Current status: ${room.status}. Availability for your date and time is checked below.`);
      highlightRoom();
    } catch (error) {
      if (requestVersion === version && error.name !== 'AbortError') {
        message('Unable to look up rooms. Check your connection and try again, or select manually below.', true);
      }
    }
  }

  input.addEventListener('input', () => {
    cancelLookup();
    message(hint);
    if (input.value.trim()) timer = setTimeout(scanRoom, 350);
  });
  input.addEventListener('change', scanRoom);
  input.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
      event.preventDefault(); // Scanner suffix must never submit a reservation.
      scanRoom();
    }
  });
  document.getElementById('roomQrScanBtn')?.addEventListener('click', scanRoom);
  select.addEventListener('change', () => {
    if (!applyingScan) {
      cancelLookup();
      input.value = '';
      message(hint);
    }
    highlightRoom();
  });
  document.querySelector('.dash-tab[data-tab="reserve"]')?.addEventListener('click', () => {
    // The shared tab handler reveals the panel later in the click's bubbling phase.
    setTimeout(() => input.focus(), 0);
  });
  document.getElementById('clearResBtn')?.addEventListener('click', () => {
    cancelLookup();
    input.value = '';
    message(hint);
    highlightRoom();
    input.focus();
  });
  // Existing location filtering replaces tiles asynchronously; restore the outline.
  if (grid) new MutationObserver(highlightRoom).observe(grid, { childList: true });
})();
