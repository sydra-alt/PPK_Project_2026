<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Task Management Routes (Programmer 2)
|--------------------------------------------------------------------------
| SRS-004: Membuat tugas
| SRS-005: Mengatur prioritas tugas
| SRS-006: Mengatur tenggat waktu tugas
| SRS-007: Menandai tugas selesai
|
| Catatan: Auth middleware belum diterapkan — akan ditambahkan saat
| integrasi dengan Programmer 1 (SRS-001).
*/
Route::resource('task-lists.tasks', TaskController::class);
Route::patch('task-lists/{task_list}/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])
     ->name('task-lists.tasks.toggle');
