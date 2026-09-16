<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'user@jara.com')->first() ?? User::first();
        $list = TaskList::first();

        if (! $list) {
            return;
        }

        // Check if tasks already seeded
        if (Task::where('task_list_id', $list->id)->count() > 0) {
            return;
        }

        $tasks = [
            [
                'task_list_id' => $list->id,
                'user_id' => $user->id,
                'title' => 'Design database schema (SRS-001 s/d SRS-011)',
                'description' => 'Merancang ERD dan schema database untuk aplikasi JARA termasuk tabel users, task_lists, tasks, dan relasi kolaborasi task_list_user.',
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
                'status' => 'pending',
            ],
            [
                'task_list_id' => $list->id,
                'user_id' => $user->id,
                'title' => 'Setup Laravel project & Environment',
                'description' => 'Initialize Laravel project, setup database connection, dan konfigurasi environment.',
                'priority' => 'urgent',
                'due_date' => now()->subDays(1)->toDateString(), // Overdue!
                'status' => 'pending',
            ],
            [
                'task_list_id' => $list->id,
                'user_id' => $user->id,
                'title' => 'Create wireframe mockups & Blade views',
                'description' => 'Buat wireframe untuk halaman utama, task list, monitoring progress, dan form tugas.',
                'priority' => 'medium',
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'completed',
                'completed_at' => now()->subHours(2),
            ],
            [
                'task_list_id' => $list->id,
                'user_id' => $user->id,
                'title' => 'Write API and SRS documentation',
                'description' => 'Dokumentasi lengkap acceptance criteria untuk seluruh modul aplikasi.',
                'priority' => 'low',
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'pending',
            ],
            [
                'task_list_id' => $list->id,
                'user_id' => $user->id,
                'title' => 'Implement user authentication & Policy checks',
                'description' => 'Setup Laravel Breeze untuk login, register, profile management, dan TaskListPolicy.',
                'priority' => 'high',
                'due_date' => now()->addDays(5)->toDateString(),
                'status' => 'pending',
            ],
            [
                'task_list_id' => $list->id,
                'user_id' => $user->id,
                'title' => 'Implement collaboration & members invitation (SRS-008)',
                'description' => 'Memungkinkan pemilik list menambahkan kolaborator ke dalam list/proyek.',
                'priority' => 'urgent',
                'due_date' => now()->addDays(2)->toDateString(),
                'status' => 'completed',
                'completed_at' => now()->subDays(1),
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
