/**
 * JARA — Mock Auth Helper (Programmer 3)
 *
 * Menyimulasikan sistem autentikasi menggunakan localStorage.
 * Saat integrasi dengan backend, ganti fungsi-fungsi ini dengan
 * pemanggilan API auth yang sesungguhnya.
 *
 * Bergantung pada: mockData.js (harus di-load lebih dulu)
 */

const AUTH_KEY = 'jara_current_user';

// ─── Login Path ────────────────────────────────────────────────────────────────
// Path relatif ke halaman login dari folder manapun di dalam /jara/
// Disesuaikan saat integrasi backend.
const LOGIN_PATH = '../login/index.html';

// ─── Current User ─────────────────────────────────────────────────────────────

/**
 * Ambil data user yang sedang login.
 * @returns {Object|null} User object atau null jika belum login
 */
function getCurrentUser() {
  const userId = localStorage.getItem(AUTH_KEY);
  if (!userId) return null;
  return window.JaraMockData ? window.JaraMockData.getUserById(userId) : null;
}

/**
 * Set current user (simulasi login / user switcher).
 * @param {string} userId
 */
function setCurrentUser(userId) {
  localStorage.setItem(AUTH_KEY, userId);
}

/**
 * Logout: hapus sesi dan redirect ke halaman login.
 */
function logout() {
  localStorage.removeItem(AUTH_KEY);
  window.location.href = LOGIN_PATH;
}

/**
 * Alias untuk backward-compatibility.
 */
function logoutCurrentUser() {
  logout();
}

/**
 * Guard: jika belum login, redirect ke login page dengan returnTo parameter.
 * Panggil di awal setiap halaman yang membutuhkan autentikasi.
 * @returns {boolean} true jika sudah login, false (dan redirect) jika belum
 */
function requireAuth() {
  // Pastikan mock data ada
  if (window.JaraMockData) window.JaraMockData.initMockData();

  const userId = localStorage.getItem(AUTH_KEY);
  const user   = userId && window.JaraMockData ? window.JaraMockData.getUserById(userId) : null;

  if (!user) {
    const returnTo = encodeURIComponent(window.location.href);
    window.location.href = `${LOGIN_PATH}?returnTo=${returnTo}`;
    return false;
  }
  return true;
}

/**
 * Pastikan ada current user (fallback tanpa redirect).
 * Hanya digunakan di halaman yang tidak butuh strict auth.
 */
function ensureCurrentUser() {
  const existing = localStorage.getItem(AUTH_KEY);
  if (existing && window.JaraMockData?.getUserById(existing)) return;

  const users = window.JaraMockData?.getAllUsers() || [];
  const defaultUser = users.find(u => u.role === 'user') || users[0];
  if (defaultUser) {
    localStorage.setItem(AUTH_KEY, defaultUser.id);
  }
}

// ─── Role Checks ─────────────────────────────────────────────────────────────

/** Cek apakah current user adalah admin. */
function isAdmin() {
  const user = getCurrentUser();
  return user?.role === 'admin';
}

/** Cek apakah current user adalah owner dari sebuah list. */
function isOwner(listId) {
  const user = getCurrentUser();
  const list = window.JaraMockData?.getListById(listId);
  return user && list ? list.ownerId === user.id : false;
}

/** Cek apakah current user adalah kolaborator di sebuah list. */
function isCollaboratorOfList(listId) {
  const user = getCurrentUser();
  if (!user) return false;
  return window.JaraMockData?.isCollaborator(listId, user.id) || false;
}

/** Cek apakah current user punya akses ke sebuah list. */
function hasAccessToList(listId) {
  return isOwner(listId) || isCollaboratorOfList(listId);
}

/** Ambil role user di sebuah list: 'owner', 'collaborator', atau null. */
function getRoleInList(listId) {
  if (isOwner(listId)) return 'owner';
  if (isCollaboratorOfList(listId)) return 'collaborator';
  return null;
}

// ─── Export ke global window ──────────────────────────────────────────────────
window.JaraAuth = {
  getCurrentUser,
  setCurrentUser,
  logout,
  logoutCurrentUser,
  requireAuth,
  ensureCurrentUser,
  isAdmin,
  isOwner,
  isCollaboratorOfList,
  hasAccessToList,
  getRoleInList,
};
