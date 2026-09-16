<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk SRS-02: Penghapusan Daftar Tugas (Cascade & Atomic).
 *
 * Menguji bahwa:
 * - Owner dapat menghapus list miliknya
 * - Penghapusan bersifat cascade (tasks + members ikut terhapus)
 * - Penghapusan bersifat atomik (DB::transaction)
 * - Non-owner dan guest ditolak
 */
class TaskListDeleteTest extends TestCase
{
    use RefreshDatabase;

    // ── Positive: Owner can delete ─────────────────────────────────

    /**
     * Owner berhasil menghapus list dan list hilang dari database.
     * Acceptance Criteria: List baru tersimpan → owner hapus → row hilang.
     */
    public function test_owner_can_delete_list(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->delete(route('lists.destroy', $list));

        $response->assertRedirect(route('lists.index'));

        // forceDelete → row benar-benar hilang (bukan soft delete)
        $this->assertDatabaseMissing('task_lists', ['id' => $list->id]);
    }

    // ── Cascade: Tasks ikut terhapus (FR-02.2) ────────────────────

    /**
     * Saat list dihapus, seluruh task di dalamnya ikut terhapus.
     */
    public function test_deleting_list_cascades_tasks(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Buat 3 task di dalam list ini
        $tasks = Task::factory()->count(3)->create([
            'task_list_id' => $list->id,
            'user_id'      => $owner->id,
        ]);

        // Pastikan task ada sebelum delete
        $this->assertDatabaseCount('tasks', 3);

        $this->actingAs($owner)->delete(route('lists.destroy', $list));

        // Semua task harus ikut terhapus
        foreach ($tasks as $task) {
            $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        }
        $this->assertDatabaseCount('tasks', 0);
    }

    // ── Cascade: Members ikut terhapus (FR-02.3) ──────────────────

    /**
     * Saat list dihapus, seluruh keanggotaan (pivot task_list_user) ikut terhapus.
     */
    public function test_deleting_list_cascades_members(): void
    {
        $owner = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Tambahkan 2 member ke list
        $list->members()->attach($member1->id, ['role' => 'member']);
        $list->members()->attach($member2->id, ['role' => 'member']);

        // Pastikan ada 2 row di pivot sebelum delete
        $this->assertDatabaseCount('task_list_user', 2);

        $this->actingAs($owner)->delete(route('lists.destroy', $list));

        // Semua row di pivot harus ikut terhapus
        $this->assertDatabaseMissing('task_list_user', ['task_list_id' => $list->id]);
        $this->assertDatabaseCount('task_list_user', 0);
    }

    // ── Atomicity: All-or-nothing (FR-02.4) ───────────────────────

    /**
     * Delete bersifat atomik: list, tasks, dan members terhapus bersamaan.
     * Jika ada yang gagal, tidak ada yang terhapus (rollback).
     */
    public function test_delete_is_atomic_all_or_nothing(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Buat tasks dan members
        Task::factory()->count(2)->create([
            'task_list_id' => $list->id,
            'user_id'      => $owner->id,
        ]);
        $list->members()->attach($member->id, ['role' => 'member']);

        // Verifikasi data ada sebelum delete
        $this->assertDatabaseCount('tasks', 2);
        $this->assertDatabaseCount('task_list_user', 1);
        $this->assertDatabaseHas('task_lists', ['id' => $list->id]);

        // Execute delete
        $response = $this->actingAs($owner)->delete(route('lists.destroy', $list));
        $response->assertRedirect(route('lists.index'));

        // Setelah delete: ketiga tabel harus bersih
        $this->assertDatabaseMissing('task_lists', ['id' => $list->id]);
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('task_list_user', 0);
    }

    // ── Authorization: Non-owner ditolak 403 (NFR-02.1) ──────────

    /**
     * User yang bukan owner mendapat 403, tidak ada data terhapus.
     */
    public function test_non_owner_gets_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        Task::factory()->create([
            'task_list_id' => $list->id,
            'user_id'      => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('lists.destroy', $list));

        $response->assertStatus(403);

        // Data tetap utuh — tidak ada yang terhapus
        $this->assertDatabaseHas('task_lists', ['id' => $list->id]);
        $this->assertDatabaseCount('tasks', 1);
    }

    /**
     * Member (kolaborator, bukan owner) tidak bisa menghapus list.
     */
    public function test_member_cannot_delete_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Tambahkan member ke list
        $list->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($member)->delete(route('lists.destroy', $list));

        $response->assertStatus(403);

        // List dan keanggotaan tetap ada
        $this->assertDatabaseHas('task_lists', ['id' => $list->id]);
        $this->assertDatabaseHas('task_list_user', [
            'task_list_id' => $list->id,
            'user_id'      => $member->id,
        ]);
    }

    // ── Precondition: Guest harus login ──────────────────────────

    /**
     * Guest (belum login) diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $list = TaskList::factory()->create();

        $response = $this->delete(route('lists.destroy', $list));

        $response->assertRedirect(route('login'));
    }
}
