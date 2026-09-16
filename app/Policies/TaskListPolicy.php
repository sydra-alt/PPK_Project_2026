<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

/**
 * TaskListPolicy — otorisasi terpusat untuk aksi pada TaskList.
 *
 * Dipakai oleh SRS-001 s/d SRS-011.
 * Semua pengecekan owner/member/admin dilakukan di sini, bukan di controller,
 * agar logic tidak terduplikasi.
 *
 * Refactored based on Autheticator branch.
 */
class TaskListPolicy
{
    /**
     * Izinkan semua user login untuk membuat list baru.
     * Owner ditetapkan saat store(), bukan di sini.
     * (Dari branch Autheticator)
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the list.
     * Owner, member, or admin can view.
     */
    public function view(User $user, TaskList $taskList): bool
    {
        return $taskList->canAccess($user);
    }

    /**
     * Determine whether the user can update the list details.
     * Only owner or admin.
     */
    public function update(User $user, TaskList $taskList): bool
    {
        return $taskList->isOwner($user) || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the list.
     * Only owner or admin.
     */
    public function delete(User $user, TaskList $taskList): bool
    {
        return $taskList->isOwner($user) || $user->isAdmin();
    }

    /**
     * Determine whether the user can manage collaborators/members (invite/remove).
     * Only owner or admin.
     * SRS-008: Kolaborasi dalam list/project
     */
    public function manageMembers(User $user, TaskList $taskList): bool
    {
        return $taskList->isOwner($user) || $user->isAdmin();
    }

    /**
     * Determine whether the user can view/create/edit tasks within the list.
     * Owner, members, or admin can manage tasks.
     * SRS-009: Mengelola tugas bersama
     */
    public function manageTasks(User $user, TaskList $taskList): bool
    {
        return $taskList->canAccess($user);
    }

    /**
     * Determine whether the user can view progress monitoring.
     * SRS-010: Memantau progres list/project
     */
    public function viewProgress(User $user, TaskList $taskList): bool
    {
        return $taskList->canAccess($user);
    }
}
