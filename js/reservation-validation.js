(() => {
  'use strict';
  const byId = id => document.getElementById(id);
  const room = byId('resRoomSelect');
  const date = byId('resDate');
  const start = byId('resTimeStart');
  const end = byId('resTimeEnd');
  const attendees = byId('resAttendees');
  const submit = byId('submitResBtn');
  const status = byId('scheduleValidationError');
  if (!room || !date || !start || !end || !attendees || !submit || !status) return;
  const endpoint = new URL('../modules/check_availability.php', document.currentScript.src);
  let controller;
  let version = 0;
  let timer;
  let blocked = false;

  function show(element, text) {
    if (!element) return;
    element.textContent = text;
    element.classList.toggle('is-visible', Boolean(text));
  }
  function clear() {
    controller?.abort();
    clearTimeout(timer);
    version++;
    blocked = false;
    submit.disabled = false;
    submit.style.opacity = '';
    submit.style.cursor = '';
    [room, date, start, end, attendees].forEach(field => field.classList.remove('validation-invalid'));
    show(byId('roomValidationError'), '');
    show(byId('capacityValidationError'), '');
    show(status, '');
    status.dataset.state = '';
  }

  async function validate() {
    clear();
    if (!room.value) return;
    const requestVersion = version;
    controller = new AbortController();
    const params = new URLSearchParams({room_id: room.value, res_date: date.value,
      time_start: start.value, time_end: end.value, attendees: attendees.value || '0'});
    if (date.value && start.value) {
      status.dataset.state = 'checking';
      show(status, 'Checking room availability…');
    }
    try {
      const response = await fetch(`${endpoint}?${params}`, {credentials: 'include', cache: 'no-store', signal: controller.signal});
      const data = await response.json();
      if (requestVersion !== version) return;
      if (!data.success) {
        if (response.status >= 500) throw new Error('Unavailable');
        blocked = true;
        show(byId('roomValidationError'), data.message || 'Please check the selected date and times.');
        show(status, '');
      } else {
        blocked = Boolean(data.capacity_exceeded || data.maintenance);
        show(byId('capacityValidationError'), data.capacity_exceeded ? `This room has a capacity limit of ${data.capacity} students.` : '');
        attendees.classList.toggle('validation-invalid', Boolean(data.capacity_exceeded));
        show(byId('roomValidationError'), data.maintenance ? 'This room is under maintenance. Please select another room.' : '');
        room.classList.toggle('validation-invalid', Boolean(data.maintenance));
        // Conflicts are warnings and never disable submission.
        status.dataset.state = data.conflict ? 'waiting' : 'available';
        show(status, data.checked && !data.maintenance ? data.message : '');
      }
      submit.disabled = blocked;
    } catch (error) {
      if (requestVersion !== version || error.name === 'AbortError') return;
      status.dataset.state = 'waiting';
      show(status, 'Live availability could not be checked. Try again; your slot will be checked when you submit.');
    }
  }

  [room, date, start, end, attendees].forEach(field => {
    field.addEventListener('change', validate);
    field.addEventListener('input', () => {
      clear(); // Invalidate an in-flight response as soon as a field changes.
      timer = setTimeout(validate, 120);
    });
  });
  submit.addEventListener('click', event => {
    const required = [room, date, start, end, attendees];
    const invalid = required.find(field => !field.value || !field.checkValidity());
    if (invalid || blocked || (start.value && end.value && start.value >= end.value)) {
      event.preventDefault();
      event.stopImmediatePropagation();
      if (invalid) { invalid.focus(); invalid.reportValidity(); }
      if (start.value && end.value && start.value >= end.value) show(byId('roomValidationError'), 'End time must be after start time.');
    }
  }, true);
  byId('clearResBtn')?.addEventListener('click', clear);
  window.validateFacultyReservationForm = validate;
  setInterval(() => {
    if (!document.hidden && byId('tab-reserve')?.classList.contains('active') && room.value && date.value && start.value) validate();
  }, 20000);
})();
