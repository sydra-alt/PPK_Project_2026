<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\TaskListMemberController;
use App\Http\Controllers\TaskListProgressController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('lists.index')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('lists.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // ── Task Lists / Projects (SRS-002, SRS-003) ─────────────────
    Route::resource('lists', TaskListController::class)
        ->parameters(['lists' => 'list'])
        ->except(['show']);

    // ── Collaboration & Members (SRS-008, SRS-009) ───────────────
    Route::get('lists/{list}/members', [TaskListMemberController::class, 'index'])
        ->name('lists.members.index');
    Route::post('lists/{list}/members', [TaskListMemberController::class, 'store'])
        ->name('lists.members.store');
    Route::delete('lists/{list}/members/{user}', [TaskListMemberController::class, 'destroy'])
        ->name('lists.members.destroy');

    // ── Progress Monitoring (SRS-010) ───────────────────────────
    Route::get('lists/{list}/progress', [TaskListProgressController::class, 'show'])
        ->name('lists.progress');

    // ── Profile Management (SRS-001) ────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Task Management (SRS-004, SRS-005, SRS-006, SRS-007) ────
    Route::resource('task-lists.tasks', TaskController::class);
    Route::patch('task-lists/{task_list}/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])
         ->name('task-lists.tasks.toggle');

    // ── Admin User Management (SRS-011) ─────────────────────────
    Route::middleware(EnsureUserIsAdmin::class)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });
});

require __DIR__.'/auth.php';
