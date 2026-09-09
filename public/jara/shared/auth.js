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

// ─── Current User ─────────────────────────────────────────────────────────────

/**
 * Ambil data user yang sedang login.
 * @returns {Object|null} User object atau null jika belum login
 */
function getCurrentUser() {
  const userId = localStorage.getItem(AUTH_KEY);
  if (!userId) return null;
  // Bergantung pada JaraMockData yang sudah di-load
  return window.JaraMockData ? window.JaraMockData.getUserById(userId) : null;
}

/**
 * Set current user (simulasi login).
 * @param {string} userId
 */
function setCurrentUser(userId) {
  localStorage.setItem(AUTH_KEY, userId);
}

/**
 * Hapus current user (simulasi logout).
 */
function logoutCurrentUser() {
  localStorage.removeItem(AUTH_KEY);
}

/**
 * Pastikan ada current user, jika tidak set ke user pertama yang tersedia.
 * Fungsi ini dipanggil di awal tiap halaman sebagai fallback.
 */
function ensureCurrentUser() {
  const existing = localStorage.getItem(AUTH_KEY);
  if (existing && window.JaraMockData?.getUserById(existing)) return;

  // Fallback: set ke user pertama (non-admin) yang tersedia
  const users = window.JaraMockData?.getAllUsers() || [];
  const defaultUser = users.find(u => u.role === 'user') || users[0];
  if (defaultUser) {
    localStorage.setItem(AUTH_KEY, defaultUser.id);
  }
}

// ─── Role Checks ─────────────────────────────────────────────────────────────

/**
 * Cek apakah current user adalah admin.
 * @returns {boolean}
 */
function isAdmin() {
  const user = getCurrentUser();
  return user?.role === 'admin';
}

/**
 * Cek apakah current user adalah owner dari sebuah list.
 * @param {string} listId
 * @returns {boolean}
 */
function isOwner(listId) {
  const user = getCurrentUser();
  const list = window.JaraMockData?.getListById(listId);
  return user && list ? list.ownerId === user.id : false;
}

/**
 * Cek apakah current user adalah kolaborator di sebuah list.
 * @param {string} listId
 * @returns {boolean}
 */
function isCollaboratorOfList(listId) {
  const user = getCurrentUser();
  if (!user) return false;
  return window.JaraMockData?.isCollaborator(listId, user.id) || false;
}

/**
 * Cek apakah current user punya akses ke sebuah list (owner atau kolaborator).
 * @param {string} listId
 * @returns {boolean}
 */
function hasAccessToList(listId) {
  return isOwner(listId) || isCollaboratorOfList(listId);
}

/**
 * Ambil role user di sebuah list: 'owner', 'collaborator', atau null (no access).
 * @param {string} listId
 * @returns {'owner'|'collaborator'|null}
 */
function getRoleInList(listId) {
  if (isOwner(listId)) return 'owner';
  if (isCollaboratorOfList(listId)) return 'collaborator';
  return null;
}

// ─── Export ke global window ──────────────────────────────────────────────────
window.JaraAuth = {
  getCurrentUser,
  setCurrentUser,
  logoutCurrentUser,
  ensureCurrentUser,
  isAdmin,
  isOwner,
  isCollaboratorOfList,
  hasAccessToList,
  getRoleInList,
};
