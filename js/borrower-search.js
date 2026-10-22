'use strict';

// Initialize independently of the dashboard's charts, tables, and API helpers.
(() => {
  const search = document.getElementById('borrower_search');
  const name = document.getElementById('borrower_name');
  const department = document.getElementById('department');
  const list = document.getElementById('borrower-list');
  const studentId = document.getElementById('borrower_student_faculty_id');
  if (!search || !name || !department || !list) return;

  // This script lives in backend/js; the PHP endpoint lives in backend.
  const endpoint = new URL('../get-borrowers.php', document.currentScript.src);
  const status = document.createElement('small');
  status.id = 'borrower-search-status';
  status.setAttribute('role', 'status');
  status.hidden = true;
  list.after(status);
  search.setAttribute('aria-describedby', status.id);
  department.readOnly = true;

  let records = [];
  let timer;
  let controller;
  let version = 0;
  const normalize = value => String(value ?? '').trim().replace(/\s+/g, ' ').toLowerCase();
  const fullName = b => `${b.first_name} ${b.last_name}`.trim().replace(/\s+/g, ' ');
  const label = b => `${fullName(b)} (${b.student_faculty_id})`;

  function cancelPending() {
    clearTimeout(timer);
    controller?.abort();
    search.setAttribute('aria-busy', 'false');
    return ++version;
  }

  function clearSelection() {
    name.value = '';
    department.value = '';
    if (studentId) studentId.value = '';
  }

  function apply(borrower) {
    name.value = fullName(borrower);
    department.value = borrower.department_course || '';
    if (studentId) studentId.value = borrower.student_faculty_id;
  }

  function match(value, includeNames = false) {
    const term = normalize(value);
    if (!term) return null;
    const identified = records.find(b =>
      normalize(b.student_faculty_id) === term || normalize(label(b)) === term);
    if (identified || !includeNames) return identified;
    const named = records.filter(b =>
      [fullName(b), b.first_name, b.last_name].some(v => normalize(v) === term));
    // Shared names require selecting a suggestion containing a specific ID.
    return named.length === 1 ? named[0] : null;
  }

  async function load(term, requestVersion) {
    controller = new AbortController();
    search.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(endpoint.href + '?query=' + encodeURIComponent(term), {
        signal: controller.signal,
        credentials: 'same-origin',
        cache: 'no-store',
      });
      if (!response.ok) throw new Error(`Borrower lookup returned HTTP ${response.status}`);
      const data = await response.json();
      if (!Array.isArray(data)) throw new Error('Expected a JSON array of borrowers');
      if (requestVersion !== version || term !== search.value.trim()) return;

      records = data;
      const options = document.createDocumentFragment();
      for (const borrower of records) {
        const option = document.createElement('option');
        option.value = label(borrower);
        options.append(option);
      }
      list.replaceChildren(options);
      const selected = match(term, true);
      if (selected) apply(selected);
    } catch (error) {
      if (error.name === 'AbortError' || requestVersion !== version) return;
      records = [];
      list.replaceChildren();
      clearSelection();
      status.textContent = 'Unable to load borrowers. Please try again.';
      status.hidden = false;
      console.error('Borrower search failed:', error);
    } finally {
      if (requestVersion === version) search.setAttribute('aria-busy', 'false');
    }
  }

  function update(immediate = false) {
    const requestVersion = cancelPending();
    status.hidden = true;
    const term = search.value.trim();
    const selected = match(term);
    if (selected) {
      apply(selected);
      return;
    }
    clearSelection();
    list.replaceChildren();
    if (!term) {
      records = [];
      return;
    }
    if (immediate) load(term, requestVersion);
    else timer = setTimeout(() => load(term, requestVersion), 250);
  }

  search.addEventListener('input', () => update());
  search.addEventListener('change', () => update(true));
  search.addEventListener('focus', () => update(true));
  name.addEventListener('input', () => {
    cancelPending();
    department.value = '';
    if (studentId) studentId.value = '';
  });
  search.form?.addEventListener('reset', () => {
    cancelPending();
    records = [];
    list.replaceChildren();
    status.hidden = true;
  });
})();
