<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

/**
 * TaskListPolicy — otorisasi terpusat untuk aksi pada TaskList.
 *
 * Dipakai oleh SRS-01 (create) dan SRS-02 (delete).
 * Semua pengecekan owner dilakukan di sini, bukan di controller,
 * agar logic tidak terduplikasi (FR-03.3).
 */
class TaskListPolicy
{
    /**
     * Izinkan semua user login untuk membuat list baru.
     * Owner ditetapkan saat store(), bukan di sini.
     */
    public function create(User $user): bool
    {
        // Setiap user terautentikasi boleh membuat list
        return true;
    }

    /**
     * Hanya owner yang boleh melihat detail list-nya.
     */
    public function view(User $user, TaskList $taskList): bool
    {
        // Bandingkan ID user dengan owner_id di tabel task_lists
        return $user->id === $taskList->owner_id;
    }

    /**
     * Hanya owner yang boleh mengubah list.
     */
    public function update(User $user, TaskList $taskList): bool
    {
        return $user->id === $taskList->owner_id;
    }

    /**
     * Hanya owner yang boleh menghapus list (FR-03.1, FR-03.2).
     * Mengembalikan false → Laravel otomatis throw 403 (NFR-03.1).
     */
    public function delete(User $user, TaskList $taskList): bool
    {
        return $user->id === $taskList->owner_id;
    }
}
