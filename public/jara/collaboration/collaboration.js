/**
 * JARA — Collaboration Module JS (SRS-008)
 * Logic: menambah / menghapus kolaborator dari sebuah list/project
 *
 * Bergantung pada: mockData.js, auth.js (harus di-load lebih dulu)
 * UI rendering dipisah dari data layer (fungsi render* hanya urus DOM)
 */

'use strict';

/* ─── State ────────────────────────────────────────────────── */
let _currentListId = null;
let _searchTimeout = null;
let _selectedSearchUser = null;

/* ─── Toast Helper ──────────────────────────────────────────── */
/**
 * Tampilkan notifikasi toast di pojok kanan bawah.
 * @param {string} message
 * @param {'success'|'error'|'info'|'warning'} type
 */
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

/* ─── Modal Helper ──────────────────────────────────────────── */
function openModal(id) {
  document.getElementById(id)?.classList.add('is-active');
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('is-active');
}

/* ─── Utility ───────────────────────────────────────────────── */
/**
 * Ambil 1-2 huruf awal untuk avatar dari username.
 */
function getAvatarText(username) {
  return username ? username.slice(0, 2).toUpperCase() : '??';
}

/**
 * Format tanggal ISO ke string lokal.
 */
function formatDate(isoStr) {
  if (!isoStr) return '-';
  return new Date(isoStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

/* ─── UI: Render List Selector ──────────────────────────────── */
/**
 * Isi dropdown pemilih list berdasarkan list yang dimiliki/diikuti user.
 */
function renderListSelector() {
  const { getCurrentUser } = window.JaraAuth;
  const { getListsByUser, getAllLists } = window.JaraMockData;

  const currentUser = getCurrentUser();
  const select = document.getElementById('list-select');
  if (!select) return;

  // Untuk halaman ini, hanya tampilkan list di mana user adalah OWNER
  const lists = getAllLists().filter(l => l.ownerId === currentUser.id);

  if (lists.length === 0) {
    select.innerHTML = '<option value="">Tidak ada list yang kamu miliki</option>';
    return;
  }

  select.innerHTML = lists.map(l =>
    `<option value="${l.id}">${l.name}</option>`
  ).join('');

  // Pilih list pertama secara default
  _currentListId = lists[0].id;
  select.value = _currentListId;
}

/* ─── UI: Render Collaborator List ─────────────────────────── */
/**
 * Render daftar kolaborator aktif pada list yang dipilih.
 * Fungsi ini hanya berinteraksi dengan DOM.
 */
function renderCollaboratorList() {
  const { getCollaborators, getListById } = window.JaraMockData;
  const { getCurrentUser, isOwner } = window.JaraAuth;

  const container = document.getElementById('collab-list');
  const countEl   = document.getElementById('collab-count');
  if (!container || !_currentListId) return;

  const collaborators = getCollaborators(_currentListId);
  const currentUser   = getCurrentUser();
  const userIsOwner   = isOwner(_currentListId);

  // Update count badge
  if (countEl) countEl.textContent = collaborators.length;

  if (collaborators.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state__icon">👥</div>
        <div class="empty-state__title">Belum ada kolaborator</div>
        <div class="empty-state__desc">
          Tambahkan rekan tim untuk mulai berkolaborasi dalam list ini.
        </div>
      </div>
    `;
    return;
  }

  container.innerHTML = collaborators.map(user => `
    <div class="collab-item" data-user-id="${user.id}">
      <div class="avatar">${getAvatarText(user.username)}</div>
      <div class="collab-item__info">
        <div class="collab-item__username">${user.username}</div>
        <div class="collab-item__email">${user.email}</div>
      </div>
      <span class="badge badge--collab">Kolaborator</span>
      ${userIsOwner ? `
        <button
          class="btn btn--danger btn--sm"
          onclick="confirmRemoveCollaborator('${user.id}', '${user.username}')"
          title="Hapus kolaborator"
        >
          Hapus
        </button>
      ` : ''}
    </div>
  `).join('');
}

/* ─── UI: Render Access Denied ──────────────────────────────── */
/**
 * Tampilkan pesan "bukan owner" dan sembunyikan form tambah kolaborator.
 */
function renderAccessDenied() {
  const addPanel = document.getElementById('add-collab-panel');
  if (addPanel) {
    addPanel.innerHTML = `
      <div class="card access-denied">
        <div class="access-denied__icon">🔒</div>
        <div class="access-denied__title">Akses Terbatas</div>
        <div class="access-denied__desc">
          Hanya pemilik list yang dapat mengelola kolaborator.
        </div>
      </div>
    `;
  }
}

/* ─── Logic: Handle List Change ─────────────────────────────── */
/**
 * Dipanggil saat dropdown list berubah.
 */
function handleListChange(listId) {
  _currentListId = listId;
  _selectedSearchUser = null;

  // Update info banner
  const list = window.JaraMockData.getListById(listId);
  if (list) {
    const nameEl = document.getElementById('banner-list-name');
    const descEl = document.getElementById('banner-list-desc');
    if (nameEl) nameEl.textContent = list.name;
    if (descEl) descEl.textContent = list.description || 'Tidak ada deskripsi';
  }

  const isOwnerOfList = window.JaraAuth.isOwner(listId);

  // Render kolaborator
  renderCollaboratorList();

  // Tampilkan/sembunyikan form berdasarkan role
  if (!isOwnerOfList) {
    renderAccessDenied();
  } else {
    const addPanel = document.getElementById('add-collab-panel');
    if (addPanel && addPanel.dataset.original) {
      addPanel.innerHTML = addPanel.dataset.original;
      bindSearchInput();
    }
  }

  // Reset form
  const form = document.getElementById('add-collab-form');
  if (form) form.reset();
  clearSearchDropdown();
}

/* ─── Logic: Search Users ───────────────────────────────────── */
/**
 * Live search user saat mengetik di input.
 * Menampilkan dropdown suggestion dan memfilter user yang sudah menjadi kolaborator.
 */
function handleSearchInput(e) {
  const query = e.target.value.trim();
  clearTimeout(_searchTimeout);

  if (query.length < 1) {
    clearSearchDropdown();
    _selectedSearchUser = null;
    return;
  }

  _searchTimeout = setTimeout(() => {
    const { getAllUsers, getCollaborators, getListById } = window.JaraMockData;
    const currentUser = window.JaraAuth.getCurrentUser();
    const list        = getListById(_currentListId);
    const collabIds   = (list?.collaborators || []);

    const results = getAllUsers().filter(u => {
      const q = query.toLowerCase();
      return (
        u.id !== currentUser.id &&                           // bukan diri sendiri
        u.id !== list?.ownerId &&                            // bukan owner
        (u.username.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
      );
    });

    renderSearchDropdown(results, collabIds);
  }, 200);
}

/**
 * Render dropdown hasil pencarian.
 */
function renderSearchDropdown(users, existingCollabIds) {
  const dropdown = document.getElementById('search-dropdown');
  if (!dropdown) return;

  if (users.length === 0) {
    dropdown.innerHTML = '<div class="search-no-result">Pengguna tidak ditemukan</div>';
    dropdown.classList.add('is-open');
    return;
  }

  dropdown.innerHTML = users.map(user => {
    const isAdded = existingCollabIds.includes(user.id);
    return `
      <div
        class="search-result-item ${isAdded ? 'is-already-added' : ''}"
        data-user-id="${user.id}"
        onclick="selectUserFromDropdown('${user.id}')"
      >
        <div class="avatar avatar--sm">${getAvatarText(user.username)}</div>
        <div>
          <div class="search-result-item__name">${user.username} ${isAdded ? '(Sudah ditambahkan)' : ''}</div>
          <div class="search-result-item__email">${user.email}</div>
        </div>
      </div>
    `;
  }).join('');

  dropdown.classList.add('is-open');
}

/**
 * Saat user memilih suggestion dari dropdown.
 */
function selectUserFromDropdown(userId) {
  const user = window.JaraMockData.getUserById(userId);
  if (!user) return;

  _selectedSearchUser = user;

  const input = document.getElementById('collab-search-input');
  if (input) input.value = user.email;

  clearSearchDropdown();
}

/**
 * Tutup dan kosongkan dropdown.
 */
function clearSearchDropdown() {
  const dropdown = document.getElementById('search-dropdown');
  if (dropdown) {
    dropdown.classList.remove('is-open');
    dropdown.innerHTML = '';
  }
}

/* ─── Logic: Add Collaborator ───────────────────────────────── */
/**
 * Handle submit form tambah kolaborator.
 * Melakukan validasi dan memanggil data layer.
 */
function handleAddCollaborator(e) {
  e.preventDefault();

  const input       = document.getElementById('collab-search-input');
  const identifier  = input?.value.trim();
  const currentUser = window.JaraAuth.getCurrentUser();

  if (!identifier) {
    showToast('Masukkan username atau email pengguna.', 'warning');
    return;
  }

  if (!_currentListId) {
    showToast('Pilih list terlebih dahulu.', 'warning');
    return;
  }

  const result = window.JaraMockData.addCollaborator(_currentListId, identifier, currentUser.id);

  if (result.success) {
    showToast(`✅ ${result.user.username} berhasil ditambahkan sebagai kolaborator!`, 'success');
    renderCollaboratorList();
    if (input) input.value = '';
    _selectedSearchUser = null;
  } else {
    showToast(result.error, 'error');
  }
}

/* ─── Logic: Remove Collaborator ───────────────────────────── */
/**
 * Tampilkan modal konfirmasi sebelum menghapus kolaborator.
 */
function confirmRemoveCollaborator(targetUserId, username) {
  // Simpan target ke dataset modal
  const modal = document.getElementById('remove-confirm-modal');
  if (modal) {
    modal.dataset.targetUserId = targetUserId;
    document.getElementById('remove-confirm-name').textContent = username;
  }
  openModal('remove-confirm-modal');
}

/**
 * Eksekusi penghapusan kolaborator setelah konfirmasi.
 */
function handleConfirmRemove() {
  const modal       = document.getElementById('remove-confirm-modal');
  const targetId    = modal?.dataset.targetUserId;
  const currentUser = window.JaraAuth.getCurrentUser();

  if (!targetId || !_currentListId) return;

  const result = window.JaraMockData.removeCollaborator(_currentListId, targetId, currentUser.id);

  if (result.success) {
    showToast('Kolaborator berhasil dihapus dari list.', 'success');
    renderCollaboratorList();
  } else {
    showToast(result.error, 'error');
  }

  closeModal('remove-confirm-modal');
}

/* ─── Bind Events ───────────────────────────────────────────── */
/**
 * Pasang event listener ke input search.
 */
function bindSearchInput() {
  const input = document.getElementById('collab-search-input');
  if (input) {
    input.removeEventListener('input', handleSearchInput);
    input.addEventListener('input', handleSearchInput);
  }

  // Tutup dropdown saat klik di luar
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-wrapper')) {
      clearSearchDropdown();
    }
  });
}

/* ─── User Switcher (Dev Tool) ──────────────────────────────── */
/**
 * Render widget ganti user di pojok kiri bawah (hanya untuk demo/development).
 */
function renderUserSwitcher() {
  const { getAllUsers } = window.JaraMockData;
  const { getCurrentUser, setCurrentUser } = window.JaraAuth;

  const currentUser = getCurrentUser();
  const users = getAllUsers();

  const switcher = document.getElementById('user-switcher');
  if (!switcher) return;

  switcher.innerHTML = `
    <span class="user-switcher__label">🧑 Login sebagai:</span>
    <select id="user-switch-select" onchange="switchUser(this.value)">
      ${users.map(u => `
        <option value="${u.id}" ${u.id === currentUser?.id ? 'selected' : ''}>
          ${u.username} (${u.role})
        </option>
      `).join('')}
    </select>
  `;
}

/**
 * Ganti current user dan refresh halaman.
 */
function switchUser(userId) {
  window.JaraAuth.setCurrentUser(userId);
  window.location.reload();
}

/* ─── Init ──────────────────────────────────────────────────── */
/**
 * Inisialisasi halaman kolaborasi.
 * Dipanggil saat DOMContentLoaded.
 */
function initCollaborationPage() {
  // Inisialisasi data mock
  window.JaraMockData.initMockData();

  // Pastikan ada current user
  window.JaraAuth.ensureCurrentUser();

  const currentUser = window.JaraAuth.getCurrentUser();
  if (!currentUser) {
    console.error('[JARA] Tidak ada current user.');
    return;
  }

  // Simpan konten asli panel add untuk restore saat ganti list
  const addPanel = document.getElementById('add-collab-panel');
  if (addPanel) addPanel.dataset.original = addPanel.innerHTML;

  // Render list selector dan user switcher
  renderListSelector();
  renderUserSwitcher();

  // Bind list selector change
  const listSelect = document.getElementById('list-select');
  if (listSelect) {
    listSelect.addEventListener('change', (e) => handleListChange(e.target.value));
    // Trigger untuk list pertama
    if (listSelect.value) handleListChange(listSelect.value);
  }

  // Bind form submit
  const form = document.getElementById('add-collab-form');
  if (form) form.addEventListener('submit', handleAddCollaborator);

  // Bind search input
  bindSearchInput();

  // Bind modal buttons
  document.getElementById('btn-confirm-remove')?.addEventListener('click', handleConfirmRemove);
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.closeModal));
  });
}

document.addEventListener('DOMContentLoaded', initCollaborationPage);
