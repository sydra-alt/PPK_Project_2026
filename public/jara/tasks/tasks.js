/**
 * JARA — Tasks Module JS (SRS-009)
 * Logic: menampilkan dan mengelola tugas bersama dalam sebuah list.
 * Mendukung perbedaan tampilan owner vs kolaborator.
 *
 * Bergantung pada: mockData.js, auth.js (harus di-load lebih dulu)
 */

'use strict';

/* ─── State ─────────────────────────────────────────────────── */
let _currentListId = null;
let _activeFilters = { status: 'all', assignee: 'all', priority: 'all' };

/* ─── Toast (shared helper) ─────────────────────────────────── */
function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.innerHTML = `<span>${icons[type] || '•'}</span> <span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('is-hiding');
    setTimeout(() => toast.remove(), 350);
  }, 3500);
}

/* ─── Utility ───────────────────────────────────────────────── */
function getAvatarText(username) {
  return username ? username.slice(0, 2).toUpperCase() : '??';
}

/**
 * Format tanggal dan tentukan apakah overdue atau mendekati deadline.
 * @returns {{ text: string, cssClass: string }}
 */
function formatDueDate(dueDate) {
  if (!dueDate) return { text: 'Tidak ada', cssClass: '' };

  const due  = new Date(dueDate);
  const now  = new Date();
  const diff = Math.ceil((due - now) / (1000 * 60 * 60 * 24));

  const text = due.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

  if (diff < 0) return { text: `${text} (Terlambat!)`, cssClass: 'due-date--overdue' };
  if (diff <= 3) return { text: `${text} (${diff} hari lagi)`, cssClass: 'due-date--soon' };
  return { text, cssClass: '' };
}

/**
 * Format waktu "last modified" menjadi human-readable relatif.
 */
function formatRelativeTime(isoStr) {
  if (!isoStr) return null;
  const diff = Date.now() - new Date(isoStr).getTime();
  const mins  = Math.floor(diff / 60000);
  const hours = Math.floor(mins / 60);
  const days  = Math.floor(hours / 24);
  if (mins < 1)  return 'baru saja';
  if (mins < 60) return `${mins} menit lalu`;
  if (hours < 24) return `${hours} jam lalu`;
  if (days < 7)  return `${days} hari lalu`;
  return new Date(isoStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

/**
 * Label status untuk display.
 */
const STATUS_LABELS = {
  todo:        'Belum Mulai',
  in_progress: 'Dikerjakan',
  done:        'Selesai',
};

const PRIORITY_LABELS = {
  high:   'Tinggi',
  medium: 'Sedang',
  low:    'Rendah',
};

/* ─── UI: Render List Selector ──────────────────────────────── */
function renderListSelector() {
  const { getCurrentUser } = window.JaraAuth;
  const { getListsByUser }  = window.JaraMockData;

  const currentUser = getCurrentUser();
  const lists       = getListsByUser(currentUser.id);
  const select      = document.getElementById('list-select');
  if (!select) return;

  if (lists.length === 0) {
    select.innerHTML = '<option value="">Tidak ada list yang tersedia</option>';
    return;
  }

  select.innerHTML = lists.map(l =>
    `<option value="${l.id}">${l.name}</option>`
  ).join('');

  _currentListId = lists[0].id;
  select.value = _currentListId;
}

/* ─── UI: Update Header Info ────────────────────────────────── */
/**
 * Perbarui judul halaman, badge role, dan mini progress bar di header.
 */
function updateHeaderInfo() {
  const { getListById, calculateProgress } = window.JaraMockData;
  const { getRoleInList }                  = window.JaraAuth;

  const list   = getListById(_currentListId);
  const role   = getRoleInList(_currentListId);
  const prog   = calculateProgress(_currentListId);

  // List name
  const titleEl = document.getElementById('list-title');
  if (titleEl) titleEl.textContent = list?.name || 'List';

  // Role badge
  const roleEl = document.getElementById('role-badge');
  if (roleEl) {
    roleEl.textContent = role === 'owner' ? '👑 Owner' : '🤝 Kolaborator';
    roleEl.className   = `badge badge--${role === 'owner' ? 'owner' : 'collab'}`;
  }

  // Mini progress bar
  const fillEl = document.getElementById('header-progress-fill');
  const pctEl  = document.getElementById('header-progress-pct');
  if (fillEl) fillEl.style.width = `${prog.percent}%`;
  if (pctEl)  pctEl.textContent  = `${prog.percent}%`;
}

/* ─── UI: Render Stats Bar ──────────────────────────────────── */
function renderStatsBar() {
  const { calculateProgress } = window.JaraMockData;
  const prog = calculateProgress(_currentListId);

  const el = document.getElementById('task-stats-bar');
  if (!el) return;

  el.innerHTML = `
    <div class="stat-chip">
      <div class="stat-chip__value">${prog.total}</div>
      <div class="stat-chip__label">Total Task</div>
    </div>
    <div class="stat-chip stat-chip--done">
      <div class="stat-chip__value">${prog.done}</div>
      <div class="stat-chip__label">Selesai</div>
    </div>
    <div class="stat-chip stat-chip--progress">
      <div class="stat-chip__value">${prog.inProgress}</div>
      <div class="stat-chip__label">Dikerjakan</div>
    </div>
    <div class="stat-chip stat-chip--todo">
      <div class="stat-chip__value">${prog.todo}</div>
      <div class="stat-chip__label">Belum Mulai</div>
    </div>
  `;
}

/* ─── UI: Render Filter Bar ─────────────────────────────────── */
/**
 * Isi dropdown filter assignee berdasarkan task yang ada di list.
 */
function renderFilterBar() {
  const { getCollaborators, getListById, getUserById } = window.JaraMockData;

  const list = getListById(_currentListId);
  if (!list) return;

  // Bangun daftar semua user yang terlibat (owner + kolaborator)
  const involvedIds = [list.ownerId, ...(list.collaborators || [])];
  const involvedUsers = involvedIds.map(id => getUserById(id)).filter(Boolean);

  const assigneeSelect = document.getElementById('filter-assignee');
  if (assigneeSelect) {
    assigneeSelect.innerHTML = `
      <option value="all">Semua Anggota</option>
      ${involvedUsers.map(u => `<option value="${u.id}">${u.username}</option>`).join('')}
    `;
  }
}

/* ─── UI: Render Task List ──────────────────────────────────── */
/**
 * Render semua task card sesuai filter aktif dan role user.
 */
function renderTaskList() {
  const { getTasksByList, getUserById } = window.JaraMockData;
  const { getRoleInList }              = window.JaraAuth;

  const container = document.getElementById('task-list');
  if (!container || !_currentListId) return;

  const role     = getRoleInList(_currentListId);
  const canEdit  = role === 'owner' || role === 'collaborator';
  let   tasks    = getTasksByList(_currentListId);

  // Terapkan filter
  if (_activeFilters.status !== 'all') {
    tasks = tasks.filter(t => t.status === _activeFilters.status);
  }
  if (_activeFilters.priority !== 'all') {
    tasks = tasks.filter(t => t.priority === _activeFilters.priority);
  }
  if (_activeFilters.assignee !== 'all') {
    tasks = tasks.filter(t => t.assigneeId === _activeFilters.assignee);
  }

  if (tasks.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state__icon">📭</div>
        <div class="empty-state__title">Tidak ada task</div>
        <div class="empty-state__desc">
          ${_activeFilters.status !== 'all' || _activeFilters.priority !== 'all' || _activeFilters.assignee !== 'all'
            ? 'Tidak ada task yang cocok dengan filter yang dipilih.'
            : 'Belum ada task di list ini. Task akan muncul setelah ditambahkan oleh pemilik list.'}
        </div>
      </div>
    `;
    return;
  }

  container.innerHTML = tasks.map(task => renderTaskCard(task, canEdit)).join('');
}

/**
 * Render satu task card menjadi HTML string.
 * @param {Object} task
 * @param {boolean} canEdit - apakah user boleh mengubah status
 * @returns {string} HTML string
 */
function renderTaskCard(task, canEdit) {
  const { getUserById } = window.JaraMockData;

  const assignee   = task.assigneeId ? getUserById(task.assigneeId) : null;
  const modifiedBy = task.lastModifiedBy ? getUserById(task.lastModifiedBy) : null;
  const dueInfo    = formatDueDate(task.dueDate);
  const modTime    = formatRelativeTime(task.lastModifiedAt);

  const assigneeHtml = assignee
    ? `<div class="assignee-chip">
         <div class="avatar avatar--sm">${getAvatarText(assignee.username)}</div>
         ${assignee.username}
       </div>`
    : '<span class="text-muted">Tidak ada</span>';

  const modifiedHtml = modifiedBy && modTime
    ? `<div class="task-card__modified">
         ✏️ Terakhir diubah oleh <strong>${modifiedBy.username}</strong> · ${modTime}
       </div>`
    : '';

  return `
    <div class="task-card" data-task-id="${task.id}" data-status="${task.status}">
      <div class="task-card__priority-stripe task-card__priority-stripe--${task.priority}"></div>

      <div class="task-card__main">
        <div class="task-card__header">
          <div class="task-card__title">${task.title}</div>
          <span class="badge badge--${task.priority}">${PRIORITY_LABELS[task.priority] || task.priority}</span>
        </div>
        <div class="task-card__desc">${task.description || 'Tidak ada deskripsi.'}</div>
        <div class="task-card__meta">
          <div class="task-card__meta-item">
            📅 <span class="${dueInfo.cssClass}">${dueInfo.text}</span>
          </div>
          <div class="task-card__meta-item">
            👤 ${assigneeHtml}
          </div>
          <div class="task-card__meta-item">
            <span class="badge badge--${task.status}">${STATUS_LABELS[task.status] || task.status}</span>
          </div>
        </div>
        ${modifiedHtml}
      </div>

      <div class="task-card__actions">
        <select
          class="task-status-select"
          data-task-id="${task.id}"
          onchange="handleStatusChange('${task.id}', this.value)"
          ${canEdit ? '' : 'disabled title="Kamu tidak punya akses ke list ini"'}
          aria-label="Ubah status task"
        >
          <option value="todo"        ${task.status === 'todo'        ? 'selected' : ''}>⬜ Belum Mulai</option>
          <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>🔵 Dikerjakan</option>
          <option value="done"        ${task.status === 'done'        ? 'selected' : ''}>✅ Selesai</option>
        </select>
      </div>
    </div>
  `;
}

/* ─── Logic: Handle Status Change ──────────────────────────── */
/**
 * Dipanggil saat dropdown status di task card berubah.
 * Update data dan re-render card yang terpengaruh.
 */
function handleStatusChange(taskId, newStatus) {
  const currentUser = window.JaraAuth.getCurrentUser();
  const result = window.JaraMockData.updateTaskStatus(taskId, newStatus, currentUser.id);

  if (result.success) {
    showToast(`Status task berhasil diubah ke "${STATUS_LABELS[newStatus]}"`, 'success');
    // Re-render semua (untuk update stats, modified info, dll)
    renderTaskList();
    renderStatsBar();
    updateHeaderInfo();
  } else {
    showToast(result.error, 'error');
  }
}

/* ─── Logic: Apply Filters ──────────────────────────────────── */
function applyFilters() {
  _activeFilters.status   = document.getElementById('filter-status')?.value  || 'all';
  _activeFilters.priority = document.getElementById('filter-priority')?.value || 'all';
  _activeFilters.assignee = document.getElementById('filter-assignee')?.value || 'all';
  renderTaskList();
}

/* ─── Logic: Handle List Change ─────────────────────────────── */
function handleListChange(listId) {
  _currentListId  = listId;
  _activeFilters  = { status: 'all', assignee: 'all', priority: 'all' };

  // Reset filter dropdowns
  ['filter-status', 'filter-priority', 'filter-assignee'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = 'all';
  });

  updateHeaderInfo();
  renderStatsBar();
  renderFilterBar();
  renderTaskList();
}

/* ─── UI: User Switcher ─────────────────────────────────────── */
function renderUserSwitcher() {
  const { getAllUsers }    = window.JaraMockData;
  const { getCurrentUser } = window.JaraAuth;

  const currentUser = getCurrentUser();
  const users       = getAllUsers();
  const switcher    = document.getElementById('user-switcher');
  if (!switcher) return;

  switcher.innerHTML = `
    <span class="user-switcher__label">🧑 Login sebagai:</span>
    <select onchange="switchUser(this.value)">
      ${users.map(u => `
        <option value="${u.id}" ${u.id === currentUser?.id ? 'selected' : ''}>
          ${u.username} (${u.role})
        </option>
      `).join('')}
    </select>
  `;
}

function switchUser(userId) {
  window.JaraAuth.setCurrentUser(userId);
  window.location.reload();
}

/* ─── Init ──────────────────────────────────────────────────── */
function initTasksPage() {
  window.JaraMockData.initMockData();
  window.JaraAuth.ensureCurrentUser();

  const currentUser = window.JaraAuth.getCurrentUser();
  if (!currentUser) return;

  // Update badge current user di header
  const userBadge = document.getElementById('current-user-badge');
  if (userBadge) {
    userBadge.textContent = `${currentUser.username} (${currentUser.role})`;
    userBadge.className   = `badge badge--${currentUser.role}`;
  }

  renderListSelector();
  renderUserSwitcher();

  // List selector change
  const listSelect = document.getElementById('list-select');
  if (listSelect) {
    listSelect.addEventListener('change', (e) => handleListChange(e.target.value));
    if (listSelect.value) handleListChange(listSelect.value);
  }

  // Filter changes
  ['filter-status', 'filter-priority', 'filter-assignee'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyFilters);
  });
}

document.addEventListener('DOMContentLoaded', initTasksPage);
