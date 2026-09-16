<?php

namespace Database\Seeders;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User (SRS-011)
        $admin = User::firstOrCreate(
            ['email' => 'admin@jara.com'],
            [
                'name' => 'Admin JARA',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->update(['role' => 'admin']);

        // 2. Regular Test User (SRS-001)
        $user = User::firstOrCreate(
            ['email' => 'user@jara.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // 3. Collaborator User (SRS-008, SRS-009)
        $collaborator = User::firstOrCreate(
            ['email' => 'collab@jara.com'],
            [
                'name' => 'Siti Rahma',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // 4. Sample Task List (SRS-002)
        $taskList = TaskList::withTrashed()->where('name', 'Pengembangan Aplikasi JARA 2026')->first();
        if (! $taskList) {
            $taskList = TaskList::create([
                'name' => 'Pengembangan Aplikasi JARA 2026',
                'description' => 'Project pengembangan fitur todo list canggih berbasis Laravel untuk PPK.',
                'owner_id' => $user->id,
            ]);
        } elseif ($taskList->trashed()) {
            $taskList->restore();
        }

        // 5. Attach collaborator to list (SRS-008)
        if (! $taskList->hasMember($collaborator)) {
            $taskList->members()->syncWithoutDetaching([$collaborator->id => ['role' => 'member']]);
        }

        // 6. Call TaskSeeder (SRS-004 to SRS-007)
        $this->call(TaskSeeder::class);
    }
}
