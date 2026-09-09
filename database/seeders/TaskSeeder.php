<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat dummy user dan sample tasks untuk testing fitur Task Management.
     */
    public function run(): void
    {
        // Buat dummy user jika belum ada
        $user = User::firstOrCreate(
            ['email' => 'test@jara.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Sample tasks dengan variasi prioritas, status, dan deadline
        $tasks = [
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Design database schema',
                'description' => 'Merancang ERD dan schema database untuk aplikasi JARA termasuk tabel users, task_lists, tasks, dan relasi kolaborasi.',
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
                'status' => 'pending',
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Setup Laravel project',
                'description' => 'Initialize Laravel project, setup database connection, dan konfigurasi environment.',
                'priority' => 'urgent',
                'due_date' => now()->subDays(1)->toDateString(), // Overdue!
                'status' => 'pending',
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Create wireframe mockups',
                'description' => 'Buat wireframe untuk halaman utama, task list, dan form tugas.',
                'priority' => 'medium',
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'completed',
                'completed_at' => now()->subHours(2),
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Write API documentation',
                'description' => null,
                'priority' => 'low',
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'pending',
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Implement user authentication',
                'description' => 'Setup Laravel Breeze untuk login, register, dan profile management.',
                'priority' => 'high',
                'due_date' => now()->addDays(5)->toDateString(),
                'status' => 'pending',
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Setup CI/CD pipeline',
                'description' => 'Konfigurasi GitHub Actions untuk automated testing dan deployment.',
                'priority' => 'medium',
                'due_date' => null, // No deadline
                'status' => 'pending',
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Initialize Git repository',
                'description' => 'Buat repository di GitHub dan push initial commit.',
                'priority' => 'urgent',
                'due_date' => now()->subDays(3)->toDateString(),
                'status' => 'completed',
                'completed_at' => now()->subDays(3),
            ],
            [
                'task_list_id' => 1,
                'user_id' => $user->id,
                'title' => 'Code review sprint 1',
                'description' => 'Review semua PR dari sprint 1 dan berikan feedback.',
                'priority' => 'medium',
                'due_date' => now()->addDays(2)->toDateString(),
                'status' => 'pending',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
