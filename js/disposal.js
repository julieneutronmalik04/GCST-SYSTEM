'use strict';
(() => {
  const pending = document.getElementById('disposalRequestBody');
  if (!pending) return;
  const approved = document.getElementById('disposalReportBody');
  const feedback = document.getElementById('disposalFeedback');
  const endpoint = new URL('../modules/disposal.php', document.currentScript.src);
  function text(parent, tag, value, className) {
    const element = document.createElement(tag);
    element.textContent = value ?? '';
    if (className) element.className = className;
    parent.append(element);
    return element;
  }
  const money = value => Number(value || 0).toFixed(2);
  async function request(options) {
    const response = await fetch(endpoint, { credentials: 'same-origin', cache: 'no-store', ...options });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Unable to process disposal requests.');
    return data;
  }
  function show(message) { feedback.textContent = message; feedback.hidden = !message; }
  function render(body, records, isPending) {
    body.replaceChildren();
    if (!records.length) {
      const cell = text(text(body, 'tr', ''), 'td', isPending ? 'No pending disposal requests.' : 'No approved disposed equipment.');
      cell.colSpan = isPending ? 6 : 7;
    }
    for (const item of records) {
      const row = text(body, 'tr', '');
      text(row, 'td', `${item.equip_code} — ${item.equipment_name}`);
      text(row, 'td', isPending ? item.requested_at : item.reviewed_at);
      text(row, 'td', item.reason);
      text(row, 'td', money(item.assessed_cost));
      text(row, 'td', `Damage: ${money(item.damage_penalty_amount)}; Overdue: ${money(item.overdue_penalty_amount)}`);
      if (isPending) {
        const actions = text(row, 'td', '');
        const note = document.createElement('input');
        note.className = 'modal-form-input';
        note.placeholder = 'Review note (required to reject)';
        note.setAttribute('aria-label', `Review note for ${item.equip_code}`);
        actions.append(note);
        for (const decision of ['Approved', 'Rejected']) {
          const button = text(actions, 'button', decision === 'Approved' ? 'Approve disposal' : 'Reject / Repair', 'btn btn-secondary btn-sm');
          button.type = 'button';
          button.addEventListener('click', async () => {
            const buttons = actions.querySelectorAll('button');
            buttons.forEach(b => { b.disabled = true; });
            try {
              const result = await request({ method: 'POST', body: new URLSearchParams({ id: item.id, decision, note: note.value.trim() }) });
              show(result.message);
              await load();
              window.dispatchEvent(new Event('disposal:changed'));
            } catch (error) { show(error.message); buttons.forEach(b => { b.disabled = false; }); }
          });
        }
      } else {
        text(row, 'td', item.borrower_name ? `${item.borrower_name} (${item.student_faculty_id || 'legacy borrower'})` : 'Inspection; no borrower charged');
        text(row, 'td', item.review_note || 'Approved');
      }
    }
  }
  async function load() {
    try { const data = await request(); render(pending, data.pending, true); render(approved, data.approved, false); }
    catch (error) { show(error.message); }
  }
  document.getElementById('refreshDisposalBtn')?.addEventListener('click', load);
  document.querySelector('.dash-tab[data-tab="equipment"]')?.addEventListener('click', load);
  load();
})();
