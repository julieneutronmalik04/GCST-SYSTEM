'use strict';

(() => {
  const body = document.getElementById('overdueTableBody');
  if (!body) return;
  const endpoint = new URL('../modules/overdue.php', document.currentScript.src);
  const count = document.getElementById('overdueCount');
  const note = document.getElementById('overdueModeNote');
  const feedback = document.getElementById('overdueFeedback');
  const refresh = document.getElementById('refreshOverdueBtn');
  let preview = true;
  let loading = false;

  function text(parent, tag, value, className) {
    const element = document.createElement(tag);
    element.textContent = value;
    if (className) element.className = className;
    parent.append(element);
    return element;
  }
  function message(value, error = false) {
    feedback.textContent = value;
    feedback.hidden = !value;
    feedback.classList.toggle('overdue-error', error);
  }
  async function request(options) {
    const response = await fetch(endpoint, { credentials: 'same-origin', cache: 'no-store', ...options });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Unable to load overdue records.');
    return data;
  }
  function render(rows) {
    body.replaceChildren();
    count.textContent = String(rows.length);
    if (!rows.length) {
      const cell = text(text(body, 'tr', ''), 'td', 'No overdue equipment.');
      cell.colSpan = 6;
      return;
    }
    for (const loan of rows) {
      const row = text(body, 'tr', '');
      const borrower = text(row, 'td', '');
      text(borrower, 'strong', loan.borrower_name);
      text(borrower, 'small', loan.student_faculty_id || 'No registered borrower linked');
      text(borrower, 'small', loan.contact_number || 'No contact number');
      if (loan.email) text(borrower, 'small', loan.email);
      const equipment = text(row, 'td', '');
      text(equipment, 'strong', loan.equip_code);
      text(equipment, 'small', loan.equipment_name);
      text(row, 'td', loan.due_date);
      text(row, 'td', String(loan.days_overdue));
      text(row, 'td', Number(loan.penalty_amount).toFixed(2));
      const actions = text(row, 'td', '');
      const status = loan.sms_status === 'Accepted' ? 'Accepted by provider' : loan.sms_status === 'Simulated' ? 'Preview recorded' : loan.sms_status || 'Not notified';
      text(actions, 'small', status);
      if (loan.sms_error) text(actions, 'small', loan.sms_error, 'overdue-error');
      if (!loan.student_faculty_id) {
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'modal-form-input overdue-link-input';
        input.placeholder = 'Student/Faculty ID';
        input.setAttribute('aria-label', `Registered borrower ID for ${loan.equip_code}`);
        actions.append(input);
        const link = text(actions, 'button', 'Link borrower', 'btn btn-secondary btn-sm');
        link.type = 'button';
        link.addEventListener('click', () => act(link, { action: 'link_borrower', id: loan.id, student_faculty_id: input.value.trim() }));
      } else {
        const notify = text(actions, 'button', preview ? 'Preview SMS' : 'Send SMS', 'btn btn-primary btn-sm');
        notify.type = 'button';
        const today = loan.sms_processed_at?.slice(0, 10);
        const handledToday = loan.sms_notification_date === loan.database_today;
        // The server enforces daily deduplication; the UI also prevents repeated accepted sends.
        notify.disabled = !loan.contact_number || (handledToday && ['Accepted', 'Processing', 'Unknown'].includes(loan.sms_status));
        if (handledToday && loan.sms_status === 'Accepted') notify.textContent = today ? `Accepted ${today}` : 'Accepted';
        notify.addEventListener('click', () => act(notify, { action: 'notify', id: loan.id }));
      }
    }
  }
  async function load() {
    if (loading) return;
    loading = true;
    refresh.disabled = true;
    try {
      const data = await request();
      preview = data.sms_mode === 'preview';
      note.textContent = (preview ? 'SMS preview mode: no messages are sent. ' : 'SMS reminders are enabled. ')
        + (Number(data.daily_penalty) > 0 ? `Daily penalty: ${Number(data.daily_penalty).toFixed(2)} per overdue day.` : 'No daily penalty is configured.');
      render(data.overdue);
    } catch (error) {
      message(error.message, true);
      note.textContent = 'Overdue records are unavailable. Select Check overdue to retry.';
    } finally {
      loading = false;
      refresh.disabled = false;
    }
  }
  async function act(button, fields) {
    button.disabled = true;
    message('');
    try {
      const data = await request({ method: 'POST', body: new URLSearchParams(fields) });
      message(data.message);
    } catch (error) {
      message(error.message, true);
    } finally {
      button.disabled = false;
      await load();
    }
  }
  refresh.addEventListener('click', () => { message(''); load(); });
  window.addEventListener('borrowing:changed', load);
  setInterval(() => {
    if (!document.hidden && document.getElementById('tab-borrowing')?.classList.contains('active')) load();
  }, 60000);
  load();
})();
