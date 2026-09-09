<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\TaskRequest;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for a given task list.
     * SRS-004: Membuat tugas (view daftar tugas dalam list/project)
     */
    public function index(int $taskList)
    {
        $tasks = Task::where('task_list_id', $taskList)
            ->orderByRaw("CASE
                WHEN priority = 'urgent' THEN 1
                WHEN priority = 'high' THEN 2
                WHEN priority = 'medium' THEN 3
                WHEN priority = 'low' THEN 4
                ELSE 5
            END")
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', TaskStatus::Completed)->count(),
            'pending' => $tasks->where('status', TaskStatus::Pending)->count(),
            'overdue' => $tasks->filter(fn (Task $task) => $task->isOverdue())->count(),
        ];

        return view('tasks.index', compact('tasks', 'taskList', 'stats'));
    }

    /**
     * Show the form for creating a new task.
     * SRS-004: Membuat tugas
     */
    public function create(int $taskList)
    {
        $priorities = TaskPriority::cases();

        return view('tasks.create', compact('taskList', 'priorities'));
    }

    /**
     * Store a newly created task in storage.
     * SRS-004: Membuat tugas dan memasukkannya ke dalam list/project
     * SRS-005: Menetapkan prioritas pada suatu tugas
     * SRS-006: Menetapkan deadline/tenggat waktu pada tugas
     */
    public function store(TaskRequest $request, int $taskList)
    {
        Task::create([
            'task_list_id' => $taskList,
            'user_id' => 1, // Sementara hardcoded — diganti auth()->id() saat integrasi
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'priority' => $request->validated('priority'),
            'due_date' => $request->validated('due_date'),
        ]);

        return redirect()
            ->route('task-lists.tasks.index', $taskList)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Display the specified task.
     * SRS-004: Membuat tugas (view detail tugas)
     */
    public function show(int $taskList, Task $task)
    {
        return view('tasks.show', compact('taskList', 'task'));
    }

    /**
     * Show the form for editing the specified task.
     * SRS-004, SRS-005, SRS-006: Edit tugas, prioritas, dan deadline
     */
    public function edit(int $taskList, Task $task)
    {
        $priorities = TaskPriority::cases();

        return view('tasks.edit', compact('taskList', 'task', 'priorities'));
    }

    /**
     * Update the specified task in storage.
     * SRS-004, SRS-005, SRS-006: Update tugas, prioritas, dan deadline
     */
    public function update(TaskRequest $request, int $taskList, Task $task)
    {
        $task->update($request->validated());

        return redirect()
            ->route('task-lists.tasks.index', $taskList)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Remove the specified task from storage.
     * SRS-004: Mengelola tugas (hapus)
     */
    public function destroy(int $taskList, Task $task)
    {
        $task->delete();

        return redirect()
            ->route('task-lists.tasks.index', $taskList)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Toggle the task status between pending and completed.
     * SRS-007: Menandai tugas selesai
     */
    public function toggleStatus(int $taskList, Task $task)
    {
        $task->toggleStatus();

        $message = $task->status === TaskStatus::Completed
            ? 'Tugas ditandai selesai.'
            : 'Tugas dikembalikan ke pending.';

        return redirect()
            ->route('task-lists.tasks.index', $taskList)
            ->with('success', $message);
    }
}
