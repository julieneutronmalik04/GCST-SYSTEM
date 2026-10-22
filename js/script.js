   'use strict';

   const BASE = '/gcst-system/backend';
   
   const API = {
     login         : `${BASE}/login.php`,
     logout        : `${BASE}/logout.php`,
     authCheck     : `${BASE}/modules/auth_check.php`,
     users         : `${BASE}/modules/users.php`,
     rooms         : `${BASE}/modules/rooms.php`,
     equipment     : `${BASE}/modules/equipment.php`,
     reservations  : `${BASE}/modules/reservations.php`, 
     profile       : `${BASE}/modules/profile.php`,
     reports       : `${BASE}/modules/condition_reports.php`,
     notifications : `${BASE}/modules/notifications.php`,
     borrowing     : `${BASE}/modules/equipment_borrowing.php`,
     borrowers     : `${BASE}/get-borrowers.php`,
     dashStats     : `${BASE}/modules/dashboard_stats.php`,
     analytics     : `${BASE}/modules/analytics.php`,
     fetchAnalytics : `${BASE}/modules/fetch_analytics.php`,
     predictionsReport : `${BASE}/modules/predictions_report.php`,
     activityLog   : `${BASE}/modules/activity_log.php`,
     schedules     : `${BASE}/modules/schedules.php`,
     equipmentPredictions : `${BASE}/modules/equipment_predictions.php`,
     reportExport  : `${BASE}/modules/report_export.php`,
   };
   
   
   async function http(url, method = 'GET', body = null) {
     const opts = { method, credentials: 'include', headers: {} };
   
     if (body instanceof FormData) {
       opts.body = body;                             
     } else if (typeof body === 'object' && body !== null) {
       if (method === 'PATCH') {
         opts.headers['Content-Type'] = 'application/json';
         opts.body = JSON.stringify(body);
       } else {
         opts.headers['Content-Type'] = 'application/x-www-form-urlencoded';
         opts.body = new URLSearchParams(body).toString();
       }
     } else if (typeof body === 'string') {
       opts.body = body;
     }
   
     return fetch(url, opts)
       .then(async res => {
         const text = await res.text();
         try {
           const data = JSON.parse(text);
           return data;
         } catch (e) {
           console.error("RAW RESPONSE:", text);
           return {
             success: false,
             status: 'error',
             message: `Invalid JSON response from server. Check console.`
           };
         }
       })
       .catch(err => {
         console.error("Fetch Error:", err);
         return { success: false, status: 'error', message: `Fetch Error: ${err.message}` };
       });
   }
   
   const api = {
     get   : (url, params = {}) => { const q = new URLSearchParams(params).toString(); return http(q ? `${url}?${q}` : url, 'GET'); },
     post  : (url, body)        => http(url, 'POST',   body),
     put   : (url, body)        => http(url, 'PUT',    body),
     patch : (url, body)        => http(url, 'PATCH',  body),
     del   : (url, body)        => http(url, 'DELETE', body),
   };
   
   //utilities ui
   function toast(msg, type = 'success') {
     const c = document.getElementById('toastContainer');
     if (!c) return;
     const icons = {
       success: `<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`,
       error  : `<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
       warning: `<svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>`,
     };
     const t = Object.assign(document.createElement('div'), {
       className: `toast toast-${type}`,
       innerHTML: `${icons[type] || icons.success}<span>${msg}</span>`,
     });
     c.appendChild(t);
     setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 350); }, 3500);
   }
   
   function openModal(id)  { const m = document.getElementById(id); if (m) { m.classList.remove('hidden'); document.body.style.overflow = 'hidden'; } }
   function closeModal(id) { const m = document.getElementById(id); if (m) { m.classList.add('hidden');    document.body.style.overflow = '';       } }

   (function initReportExportModal() {
     const modal = document.getElementById('reportExportModal');
     if (!modal) return;

     const typeSelect = document.getElementById('exportReportType');
     const fromInput = document.getElementById('exportDateFrom');
     const toInput = document.getElementById('exportDateTo');
     const formatSelect = document.getElementById('exportFormat');
     const downloadBtn = document.getElementById('downloadReportBtn');

     const today = new Date();
     const pad = value => String(value).padStart(2, '0');
     const toDateValue = date => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
     const firstDayOfMonth = () => new Date(today.getFullYear(), today.getMonth(), 1);

     function setReportDefaults(source) {
       if (!typeSelect || !fromInput || !toInput || !formatSelect) return;

       const typeBySource = {
         rooms: 'room_schedule',
         equipment: 'maintenance_predictor',
         disposal: 'disposal',
         'visual-dashboard': 'room_utilization',
       };

       typeSelect.value = typeBySource[source] || 'room_utilization';
       formatSelect.value = 'pdf';
       fromInput.value = toDateValue(firstDayOfMonth());
       toInput.value = toDateValue(today);
       if (source === 'disposal') { fromInput.value = ''; toInput.value = ''; }
     }

     document.querySelectorAll('[data-open-report]').forEach(button => {
       button.addEventListener('click', () => {
         setReportDefaults(button.dataset.openReport || '');
         openModal('reportExportModal');
       });
     });

     downloadBtn?.addEventListener('click', () => {
       if (!typeSelect || !formatSelect) return;

       const params = new URLSearchParams({
         type: typeSelect.value,
         format: formatSelect.value,
       });
       if (fromInput?.value) params.set('from', fromInput.value);
       if (toInput?.value) params.set('to', toInput.value);

       window.location.href = `${API.reportExport}?${params.toString()}`;
       closeModal('reportExportModal');
     });
   })();
   
   function confirmDialog(msg, options = {}) {
     return new Promise(resolve => {
       const msgEl      = document.getElementById('deleteModalMsg');
       const confirmBtn = document.getElementById('confirmDeleteBtn');
       if (!msgEl || !confirmBtn) { resolve(window.confirm(msg)); return; }
       const heading = document.getElementById('deleteModal')?.querySelector('h3');
       const icon = document.getElementById('deleteModal')?.querySelector('.logout-icon');
       const originalIconDisplay = icon?.style.display;
       const originalHeading = heading?.innerHTML;
       const originalButton = confirmBtn.innerHTML;
       const originalClass = confirmBtn.className;
       if (options.title && heading) heading.textContent = options.title;
       if (options.title && icon) icon.style.display = 'none';
       if (options.confirmText) {
         confirmBtn.textContent = options.confirmText;
         confirmBtn.classList.remove('btn-danger');
         confirmBtn.classList.add('btn-primary');
       }
       msgEl.textContent = msg;
       openModal('deleteModal');
       const cancelBtns = document.querySelectorAll('[data-close="deleteModal"]');
       const cleanup = (val) => {
         closeModal('deleteModal');
         confirmBtn.removeEventListener('click', yes);
         cancelBtns.forEach(b => b.removeEventListener('click', no));
         if (heading && originalHeading !== undefined) heading.innerHTML = originalHeading;
         if (icon) icon.style.display = originalIconDisplay || '';
         confirmBtn.innerHTML = originalButton;
         confirmBtn.className = originalClass;
         resolve(val);
       };
       const yes = () => cleanup(true);
       const no  = () => cleanup(false);
       confirmBtn.addEventListener('click', yes);
       cancelBtns.forEach(b => b.addEventListener('click', no));
     });
   }
   
   function badge(status) {
     const map = {
       'Available':'badge-available','Occupied':'badge-occupied',
       'Under Maintenance':'badge-maintenance','In Use':'badge-inuse',
       'Damaged':'badge-damaged','Under Repair':'badge-damaged',
       'Repair Needed':'badge-damaged',
       'Pending':'badge-pending','Waiting List':'badge-pending','Approved':'badge-approved',
       'Rejected':'badge-rejected','Cancelled':'badge-maintenance',
       'Open':'badge-pending','In Progress':'badge-inuse','Resolved':'badge-approved',
       'Returned':'badge-available','Returned (Damaged)':'badge-damaged','Overdue':'badge-damaged',
       'For Disposal':'badge-damaged','Disposed':'badge-damaged',
       'Borrowed':'badge-inuse',
     };
     return `<span class="badge ${map[status] || ''}">${status}</span>`;
   }
   
   function roleTag(role) {
     const m = { admin: 'badge-approved', faculty: 'badge-inuse', vip: 'badge-vip', maintenance: 'badge-pending' };
     return `<span class="badge ${m[role] || ''}" style="text-transform:capitalize">${role === 'vip' ? 'VIP' : role}</span>`;
   }

   function healthBadge(prediction) {
     const status = prediction?.health_status || 'Healthy';
     const cls = status.toLowerCase().replace(/\s+/g, '-');
     const score = prediction?.health_score ?? 100;
     return `<span class="health-badge ${cls}" title="Health score: ${score}%">${status}</span>`;
   }

   function lifecycleBar(prediction) {
     const pct = Math.max(0, Math.min(100, Number(prediction?.lifecycle_percent ?? 100)));
     return `<div class="lifecycle-meter" title="${pct}% estimated lifespan remaining"><span style="width:${pct}%"></span></div><div class="lifecycle-label">${pct}% remaining</div>`;
   }
   
   function fmtDate(d) {
     if (!d) return '—';
     const datePart = d.split(' ')[0];
     return new Date(datePart + 'T00:00:00').toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
   }
   
   function fmtTime(t) {
     if (!t) return '';
     const [h, m] = t.split(':');
     const hr = parseInt(h, 10);
     return `${hr > 12 ? hr - 12 : hr || 12}:${m} ${hr >= 12 ? 'PM' : 'AM'}`;
   }

   function fmtDateTime(dt) {
     if (!dt) return '—';
     try {
       const date = new Date(dt);
       const dateStr = date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
       const timeStr = date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
       return `${dateStr} at ${timeStr}`;
     } catch (e) {
       return '—';
     }
   }

   function escapeHtml(text) {
     if (!text) return '';
     const div = document.createElement('div');
     div.textContent = text;
     return div.innerHTML;
   }
   
   function timeAgo(dateStr) {
     const diff = (Date.now() - new Date(dateStr).getTime()) / 1000;
     if (diff < 60)   return 'Just now';
     if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
     if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
     return `${Math.floor(diff / 86400)}d ago`;
   }
   
   function emptyRow(cols, icon, msg) {
     return `<tr><td colspan="${cols}"><div class="table-empty">
       <svg viewBox="0 0 24 24">${icon}</svg><p>${msg}</p>
     </div></td></tr>`;
   }

   const chartStore = {};

   function renderChart(id, type, labels, data, opts = {}) {
     const canvas = document.getElementById(id);
     if (!canvas) return null;
     if (typeof Chart === 'undefined') {
       console.error(`Chart.js is not loaded; cannot render chart "${id}".`);
       const ctx = canvas.getContext?.('2d');
       if (ctx) {
         ctx.clearRect(0, 0, canvas.width, canvas.height);
         ctx.fillStyle = '#64748b';
         ctx.font = '600 13px sans-serif';
         ctx.textAlign = 'center';
         ctx.fillText('Chart library unavailable', canvas.width / 2, Math.max(24, canvas.height / 2));
       }
       return null;
     }

     if (!canvas.getContext) return null;
     if (chartStore[id]) {
       chartStore[id].destroy();
       delete chartStore[id];
     }

     const safeLabels = Array.isArray(labels) ? labels : [];
     const numericData = Array.isArray(data) ? data.map(value => Number(value) || 0) : [];
     const hasData = numericData.some(value => value > 0);
     const isDoughnut = type === 'doughnut';
     const palette = opts.palette || ['#2563eb', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
     const chartLabels = safeLabels.length ? safeLabels : ['No data'];
     const chartData = numericData.length ? numericData : [0];
     const displayLabels = hasData ? chartLabels : ['No data'];
     const displayData = hasData ? chartData : (isDoughnut ? [1] : chartData.length ? chartData : [0]);
     const datasetColors = Array.isArray(opts.backgroundColor)
       ? opts.backgroundColor
       : (opts.backgroundColor || (isDoughnut ? palette.slice(0, displayLabels.length) : palette[0]));
     const displayColors = hasData ? datasetColors : (isDoughnut ? ['#e2e8f0'] : datasetColors);
     const noDataPlugin = {
       id: `gcstNoData-${id}`,
       afterDraw(chart) {
         if (hasData || isDoughnut) return;
         const { ctx, chartArea } = chart;
         if (!chartArea) return;
         ctx.save();
         ctx.fillStyle = '#64748b';
         ctx.font = '600 13px sans-serif';
         ctx.textAlign = 'center';
         ctx.textBaseline = 'middle';
         ctx.fillText('No data available', (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2);
         ctx.restore();
       },
     };

     const config = {
       type,
       data: {
         labels: displayLabels,
         datasets: [{
           label: opts.label || 'Count',
           data: displayData,
           backgroundColor: displayColors,
           borderColor: opts.borderColor || (type === 'line' ? palette[0] : '#dbeafe'),
           borderWidth: opts.borderWidth ?? (type === 'line' ? 3 : 1),
           tension: opts.tension ?? (type === 'line' ? 0.35 : 0),
           fill: opts.fill ?? false,
           pointRadius: opts.pointRadius ?? (type === 'line' ? 3 : 0),
           pointHoverRadius: opts.pointHoverRadius ?? (type === 'line' ? 5 : 0),
           borderRadius: opts.borderRadius ?? (type === 'bar' ? 8 : 0),
           maxBarThickness: opts.maxBarThickness ?? 42,
         }],
       },
       options: {
         responsive: true,
         maintainAspectRatio: false,
         animation: false,
         indexAxis: opts.indexAxis || 'x',
         plugins: {
           legend: {
             display: opts.legend !== false,
             position: opts.legendPosition || 'bottom',
             labels: {
               usePointStyle: true,
               boxWidth: 10,
               boxHeight: 10,
               padding: 14,
             },
           },
           tooltip: {
             callbacks: opts.tooltipCallbacks || {},
           },
         },
         scales: opts.scales || undefined,
         cutout: opts.cutout || '62%',
       },
       plugins: [noDataPlugin],
     };

     chartStore[id] = new Chart(canvas.getContext('2d'), config);
     return chartStore[id];
   }

   function setChartDefaults() {
     if (typeof Chart === 'undefined' || window.__gcstChartDefaults) return;
     Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
     Chart.defaults.color = '#475569';
     window.__gcstChartDefaults = true;
   }
   
   
   window.togglePw = function(id, iconEl) {
     const el = document.getElementById(id);
     if (!el) return;
     if (el.type === 'password') { 
       el.type = 'text'; 
       iconEl.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>';
     } else { 
       el.type = 'password'; 
       iconEl.innerHTML = '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>';
     }
   };

document.addEventListener('click', e => {
     const b = e.target.closest('[data-close]');
     if (b) closeModal(b.dataset.close);
     if (e.target.classList.contains('modal-overlay')) {
       e.target.classList.add('hidden');
       document.body.style.overflow = '';
     }
   });
   
   
   window.togglePw = function(id, iconEl) {
     const el = document.getElementById(id);
     if (!el) return;
     if (el.type === 'password') { el.type = 'text'; iconEl.classList.remove('fa-eye'); iconEl.classList.add('fa-eye-slash'); }
     else { el.type = 'password'; iconEl.classList.remove('fa-eye-slash'); iconEl.classList.add('fa-eye'); }
   };

document.addEventListener('click', e => {
     const tab = e.target.closest('.dash-tab');
     if (!tab) return;
      const container = tab.closest('.dash-sidebar') || tab.closest('.dash-tabs');
      if (container) container.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
     tab.classList.add('active');
     const panelId = `tab-${tab.dataset.tab}`;
     document.querySelectorAll('.tab-panel').forEach(p =>
       p.classList.toggle('active', p.id === panelId)
     );
     
     // Auto-reload data when switching tabs
     if (tab.dataset.tab === 'my-reservations' && typeof loadMyReservations === 'function') loadMyReservations();
     if (tab.dataset.tab === 'available' && typeof loadAvailRooms === 'function') loadAvailRooms();
     if (tab.dataset.tab === 'reserve' && typeof loadAvailRooms === 'function') loadAvailRooms(); // refresh select
     if (tab.dataset.tab === 'reservations' && typeof loadReservations === 'function') loadReservations();
     if (tab.dataset.tab === 'dashboard' && typeof window.loadAdminAnalytics === 'function') window.loadAdminAnalytics();
     if (tab.dataset.tab === 'available' && typeof window.loadFacultyAnalytics === 'function') window.loadFacultyAnalytics();
     if ((tab.dataset.tab === 'equip-list' || tab.dataset.tab === 'equip-reports' || tab.dataset.tab === 'borrowing') && typeof window.loadMaintenanceAnalytics === 'function') {
       window.loadMaintenanceAnalytics();
     }
   });
   
   /* SESSION CHECK */
   (async function checkSession() {
     if (!document.getElementById('dashTabs')) return;
     const data = await api.get(API.authCheck);
   if (!data.success) { window.location.href = '/gcst-system/frontend/login.html'; return; }
   
     const s = (sel, val) => { const el = document.querySelector(sel); if (el) el.textContent = val; };
     s('.profile-name', data.full_name);
     s('.profile-role', data.role === 'vip' ? 'VIP' : data.role.charAt(0).toUpperCase() + data.role.slice(1));
     if (data.role === 'vip') {
       s('.dash-header-title span', 'VIP Dashboard');
       document.getElementById('vipReservationNotice')?.removeAttribute('hidden');
     }
     const av = document.querySelector('.profile-avatar');
     if (av) av.textContent = data.full_name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
   
     window._session = data;
   })();
   
   /* LOGIN */
   (function initLogin() {
     const loginBtn = document.getElementById('loginBtn');
     if (!loginBtn) return;
   
     async function doLogin() {
       const errorBox = document.getElementById('loginError');
       if (errorBox) errorBox.classList.remove('show');
       const u = document.getElementById('username').value.trim();
       const p = document.getElementById('password').value;
       if (!u || !p) {
         if (errorBox) { errorBox.textContent = 'Please enter username and password.'; errorBox.classList.add('show'); }
         return;
       }
       loginBtn.textContent = 'Signing in…'; loginBtn.disabled = true;
       const fd = new FormData();
       fd.append('username', u); fd.append('password', p);
       const data = await api.post(API.login, fd);
       if (data.success) {
         window.location.href = data.redirect;
       } else {
         if (errorBox) { errorBox.textContent = data.message; errorBox.classList.add('show'); }
         loginBtn.textContent = 'Sign In'; loginBtn.disabled = false;
       }
     }
   
     loginBtn.addEventListener('click', doLogin);
     document.addEventListener('keydown', e => {
       if (e.key === 'Enter' && document.getElementById('loginBtn')) doLogin();
     });
   })();
   
   /* LOGOUT */
   
   window.togglePw = function(id, iconEl) {
     const el = document.getElementById(id);
     if (!el) return;
     if (el.type === 'password') { el.type = 'text'; iconEl.classList.remove('fa-eye'); iconEl.classList.add('fa-eye-slash'); }
     else { el.type = 'password'; iconEl.classList.remove('fa-eye-slash'); iconEl.classList.add('fa-eye'); }
   };

document.addEventListener('click', e => {
     if (e.target.closest('#profileBtn'))       openModal('logoutModal');
     if (e.target.closest('#confirmLogoutBtn')) window.location.href = API.logout;
   });
   
   /* NOTIFICATIONS */
   (function initNotifications() {
     const notifBtn = document.getElementById('notifBtn');
     if (!notifBtn) return;
   
     if (!document.getElementById('notifDropdown')) {
       const panel = document.createElement('div');
       panel.id        = 'notifDropdown';
       panel.className = 'notif-dropdown hidden';
       panel.innerHTML = `
         <div class="notif-header">
           <span class="notif-title">Notifications</span>
           <button class="notif-mark-all btn btn-secondary btn-sm" id="markAllReadBtn">Mark all read</button>
         </div>
         <div class="notif-list" id="notifList">
           <div class="notif-empty">Loading…</div>
         </div>`;
       
       notifBtn.parentNode.style.position = 'relative';
       notifBtn.parentNode.appendChild(panel);
     }
   
     const dropdown = document.getElementById('notifDropdown');
   
     notifBtn.addEventListener('click', e => {
       e.stopPropagation();
       const isOpen = !dropdown.classList.contains('hidden');
       if (isOpen) {
         dropdown.classList.add('hidden');
       } else {
         dropdown.classList.remove('hidden');
         fetchNotifications();
       }
     });
   
     
   window.togglePw = function(id, iconEl) {
     const el = document.getElementById(id);
     if (!el) return;
     if (el.type === 'password') { el.type = 'text'; iconEl.classList.remove('fa-eye'); iconEl.classList.add('fa-eye-slash'); }
     else { el.type = 'password'; iconEl.classList.remove('fa-eye-slash'); iconEl.classList.add('fa-eye'); }
   };

document.addEventListener('click', e => {
       if (!dropdown.contains(e.target) && e.target !== notifBtn) {
         dropdown.classList.add('hidden');
       }
     });
   
     document.addEventListener('click', async e => {
       if (!e.target.closest('#markAllReadBtn')) return;
       const data = await api.patch(API.notifications, { action: 'mark_all_read' });
       if (data.success) { updateBadge(0); fetchNotifications(); }
     });
   
     async function fetchNotifications() {
       const list = document.getElementById('notifList');
       if (!list) return;
       const data = await api.get(API.notifications, { limit: 15 });
       if (!data.success) {
         list.innerHTML = '<div class="notif-empty">Could not load notifications.</div>';
         return;
       }
       updateBadge(data.unread_count);
   
       if (!data.notifications.length) {
         list.innerHTML = '<div class="notif-empty">No notifications yet.</div>';
         return;
       }
   
       const typeIcons = {
         reservation_new     : '📅',
         reservation_approved: '✅',
         reservation_reject  : '❌',
         reservation_cancel  : '🚫',
         equipment_damaged   : '⚠️',
         equipment_added     : '🖥️',
         report_submitted    : '🔧',
         default             : '🔔',
       };
   
       list.innerHTML = data.notifications.map(n => `
         <div class="notif-item ${n.is_read ? '' : 'notif-unread'}"
              data-id="${n.id}"
              data-link-ref="${n.link_ref || ''}">
           <div class="notif-icon">${typeIcons[n.type] || typeIcons.default}</div>
           <div class="notif-body">
             <div class="notif-item-title">${escapeHtml(n.title)}</div>
             <div class="notif-item-body">${escapeHtml(n.body)}</div>
             <div class="notif-item-time">${timeAgo(n.created_at)}</div>
           </div>
           ${n.is_read ? '' : '<div class="notif-dot"></div>'}
         </div>`).join('');
     }

     document.getElementById('notifList')?.addEventListener('click', async e => {
       const item = e.target.closest('.notif-item');
       if (!item) return;
       await markNotifRead(Number(item.dataset.id), item);
       dropdown.classList.add('hidden');
       navigateFromNotification(item.dataset.linkRef, item);
     });
   
     window.markNotifRead = async function(id, el) {
       if (!el || !el.classList.contains('notif-unread')) return;
       const data = await api.patch(API.notifications, { action: 'mark_read', id });
       if (data.success) {
         el.classList.remove('notif-unread');
         el.querySelector('.notif-dot')?.remove();
         
         const badge = document.querySelector('.notif-badge');
         if (badge) {
           const current = parseInt(badge.dataset.count || '0', 10);
           updateBadge(Math.max(0, current - 1));
         }
       }
     };

     function navigateFromNotification(linkRef, item) {
       if (linkRef === 'disposal') {
         document.querySelector('.dash-tab[data-tab="equipment"]')?.click();
         setTimeout(() => document.getElementById('disposalQueueHeading')?.scrollIntoView({ behavior: 'smooth' }), 120);
         return;
       }
       if (/^borrow:\d+$/.test(linkRef || '')) {
         document.querySelector('.dash-tab[data-tab="borrowing"]')?.click();
         setTimeout(() => document.getElementById('overdueHeading')?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 120);
         return;
       }
       const title = item?.querySelector('.notif-item-title')?.textContent || '';
       const shouldOpenReports = linkRef === 'condition_reports' || /condition report/i.test(title);
       if (!shouldOpenReports) return;

       const reportsTab = document.querySelector('.dash-tab[data-tab="equip-reports"]');
       if (reportsTab) reportsTab.click();

       setTimeout(() => {
         const target = document.getElementById('conditionReportLogCard') || document.getElementById('reportsTableBody');
         target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
       }, 120);
     }
   
     function updateBadge(count) {
       const badge = document.querySelector('.notif-badge');
       if (!badge) return;
       badge.dataset.count = count;
       if (count > 0) {
         badge.style.display    = 'block';
         badge.style.background = 'var(--danger)';
       } else {
         badge.style.display = 'none';
       }
     }
   
     async function refreshBadge() {
       const data = await api.get(API.notifications, { unread: '1', limit: '1' });
       if (data.success) updateBadge(data.unread_count);
     }
   
     setTimeout(refreshBadge, 800);
     setInterval(refreshBadge, 30000);
   })();
   
   /* ADMIN DASHBOARD */
   (function initAdmin() {
     if (!document.getElementById('adminUserTableBody') && !document.getElementById('roomsTableBody')) return;
   
     /* USER MANAGEMENT */
     async function loadUsers(search = '', role = '') {
       const data = await api.get(API.users, { search, role });
       if (!data.success) { toast(data.message, 'error'); return; }
       const { users, stats } = data;
   
       const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
       s('stat-total-users',   stats.total);
       s('stat-admin-users',   stats.admin       || 0);
       s('stat-faculty-users', stats.faculty     || 0);
       s('stat-maint-users',   stats.maintenance || 0);
   
       const tbody = document.getElementById('adminUserTableBody');
       if (!tbody) return;
       if (!users.length) {
         tbody.innerHTML = emptyRow(7, '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>', 'No users found');
         return;
       }
       tbody.innerHTML = users.map(u => `
         <tr>
           <td><span style="font-size:.75rem;color:var(--slate-400)">${String(u.id).padStart(3,'0')}</span></td>
           <td>
             <div style="display:flex;align-items:center;gap:10px">
               <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--primary),var(--accent));
                           display:flex;align-items:center;justify-content:center;color:white;font-size:.7rem;font-weight:700;flex-shrink:0">
                 ${u.full_name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()}
               </div>
               <div>
                 <div style="font-weight:600;color:var(--navy);font-size:.85rem">${u.full_name}</div>
                 <div style="font-size:.72rem;color:var(--slate-400)">${u.email || '—'}</div>
               </div>
             </div>
           </td>
           <td><code style="font-size:.78rem;background:var(--slate-100);padding:2px 8px;border-radius:5px">${u.username}</code></td>
           <td>${roleTag(u.role)}</td>
           <td>${u.is_active
             ? `<span class="badge badge-available">Active</span>`
             : `<span class="badge badge-rejected">Inactive</span>`}</td>
           <td style="font-size:.78rem;color:var(--slate-400)">${fmtDate(u.created_at)}</td>
           <td><div class="action-btns">
             <button class="btn btn-secondary btn-sm" onclick="editUser(${u.id})">
               <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
             </button>
             <button class="btn btn-warning btn-sm" onclick="resetUserPassword(${u.id}, '${u.full_name.replace(/'/g,"\\'")}')">
               <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>Reset PW
             </button>
             <button class="btn ${u.is_active ? 'btn-secondary' : 'btn-success'} btn-sm" onclick="toggleUserActive(${u.id})">
               ${u.is_active
                 ? `<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>Deactivate`
                 : `<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Activate`}
             </button>
             <button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id}, '${u.username.replace(/'/g,"\\'")}')">
               <i class="fa-solid fa-trash"></i> Delete
             </button>
           </div></td>
         </tr>`).join('');
     }
   
     document.getElementById('userSearch')?.addEventListener('input', e =>
       loadUsers(e.target.value, document.getElementById('userRoleFilter')?.value || ''));
     document.getElementById('userRoleFilter')?.addEventListener('change', e =>
       loadUsers(document.getElementById('userSearch')?.value || '', e.target.value));
   
     document.getElementById('addUserBtn')?.addEventListener('click', () => {
       document.getElementById('userModalTitle').textContent = 'Add New User';
       document.getElementById('userForm').reset();
       document.getElementById('userPasswordGroup').style.display = 'block';
       document.getElementById('userId').value = '';
       openModal('userModal');
     });
   
     window.editUser = async function(id) {
       const data = await api.get(API.users, { id });
       if (!data.success) { toast(data.message, 'error'); return; }
       const u = data.user;
       document.getElementById('userModalTitle').textContent = 'Edit User';
       document.getElementById('userId').value    = u.id;
       document.getElementById('uFullName').value = u.full_name;
       document.getElementById('uUsername').value = u.username;
       document.getElementById('uEmail').value    = u.email || '';
       document.getElementById('uRole').value     = u.role;
       document.getElementById('userPasswordGroup').style.display = 'none';
       openModal('userModal');
     };
   
     document.getElementById('saveUserBtn')?.addEventListener('click', async () => {
       const id = document.getElementById('userId').value;
       const fd = new FormData(document.getElementById('userForm'));
       if (id) fd.set('id', id);
       const data = id
         ? await api.put(API.users,  Object.fromEntries(fd.entries()))
         : await api.post(API.users, fd);
       if (data.success) { toast(data.message); closeModal('userModal'); loadUsers(); }
       else toast(data.message, 'error');
     });
   
     window.resetUserPassword = function(id, name) {
       document.getElementById('resetPwUserId').value = id;
       document.getElementById('resetPwUserName').textContent = name;
       document.getElementById('resetPwForm').reset();
       openModal('resetPwModal');
     };
   
     document.getElementById('saveResetPwBtn')?.addEventListener('click', async () => {
       const id  = document.getElementById('resetPwUserId').value;
       const np  = document.getElementById('newPwInput').value;
       const cnp = document.getElementById('confirmPwInput').value;
       if (np !== cnp) { toast('Passwords do not match.', 'error'); return; }
       const data = await api.patch(API.users, { id: parseInt(id, 10), action: 'reset_password', new_password: np });
       if (data.success) { toast(data.message); closeModal('resetPwModal'); }
       else toast(data.message, 'error');
     });
   
     window.toggleUserActive = async function(id) {
       const data = await api.patch(API.users, { id, action: 'toggle_active' });
       if (data.success) { toast(data.message); loadUsers(); }
       else toast(data.message, 'error');
     };
   
     window.deleteUser = async function(id, username) {
       const ok = await confirmDialog(`Delete user "${username}"? This cannot be undone.`);
       if (!ok) return;
       const data = await api.del(API.users, { id });
       if (data.success) { toast(data.message, 'warning'); loadUsers(); }
       else toast(data.message, 'error');
     };
   
      /* ROOM MANAGEMENT */
      function normalizeRoomStatus(status) {
        const value = (status || '').toLowerCase();
        if (value === 'occupied') return 'occupied';
        if (value === 'under maintenance' || value === 'maintenance') return 'maintenance';
        return 'available';
      }

      function renderRoomStatusGrid(rooms = [], stats = {}) {
        const total = Number(stats.total || rooms.length || 0);
        const available = Number(stats['Available'] || 0);
        const occupied = Number(stats['Occupied'] || 0);
        const maintenance = Number(stats['Under Maintenance'] || 0);

        const setText = (id, value) => {
          const el = document.getElementById(id);
          if (el) el.textContent = value;
        };

        setText('roomsGridTotal', `${total} room${total === 1 ? '' : 's'}`);
        setText('roomsGridOccupied', occupied);
        setText('roomsGridAvailable', available);
        setText('roomsGridMaintenance', maintenance);

        renderRoomSquares('room-grid-manage', rooms);
      }

      function renderRoomSquares(gridId, rooms = []) {
        const grid = document.getElementById(gridId);
        if (grid) {
          if (!rooms.length) {
            grid.innerHTML = '<div class="room-grid-empty">No rooms found</div>';
          } else {
            grid.innerHTML = rooms.map(r => {
              const roomCode = escapeHtml(r.room_id || '');
              const roomName = escapeHtml(r.room_name || '');
              const status = escapeHtml(r.status || 'Available');
              const roomClass = normalizeRoomStatus(r.status);
              return `<div class="room-item ${roomClass}" title="${roomCode} - ${roomName} (${status})">${roomCode}</div>`;
            }).join('');
          }
        }
      }

      function renderRoomOverviewChart(stats = {}) {
        const total = Number(stats.total || 0);
        const available = Number(stats['Available'] || 0);
        const occupied = Number(stats['Occupied'] || 0);
        const maintenance = Number(stats['Under Maintenance'] || 0);
        const occupiedPct = total ? Math.round((occupied / total) * 100) : 0;

        const setText = (id, value) => {
          const el = document.getElementById(id);
          if (el) el.textContent = value;
        };

        setText('roomsChartOccupied', occupied);
        setText('roomsChartAvailable', available);
        setText('roomsChartMaintenance', maintenance);
        setText('roomsStatusPercentage', `${occupiedPct}%`);

        if (document.getElementById('roomsStatusChart')) {
          setChartDefaults();
          renderChart('roomsStatusChart', 'doughnut', ['Occupied', 'Available', 'Maintenance'], [occupied, available, maintenance], {
            label: 'Rooms',
            legend: false,
            cutout: '68%',
            backgroundColor: ['#2563eb', '#0ea5e9', '#ef4444'],
            borderColor: '#ffffff',
            borderWidth: 2,
            tooltipCallbacks: {
              label: ctx => `${ctx.label}: ${ctx.parsed}`,
            },
          });
        }
      }

      function renderRoomVisuals(rooms = [], stats = {}) {
        renderRoomStatusGrid(rooms, stats);
        renderRoomOverviewChart(stats);
      }

      function getSelectedRoomLocation() {
        return document.getElementById('roomsLocationFilter')?.value || 'All';
      }

      async function loadRoomStatusGrid(location = getSelectedRoomLocation()) {
        const data = await api.get(API.rooms, { location: location || 'All' });
        if (!data.success) return;
        renderRoomStatusGrid(Array.isArray(data.rooms) ? data.rooms : [], data.stats || {});
      }

     async function loadRooms(search = '') {
       const data = await api.get(API.rooms, { search });
       if (!data.success) return;
       const { rooms, stats } = data;
       const normalizedRooms = Array.isArray(rooms) ? rooms : [];
   
       const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
       s('stat-total-rooms',    stats.total);
       s('stat-avail-rooms',    stats['Available']          || 0);
       s('stat-occupied-rooms', stats['Occupied']           || 0);
       s('stat-maint-rooms',    stats['Under Maintenance']  || 0);
   
       const tbody = document.getElementById('roomsTableBody');
       if (tbody) {
         if (!normalizedRooms.length) {
           tbody.innerHTML = emptyRow(6, '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>', 'No rooms found');
         } else {
           tbody.innerHTML = normalizedRooms.map(r => `
             <tr>
                <td><span class="room-code">${escapeHtml(r.room_id)}</span></td>
                <td><div class="room-name-cell"><strong>${escapeHtml(r.room_name)}</strong><span class="room-location">${escapeHtml(r.location || '—')}</span></div></td>
                <td>${escapeHtml(r.type)}</td>
                <td>${escapeHtml(String(r.capacity))}</td>
               <td>${badge(r.status)}</td>
               <td><div class="action-btns">
                 <button class="btn btn-warning btn-sm" type="button" data-room-qr="${r.id}">View QR</button>
                 <button class="btn btn-secondary btn-sm" onclick="editRoom(${r.id})">
                   <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
                 </button>
                  <button class="btn btn-danger btn-sm" onclick="deleteRoom(${r.id}, ${escapeHtml(JSON.stringify(r.room_id)).replace(/"/g, '&quot;')})">
                   <i class="fa-solid fa-trash"></i> Delete
                 </button>
               </div></td>
             </tr>`).join('');
         }
       }
         if (getSelectedRoomLocation() === 'All') {
           renderRoomVisuals(normalizedRooms || [], stats || {});
         } else {
           renderRoomOverviewChart(stats || {});
           await loadRoomStatusGrid();
         }
         populateRoomSelects(normalizedRooms || []);
      }
   
     function populateRoomSelects(rooms) {
       ['equipRoomAssign', 'resRoomSelect', 'scheduleRoomSelect'].forEach(selId => {
         const sel = document.getElementById(selId);
         if (!sel) return;
         const cur = sel.value;
         const defaultText = selId === 'equipRoomAssign' ? '— Stored in SBO Office —' : '— Select Room —';
         const defaultValue = selId === 'equipRoomAssign' ? 'SBO Office' : '';
         sel.innerHTML = `<option value="${defaultValue}">${defaultText}</option>`;
         rooms.filter(r => r.status !== 'Under Maintenance').forEach(r => {
           sel.innerHTML += `<option value="${r.room_id}">${r.room_id} — ${r.room_name}</option>`;
         });
         if (cur) sel.value = cur;
       });
     }
   
     document.getElementById('roomSearch')?.addEventListener('input', e => loadRooms(e.target.value));
     document.getElementById('roomsLocationFilter')?.addEventListener('change', e => {
       loadRoomStatusGrid(e.target.value || 'All');
     });
     document.getElementById('approveRoomsLocationFilter')?.addEventListener('change', e => {
       populateRoomStatusGrid(e.target.value || 'All');
     });
     document.querySelector('[data-tab="rooms"]')?.addEventListener('click', () => {
       setTimeout(() => loadRooms(document.getElementById('roomSearch')?.value || ''), 0);
     });
   
     async function loadLocations() {
       const datalist = document.getElementById('locationList');
       const gridFilter = document.getElementById('roomsLocationFilter');
       const approveGridFilter = document.getElementById('approveRoomsLocationFilter');
       if (!datalist && !gridFilter && !approveGridFilter) return;
       const data = await api.get(API.rooms, { action: 'get_locations' });
       if (data.success && data.locations) {
         if (datalist) {
           datalist.innerHTML = data.locations.map(loc => `<option value="${escapeHtml(loc.location_name)}"></option>`).join('');
         }
         if (gridFilter) {
           const current = gridFilter.value || 'All';
           gridFilter.innerHTML = '<option value="All">All Rooms</option>' +
             data.locations.map(loc => `<option value="${escapeHtml(loc.location_name)}">${escapeHtml(loc.location_name)}</option>`).join('');
           gridFilter.value = [...gridFilter.options].some(opt => opt.value === current) ? current : 'All';
         }
         if (approveGridFilter) {
           const current = approveGridFilter.value || 'All';
           approveGridFilter.innerHTML = '<option value="All">All Locations</option>' +
             data.locations.map(loc => `<option value="${escapeHtml(loc.location_name)}">${escapeHtml(loc.location_name)}</option>`).join('');
           approveGridFilter.value = [...approveGridFilter.options].some(opt => opt.value === current) ? current : 'All';
         }
       }
     }
     window.refreshRoomLocations = loadLocations;

     document.getElementById('addRoomBtn')?.addEventListener('click', () => {
       document.getElementById('roomModalTitle').textContent = 'Add New Room';
       document.getElementById('roomForm')?.reset();
       document.getElementById('roomId').value = '';
       loadLocations();
       openModal('roomModal');
     });
   
     window.editRoom = async function(id) {
       const data = await api.get(API.rooms, { id });
       if (!data.success) { toast(data.message, 'error'); return; }
       await loadLocations();
       const r = data.room;
       document.getElementById('roomModalTitle').textContent = 'Edit Room';
       document.getElementById('roomId').value      = r.id;
       document.getElementById('rCode').value       = r.room_id;    
       document.getElementById('rName').value       = r.room_name;
       document.getElementById('rType').value       = r.type;
       document.getElementById('rCapacity').value   = r.capacity;
       document.getElementById('locationDropdown').value   = r.location || '';
       document.getElementById('rStatus').value     = r.status;
       openModal('roomModal');
     };
   
     document.getElementById('roomForm')?.addEventListener('submit', event => {
       event.preventDefault();
       document.getElementById('saveRoomBtn')?.click();
     });
     document.getElementById('saveRoomBtn')?.addEventListener('click', async () => {
       for (const field of ['rName', 'rCapacity']) {
         if (!document.getElementById(field).reportValidity()) return;
       }
       const saveButton = document.getElementById('saveRoomBtn');
       saveButton.disabled = true;
       const id = document.getElementById('roomId').value;
       const body = {
         room_id  : document.getElementById('rCode').value,
         room_name: document.getElementById('rName').value,
         type     : document.getElementById('rType').value,
         capacity : document.getElementById('rCapacity').value,
         location : document.getElementById('locationDropdown').value,
         status   : document.getElementById('rStatus').value,
       };
       const data = id
         ? await api.put(API.rooms,  { ...body, id })
         : await api.post(API.rooms, (() => { const fd = new FormData(); Object.entries(body).forEach(([k, v]) => fd.append(k, v)); return fd; })());
       saveButton.disabled = false;
       if (data.success) { toast(data.message); closeModal('roomModal'); loadRooms(); }
       else toast(data.message, 'error');
     });
   
     window.deleteRoom = async function(id, code) {
       const ok = await confirmDialog(`Delete room "${code}"? This cannot be undone.`);
       if (!ok) return;
       const data = await api.del(API.rooms, { id });
       if (data.success) { toast(data.message, 'warning'); loadRooms(); }
       else toast(data.message, 'error');
     };
   
     /* EQUIPMENT + QR */
     window.addEventListener('disposal:changed', () => {
       loadEquipment(document.getElementById('equipSearch')?.value || '');
       if (typeof window.loadAdminAnalytics === 'function') window.loadAdminAnalytics();
     });

     async function loadEquipment(search = '') {
       const data = await api.get(API.equipment, { search });
       if (!data.success) return;
       const { equipment, stats } = data;
   
       const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
       s('stat-total-equip',   stats.total);
       s('stat-avail-equip',   stats['Available']  || 0);
       s('stat-inuse-equip',   stats['In Use']     || 0);
       s('stat-damaged-equip', stats['Damaged']    || 0);
   
       const tbody = document.getElementById('equipTableBody');
       if (!tbody) return;
       if (!equipment.length) {
         tbody.innerHTML = emptyRow(9, '<rect x="2" y="3" width="20" height="14" rx="2"/>', 'No equipment found');
         return;
       }
       tbody.innerHTML = equipment.map(e => `
         <tr>
           <td><code style="font-size:.78rem;background:var(--slate-100);padding:2px 8px;border-radius:5px">${e.equip_code}</code></td>
           <td><strong>${e.name}</strong>${e.serial_number ? `<br><span style="font-size:.72rem;color:var(--slate-400)">${e.serial_number}</span>` : ''}</td>
           <td>
             ${e.qr_image_path
               ? `<img src="${BASE}/${e.qr_image_path}"
                       alt="QR ${e.equip_code}"
                       style="width:44px;height:44px;border-radius:8px;cursor:pointer;border:1px solid var(--slate-200);object-fit:cover"
                       onclick="viewQR(${e.id})"
                       title="Click to view/download QR"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">`
               : ''}
             <span style="${e.qr_image_path ? 'display:none' : ''};font-size:.72rem;color:var(--slate-400)">
               ${e.qr_image_path ? 'Load error' : 'No QR yet'}
             </span>
           </td>
           <td>${badge(e.status)}</td>
           <td>
             ${healthBadge(e.prediction)}
             ${e.prediction?.maintenance_prediction ? `<div class="health-note">${e.prediction.maintenance_prediction}</div>` : ''}
           </td>
           <td>${lifecycleBar(e.prediction)}</td>
           <td>${e.assigned_room || '—'}${e.room_name ? `<br><span style="font-size:.72rem;color:var(--slate-400)">${e.room_name}</span>` : ''}</td>
           <td>${e.purchased_date ? fmtDate(e.purchased_date) : '—'}</td>
           <td><div class="action-btns">
             <button class="btn btn-secondary btn-sm" onclick="editEquip(${e.id})">
               <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
             </button>
             <button class="btn btn-warning btn-sm" onclick="viewQR(${e.id})">
               <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 17h3m3 0h-3m0-3v3m0 3v-3"/></svg>QR
             </button>
             <button class="btn btn-danger btn-sm" onclick="deleteEquip(${e.id},'${e.equip_code}')">
               <i class="fa-solid fa-trash"></i> Delete
             </button>
           </div></td>
         </tr>`).join('');
     }
   
     document.getElementById('equipSearch')?.addEventListener('input', e => loadEquipment(e.target.value));
   
     document.getElementById('addEquipBtn')?.addEventListener('click', () => {
       document.getElementById('equipModalTitle').textContent = 'Add New Equipment';
       document.getElementById('equipForm')?.reset();
       document.getElementById('equipId').value = '';
       openModal('equipModal');
     });
   
     window.editEquip = async function(id) {
       const data = await api.get(API.equipment, { id });
       if (!data.success) { toast(data.message, 'error'); return; }
       const e = data.equipment;
       document.getElementById('equipModalTitle').textContent = 'Edit Equipment';
       document.getElementById('equipId').value         = e.id;
       document.getElementById('eCode').value           = e.equip_code;
       document.getElementById('eName').value           = e.name;
       document.getElementById('eDesc').value           = e.description || '';
       document.getElementById('eStatus').value         = e.status;
       document.getElementById('equipRoomAssign').value = e.assigned_room || '';
       document.getElementById('eSerial').value         = e.serial_number || '';
       document.getElementById('ePurchased').value      = e.purchased_date || '';
       openModal('equipModal');
     };
   
     document.getElementById('saveEquipBtn')?.addEventListener('click', async () => {
       const id   = document.getElementById('equipId').value;
       const body = {
         equip_code    : document.getElementById('eCode').value,
         name          : document.getElementById('eName').value,
         description   : document.getElementById('eDesc').value,
         status        : document.getElementById('eStatus').value,
         assigned_room : document.getElementById('equipRoomAssign').value,
         serial_number : document.getElementById('eSerial').value,
         purchased_date: document.getElementById('ePurchased').value,
       };
   
       const data = id
         ? await api.put(API.equipment,  { ...body, id })
         : await api.post(API.equipment, (() => {
             const fd = new FormData();
             Object.entries(body).forEach(([k, v]) => fd.append(k, v));
             return fd;
           })());
   
       if (data.success) {
         toast(id ? data.message : 'Equipment added and QR code generated!');
         closeModal('equipModal');
         loadEquipment();
       } else {
         toast(data.message, 'error');
       }
     });
   
     //  QR Viewer + Download 
     window.viewQR = async function(id) {
       const data = await api.get(API.equipment, { id });
       if (!data.success) { toast('Could not load equipment details.', 'error'); return; }
       const e = data.equipment;
   
       document.getElementById('qrEquipLabel').textContent = `${e.equip_code} — ${e.name}`;
       document.getElementById('qrEquipRoom').textContent  = `Room: ${e.assigned_room || '—'}`;
       document.getElementById('qrString').textContent     = e.qr_string || e.equip_code;
   
       const img    = document.getElementById('qrImageDisplay');
       const dlBtn  = document.getElementById('downloadQrBtn');
   
       if (e.qr_image_path) {
         const imgUrl = `${BASE}/${e.qr_image_path}`;
         img.src          = imgUrl;
         img.style.display= 'block';
         img.alt          = `QR Code for ${e.equip_code}`;
   
         dlBtn.onclick = async () => {
           try {
             dlBtn.textContent = 'Downloading…';
             dlBtn.disabled    = true;
             const res  = await fetch(imgUrl, { credentials: 'include' });
             if (!res.ok) throw new Error(`HTTP ${res.status}`);
             const blob = await res.blob();
             const url  = URL.createObjectURL(blob);
             const a    = document.createElement('a');
             a.href     = url;
             a.download = `${e.equip_code}-QR.png`;
             document.body.appendChild(a);
             a.click();
             document.body.removeChild(a);
             URL.revokeObjectURL(url);
             toast('QR Code downloaded!');
           } catch (err) {
             toast('Download failed: ' + err.message, 'error');
           } finally {
             dlBtn.innerHTML  = '<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Download QR';
             dlBtn.disabled   = false;
           }
         };
       } else {
         img.style.display = 'none';
         dlBtn.onclick     = () => toast('QR image not yet generated for this equipment.', 'warning');
       }
   
       openModal('qrModal');
     };
   
     window.deleteEquip = async function(id, code) {
       const ok = await confirmDialog(`Delete equipment "${code}"? QR image will also be removed.`);
       if (!ok) return;
       const data = await api.del(API.equipment, { id });
       if (data.success) { toast(data.message, 'warning'); loadEquipment(); }
       else toast(data.message, 'error');
     };
   
           /* RESERVATION APPROVAL */
  const reservationQueue = new Map();
  async function loadReservations(search = '', status = '') {
    const data = await api.get(API.reservations, { search, status });
    if (!data.success) return;
    const { reservations, stats } = data;
    reservationQueue.clear();
    reservations.forEach(r => reservationQueue.set(Number(r.id), r));

    if (stats) {
      const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
      s('stat-total-res',    stats.total);
      s('stat-pending-res',  stats['Pending']  || 0);
      s('stat-approved-res', stats['Approved'] || 0);
      s('stat-rejected-res', stats['Rejected'] || 0);
    }

    const tbody = document.getElementById('resTableBody');
    if (!tbody) return;
    
    const emptyDiv = document.getElementById('resTableEmpty');
    
    if (!reservations.length) {
      if (emptyDiv) emptyDiv.style.display = 'block';
      tbody.innerHTML = '';
      return;
    }
    
    if (emptyDiv) emptyDiv.style.display = 'none';
    
    tbody.innerHTML = reservations.map(r => `
      <tr class="${r.priority === 'VIP' ? 'reservation-vip-row' : ''}">
        <td><code style="font-size:.75rem;background:var(--slate-100);padding:2px 7px;border-radius:5px">${r.display_res_id || r.res_id}</code></td>
        <td>${escapeHtml(r.faculty_name)}${r.priority === 'VIP' ? '<br><span class="badge badge-vip">Priority: VIP</span>' : ''}</td>
        <td><strong>${r.display_room_id || r.room_id}</strong>${r.room_name ? `<br><span style="font-size:.72rem;color:var(--slate-400)">${r.room_name}</span>` : ''}</td>
        <td>${fmtDate(r.res_date)}</td>
        <td style="white-space:nowrap">${fmtTime(r.time_start)} – ${fmtTime(r.time_end)}</td>
        <td style="max-width:160px;white-space:normal;font-size:.8rem">${r.purpose || '—'}</td>
        <td>
          ${badge(r.status)}
          ${r.priority_conflicts?.length ? `<span class="priority-conflict-label">Priority conflict: ${r.priority_conflicts.length} approved regular booking(s)</span>` : ''}
          ${r.vip_conflict_count ? '<span class="priority-conflict-label">Conflicts with an approved VIP booking</span>' : ''}
          ${r.schedule_conflict_label ? `<span class="schedule-conflict-badge">${escapeHtml(r.schedule_conflict_label)}</span>` : ''}
        </td>
        <td><div class="action-btns reservation-action-btns">
          ${['Pending', 'Waiting List'].includes(r.status) ? `
            <button class="action-btn approve" onclick="actReservation(${r.id},'approve')" ${r.vip_conflict_count ? 'disabled title="An approved VIP booking already occupies this slot"' : ''}>
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>${r.priority === 'VIP' ? (r.priority_conflicts?.length ? 'Approve VIP &amp; Waitlist Regular' : 'Approve VIP') : 'Approve'}
            </button>
            <button class="action-btn reject" onclick="actReservation(${r.id},'reject')">
              <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Reject
            </button>
            <button class="action-btn check-schedule" title="Check room schedule for this slot" onclick="checkRoomScheduleForSlot('${r.res_date || ''}','${r.time_start || ''}','${r.time_end || ''}')">
              <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M9 14h6"/></svg>Check Schedule
            </button>` : `<span style="font-size:.75rem;color:var(--slate-400)">${r.status}</span>`}
          <button class="action-btn delete" title="Delete reservation" onclick="deleteReservation(${r.id},'${r.res_id}')">
            <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
          </button>
        </div></td>
      </tr>`).join('');
  }

  document.getElementById('resSearch')?.addEventListener('input', e =>
    loadReservations(e.target.value, currentResFilter));
  document.getElementById('resStatusFilter')?.addEventListener('change', e =>
    loadReservations(document.getElementById('resSearch')?.value || '', e.target.value));

  // Filter buttons for modern UI
  let currentResFilter = '';
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-btn').forEach(b => b.removeAttribute('data-active'));
      this.setAttribute('data-active', 'true');
      currentResFilter = this.dataset.filter === 'all' ? '' : this.dataset.filter;
      loadReservations(document.getElementById('resSearch')?.value || '', currentResFilter);
    });
  });

  // Load and populate room status grid
  async function populateRoomStatusGrid(location = document.getElementById('approveRoomsLocationFilter')?.value || 'All') {
    const grid = document.getElementById('room-grid-approve');
    if (!grid) return;
    
    const data = await api.get(API.rooms, { location: location || 'All' });
    if (!data.success || !data.rooms) return;

    renderRoomSquares('room-grid-approve', data.rooms);
  }

  async function updateWeeklyStats() {
    const data = await api.get(API.reservations);
    if (!data.success || !data.reservations) return;
    
    const roomData = await api.get(API.rooms);
    if (!roomData.success) return;
    
    const today = new Date();
    const weekStart = new Date(today);
    weekStart.setDate(today.getDate() - today.getDay());
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    
    let bookedRooms = 0;
    let pending = 0;
    let rejected = 0;
    
    data.reservations.forEach(r => {
      if (r.status === 'Pending') pending++;
      if (r.status === 'Rejected') rejected++;
      
      const resDate = new Date(r.res_date);
      if (r.status === 'Approved' && resDate >= weekStart && resDate <= weekEnd) {
        bookedRooms++;
      }
    });
    
    const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    s('weekly-booked', bookedRooms);
    s('weekly-pending', pending);
    s('weekly-rejected', rejected);
    s('weekly-total', roomData.rooms.length);
  }

  setInterval(() => {
    if (!document.hidden && document.getElementById('tab-reservations')?.classList.contains('active')) {
      loadReservations(document.getElementById('resSearch')?.value || '', currentResFilter);
    }
  }, 30000);

  const tabEl = document.querySelector('[data-tab="reservations"]');
  if (tabEl) {
    tabEl.addEventListener('click', () => {
      setTimeout(() => {
        populateRoomStatusGrid();
        updateWeeklyStats();
      }, 100);
    });
  }

  window.actReservation = async function(id, action) {
    let note = '';
    if (action === 'reject') {
      const input = prompt('Optional: Add a rejection note (press OK to confirm, Cancel to abort):');
      if (input === null) return;
      note = input;
    }
    let priorityOverride = false;
    let expectedConflictIds = null;
    if (action === 'approve' && reservationQueue.get(Number(id))?.priority === 'VIP') {
      const latest = await api.get(API.reservations, { id });
      if (!latest.success) { toast(latest.message, 'error'); return; }
      const current = latest.reservation;
      if (current.vip_conflict_count) { toast('An approved VIP booking overlaps this slot. Select another time.', 'error'); return; }
      if (current.priority_conflicts?.length) {
        const confirmed = await confirmDialog(`Approve this VIP request and move ${current.priority_conflicts.length} overlapping approved regular reservation(s) to the Waiting List? The affected requesters will be notified.`, {title: 'Approve VIP Priority', confirmText: 'Approve VIP'});
        if (!confirmed) return;
        priorityOverride = true;
        expectedConflictIds = current.priority_conflicts.map(conflict => Number(conflict.id));
      }
    }
    const data = await api.patch(API.reservations, { id, action, admin_note: note, priority_override: priorityOverride, expected_conflict_ids: expectedConflictIds });
    if (data.success) { toast(data.message); loadReservations(document.getElementById('resSearch')?.value || '', currentResFilter); loadRooms(); populateRoomStatusGrid(); updateWeeklyStats(); }
    else { toast(data.message, 'error'); loadReservations(document.getElementById('resSearch')?.value || '', currentResFilter); }
  };

  window.deleteReservation = async function(id, code) {
    const ok = await confirmDialog(`Delete reservation "${code}"?`);
    if (!ok) return;
    const data = await api.del(API.reservations, { id });
    if (data.success) { toast(data.message, 'warning'); loadReservations(); populateRoomStatusGrid(); updateWeeklyStats(); }
    else toast(data.message, 'error');
  }; 

  loadUsers();
  loadLocations();
  loadRooms();
  loadEquipment();
  loadReservations();
  populateRoomStatusGrid();
  updateWeeklyStats();
})();

/* PROFILE + CHANGE PASSWORD */
(function initProfile() {
  if (!document.getElementById('tab-profile')) return;

  async function loadProfile() {
    const data = await api.get(API.profile);
    if (!data.success) return;
    const u = data.user;
    const s = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
    s('profileFullName', u.full_name);
    s('profileEmail',    u.email);
    s('profileUsername', u.username);
    s('profileRole',     u.role === 'vip' ? 'VIP' : u.role);
    const created = document.getElementById('profileCreated');
    if (created) created.textContent = fmtDate(u.created_at);
    const av = document.getElementById('profileAvatarDisplay');
    if (av) av.textContent = u.full_name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
  }

  async function loadRecentActivities(limit = 10) {
    const data = await api.get(API.profile + '?view=activities&limit=' + limit);
    if (!data.success) return;
    
    const activities = data.activities || [];
    const listEl = document.getElementById('profileActivityList');
    if (!listEl) return;

    if (!activities.length) {
         listEl.innerHTML = '<li style="text-align:center;color:var(--slate-400);padding:40px 0">No activities yet</li>';
         return;
       }
       
       listEl.innerHTML = activities.map(act => {
         const time = fmtDateTime(act.created_at);
         const actionLabel = formatActivityAction(act.action, act.target);
         const icon = getActivityIcon(act.action);
         
         return `
           <li>
             <div class="act-header">
               <div style="display:flex;align-items:center;gap:8px">
                 ${icon}
                 <span class="act-title">${escapeHtml(actionLabel)}</span>
               </div>
               <span class="act-time">${time}</span>
             </div>
             ${act.details ? `<div class="act-details">${escapeHtml(act.details)}</div>` : ''}
           </li>
         `;
       }).join('');
     }

     function formatActivityAction(action, target) {
       const actionMap = {
         'RESERVATION_CREATED': 'Created reservation',
         'RESERVATION_UPDATED': 'Updated reservation',
         'RESERVATION_CANCELLED': 'Cancelled reservation',
         'EQUIPMENT_BORROWED': 'Borrowed equipment',
         'EQUIPMENT_RETURNED': 'Returned equipment',
         'UPDATE_PROFILE': 'Updated profile',
         'CHANGE_PASSWORD': 'Changed password',
         'LOGIN': 'Logged in',
         'LOGOUT': 'Logged out',
       };
       return actionMap[action] || action.replace(/_/g, ' ').toLowerCase();
     }

     function getActivityIcon(action) {
       const icons = {
         'RESERVATION_CREATED': '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--success)"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
         'EQUIPMENT_BORROWED': '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--info)"><path d="M20 7l-8-4-8 4m0 0l8 4 8-4m-8 4v10l-8-4v10l8 4 8-4v-10l-8-4z"/></svg>',
         'UPDATE_PROFILE': '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--primary)"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
         'CHANGE_PASSWORD': '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--warning)"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
         'LOGIN': '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--success)"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>',
         'LOGOUT': '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--slate-500)"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4m7 14l5-5m0 0l-5-5m5 5H9"/></svg>',
       };
       return icons[action] || '<svg viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--slate-400)"><path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"/></svg>';
     }
   
     document.getElementById('saveProfileBtn')?.addEventListener('click', async () => {
       const data = await api.put(API.profile, {
         full_name: document.getElementById('profileFullName').value,
         email    : document.getElementById('profileEmail').value,
       });
       if (data.success) {
         toast(data.message);
         const el = document.querySelector('.profile-name');
         if (el) el.textContent = data.full_name;
         const av = document.querySelector('.profile-avatar');
         if (av) av.textContent = data.full_name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
         loadProfile();
       } else toast(data.message, 'error');
     });
   
     document.getElementById('savePasswordBtn')?.addEventListener('click', async () => {
       const data = await api.patch(API.profile, {
         current_password: document.getElementById('currentPassword').value,
         new_password    : document.getElementById('newPassword').value,
         confirm_password: document.getElementById('confirmNewPassword').value,
       });
       if (data.success) {
         toast(data.message);
         document.getElementById('passwordForm')?.reset();
       } else toast(data.message, 'error');
     });
   
     document.getElementById('fabSettings')?.addEventListener('click', () => {
       document.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
       document.querySelector('[data-tab="profile"]')?.classList.add('active');
       document.querySelectorAll('.tab-panel').forEach(p =>
         p.classList.toggle('active', p.id === 'tab-profile')
       );
     });
   
     loadProfile();
     loadRecentActivities(10);
   })();
   
   /* FACULTY DASHBOARD */
   (function initFaculty() {
     if (!document.getElementById('availRoomsGrid')) return;
   
     async function loadAnalytics(period = 'day') {
        setChartDefaults();
        const data = await api.get(API.analytics, { period: period });
       if (!data.success) return;

       const roomStatus = data.charts?.room_status || { labels: ['Available', 'Occupied', 'Under Maintenance'], data: [0, 0, 0] };
       const reservations = data.charts?.my_reservations || { labels: ['Pending', 'Approved', 'Rejected'], data: [0, 0, 0] };

       renderChart('facultyReservationChart', 'doughnut', reservations.labels, reservations.data, {
         label: 'Reservations',
         cutout: '64%',
         palette: ['#f59e0b', '#10b981', '#ef4444'],
       });
       renderChart('facultyRoomChart', 'doughnut', roomStatus.labels, roomStatus.data, {
         label: 'Rooms',
         cutout: '64%',
         palette: ['#10b981', '#f59e0b', '#ef4444'],
       });
     }

     function getFacultyRoomLocation() {
       return document.getElementById('facultyLocationFilter')?.value || 'All';
     }

     async function loadFacultyLocations() {
       const filter = document.getElementById('facultyLocationFilter');
       if (!filter) return;

       const data = await api.get(API.rooms, { action: 'get_locations' });
       if (!data.success || !Array.isArray(data.locations)) return;

       const current = filter.value || 'All';
       filter.innerHTML = '<option value="All">All Locations</option>' +
         data.locations.map(loc => `<option value="${escapeHtml(loc.location_name)}">${escapeHtml(loc.location_name)}</option>`).join('');
       filter.value = [...filter.options].some(opt => opt.value === current) ? current : 'All';
     }

     async function loadAvailRooms(search = '', location = getFacultyRoomLocation()) {
       const data = await api.get(API.rooms, { search, location: location || 'All' });
       if (!data.success) return;

       // Update Stats
       const s = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
       s('facTotalCount',    data.stats.total || 0);
       s('facAvailCount',    data.stats['Available'] || 0);
       s('facOccupiedCount', data.stats['Occupied'] || 0);
       s('facMaintCount',    data.stats['Under Maintenance'] || 0);
   
       const grid = document.getElementById('availRoomsGrid');
       if (!grid) return;
       
       const allRooms = data.rooms;
   
       if (!allRooms.length) {
         grid.innerHTML = '<p style="color:var(--slate-400)">No rooms found.</p>';
         return;
       }
       grid.innerHTML = allRooms.map(r => {
         const isAvail = r.status !== 'Under Maintenance';
         const btnAttr = isAvail ? '' : 'disabled style="opacity:0.6;cursor:not-allowed"';
         const btnText = isAvail ? 'Reserve' : (r.status === 'Occupied' ? 'Occupied' : 'Under Maintenance');
         const btnClass = isAvail ? 'btn-primary' : 'btn-secondary';
         
         return `
         <div class="equip-card">
           <div class="equip-card-head">
             <div><div class="equip-card-name">${r.room_name}</div><div class="equip-card-id">${r.room_id}</div></div>
             ${badge(r.status)}
           </div>
           <div class="equip-card-body">
             <div class="equip-detail"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>${r.capacity} seats</div>
             <div class="equip-detail"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>${r.type}</div>
             ${r.location ? `<div class="equip-detail"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>${r.location}</div>` : ''}
           </div>
           <div class="equip-card-footer">
             <button class="btn ${btnClass} btn-sm" style="width:100%" onclick="facQuickReserve('${r.room_id}')" ${btnAttr}>
               <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>${btnText}
             </button>
           </div>
         </div>`;
       }).join('');
   
       const sel = document.getElementById('resRoomSelect');
       if (sel) {
         const cur = sel.value;
         const selectedOption = sel.selectedOptions[0]?.cloneNode(true);
         sel.innerHTML = '<option value="">— Choose a room —</option>';
         allRooms.forEach(r => {
           const isAvail = r.status !== 'Under Maintenance';
           sel.innerHTML += `<option value="${r.room_id}" ${isAvail ? '' : 'disabled'}>${r.room_id} — ${r.room_name} (${r.status})</option>`;
         });
         // A scanned room can be outside the Available Rooms search/location filter.
         if (cur && selectedOption && ![...sel.options].some(option => option.value === cur)) {
           sel.add(selectedOption);
         }
         if (cur) sel.value = cur;
       }

       const facResGrid = document.getElementById('facultyReserveRoomsGrid');
       if (facResGrid) {
         if (!allRooms.length) {
           facResGrid.innerHTML = '<div class="room-grid-empty">No rooms found</div>';
         } else {
           facResGrid.innerHTML = allRooms.map(r => {
             const status = r.status.toLowerCase();
             const statusClass = status === 'occupied' ? 'occupied' : status === 'available' ? 'available' : 'maintenance';
             const titleText = `Click to select ${r.room_id} (${r.status})`;
             return `<div class="room-item ${statusClass}" style="cursor:pointer" title="${titleText}" data-room-pick="${r.room_id}">${r.room_id}</div>`;
           }).join('');
         }
       }
     }

     function hideRoomSuggestion() {
       const box = document.getElementById('roomSmartSuggestion');
       if (!box) return;
       box.hidden = true;
       box.textContent = '';
     }

     async function loadRoomSuggestion(roomId) {
       const box = document.getElementById('roomSmartSuggestion');
       if (!box) return;
       if (!roomId) {
         hideRoomSuggestion();
         return;
       }

       const data = await api.get(API.fetchAnalytics, { action: 'suggestion', room_id: roomId });
       const suggestion = data.suggestion || {};
       if (!data.success || !suggestion.show) {
         hideRoomSuggestion();
         return;
       }

       box.textContent = suggestion.message;
       box.hidden = false;
     }

     window.loadFacultyAnalytics = loadAnalytics;

     function applyFacultyReservationStatusFilter() {
       const filter = document.getElementById('facultyReservationStatusFilter')?.value || 'all';
       const rows = document.querySelectorAll('#myResTableBody tr[data-reservation-status]');
       let visibleCount = 0;

       rows.forEach(row => {
         const visible = filter === 'all' || row.dataset.reservationStatus === filter;
         row.hidden = !visible;
         if (visible) visibleCount++;
       });

       const tbody = document.getElementById('myResTableBody');
       const emptyRowEl = document.getElementById('facultyReservationFilterEmptyRow');
       if (!tbody || !rows.length) {
         emptyRowEl?.remove();
         return;
       }

       if (!visibleCount && !emptyRowEl) {
         tbody.insertAdjacentHTML('beforeend', `<tr id="facultyReservationFilterEmptyRow">${emptyRow(7, '<path d="M3 6h18M8 12h8M10 18h4"/>', 'No reservations match this filter.').replace(/^<tr>|<\/tr>$/g, '')}</tr>`);
       } else if (visibleCount && emptyRowEl) {
         emptyRowEl.remove();
       }
     }
   
     async function loadMyReservations() {
       const data = await api.get(API.reservations);
       if (!data.success) return;
       const el = document.getElementById('facMyCount');
       if (el) el.textContent = data.reservations.length;
       const tbody = document.getElementById('myResTableBody');
       if (!tbody) return;
       if (!data.reservations.length) {
         tbody.innerHTML = emptyRow(7, '<rect x="3" y="4" width="18" height="18" rx="2"/>', 'No reservations yet');
         return;
       }
       tbody.innerHTML = data.reservations.map(r => {
         const canCancel = ['Pending', 'Waiting List', 'Approved'].includes(r.status);
         const canArchive = ['Approved', 'Rejected', 'Cancelled'].includes(r.status);
         return `
         <tr data-reservation-status="${r.status}">
           <td>${r.display_res_id || r.res_id}</td>
           <td>${r.display_room_id || r.room_id}${r.room_name ? `<br><span style="font-size:.72rem;color:var(--slate-400)">${r.room_name}</span>` : ''}</td>
           <td>${fmtDate(r.res_date)}</td>
           <td>${fmtTime(r.time_start)} – ${fmtTime(r.time_end)}</td>
           <td style="max-width:180px;white-space:normal;font-size:.8rem">${r.purpose || '—'}</td>
           <td>${badge(r.status)}${r.priority === 'VIP' ? ' <span class="badge badge-vip">Priority: VIP</span>' : ''}${r.admin_note ? `<br><span style="font-size:.7rem;color:var(--slate-400)">${escapeHtml(r.admin_note)}</span>` : ''}</td>
           <td>
             <div class="action-btns" style="min-width:110px;gap:4px">
               ${canCancel ? `<button class="btn btn-warning btn-sm" onclick="facCancelReservation(${r.id}, '${(r.display_res_id || r.res_id).replace(/'/g, "\\'")}')">
                 <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>Cancel
               </button>` : ''}
               ${canArchive ? `<button class="btn btn-secondary btn-sm" onclick="facArchiveReservation(${r.id}, '${(r.display_res_id || r.res_id).replace(/'/g, "\\'")}')">
                 <svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="3" rx="1"/><path d="M6 7l0.6 8.4a2 2 0 001.98 1.6h7.84a2 2 0 001.98-1.6L18 7"/></svg>Archive
               </button>` : ''}
             </div>
           </td>
         </tr>`;
       }).join('');
       applyFacultyReservationStatusFilter();
     }

     // Cancel reservation (Faculty)
     window.facCancelReservation = async function(resId, resCode) {
       const ok = await confirmDialog(`Cancel reservation ${resCode}? This action cannot be undone.`);
       if (!ok) return;
       const data = await api.patch(API.reservations, { id: resId, action: 'cancel' });
       if (data.success) { toast('Reservation cancelled.'); loadMyReservations(); }
       else toast(data.message, 'error');
     };

     // Archive reservation (Faculty)
     window.facArchiveReservation = async function(resId, resCode) {
       const data = await api.patch(API.reservations, { id: resId, action: 'archive' });
       if (data.success) { toast('Reservation archived.'); loadMyReservations(); }
       else toast(data.message, 'error');
     };
   
     window.facQuickReserve = function(roomId) {
       document.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
       document.querySelector('[data-tab="reserve"]')?.classList.add('active');
       document.querySelectorAll('.tab-panel').forEach(p => p.classList.toggle('active', p.id === 'tab-reserve'));
       setTimeout(() => {
         const s = document.getElementById('resRoomSelect');
         if (s) {
           s.value = roomId;
           s.dispatchEvent(new Event('change', { bubbles: true }));
         }
       }, 80);
     };
   
     const resDate = document.getElementById('resDate');
     if (resDate) resDate.value = new Date().toISOString().split('T')[0];
   
     document.getElementById('submitResBtn')?.addEventListener('click', async () => {
       const fd = new FormData();
       fd.append('room_id',    document.getElementById('resRoomSelect').value);  // key = room_id
       fd.append('res_date',   document.getElementById('resDate').value);
       fd.append('time_start', document.getElementById('resTimeStart').value);
       fd.append('time_end',   document.getElementById('resTimeEnd').value);
       fd.append('purpose',    document.getElementById('resPurpose').value);
       fd.append('attendees',  document.getElementById('resAttendees')?.value || '1');
       const data = await api.post(API.reservations, fd);
       if (data.success) {
         toast(data.message);
         ['resRoomSelect','resTimeStart','resTimeEnd','resPurpose'].forEach(id => {
           const el = document.getElementById(id); if (el) el.value = '';
         });
         const attendees = document.getElementById('resAttendees');
         if (attendees) attendees.value = '1';
         document.getElementById('resRoomSelect')?.dispatchEvent(new Event('change', { bubbles: true }));
         loadMyReservations();
       } else toast(data.message, 'error');
     });
   
     document.getElementById('clearResBtn')?.addEventListener('click', () => {
       ['resRoomSelect','resTimeStart','resTimeEnd','resPurpose','resAttendees'].forEach(id => {
         const el = document.getElementById(id); if (el) el.value = '';
       });
       if (resDate) resDate.value = new Date().toISOString().split('T')[0];
       const attendees = document.getElementById('resAttendees');
       if (attendees) attendees.value = '1';
       hideRoomSuggestion();
     });

     document.getElementById('resRoomSelect')?.addEventListener('change', e => {
       loadRoomSuggestion(e.target.value);
     });

     document.getElementById('facultyReserveRoomsGrid')?.addEventListener('click', e => {
       const tile = e.target.closest('[data-room-pick]');
       if (!tile) return;
       const s = document.getElementById('resRoomSelect');
       if (!s) return;
       s.value = tile.dataset.roomPick || '';
       s.dispatchEvent(new Event('change', { bubbles: true }));
     });
   
     document.getElementById('availRoomSearch')?.addEventListener('input', e => loadAvailRooms(e.target.value, getFacultyRoomLocation()));
     document.getElementById('facultyLocationFilter')?.addEventListener('change', e => {
       loadAvailRooms(document.getElementById('availRoomSearch')?.value || '', e.target.value || 'All');
     });
     document.getElementById('facultyReservationStatusFilter')?.addEventListener('change', applyFacultyReservationStatusFilter);

     loadAnalytics();
     loadFacultyLocations();
     loadAvailRooms();
     loadMyReservations();

     setInterval(() => {
       loadAnalytics();
       if (document.getElementById('tab-available')?.classList.contains('active')) loadAvailRooms(document.getElementById('availRoomSearch')?.value || '', getFacultyRoomLocation());
       if (document.getElementById('tab-my-reservations')?.classList.contains('active')) loadMyReservations();
     }, 30000);
   })();
   
   /* MAINTENANCE DASHBOARD */
   (function initMaintenance() {
     if (!document.getElementById('maintEquipGrid')) return;
   
     let filterSearch = '', filterStatus = '';

     async function loadAnalytics(period = 'day') {
        setChartDefaults();
        const data = await api.get(API.analytics, { period: period });
       if (!data.success) return;

       const condition = data.charts?.condition_overview || { labels: ['Good', 'Damaged', 'Repair Needed'], data: [0, 0, 0] };
       const borrowed = data.charts?.borrowed_top || { labels: ['No borrowings'], data: [0] };
       const status = data.charts?.equipment_status || { labels: ['Available', 'Borrowed', 'Maintenance'], data: [0, 0, 0] };

       renderChart('maintConditionChart', 'doughnut', condition.labels, condition.data, {
         label: 'Condition',
         cutout: '64%',
         backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
         borderColor: '#ffffff',
         borderWidth: 2,
       });
       renderChart('maintBorrowedChart', 'bar', borrowed.labels, borrowed.data, {
         label: 'Borrow count',
         legend: false,
         palette: ['#2563eb', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'],
         backgroundColor: ['#2563eb', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'],
         borderColor: '#2563eb',
         scales: {
           x: {
             grid: { display: false },
             ticks: { maxRotation: 28, minRotation: 20, autoSkip: false },
           },
           y: {
             beginAtZero: true,
             ticks: { precision: 0 },
             grid: { color: '#e2e8f0' },
           },
         },
         tooltipCallbacks: {
           label: ctx => `${ctx.parsed.y} borrows`,
         },
       });
       renderChart('maintStatusChart', 'doughnut', status.labels, status.data, {
         label: 'Equipment',
         cutout: '64%',
         backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
         borderColor: '#ffffff',
         borderWidth: 2,
       });
     }

     async function loadMaintEquip() {
       const data = await api.get(API.equipment, { search: filterSearch, status: filterStatus });
       if (!data.success) return;
       const { equipment, stats } = data;
   
       const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
       s('maint-total',   stats.total);
       s('maint-avail',   stats['Available']  || 0);
       s('maint-inuse',   stats['In Use']     || 0);
       s('maint-damaged', stats['Damaged']    || 0);
   
       const grid = document.getElementById('maintEquipGrid');
       if (grid) {
         grid.innerHTML = equipment.length
           ? equipment.map(e => `
               <div class="equip-card">
                 <div class="equip-card-head">
                   <div><div class="equip-card-name">${e.name}</div><div class="equip-card-id">${e.equip_code}</div></div>
                   <div style="display:flex;gap:6px;align-items:flex-end;flex-direction:column">${badge(e.status)}${healthBadge(e.prediction)}</div>
                 </div>
                 <div class="equip-card-body">
                   <div class="equip-detail"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>${e.assigned_room || '—'}</div>
                   ${e.serial_number ? `<div class="equip-detail"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16"/></svg>${e.serial_number}</div>` : ''}
                   <div class="equip-health-block">${lifecycleBar(e.prediction)}</div>
                 </div>
                 <div class="equip-card-footer">
                   <select class="status-select" ${['For Disposal','Disposed'].includes(e.status) ? 'disabled' : ''} onchange="maintUpdateStatus(${e.id},this.value)">
                     ${['For Disposal','Disposed'].includes(e.status) ? `<option selected>${e.status}</option>` : ''}
                     <option ${e.status==='Available'  ?'selected':''}>Available</option>
                     <option ${e.status==='In Use'     ?'selected':''}>In Use</option>
                     <option ${e.status==='Damaged'    ?'selected':''}>Damaged</option>
                     <option ${e.status==='Under Repair'?'selected':''}>Under Repair</option>
                   </select>
                 </div>
               </div>`).join('')
           : '<p style="color:var(--slate-400);grid-column:1/-1">No equipment found.</p>';
       }
   
       const tbody = document.getElementById('maintTableBody');
       if (tbody) {
         tbody.innerHTML = equipment.length
           ? equipment.map(e => `
               <tr>
                 <td>${e.equip_code}</td>
                 <td>${e.name}</td>
                 <td>${e.assigned_room || '—'}</td>
                 <td>${badge(e.status)}</td>
                 <td>${healthBadge(e.prediction)}${e.prediction?.maintenance_prediction ? `<div class="health-note">${e.prediction.maintenance_prediction}</div>` : ''}</td>
                 <td>${lifecycleBar(e.prediction)}</td>
                 <td>
                   <select class="status-select" style="width:140px" ${['For Disposal','Disposed'].includes(e.status) ? 'disabled' : ''} onchange="maintUpdateStatus(${e.id},this.value)">
                     ${['For Disposal','Disposed'].includes(e.status) ? `<option selected>${e.status}</option>` : ''}
                     <option ${e.status==='Available'  ?'selected':''}>Available</option>
                     <option ${e.status==='In Use'     ?'selected':''}>In Use</option>
                     <option ${e.status==='Damaged'    ?'selected':''}>Damaged</option>
                     <option ${e.status==='Under Repair'?'selected':''}>Under Repair</option>
                   </select>
                 </td>
               </tr>`).join('')
           : emptyRow(7, '<rect x="2" y="3" width="20" height="14" rx="2"/>', 'No equipment found');
       }
   
       const sel = document.getElementById('reportEquipSelect');
       if (sel) {
         sel.innerHTML = '<option value="">— Select Equipment —</option>';
         equipment.forEach(e => sel.innerHTML += `<option value="${e.id}">${e.equip_code} — ${e.name}</option>`);
       }
     }

     window.loadMaintenanceAnalytics = loadAnalytics;
     window.loadMaintenanceEquipment = loadMaintEquip;
   
     window.maintUpdateStatus = async function(id, status) {
       const data = await api.patch(API.equipment, { id, status });
       if (data.success) { toast(data.message); loadMaintEquip(); }
       else toast(data.message, 'error');
     };
   
     document.getElementById('maintEquipSearch')?.addEventListener('input', e => {
       filterSearch = e.target.value; loadMaintEquip();
     });
     document.getElementById('statusFilter')?.addEventListener('change', e => {
       filterStatus = e.target.value; loadMaintEquip();
     });

     loadAnalytics();
     loadMaintEquip();

     setInterval(() => {
       loadAnalytics();
       if (document.getElementById('tab-equip-list')?.classList.contains('active')) loadMaintEquip();
     }, 30000);
   })();

/* EQUIPMENT BORROWING (MAINTENANCE) */
(function initBorrowing() {
  if (!document.getElementById('tab-borrowing')) return;

  let borrowingsCache = [];
  let pendingReturn = null;

  function getBorrowingStatusFilter() {
    return document.getElementById('borrowingStatusFilter')?.value || 'all';
  }

  function matchesBorrowingStatusFilter(borrowing, filter) {
    const status = String(borrowing.status || '');
    if (filter === 'active') return !status.startsWith('Returned');
    if (filter === 'returned') return status === 'Returned';
    if (filter === 'returned-damaged') return status === 'Returned (Damaged)';
    if (filter === 'overdue') return status === 'Overdue';
    return true;
  }

  function renderBorrowings() {
    const tbody = document.getElementById('borrowingTableBody');
    if (!tbody) return;

    if (!borrowingsCache.length) {
      tbody.innerHTML = emptyRow(7, '<rect x="3" y="4" width="18" height="18" rx="2"/>', 'No equipment currently borrowed.');
      return;
    }

    const filter = getBorrowingStatusFilter();
    const visibleBorrowings = borrowingsCache.filter(b => matchesBorrowingStatusFilter(b, filter));

    if (!visibleBorrowings.length) {
      tbody.innerHTML = emptyRow(7, '<path d="M3 6h18M8 12h8M10 18h4"/>', 'No borrowing records match this filter.');
      return;
    }

    tbody.innerHTML = visibleBorrowings.map(b => {
      const displayStatus = b.current_status || b.status;
      const isReturned = String(b.status || '').startsWith('Returned');
      const actionBtn = isReturned 
        ? `<span style="font-size:0.8rem;color:var(--slate-400)">${b.status}<br>${fmtDate(b.actual_time_in)}</span>`
        : `<button class="btn btn-primary btn-sm" onclick="returnEquipment(${b.id})">Mark Returned</button>`;
        
      return `
      <tr>
        <td><strong>${b.equip_code}</strong><br><span style="font-size:0.8rem;color:var(--slate-500)">${b.equip_name}</span></td>
        <td>${escapeHtml(b.borrower_name)}<br><span style="font-size:0.8rem;color:var(--slate-500)">${escapeHtml(b.department || '')}</span></td>
        <td>${fmtDate(b.borrow_date)}<br><span style="font-size:0.8rem;color:var(--slate-500)">Out: ${fmtTime(b.time_out)}</span></td>
        <td>${fmtDate(b.expected_return_date)}<br><span style="font-size:0.8rem;color:var(--slate-500)">Due: ${fmtTime(b.expected_return_time)}</span></td>
        <td>${badge(displayStatus)}</td>
        <td>Overdue: ${Number(b.penalty_amount || 0).toFixed(2)}<br>Damage: ${Number(b.damage_penalty_amount || 0).toFixed(2)}<br><strong>Total: ${Number(b.total_penalty_amount || 0).toFixed(2)}</strong></td>
        <td>${actionBtn}</td>
      </tr>`;
    }).join('');
  }

  async function loadBorrowings() {
    const data = await api.get(API.borrowing);
    if (!data.success) return;
    borrowingsCache = data.borrowings || [];
    renderBorrowings();
    window.dispatchEvent(new Event('borrowing:changed'));
  }

  function setReturnButtonsDisabled(disabled) {
    ['returnGoodBtn', 'returnDamagedBtn'].forEach(id => {
      const btn = document.getElementById(id);
      if (btn) btn.disabled = disabled;
    });
  }

  async function submitReturnCondition(condition) {
    if (!pendingReturn) return;
    setReturnButtonsDisabled(true);
    const fields = { student_faculty_id: document.getElementById('returnBorrowerId').value.trim() };
    if (condition === 'damaged') {
      for (const id of ['returnDamageReason', 'returnDamageCost']) {
        if (!document.getElementById(id).reportValidity()) { setReturnButtonsDisabled(false); return; }
      }
      fields.damage_level = document.getElementById('returnDamageLevel').value;
      fields.reason = document.getElementById('returnDamageReason').value.trim();
      fields.assessed_cost = document.getElementById('returnDamageCost').value;
      fields.student_faculty_id = document.getElementById('returnBorrowerId').value.trim();
    }
    const data = await api.patch(API.borrowing, {
      id: pendingReturn.id,
      action: 'return',
      return_condition: condition,
      ...fields,
    });
    setReturnButtonsDisabled(false);

    if (data.success) {
      toast(data.message);
      closeModal('returnConditionModal');
      pendingReturn.status = data.borrowing_status || (condition === 'damaged' ? 'Returned (Damaged)' : 'Returned');
      pendingReturn.current_status = pendingReturn.status;
      pendingReturn.actual_time_in = new Date().toISOString().slice(0, 19).replace('T', ' ');
      await loadBorrowings();
      if (typeof window.loadMaintenanceAnalytics === 'function') window.loadMaintenanceAnalytics();
      if (typeof window.loadMaintenanceEquipment === 'function') window.loadMaintenanceEquipment();

      // The server creates the condition report and fee atomically with the return.
      window.dispatchEvent(new Event('damage:changed'));
    }
    else toast(data.message, 'error');
  }

  window.returnEquipment = function(id) {
    const borrowing = borrowingsCache.find(b => Number(b.id) === Number(id));
    if (!borrowing) {
      toast('Borrowing record not found. Please refresh and try again.', 'error');
      return;
    }
    pendingReturn = borrowing;
    document.getElementById('returnDamageLevel').value = 'Not Critical';
    document.getElementById('returnDamageReason').value = '';
    document.getElementById('returnDamageCost').value = '';
    document.getElementById('returnBorrowerId').value = borrowing.student_faculty_id || '';
    const label = document.getElementById('returnConditionEquipment');
    if (label) label.textContent = `${borrowing.equip_code} - ${borrowing.equip_name}`;
    openModal('returnConditionModal');
  };

  document.getElementById('returnGoodBtn')?.addEventListener('click', () => submitReturnCondition('good'));
  document.getElementById('returnDamagedBtn')?.addEventListener('click', () => submitReturnCondition('damaged'));
  async function findReturn() {
    const input = document.getElementById('returnEquipmentLookup');
    const code = input.value.trim();
    if (!code) return;
    const data = await api.get(API.borrowing, { search: code });
    if (!data.success) { toast(data.message, 'error'); return; }
    const matches = data.borrowings.filter(b => ['Borrowed', 'Overdue'].includes(b.status) && b.equip_code.toLowerCase() === code.toLowerCase());
    if (!matches.length) { toast('No active borrowing matches this equipment code.', 'error'); return; }
    borrowingsCache = matches;
    document.getElementById('borrowingStatusFilter').value = 'active';
    renderBorrowings();
    if (matches.length === 1) window.returnEquipment(matches[0].id);
    else toast('Several loans match this equipment. Select the correct borrower using Mark Returned.', 'info');
    input.value = '';
  }
  document.getElementById('findReturnBtn')?.addEventListener('click', findReturn);
  document.getElementById('returnEquipmentLookup')?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); findReturn(); } });
  document.getElementById('borrowingStatusFilter')?.addEventListener('change', renderBorrowings);

  const qrInput = document.getElementById('qrScanInput');
  if (qrInput) {
    let lastScan = '';
    let scanTimer = null;

    const pad = n => String(n).padStart(2, '0');
    const setCurrentBorrowDateTime = () => {
      const now = new Date();
      const dateInput = document.querySelector('#borrowingForm [name="borrow_date"]');
      const timeInput = document.querySelector('#borrowingForm [name="time_out"]');
      if (dateInput) dateInput.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
      if (timeInput) timeInput.value = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };

    async function handleQrScan() {
      const code = qrInput.value.trim();
      if (!code || code === lastScan) return;
      lastScan = code;

      const equipCodeInput = document.getElementById('bEquipCode');
      const borrowerInput = document.querySelector('#borrowingForm [name="borrower_name"]');
      const data = await api.get(API.equipment, { search: code });
      const eq = data.success
        ? data.equipment.find(item => String(item.equip_code).toLowerCase() === code.toLowerCase())
        : null;

      if (!eq) {
        if (equipCodeInput) equipCodeInput.value = '';
        qrInput.value = '';
        toast('Invalid Equipment Code.', 'error');
        lastScan = '';
        return;
      }

      if (equipCodeInput) equipCodeInput.value = code;
      setCurrentBorrowDateTime();
      qrInput.value = '';
      toast(`Scanned: ${eq.equip_code} - ${eq.name}`);
      borrowerInput?.focus();
      setTimeout(() => { lastScan = ''; }, 350);
    }

    qrInput.addEventListener('keydown', e => {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      handleQrScan();
    });
    qrInput.addEventListener('change', () => {
      window.clearTimeout(scanTimer);
      scanTimer = window.setTimeout(handleQrScan, 30);
    });
  }

  document.getElementById('saveBorrowingBtn')?.addEventListener('click', async () => {
    const form = document.getElementById('borrowingForm');
    if (!form.reportValidity()) return;
    if (!form.elements.student_faculty_id.value) {
      toast('Select a registered borrower from the search suggestions.', 'error');
      document.getElementById('borrower_search')?.focus();
      return;
    }
    const fd = new FormData(document.getElementById('borrowingForm'));
    const data = await api.post(API.borrowing, fd);
    if (data.success) {
      toast(data.message);
      document.getElementById('borrowingForm').reset();
      loadBorrowings();
      if (typeof window.loadMaintenanceAnalytics === 'function') window.loadMaintenanceAnalytics();
    } else {
      toast(data.message, 'error');
    }
  });

  loadBorrowings();
})();

/* CONDITION REPORTS */
(function initConditionReports() {
  if (!document.getElementById('reportsTableBody') && !document.getElementById('addReportBtn')) return;

  const addBtn = document.getElementById('addReportBtn');
  const saveBtn = document.getElementById('saveReportBtn');
  const tbody = document.getElementById('reportsTableBody');
  const equipSelect = document.getElementById('reportEquip');
  const reportedByInput = document.getElementById('reportReportedBy');

  function setReportedBy() {
    if (reportedByInput) reportedByInput.value = window._session?.full_name || 'Carlos Mendoza';
  }

  async function loadReportEquipmentOptions(selectedId = '') {
    if (!equipSelect) return;
    equipSelect.innerHTML = '<option value="">Loading equipment...</option>';
    const data = await api.get(API.equipment);
    if (data.success) {
      equipSelect.innerHTML = '<option value="">— Select Equipment —</option>' +
        data.equipment.filter(e => !['Decommissioned','Disposed'].includes(e.status)).map(e => `<option value="${e.id}">${escapeHtml(e.equip_code)} — ${escapeHtml(e.name)} (${e.status})</option>`).join('');
      if (selectedId) equipSelect.value = String(selectedId);
    } else {
      equipSelect.innerHTML = '<option value="">Error loading equipment</option>';
    }
  }

  window.openConditionReportModal = async function(prefill = {}) {
    setReportedBy();
    document.getElementById('reportIssue').value = prefill.issue || '';
    document.getElementById('reportSeverity').value = prefill.severity || 'Low';
    document.getElementById('reportAction').value = prefill.action || 'Monitor';
    document.getElementById('reportDamageLevel').value = prefill.damage_level || 'None';
    document.getElementById('reportAssessedCost').value = prefill.assessed_cost ?? '';
    await loadReportEquipmentOptions(prefill.equipment?.id || prefill.equipment_id || '');
    openModal('reportModal');
  };

  if (addBtn) {
    addBtn.addEventListener('click', async () => {
      window.openConditionReportModal();
      return;
      if (equipSelect) {
        equipSelect.innerHTML = '<option value="">Loading equipment...</option>';
        const data = await api.get(API.equipment);
        if (data.success) {
          equipSelect.innerHTML = '<option value="">— Select Equipment —</option>' + 
            data.equipment.filter(e => e.status !== 'Decommissioned').map(e => `<option value="${e.id}">${e.equip_code} — ${e.name} (${e.status})</option>`).join('');
        } else {
          equipSelect.innerHTML = '<option value="">Error loading equipment</option>';
        }
      }
      openModal('reportModal');
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', async () => {
      const equipId = equipSelect ? equipSelect.value : null;
      const issue = document.getElementById('reportIssue').value.trim();
      const severity = document.getElementById('reportSeverity').value;
      const actionNeeded = document.getElementById('reportAction').value;

      if (!equipId || !issue) {
        toast('Equipment and issue description are required.', 'error');
        return;
      }

      const fd = new FormData();
      fd.append('equipment_id', equipId);
      fd.append('issue', issue);
      fd.append('severity', severity);
      fd.append('action_needed', actionNeeded);
      const damageLevel = document.getElementById('reportDamageLevel').value;
      const cost = document.getElementById('reportAssessedCost');
      cost.required = damageLevel !== 'None';
      if (!cost.reportValidity()) return;
      fd.append('damage_level', damageLevel);
      fd.append('assessed_cost', cost.value);

      const oldHtml = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = 'Submitting...';

      const data = await api.post(API.reports, fd);
      saveBtn.disabled = false;
      saveBtn.innerHTML = oldHtml;

      if (data.success) {
        toast('Report submitted successfully!');
        closeModal('reportModal');
        loadConditionReports();
        if (typeof window.loadMaintenanceAnalytics === 'function') window.loadMaintenanceAnalytics();
        if (typeof window.loadMaintenanceEquipment === 'function') window.loadMaintenanceEquipment();
      } else {
        toast(data.message, 'error');
      }
    });
  }

  async function loadConditionReports() {
    if (!tbody) return;
    const data = await api.get(API.reports);
    if (!data.success) {
      tbody.innerHTML = emptyRow(6, '<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>', 'Failed to load reports');
      return;
    }

    if (!data.reports || data.reports.length === 0) {
      tbody.innerHTML = emptyRow(6, '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>', 'No condition reports found.');
      return;
    }

    const severityColor = {
      'Low': 'var(--blue)',
      'Medium': 'var(--warning)',
      'High': 'var(--danger)'
    };

    tbody.innerHTML = data.reports.map(r => `
      <tr>
        <td><code style="font-size:.78rem;background:var(--slate-100);padding:2px 8px;border-radius:5px">${r.report_code || r.id}</code></td>
        <td><strong>${r.equip_name || 'Unknown'}</strong><br><span style="font-size:0.75rem;color:var(--slate-500)">ID: ${r.equip_id || 'N/A'}</span></td>
        <td style="max-width: 250px; white-space: normal; font-size: 0.85rem; line-height: 1.4;">
            ${escapeHtml(r.issue)}<br>
            <small>Damage: ${escapeHtml(r.damage_level || 'None')} · Assessed cost: ${Number(r.assessed_cost || 0).toFixed(2)}</small>
            <div style="margin-top: 4px;">
              <span style="font-size:0.75rem;color:${severityColor[r.severity]};font-weight:600;padding:2px 6px;background:var(--slate-100);border-radius:4px">Severity: ${r.severity}</span>
              <span style="font-size:0.75rem;color:var(--slate-600);margin-left:5px">Action: ${r.action_needed}</span>
            </div>
        </td>
        <td><div style="font-size:.85rem;font-weight:500">${r.reporter_name || 'System'}</div></td>
        <td style="font-size:.8rem;color:var(--slate-500)">${fmtDate(r.created_at)}</td>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            ${badge(r.status)}
            ${r.status === 'Open' ? `
              <button class="btn btn-success btn-sm" onclick="resolveReport(${r.id})">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Resolve
              </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `).join('');
  }

  window.addEventListener('damage:changed', loadConditionReports);

  window.resolveReport = async function(id) {
    const ok = await confirmDialog("Mark this report as resolved? Availability will follow any remaining damage reports, active loans, and disposal restrictions.");
    if (!ok) return;
    const data = await api.patch(API.reports, { id });
    if (data.success) { 
        toast(data.message); 
        loadConditionReports(); 
    } else {
        toast(data.message, 'error');
    }
  };

  // Initial Load
  loadConditionReports();
})();

    /* DASHBOARD OVERVIEW (ADMIN) */
    (function initDashboardOverview() {
      if (!document.getElementById('tab-dashboard')) return;

      let currentReservationPeriod = document.querySelector('[data-period].active')?.dataset.period || 'day';
      let currentTrendPeriod = document.querySelector('[data-trend-period].active')?.dataset.trendPeriod || 'month';
      let adminAnalyticsRequestSeq = 0;

      async function loadAnalytics(period = currentReservationPeriod, trendPeriod = currentTrendPeriod) {
        currentReservationPeriod = period || currentReservationPeriod || 'day';
        currentTrendPeriod = trendPeriod || currentTrendPeriod || 'month';
        const requestSeq = ++adminAnalyticsRequestSeq;
        const requestParams = { period: currentReservationPeriod, trend_period: currentTrendPeriod };
        setChartDefaults();
        console.debug('[Reservation Analytics] request', {
          selectedPeriod: currentReservationPeriod,
          url: API.analytics,
          params: requestParams,
          requestSeq,
        });
        const data = await api.get(API.analytics, requestParams);
        if (!data.success) return;
        if (requestSeq !== adminAnalyticsRequestSeq) {
          console.debug('[Reservation Analytics] stale response ignored', {
            selectedPeriod: currentReservationPeriod,
            requestSeq,
            latestRequestSeq: adminAnalyticsRequestSeq,
          });
          return;
        }

        const s = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        s('dash-total-rooms', data.stats.total_rooms || 0);
        s('dash-total-equip', data.stats.total_equipment || 0);
        s('dash-pending-res', data.stats.pending_reservations || 0);
        s('dash-borrowed-equip', data.stats.borrowed_equipment || 0);
        s('dash-total-users', data.stats.total_users || 0);

        const roomStatus = data.charts?.room_status || { labels: ['Available Rooms', 'Occupied Rooms', 'Under Maintenance'], data: [0, 0, 0] };
        const equipStatus = data.charts?.equipment_status || { labels: ['Available Equipment', 'In-Use Equipment', 'Under Maintenance'], data: [0, 0, 0] };
        const resBar = data.charts?.reservations_bar || { labels: [], data: [] };
        const resTrend = data.charts?.reservation_trend || { labels: [], data: [] };
        console.debug('[Reservation Analytics] response', {
          selectedPeriod: currentReservationPeriod,
          response: data.charts?.reservations_bar,
          labelsCount: Array.isArray(resBar.labels) ? resBar.labels.length : 0,
          valuesCount: Array.isArray(resBar.data) ? resBar.data.length : 0,
          requestSeq,
        });

        const roomAvailable = roomStatus.data[0] || 0;
        const roomOccupied = roomStatus.data[1] || 0;
        const roomMaintenance = roomStatus.data[2] || 0;
        const totalRooms = roomAvailable + roomOccupied + roomMaintenance;
        const roomOccupiedPct = totalRooms > 0 ? Math.round((roomOccupied / totalRooms) * 100) : 0;
        const roomPctEl = document.getElementById('roomStatusPercentage');
        if (roomPctEl) roomPctEl.textContent = `${roomOccupiedPct}%`;

        renderChart('adminRoomStatusChart', 'doughnut', roomStatus.labels, roomStatus.data, {
          label: 'Rooms',
          cutout: '64%',
          palette: ['#2563eb', '#f59e0b', '#ef4444'],
        });
        const equipAvailable = equipStatus.data[0] || 0;
        const equipInUse = equipStatus.data[1] || 0;
        const equipMaintenance = equipStatus.data[2] || 0;
        const totalEquipForPct = equipAvailable + equipInUse + equipMaintenance;
        const inUsePct = totalEquipForPct > 0 ? Math.round((equipInUse / totalEquipForPct) * 100) : 0;
        const equipPctEl = document.getElementById('equipStatusPercentage');
        if (equipPctEl) equipPctEl.textContent = `${inUsePct}%`;

        renderChart('adminEquipmentStatusChart', 'doughnut', equipStatus.labels, equipStatus.data, {
          label: 'Equipment',
          cutout: '64%',
          palette: ['#10b981', '#0ea5e9', '#f59e0b'],
        });
        renderChart('adminReservationsBarChart', 'bar', resBar.labels, resBar.data, {
          label: 'Reservations',
          legend: false,
          palette: ['#2563eb'],
          backgroundColor: '#2563eb',
          borderColor: '#2563eb',
          scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e2e8f0' } },
          },
          tooltipCallbacks: {
            label: ctx => `${ctx.parsed.y} reservations`,
          },
        });
        console.debug('[Reservation Analytics] chart rendered', {
          selectedPeriod: currentReservationPeriod,
          labelsCount: Array.isArray(resBar.labels) ? resBar.labels.length : 0,
          valuesCount: Array.isArray(resBar.data) ? resBar.data.length : 0,
          hasPositiveValues: Array.isArray(resBar.data) ? resBar.data.some(value => Number(value) > 0) : false,
          requestSeq,
        });
        renderChart('adminReservationTrendChart', 'line', resTrend.labels, resTrend.data, {
          label: 'Reservations',
          legend: false,
          palette: ['#0ea5e9'],
          backgroundColor: 'rgba(14, 165, 233, 0.14)',
          borderColor: '#0ea5e9',
          fill: true,
          pointRadius: 4,
          scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e2e8f0' } },
          },
          tooltipCallbacks: {
            label: ctx => `${ctx.parsed.y} reservations`,
          },
        });
      }

      // Helper function to get room status badge class
      function getStatusClass(status) {
        const map = {
          'Available': 'available',
          'Occupied': 'occupied',
          'Under Maintenance': 'maintenance',
          'In Use': 'inuse',
          'Damaged': 'damaged',
          'Under Repair': 'damaged',
          'Repair Needed': 'damaged',
        };
        return map[status] || 'available';
      }

      // Helper function to get room initials for avatar
      function getRoomInitials(roomCode) {
        return (roomCode || '').substring(0, 2).toUpperCase() || 'RM';
      }

      // Helper function to get equipment initials for avatar
      function getEquipInitials(equipCode) {
        return (equipCode || '').substring(0, 2).toUpperCase() || 'EQ';
      }

      async function loadPreviews() {
        // 1. Room Status Today
        const roomsData = await api.get(API.rooms);
        if (roomsData.success) {
          const ul = document.getElementById('dashRoomList');
          if (ul) {
            const list = roomsData.rooms.slice(0, 5);
            ul.innerHTML = list.length ? list.map(r => `
              <li>
                <div class="room-list-avatar">${getRoomInitials(r.room_id)}</div>
                <div class="room-list-info">
                  <div class="room-list-name">${r.room_id}</div>
                  <div class="room-list-type">${r.type || 'Standard'}</div>
                </div>
                <div class="room-list-status">
                  <span class="status-badge ${getStatusClass(r.status)}">${r.status}</span>
                </div>
              </li>`).join('') : '<li style="text-align:center;padding:20px;color:var(--slate-400)">No rooms found.</li>';
          }
        }

        // 2. Upcoming Reservations
        const resData = await api.get(API.reservations, { upcoming: 1 });
        if (resData.success) {
          const ul = document.getElementById('dashResList');
          if (ul) {
            const approved = resData.reservations.filter(r => r.status === 'Approved').slice(0, 5);
            ul.innerHTML = approved.length ? approved.map(r => `
              <li>
                <div class="reservation-info">
                  <div class="reservation-faculty">${r.faculty_name}</div>
                  <div class="reservation-details">${r.room_code} • ${fmtDate(r.res_date)} ${fmtTime(r.time_start)}</div>
                </div>
              </li>`).join('') : '<li style="text-align:center;padding:20px;color:var(--slate-400)">No upcoming reservations.</li>';
          }
        }

        // 3. Equipment Inventory
        const equipData = await api.get(API.equipment);
        if (equipData.success) {
          const ul = document.getElementById('dashEquipList');
          if (ul) {
            const list = equipData.equipment.slice(0, 5);
            ul.innerHTML = list.length ? list.map(e => `
              <li>
                <div class="equipment-info">
                  <div class="equipment-name">${e.name}</div>
                  <div class="equipment-code">${e.equip_code}</div>
                </div>
                <span class="status-badge ${getStatusClass(e.status)}">${e.status}</span>
              </li>`).join('') : '<li style="text-align:center;padding:20px;color:var(--slate-400)">No equipment found.</li>';
          }
        }

        // 4. Recent Activity (Timeline Format)
        const activityData = await api.get(API.activityLog, { limit: 5 });
        if (activityData.success) {
          const ul = document.getElementById('dashActivityList');
          if (ul) {
            const actionIcons = {
              'user_login': '<svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>',
              'user_logout': '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
              'room_update': '<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
              'equipment_update': '<svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>',
              'reservation_create': '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
              'reservation_approve': '<svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
              'reservation_reject': '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            };
            ul.innerHTML = activityData.logs.length ? activityData.logs.map(log => {
              const actionKey = log.action.toLowerCase().replace(/ /g, '_');
              const icon = actionIcons[actionKey] || '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/></svg>';
              return `
                <li>
                  <div class="activity-timeline-icon">${icon}</div>
                  <div class="activity-timeline-content">
                    <div class="activity-timeline-title">${log.action.replace(/_/g, ' ')}</div>
                    <div class="activity-timeline-time">${timeAgo(log.created_at)} ${log.target ? `• ${log.target}` : ''}</div>
                  </div>
                </li>`;
            }).join('') : '<li style="text-align:center;padding:20px;color:var(--slate-400)">No recent activity.</li>';
          }
        }
      }

      window.loadAdminAnalytics = loadAnalytics;

      // Chart Toggle Buttons
      document.querySelectorAll('.chart-toggle-buttons .toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const container = this.closest('.chart-toggle-buttons');
          if (container) {
            container.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
          }
          this.classList.add('active');
          
          const resPeriodBtn = document.querySelector('[data-period].active');
          const trendPeriodBtn = document.querySelector('[data-trend-period].active');
          const p = resPeriodBtn ? resPeriodBtn.dataset.period : 'day';
          const tp = trendPeriodBtn ? trendPeriodBtn.dataset.trendPeriod : 'month';
          
          loadAnalytics(p, tp);
        });
      });

      // Handle "See All" links
      document.querySelectorAll('[data-goto]').forEach(link => {
        link.addEventListener('click', e => {
          e.preventDefault();
          const targetTab = link.dataset.goto;
          const tabBtn = document.querySelector(`.dash-tab[data-tab="${targetTab}"]`);
          if (tabBtn) tabBtn.click();
        });
      });

      loadAnalytics();
      loadPreviews();
      setInterval(() => {
        if (document.getElementById('tab-dashboard').classList.contains('active')) {
          loadAnalytics(currentReservationPeriod, currentTrendPeriod);
          loadPreviews();
        }
      }, 30000);
    })();

    /* VISUAL DASHBOARD (ADMIN) */
    (function initVisualDashboard() {
      if (!document.getElementById('tab-visual-dashboard')) return;

      async function loadVisualDashboard() {
        setChartDefaults();
        const data = await api.get(API.fetchAnalytics, { period: 'month' });
        if (!data.success) return;
        const predictionData = await api.get(API.equipmentPredictions);

        const demand = data.charts?.peak_demand || { labels: [], data: [] };
        const utilization = data.charts?.room_utilization || { labels: [], data: [] };
        const alert = data.alerts?.high_demand || { active: false, label: 'Normal Demand' };
        const alertBadge = document.getElementById('highDemandAlertBadge');

        if (alertBadge) {
          alertBadge.textContent = alert.label || 'Normal Demand';
          alertBadge.title = alert.reason || '';
          alertBadge.classList.toggle('is-alert', !!alert.active);
        }

        renderChart('peakDemandForecastChart', 'line', demand.labels, demand.data, {
          label: 'Reservation Volume',
          legend: false,
          palette: ['#1d4ed8'],
          backgroundColor: 'rgba(37, 99, 235, 0.12)',
          borderColor: '#1d4ed8',
          fill: true,
          pointRadius: 4,
          pointHoverRadius: 6,
          scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#dbeafe' } },
          },
          tooltipCallbacks: {
            label: ctx => `${ctx.parsed.y} reservations`,
          },
        });

        renderChart('roomUtilizationRateChart', 'bar', utilization.labels, utilization.data, {
          label: 'Utilization Rate',
          legend: false,
          indexAxis: 'y',
          palette: ['#1d4ed8'],
          backgroundColor: '#2563eb',
          borderColor: '#1e40af',
          maxBarThickness: 24,
          scales: {
            x: { beginAtZero: true, max: 100, ticks: { callback: value => `${value}%` }, grid: { color: '#dbeafe' } },
            y: { grid: { display: false } },
          },
          tooltipCallbacks: {
            label: ctx => `${ctx.parsed.x}% utilization`,
          },
        });

        const summary = document.getElementById('inventoryAlertSummary');
        const list = document.getElementById('replenishmentForecastList');
        if (predictionData.success) {
          const items = predictionData.replenishment || [];
          if (summary) summary.textContent = predictionData.summary || 'Inventory levels are stable based on current QR scan history.';
          if (list) {
            list.innerHTML = items.length ? items.map(item => `
              <div class="replenishment-item">
                <div>
                  <strong>${item.name}</strong>
                  <span>${item.equip_code} • reorder within ${item.reorder_within_days || 15} days</span>
                </div>
                <span class="health-badge watch">${item.burn_rate_days ? `${item.burn_rate_days}d burn` : 'Monitor'}</span>
              </div>
            `).join('') : '<div class="replenishment-empty">No consumables are forecasted for reorder within 15 days.</div>';
          }
        }
      }

      document.querySelector('.dash-tab[data-tab="visual-dashboard"]')?.addEventListener('click', loadVisualDashboard);
      loadVisualDashboard();
    })();


