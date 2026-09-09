/**
 * JARA — Admin Module JS (SRS-011)
 * Logic: Manajemen akun pengguna (tambah, hapus, cari).
 * Hanya bisa diakses oleh role 'admin'.
 *
 * Bergantung pada: mockData.js, auth.js (harus di-load lebih dulu)
 */

'use strict';

/* ─── State ─────────────────────────────────────────────────── */
let _searchQuery = '';

/* ─── Toast Helper ──────────────────────────────────────────── */
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

/* ─── Modal Helpers ─────────────────────────────────────────── */
function openModal(id)  { document.getElementById(id)?.classList.add('is-active'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('is-active'); }

/* ─── Utility ───────────────────────────────────────────────── */
function getAvatarText(username) {
  return username ? username.slice(0, 2).toUpperCase() : '??';
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  });
}

/* ─── Guard: Cek Akses Admin ────────────────────────────────── */
/**
 * Tampilkan halaman "Access Denied" dan sembunyikan konten admin
 * jika current user bukan admin.
 * @returns {boolean} true jika admin, false jika bukan
 */
function checkAdminAccess() {
  const { isAdmin, getCurrentUser } = window.JaraAuth;

  if (isAdmin()) return true;

  // Sembunyikan konten admin
  const content = document.getElementById('admin-content');
  const denied  = document.getElementById('access-denied-page');
  const userSwitcher = document.getElementById('user-switcher');

  if (content) content.classList.add('hidden');
  if (denied)  denied.classList.remove('hidden');

  // Isi nama user yang mencoba akses
  const currentUser = getCurrentUser();
  const nameEl = document.getElementById('denied-username');
  if (nameEl && currentUser) nameEl.textContent = currentUser.username;

  return false;
}

/* ─── UI: Render Stats Bar ──────────────────────────────────── */
/**
 * Render kartu statistik user (total, admin, user biasa).
 */
function renderStatsBar() {
  const users = window.JaraMockData.getAllUsers();
  const totalUsers  = users.length;
  const totalAdmins = users.filter(u => u.role === 'admin').length;
  const totalRegular= users.filter(u => u.role === 'user').length;

  const statsEl = document.getElementById('admin-stats-bar');
  if (!statsEl) return;

  statsEl.innerHTML = `
    <div class="admin-stat-card">
      <div class="admin-stat-card__icon admin-stat-card__icon--total">👥</div>
      <div>
        <div class="admin-stat-card__value">${totalUsers}</div>
        <div class="admin-stat-card__label">Total Pengguna</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="admin-stat-card__icon admin-stat-card__icon--admin">⚙️</div>
      <div>
        <div class="admin-stat-card__value" style="color: var(--color-warning);">${totalAdmins}</div>
        <div class="admin-stat-card__label">Admin</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="admin-stat-card__icon admin-stat-card__icon--user">🙋</div>
      <div>
        <div class="admin-stat-card__value" style="color: var(--color-secondary);">${totalRegular}</div>
        <div class="admin-stat-card__label">Pengguna Biasa</div>
      </div>
    </div>
  `;
}

/* ─── UI: Render User Table ─────────────────────────────────── */
/**
 * Render tabel daftar user, dengan filter berdasarkan _searchQuery.
 */
function renderUserTable() {
  const { getAllUsers }    = window.JaraMockData;
  const { getCurrentUser } = window.JaraAuth;

  const currentUser = getCurrentUser();
  let users = getAllUsers();

  // Terapkan search filter
  if (_searchQuery) {
    const q = _searchQuery.toLowerCase();
    users = users.filter(u =>
      u.username.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q) ||
      u.role.toLowerCase().includes(q)
    );
  }

  const tbody = document.getElementById('user-table-body');
  if (!tbody) return;

  if (users.length === 0) {
    tbody.innerHTML = `
      <tr class="table-empty-row">
        <td colspan="5">
          ${_searchQuery
            ? `🔍 Tidak ada pengguna yang cocok dengan "<strong>${_searchQuery}</strong>"`
            : '📭 Belum ada pengguna di sistem.'}
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = users.map((user, idx) => {
    const isSelf    = user.id === currentUser?.id;
    const isLastAdmin = user.role === 'admin' &&
                        getAllUsers().filter(u => u.role === 'admin').length <= 1;

    const canDelete = !isSelf && !isLastAdmin;
    const deleteReason = isSelf
      ? 'Tidak bisa menghapus akun sendiri'
      : isLastAdmin
        ? 'Tidak bisa menghapus admin terakhir'
        : '';

    return `
      <tr class="user-table-row ${isSelf ? 'user-table-row--current' : ''}" data-user-id="${user.id}">
        <td>${idx + 1}</td>
        <td>
          <div class="user-info-cell">
            <div class="avatar avatar--sm">${getAvatarText(user.username)}</div>
            <div class="user-info-cell__details">
              <div class="user-info-cell__name">
                ${user.username}
                ${isSelf ? '<span class="badge badge--collab" style="margin-left: 4px;">Kamu</span>' : ''}
              </div>
            </div>
          </div>
        </td>
        <td style="color: var(--color-text-muted); font-size: var(--font-size-sm);">${user.email}</td>
        <td>
          <span class="badge badge--${user.role}">
            ${user.role === 'admin' ? '⚙️ Admin' : '🙋 User'}
          </span>
        </td>
        <td style="color: var(--color-text-muted); font-size: var(--font-size-sm);">
          ${formatDate(user.createdAt)}
        </td>
        <td>
          <button
            class="btn btn--danger btn--sm"
            ${canDelete ? `onclick="confirmDeleteUser('${user.id}', '${user.username}')"` : 'disabled'}
            title="${deleteReason || 'Hapus pengguna ini'}"
          >
            🗑️ Hapus
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

/* ─── Logic: Handle Search ──────────────────────────────────── */
/**
 * Filter tabel real-time saat user mengetik di search bar.
 */
function handleSearch(e) {
  _searchQuery = e.target.value.trim();
  renderUserTable();
}

/* ─── Logic: Add User ───────────────────────────────────────── */
/**
 * Handle submit form tambah user baru.
 */
function handleAddUser(e) {
  e.preventDefault();

  const form = e.target;
  const username = form.querySelector('#new-username')?.value.trim();
  const email    = form.querySelector('#new-email')?.value.trim();
  const role     = form.querySelector('#new-role')?.value;

  if (!username || !email) {
    showToast('Username dan email wajib diisi.', 'warning');
    return;
  }

  // Validasi format email sederhana
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showToast('Format email tidak valid.', 'error');
    return;
  }

  const result = window.JaraMockData.addUser({ username, email, role });

  if (result.success) {
    showToast(`✅ Pengguna "${result.user.username}" berhasil ditambahkan!`, 'success');
    form.reset();
    renderUserTable();
    renderStatsBar();
  } else {
    showToast(result.error, 'error');
  }
}

/* ─── Logic: Confirm Delete User ────────────────────────────── */
/**
 * Tampilkan modal konfirmasi sebelum menghapus user.
 */
function confirmDeleteUser(userId, username) {
  const modal = document.getElementById('delete-confirm-modal');
  if (modal) {
    modal.dataset.targetUserId = userId;
    const nameEl = document.getElementById('delete-confirm-name');
    if (nameEl) nameEl.textContent = username;
  }
  openModal('delete-confirm-modal');
}

/**
 * Eksekusi penghapusan setelah konfirmasi.
 */
function handleConfirmDelete() {
  const modal  = document.getElementById('delete-confirm-modal');
  const userId = modal?.dataset.targetUserId;

  if (!userId) return;

  const currentUser = window.JaraAuth.getCurrentUser();

  // Cegah hapus diri sendiri (double-check)
  if (userId === currentUser?.id) {
    showToast('Tidak bisa menghapus akun sendiri.', 'error');
    closeModal('delete-confirm-modal');
    return;
  }

  const result = window.JaraMockData.deleteUser(userId);

  if (result.success) {
    showToast('Pengguna berhasil dihapus dari sistem.', 'success');
    renderUserTable();
    renderStatsBar();
  } else {
    showToast(result.error, 'error');
  }

  closeModal('delete-confirm-modal');
}

/* ─── UI: User Switcher (Dev Tool) ─────────────────────────── */
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

/* ─── Init ──────────────────────────────────────────────────── */
function initAdminPage() {
  window.JaraMockData.initMockData();
  window.JaraAuth.ensureCurrentUser();

  const currentUser = window.JaraAuth.getCurrentUser();
  if (!currentUser) return;

  // Update header badge
  const userBadge = document.getElementById('current-user-badge');
  if (userBadge) {
    userBadge.textContent = `${currentUser.username} (${currentUser.role})`;
    userBadge.className   = `badge badge--${currentUser.role}`;
  }

  // Render user switcher dulu (selalu tampil)
  renderUserSwitcher();

  // Cek akses — stop di sini jika bukan admin
  if (!checkAdminAccess()) return;

  // Admin content
  renderStatsBar();
  renderUserTable();

  // Bind search
  document.getElementById('admin-search')?.addEventListener('input', handleSearch);

  // Bind add user form
  document.getElementById('add-user-form')?.addEventListener('submit', handleAddUser);

  // Bind modal confirm
  document.getElementById('btn-confirm-delete')?.addEventListener('click', handleConfirmDelete);

  // Bind modal close buttons
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.closeModal));
  });

  // Bind reset data button
  document.getElementById('btn-reset-data')?.addEventListener('click', () => {
    if (confirm('⚠️ Reset semua data ke kondisi awal? Semua perubahan akan hilang!')) {
      window.JaraMockData.resetMockData();
      window.JaraAuth.ensureCurrentUser();
      renderStatsBar();
      renderUserTable();
      renderUserSwitcher();
      showToast('Data berhasil direset ke kondisi awal.', 'info');
    }
  });
}

document.addEventListener('DOMContentLoaded', initAdminPage);
