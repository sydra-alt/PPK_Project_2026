/**
 * JARA — Mock Data Layer (SRS-008 to SRS-011)
 * Programmer 3: Collaboration, Progress & Admin
 *
 * This file is the single source of truth for all data in the mock environment.
 * All functions in this file operate on localStorage, separated from UI logic.
 * When integrating with the real backend, replace these functions with API calls.
 *
 * Data Structures:
 *   User:   { id, username, email, role: 'user'|'admin', createdAt }
 *   List:   { id, name, description, ownerId, collaborators: [userId], createdAt }
 *   Task:   { id, listId, title, description, status: 'todo'|'in_progress'|'done',
 *             priority: 'low'|'medium'|'high', dueDate, assigneeId,
 *             lastModifiedBy, lastModifiedAt }
 */

// ─── Storage Keys ────────────────────────────────────────────────────────────
const KEYS = {
  USERS:  'jara_users',
  LISTS:  'jara_lists',
  TASKS:  'jara_tasks',
  SEEDED: 'jara_seeded',
};

// ─── Seed Data ────────────────────────────────────────────────────────────────
const SEED_USERS = [
  {
    id: 'u1',
    username: 'admin_jara',
    email: 'admin@jara.app',
    role: 'admin',
    createdAt: '2026-01-10T08:00:00Z',
  },
  {
    id: 'u2',
    username: 'budi_santoso',
    email: 'budi@email.com',
    role: 'user',
    createdAt: '2026-01-12T09:00:00Z',
  },
  {
    id: 'u3',
    username: 'sari_dewi',
    email: 'sari@email.com',
    role: 'user',
    createdAt: '2026-01-15T10:00:00Z',
  },
  {
    id: 'u4',
    username: 'rian_putra',
    email: 'rian@email.com',
    role: 'user',
    createdAt: '2026-02-01T11:00:00Z',
  },
];

const SEED_LISTS = [
  {
    id: 'l1',
    name: 'Pengembangan Website JARA',
    description: 'Project utama pembuatan aplikasi JARA todo list tim kami',
    ownerId: 'u2',
    collaborators: ['u3', 'u4'],
    createdAt: '2026-02-05T08:00:00Z',
  },
  {
    id: 'l2',
    name: 'Riset UX & Desain',
    description: 'Kumpulan tugas riset user experience dan pembuatan wireframe',
    ownerId: 'u3',
    collaborators: ['u2'],
    createdAt: '2026-02-10T09:00:00Z',
  },
];

const SEED_TASKS = [
  {
    id: 't1',
    listId: 'l1',
    title: 'Setup repository dan struktur folder',
    description: 'Inisialisasi repo GitHub, buat branching strategy, setup folder modular',
    status: 'done',
    priority: 'high',
    dueDate: '2026-02-10',
    assigneeId: 'u2',
    lastModifiedBy: 'u2',
    lastModifiedAt: '2026-02-09T14:30:00Z',
  },
  {
    id: 't2',
    listId: 'l1',
    title: 'Buat desain database schema',
    description: 'Rancang tabel users, lists, tasks, dan relasi antar tabel',
    status: 'done',
    priority: 'high',
    dueDate: '2026-02-12',
    assigneeId: 'u3',
    lastModifiedBy: 'u3',
    lastModifiedAt: '2026-02-11T16:00:00Z',
  },
  {
    id: 't3',
    listId: 'l1',
    title: 'Implementasi modul authentication',
    description: 'Login, register, logout, session management dengan Laravel Sanctum',
    status: 'in_progress',
    priority: 'high',
    dueDate: '2026-09-15',
    assigneeId: 'u2',
    lastModifiedBy: 'u2',
    lastModifiedAt: '2026-09-08T10:00:00Z',
  },
  {
    id: 't4',
    listId: 'l1',
    title: 'Buat modul kolaborasi (SRS-008)',
    description: 'UI dan logic untuk menambahkan kolaborator ke dalam list/project',
    status: 'in_progress',
    priority: 'medium',
    dueDate: '2026-09-20',
    assigneeId: 'u4',
    lastModifiedBy: 'u4',
    lastModifiedAt: '2026-09-09T09:00:00Z',
  },
  {
    id: 't5',
    listId: 'l1',
    title: 'Testing dan QA modul task management',
    description: 'Unit test dan integrasi test untuk semua CRUD task',
    status: 'todo',
    priority: 'medium',
    dueDate: '2026-09-30',
    assigneeId: 'u3',
    lastModifiedBy: null,
    lastModifiedAt: null,
  },
  {
    id: 't6',
    listId: 'l2',
    title: 'Wawancara pengguna awal (user interview)',
    description: 'Wawancara 5 calon pengguna untuk kebutuhan fitur aplikasi',
    status: 'done',
    priority: 'high',
    dueDate: '2026-02-20',
    assigneeId: 'u3',
    lastModifiedBy: 'u3',
    lastModifiedAt: '2026-02-19T15:00:00Z',
  },
  {
    id: 't7',
    listId: 'l2',
    title: 'Buat wireframe halaman utama',
    description: 'Wireframe low-fidelity untuk dashboard, list view, dan task detail',
    status: 'in_progress',
    priority: 'medium',
    dueDate: '2026-09-18',
    assigneeId: 'u3',
    lastModifiedBy: 'u2',
    lastModifiedAt: '2026-09-07T11:30:00Z',
  },
  {
    id: 't8',
    listId: 'l2',
    title: 'Buat prototype interaktif Figma',
    description: 'Prototype high-fidelity berdasarkan wireframe yang sudah disetujui',
    status: 'todo',
    priority: 'low',
    dueDate: '2026-10-05',
    assigneeId: 'u2',
    lastModifiedBy: null,
    lastModifiedAt: null,
  },
];

// ─── Initialization ──────────────────────────────────────────────────────────

/**
 * Seed localStorage dengan data awal jika belum pernah di-seed.
 * Panggil ini di awal setiap halaman.
 */
function initMockData() {
  if (localStorage.getItem(KEYS.SEEDED) === 'true') return;
  localStorage.setItem(KEYS.USERS, JSON.stringify(SEED_USERS));
  localStorage.setItem(KEYS.LISTS, JSON.stringify(SEED_LISTS));
  localStorage.setItem(KEYS.TASKS, JSON.stringify(SEED_TASKS));
  localStorage.setItem(KEYS.SEEDED, 'true');
  console.log('[JARA MockData] Seed data berhasil diinisialisasi.');
}

/**
 * Reset semua data ke kondisi seed awal.
 * Berguna untuk testing / demo ulang.
 */
function resetMockData() {
  Object.values(KEYS).forEach(k => localStorage.removeItem(k));
  initMockData();
  console.log('[JARA MockData] Data direset ke kondisi awal.');
}

// ─── Generic Helpers ─────────────────────────────────────────────────────────
function _get(key) {
  try {
    return JSON.parse(localStorage.getItem(key)) || [];
  } catch {
    return [];
  }
}

function _set(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

function _generateId(prefix = 'id') {
  return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
}

// ─── USER FUNCTIONS ──────────────────────────────────────────────────────────

/** Ambil semua user di sistem */
function getAllUsers() {
  return _get(KEYS.USERS);
}

/** Ambil user berdasarkan ID */
function getUserById(userId) {
  return getAllUsers().find(u => u.id === userId) || null;
}

/** Ambil user berdasarkan username atau email */
function findUserByIdentifier(identifier) {
  const lower = identifier.trim().toLowerCase();
  return getAllUsers().find(
    u => u.username.toLowerCase() === lower || u.email.toLowerCase() === lower
  ) || null;
}

/**
 * Tambah user baru ke sistem.
 * @param {Object} userData - { username, email, role }
 * @returns {{ success: boolean, user?: Object, error?: string }}
 */
function addUser({ username, email, role = 'user' }) {
  const users = getAllUsers();

  if (!username || !email) {
    return { success: false, error: 'Username dan email wajib diisi.' };
  }

  const duplicate = users.find(
    u => u.username.toLowerCase() === username.trim().toLowerCase() ||
         u.email.toLowerCase() === email.trim().toLowerCase()
  );
  if (duplicate) {
    return { success: false, error: 'Username atau email sudah terdaftar.' };
  }

  const newUser = {
    id: _generateId('u'),
    username: username.trim(),
    email: email.trim().toLowerCase(),
    role: role === 'admin' ? 'admin' : 'user',
    createdAt: new Date().toISOString(),
  };
  _set(KEYS.USERS, [...users, newUser]);
  return { success: true, user: newUser };
}

/**
 * Hapus user dari sistem.
 * @returns {{ success: boolean, error?: string }}
 */
function deleteUser(userId) {
  const users = getAllUsers();
  const target = users.find(u => u.id === userId);

  if (!target) {
    return { success: false, error: 'User tidak ditemukan.' };
  }

  // Jangan hapus admin terakhir
  const admins = users.filter(u => u.role === 'admin');
  if (target.role === 'admin' && admins.length <= 1) {
    return { success: false, error: 'Tidak bisa menghapus admin terakhir di sistem.' };
  }

  _set(KEYS.USERS, users.filter(u => u.id !== userId));
  return { success: true };
}

// ─── LIST FUNCTIONS ───────────────────────────────────────────────────────────

/** Ambil semua list/project */
function getAllLists() {
  return _get(KEYS.LISTS);
}

/** Ambil list berdasarkan ID */
function getListById(listId) {
  return getAllLists().find(l => l.id === listId) || null;
}

/**
 * Ambil semua list di mana user terlibat (sebagai owner atau kolaborator).
 */
function getListsByUser(userId) {
  return getAllLists().filter(
    l => l.ownerId === userId || (l.collaborators || []).includes(userId)
  );
}

// ─── COLLABORATOR FUNCTIONS ──────────────────────────────────────────────────

/**
 * Ambil daftar objek user yang menjadi kolaborator di sebuah list.
 */
function getCollaborators(listId) {
  const list = getListById(listId);
  if (!list) return [];
  return (list.collaborators || []).map(uid => getUserById(uid)).filter(Boolean);
}

/**
 * Cek apakah user adalah kolaborator di sebuah list.
 */
function isCollaborator(listId, userId) {
  const list = getListById(listId);
  return list ? (list.collaborators || []).includes(userId) : false;
}

/**
 * Tambahkan kolaborator ke list.
 * @param {string} listId
 * @param {string} identifier - username atau email user yang akan ditambah
 * @param {string} currentUserId - user yang melakukan aksi (harus owner)
 * @returns {{ success: boolean, user?: Object, error?: string }}
 */
function addCollaborator(listId, identifier, currentUserId) {
  const lists = getAllLists();
  const list = lists.find(l => l.id === listId);

  if (!list) return { success: false, error: 'List tidak ditemukan.' };
  if (list.ownerId !== currentUserId) {
    return { success: false, error: 'Hanya pemilik list yang bisa menambahkan kolaborator.' };
  }

  const targetUser = findUserByIdentifier(identifier);
  if (!targetUser) {
    return { success: false, error: 'Pengguna tidak ditemukan di sistem.' };
  }
  if (targetUser.id === currentUserId) {
    return { success: false, error: 'Kamu adalah pemilik list ini, tidak perlu ditambahkan sebagai kolaborator.' };
  }
  if ((list.collaborators || []).includes(targetUser.id)) {
    return { success: false, error: `${targetUser.username} sudah menjadi kolaborator di list ini.` };
  }

  list.collaborators = [...(list.collaborators || []), targetUser.id];
  _set(KEYS.LISTS, lists);
  return { success: true, user: targetUser };
}

/**
 * Hapus kolaborator dari list.
 * @param {string} listId
 * @param {string} targetUserId - user yang akan dihapus
 * @param {string} currentUserId - user yang melakukan aksi (harus owner)
 * @returns {{ success: boolean, error?: string }}
 */
function removeCollaborator(listId, targetUserId, currentUserId) {
  const lists = getAllLists();
  const list = lists.find(l => l.id === listId);

  if (!list) return { success: false, error: 'List tidak ditemukan.' };
  if (list.ownerId !== currentUserId) {
    return { success: false, error: 'Hanya pemilik list yang bisa menghapus kolaborator.' };
  }
  if (!(list.collaborators || []).includes(targetUserId)) {
    return { success: false, error: 'User ini bukan kolaborator di list ini.' };
  }

  list.collaborators = (list.collaborators || []).filter(uid => uid !== targetUserId);
  _set(KEYS.LISTS, lists);
  return { success: true };
}

// ─── TASK FUNCTIONS ───────────────────────────────────────────────────────────

/** Ambil semua task dalam sebuah list */
function getTasksByList(listId) {
  return _get(KEYS.TASKS).filter(t => t.listId === listId);
}

/** Ambil task berdasarkan ID */
function getTaskById(taskId) {
  return _get(KEYS.TASKS).find(t => t.id === taskId) || null;
}

/**
 * Update status sebuah task dan catat siapa yang mengubahnya.
 * @param {string} taskId
 * @param {string} newStatus - 'todo'|'in_progress'|'done'
 * @param {string} modifiedBy - userId yang melakukan perubahan
 * @returns {{ success: boolean, task?: Object, error?: string }}
 */
function updateTaskStatus(taskId, newStatus, modifiedBy) {
  const validStatuses = ['todo', 'in_progress', 'done'];
  if (!validStatuses.includes(newStatus)) {
    return { success: false, error: 'Status tidak valid.' };
  }

  const tasks = _get(KEYS.TASKS);
  const idx = tasks.findIndex(t => t.id === taskId);
  if (idx === -1) return { success: false, error: 'Task tidak ditemukan.' };

  tasks[idx].status = newStatus;
  tasks[idx].lastModifiedBy = modifiedBy;
  tasks[idx].lastModifiedAt = new Date().toISOString();
  _set(KEYS.TASKS, tasks);
  return { success: true, task: tasks[idx] };
}

// ─── PROGRESS FUNCTIONS ───────────────────────────────────────────────────────

/**
 * Hitung statistik progres untuk sebuah list.
 * @returns {{ total, done, inProgress, todo, percent, byUser, byStatus }}
 */
function calculateProgress(listId) {
  const tasks = getTasksByList(listId);
  const total = tasks.length;

  if (total === 0) {
    return { total: 0, done: 0, inProgress: 0, todo: 0, percent: 0, byUser: [], byStatus: [] };
  }

  const done       = tasks.filter(t => t.status === 'done').length;
  const inProgress = tasks.filter(t => t.status === 'in_progress').length;
  const todo       = tasks.filter(t => t.status === 'todo').length;
  const percent    = Math.round((done / total) * 100);

  // Breakdown per user (assignee)
  const userMap = {};
  tasks.forEach(t => {
    if (!t.assigneeId) return;
    if (!userMap[t.assigneeId]) {
      userMap[t.assigneeId] = { userId: t.assigneeId, total: 0, done: 0 };
    }
    userMap[t.assigneeId].total++;
    if (t.status === 'done') userMap[t.assigneeId].done++;
  });
  const byUser = Object.values(userMap).map(u => ({
    ...u,
    user: getUserById(u.userId),
    percent: u.total > 0 ? Math.round((u.done / u.total) * 100) : 0,
  }));

  const byStatus = [
    { label: 'Selesai', key: 'done',        count: done,       color: '#10b981' },
    { label: 'Dikerjakan', key: 'in_progress', count: inProgress, color: '#7c3aed' },
    { label: 'Belum Mulai', key: 'todo',       count: todo,       color: '#94a3b8' },
  ];

  return { total, done, inProgress, todo, percent, byUser, byStatus };
}

// ─── Export (untuk dipakai file lain via <script type="module"> atau global) ─
// Agar kompatibel dengan script biasa (non-module), kita expose ke window global.
window.JaraMockData = {
  // Init
  initMockData,
  resetMockData,
  // Users
  getAllUsers,
  getUserById,
  findUserByIdentifier,
  addUser,
  deleteUser,
  // Lists
  getAllLists,
  getListById,
  getListsByUser,
  // Collaborators
  getCollaborators,
  isCollaborator,
  addCollaborator,
  removeCollaborator,
  // Tasks
  getTasksByList,
  getTaskById,
  updateTaskStatus,
  // Progress
  calculateProgress,
};
