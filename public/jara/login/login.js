/**
 * JARA — Login & Register JS
 * Mock authentication: username/email + password "123456"
 *
 * Bergantung pada: mockData.js, auth.js
 */

'use strict';

// ─── Password yang berlaku untuk semua akun (mock) ────────────
const MOCK_PASSWORD = '123456';

// ─── Redirect Helper ──────────────────────────────────────────
/**
 * Setelah login berhasil, redirect ke halaman yang diminta
 * atau ke halaman tasks sebagai default.
 */
function redirectAfterLogin() {
  const params    = new URLSearchParams(window.location.search);
  const returnTo  = params.get('returnTo');
  // Whitelist sederhana untuk keamanan
  const safe = ['../collaboration/index.html', '../tasks/index.html',
                 '../progress/index.html', '../admin/index.html'];
  if (returnTo && safe.some(s => returnTo.includes(s.split('/').pop().split('.')[0]))) {
    window.location.href = returnTo;
  } else {
    window.location.href = '../tasks/index.html';
  }
}

// ─── Password Toggle ──────────────────────────────────────────
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.textContent = isText ? '👁️' : '🙈';
}

// ─── Show Error ───────────────────────────────────────────────
function showError(message) {
  const el = document.getElementById('error-message');
  if (!el) return;
  el.querySelector('.error-text').textContent = message;
  el.classList.add('is-visible');
}

function hideError() {
  document.getElementById('error-message')?.classList.remove('is-visible');
}

// ─── Show Toast ───────────────────────────────────────────────
function showSuccessToast(message) {
  const toast = document.getElementById('toast-success');
  if (!toast) return;
  toast.querySelector('.toast-text').textContent = message;
  toast.classList.add('is-visible');
  setTimeout(() => toast.classList.remove('is-visible'), 3000);
}

// ─── Fill Quick Login ─────────────────────────────────────────
/**
 * Klik card quick login → isi form otomatis
 */
function quickLogin(username) {
  const usernameInput = document.getElementById('username');
  const passwordInput = document.getElementById('password');
  if (usernameInput) usernameInput.value = username;
  if (passwordInput) passwordInput.value = MOCK_PASSWORD;
  hideError();
  usernameInput?.focus();
  // Highlight visual
  usernameInput?.classList.add('is-filled');
  setTimeout(() => usernameInput?.classList.remove('is-filled'), 600);
}

// ─── Render Quick Login Cards ─────────────────────────────────
/**
 * Tampilkan kartu quick-login berdasarkan user yang tersedia di mock data.
 */
function renderQuickLoginCards() {
  const container = document.getElementById('quick-login-grid');
  if (!container) return;

  // Pastikan mock data sudah diinisialisasi
  window.JaraMockData.initMockData();
  const users = window.JaraMockData.getAllUsers();

  container.innerHTML = users.map(user => `
    <button
      class="quick-login-card"
      onclick="quickLogin('${user.username}')"
      title="Login sebagai ${user.username}"
      type="button"
    >
      <div class="quick-login-avatar">${user.username.slice(0, 2).toUpperCase()}</div>
      <div class="quick-login-info">
        <div class="quick-login-name">${user.username}</div>
        <div class="quick-login-role">${user.role === 'admin' ? '⚙️ Admin' : '🙋 User'}</div>
      </div>
    </button>
  `).join('');
}

// ─── Handle Login Submit ──────────────────────────────────────
/**
 * Validasi form login dan set current user jika berhasil.
 */
function handleLoginSubmit(e) {
  e.preventDefault();
  hideError();

  const identifier = document.getElementById('username')?.value.trim();
  const password   = document.getElementById('password')?.value;
  const btn        = document.getElementById('btn-submit');

  if (!identifier || !password) {
    showError('Isi username/email dan password terlebih dahulu.');
    return;
  }

  // Loading state
  btn.disabled = true;
  btn.classList.add('is-loading');

  // Simulasi delay network (200ms)
  setTimeout(() => {
    btn.disabled = false;
    btn.classList.remove('is-loading');

    // Cek password
    if (password !== MOCK_PASSWORD) {
      showError('Password salah. Untuk demo, gunakan password: 123456');
      document.getElementById('password').value = '';
      document.getElementById('password').focus();
      return;
    }

    // Cari user berdasarkan username atau email
    const user = window.JaraMockData.findUserByIdentifier(identifier);
    if (!user) {
      showError('Pengguna tidak ditemukan. Periksa kembali username atau email kamu.');
      return;
    }

    // Berhasil login
    window.JaraAuth.setCurrentUser(user.id);
    showSuccessToast(`Selamat datang, ${user.username}! 🎉`);
    setTimeout(redirectAfterLogin, 900);
  }, 220);
}

// ─── Handle Register Submit ────────────────────────────────────
/**
 * Validasi dan daftarkan user baru.
 */
function handleRegisterSubmit(e) {
  e.preventDefault();
  hideError();

  const username  = document.getElementById('reg-username')?.value.trim();
  const email     = document.getElementById('reg-email')?.value.trim();
  const password  = document.getElementById('reg-password')?.value;
  const password2 = document.getElementById('reg-password2')?.value;
  const role      = document.getElementById('reg-role')?.value || 'user';
  const btn       = document.getElementById('btn-submit');

  // Validasi
  if (!username || !email || !password || !password2) {
    showError('Semua field wajib diisi.');
    return;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showError('Format email tidak valid.');
    return;
  }

  if (username.length < 3) {
    showError('Username minimal 3 karakter.');
    return;
  }

  if (password.length < 6) {
    showError('Password minimal 6 karakter.');
    return;
  }

  if (password !== password2) {
    showError('Konfirmasi password tidak cocok.');
    document.getElementById('reg-password2').value = '';
    document.getElementById('reg-password2').focus();
    return;
  }

  // Loading state
  btn.disabled = true;
  btn.classList.add('is-loading');

  setTimeout(() => {
    btn.disabled = false;
    btn.classList.remove('is-loading');

    const result = window.JaraMockData.addUser({ username, email, role });

    if (!result.success) {
      showError(result.error);
      return;
    }

    // Set langsung sebagai current user
    window.JaraAuth.setCurrentUser(result.user.id);
    showSuccessToast(`Akun berhasil dibuat! Selamat datang, ${result.user.username} 🎉`);
    setTimeout(redirectAfterLogin, 1000);
  }, 280);
}

// ─── Init ─────────────────────────────────────────────────────
function initAuthPage() {
  // Inisialisasi data
  window.JaraMockData.initMockData();

  // Jika sudah login, langsung redirect
  const currentUser = window.JaraAuth.getCurrentUser();
  if (currentUser) {
    redirectAfterLogin();
    return;
  }

  renderQuickLoginCards();

  // Bind form login
  document.getElementById('login-form')?.addEventListener('submit', handleLoginSubmit);

  // Bind form register
  document.getElementById('register-form')?.addEventListener('submit', handleRegisterSubmit);

  // Clear error on input
  document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', hideError);
  });
}

document.addEventListener('DOMContentLoaded', initAuthPage);
