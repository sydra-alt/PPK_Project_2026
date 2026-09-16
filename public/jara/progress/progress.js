/**
 * JARA — Progress Module JS (SRS-010)
 * Logic: menghitung dan menampilkan progres penyelesaian tugas per list.
 *
 * Bergantung pada: mockData.js, auth.js (harus di-load lebih dulu)
 */

'use strict';

/* ─── State ─────────────────────────────────────────────────── */
let _currentListId = null;

/* ─── Constants ─────────────────────────────────────────────── */
// Panjang lingkaran SVG (2 * PI * r = 2 * 3.14159 * 54 ≈ 339.29)
const CIRCLE_CIRCUMFERENCE = 339.29;

/* ─── Utility ───────────────────────────────────────────────── */
function getAvatarText(username) {
  return username ? username.slice(0, 2).toUpperCase() : '??';
}

/* ─── UI: Render List Selector ──────────────────────────────── */
/**
 * Isi dropdown selector list dengan semua list yang diikuti user.
 */
function renderListSelector() {
  const { getCurrentUser }  = window.JaraAuth;
  const { getListsByUser }  = window.JaraMockData;

  const currentUser = getCurrentUser();
  const lists       = getListsByUser(currentUser.id);
  const select      = document.getElementById('list-select');
  if (!select) return;

  if (lists.length === 0) {
    select.innerHTML = '<option value="">Kamu belum bergabung di list manapun</option>';
    return;
  }

  select.innerHTML = lists.map(l =>
    `<option value="${l.id}">${l.name}</option>`
  ).join('');

  _currentListId = lists[0].id;
  select.value = _currentListId;
}

/* ─── UI: Render Circular Progress ─────────────────────────── */
/**
 * Animasikan lingkaran SVG sesuai persentase progress.
 * @param {number} percent - 0 to 100
 */
function renderCircularProgress(percent) {
  const fill  = document.getElementById('circle-progress-fill');
  const pctEl = document.getElementById('circle-pct');

  if (fill) {
    const offset = CIRCLE_CIRCUMFERENCE * (1 - percent / 100);
    // Set initial (tanpa animasi) lalu trigger reflow untuk animasi
    fill.style.transition = 'none';
    fill.style.strokeDashoffset = CIRCLE_CIRCUMFERENCE;

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        fill.style.transition = 'stroke-dashoffset 1s cubic-bezier(0.22, 1, 0.36, 1)';
        fill.style.strokeDashoffset = offset;
      });
    });
  }

  if (pctEl) pctEl.textContent = `${percent}%`;
}

/* ─── UI: Render Progress Bar ───────────────────────────────── */
/**
 * Animasikan progress bar horizontal.
 * @param {number} percent
 */
function renderProgressBar(percent) {
  const fill = document.getElementById('main-progress-fill');
  if (!fill) return;

  fill.style.width = '0%';
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      fill.style.width = `${percent}%`;
    });
  });
}

/* ─── UI: Render Hero Section ───────────────────────────────── */
/**
 * Render hero card dengan progress besar, nama list, dan stats.
 */
function renderHero() {
  const { getListById, calculateProgress } = window.JaraMockData;

  const list = getListById(_currentListId);
  const prog = calculateProgress(_currentListId);

  // Nama & deskripsi list
  const nameEl = document.getElementById('hero-list-name');
  const descEl = document.getElementById('hero-list-desc');
  if (nameEl) nameEl.textContent = list?.name || '—';
  if (descEl) descEl.textContent = list?.description || 'Tidak ada deskripsi';

  // Fraction label
  const fracEl = document.getElementById('progress-fraction');
  if (fracEl) fracEl.textContent = `${prog.done} / ${prog.total} task selesai`;

  // Stats
  const statsEl = document.getElementById('progress-stats');
  if (statsEl) {
    statsEl.innerHTML = `
      <div class="progress-stat progress-stat--done">
        <div class="progress-stat__value">${prog.done}</div>
        <div class="progress-stat__label">✅ Selesai</div>
      </div>
      <div class="progress-stat progress-stat--progress">
        <div class="progress-stat__value">${prog.inProgress}</div>
        <div class="progress-stat__label">🔵 Dikerjakan</div>
      </div>
      <div class="progress-stat progress-stat--todo">
        <div class="progress-stat__value">${prog.todo}</div>
        <div class="progress-stat__label">⬜ Belum Mulai</div>
      </div>
    `;
  }

  // Animasikan visual
  renderCircularProgress(prog.percent);
  renderProgressBar(prog.percent);
}

/* ─── UI: Render Collaborator Breakdown ────────────────────── */
/**
 * Render breakdown progres per kolaborator.
 */
function renderCollaboratorStats() {
  const { calculateProgress, getListById, getUserById } = window.JaraMockData;

  const prog = calculateProgress(_currentListId);
  const list = getListById(_currentListId);
  const container = document.getElementById('collab-breakdown');
  if (!container) return;

  // Gabungkan owner juga
  const ownerUser = getUserById(list?.ownerId);
  let byUser = [...prog.byUser];

  // Tambahkan owner jika belum ada di byUser
  if (ownerUser && !byUser.find(u => u.userId === ownerUser.id)) {
    byUser.unshift({ userId: ownerUser.id, user: ownerUser, total: 0, done: 0, percent: 0 });
  }

  if (byUser.length === 0) {
    container.innerHTML = `
      <div class="empty-state" style="padding: var(--space-8);">
        <div class="empty-state__icon">📊</div>
        <div class="empty-state__title">Tidak ada data</div>
        <div class="empty-state__desc">Belum ada task yang di-assign ke anggota.</div>
      </div>
    `;
    return;
  }

  container.innerHTML = byUser.map(u => {
    const user = u.user || getUserById(u.userId);
    if (!user) return '';
    const isOwner = list?.ownerId === u.userId;

    return `
      <div class="collab-progress-item">
        <div class="avatar">${getAvatarText(user.username)}</div>
        <div class="collab-progress-item__info">
          <div class="collab-progress-item__name">
            ${user.username}
            ${isOwner ? '<span class="badge badge--owner" style="margin-left: 6px;">Owner</span>' : ''}
          </div>
          <div class="collab-progress-item__bar-row">
            <div class="collab-progress-item__bar">
              <div
                class="collab-progress-item__bar-fill"
                style="width: 0%"
                data-target-width="${u.percent}%"
              ></div>
            </div>
            <span class="collab-progress-item__stats">${u.done}/${u.total} (${u.percent}%)</span>
          </div>
        </div>
      </div>
    `;
  }).join('');

  // Animasikan bar fill setelah render
  requestAnimationFrame(() => {
    container.querySelectorAll('[data-target-width]').forEach(el => {
      requestAnimationFrame(() => {
        el.style.width = el.dataset.targetWidth;
      });
    });
  });
}

/* ─── UI: Render Status Breakdown ───────────────────────────── */
/**
 * Render breakdown visual per status task.
 */
function renderStatusBreakdown() {
  const { calculateProgress } = window.JaraMockData;
  const prog    = calculateProgress(_currentListId);
  const container = document.getElementById('status-breakdown');
  if (!container) return;

  if (prog.total === 0) {
    container.innerHTML = `
      <div class="empty-state" style="padding: var(--space-8);">
        <div class="empty-state__icon">📋</div>
        <div class="empty-state__title">Tidak ada task</div>
        <div class="empty-state__desc">Belum ada task di list ini.</div>
      </div>
    `;
    return;
  }

  const statusItems = [
    { label: '✅ Selesai',      key: 'done',        count: prog.done,       color: 'var(--color-success)' },
    { label: '🔵 Dikerjakan',   key: 'in_progress', count: prog.inProgress, color: 'var(--color-primary)' },
    { label: '⬜ Belum Mulai',  key: 'todo',        count: prog.todo,       color: 'var(--color-text-faint)' },
  ];

  container.innerHTML = statusItems.map(item => {
    const widthPct = prog.total > 0 ? Math.round((item.count / prog.total) * 100) : 0;
    return `
      <div class="status-breakdown-item">
        <div class="status-breakdown-item__label">${item.label}</div>
        <div class="status-breakdown-item__bar">
          <div
            class="status-breakdown-item__fill"
            style="width: 0%; background: ${item.color};"
            data-target-width="${widthPct}%"
          ></div>
        </div>
        <div class="status-breakdown-item__count" style="color: ${item.color};">${item.count}</div>
      </div>
    `;
  }).join('');

  // Animasikan
  requestAnimationFrame(() => {
    container.querySelectorAll('[data-target-width]').forEach(el => {
      requestAnimationFrame(() => {
        el.style.width = el.dataset.targetWidth;
      });
    });
  });
}

/* ─── Logic: Handle List Change ─────────────────────────────── */
function handleListChange(listId) {
  _currentListId = listId;
  renderAll();
}

/**
 * Render ulang semua komponen progress.
 */
function renderAll() {
  renderHero();
  renderCollaboratorStats();
  renderStatusBreakdown();
}

/* ─── UI: User Switcher ─────────────────────────────────────── */
function renderUserSwitcher() {
  const { getAllUsers }    = window.JaraMockData;
  const { getCurrentUser } = window.JaraAuth;

  const currentUser = getCurrentUser();
  const users = getAllUsers();
  const switcher = document.getElementById('user-switcher');
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

/* ─── Visibility Refresh ────────────────────────────────────── */
/**
 * Re-render saat tab menjadi aktif kembali (misalnya setelah update task di tab lain).
 */
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible' && _currentListId) {
    renderAll();
  }
});

/* ─── Init ──────────────────────────────────────────────────── */
function initProgressPage() {
  window.JaraMockData.initMockData();
  window.JaraAuth.ensureCurrentUser();

  const currentUser = window.JaraAuth.getCurrentUser();
  if (!currentUser) return;

  // Update badge header
  const userBadge = document.getElementById('current-user-badge');
  if (userBadge) {
    userBadge.textContent = `${currentUser.username} (${currentUser.role})`;
    userBadge.className   = `badge badge--${currentUser.role}`;
  }

  renderListSelector();
  renderUserSwitcher();

  // Bind list selector
  const select = document.getElementById('list-select');
  if (select) {
    select.addEventListener('change', (e) => handleListChange(e.target.value));
    if (select.value) handleListChange(select.value);
  }
}

document.addEventListener('DOMContentLoaded', initProgressPage);
