<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\TaskController;
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
    Route::resource('lists', TaskListController::class)
        ->parameters(['lists' => 'list'])
        ->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Task Management Routes (Programmer 2)
    |--------------------------------------------------------------------------
    | SRS-004: Membuat tugas
    | SRS-005: Mengatur prioritas tugas
    | SRS-006: Mengatur tenggat waktu tugas
    | SRS-007: Menandai tugas selesai
    */
    Route::resource('task-lists.tasks', TaskController::class);
    Route::patch('task-lists/{task_list}/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])
         ->name('task-lists.tasks.toggle');
});

require __DIR__.'/auth.php';
