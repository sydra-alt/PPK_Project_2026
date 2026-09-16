<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for a given task list.
     * SRS-004: Menampilkan daftar tugas dalam list/project
     * SRS-009: Mengelola tugas bersama oleh pemilik dan anggota
     */
    public function index(int $taskList)
    {
        $taskListModel = TaskList::with(['owner', 'members'])->findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        $tasks = Task::where('task_list_id', $taskList)
            ->with('user')
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

        $stats = $taskListModel->progressStats();

        return view('tasks.index', compact('tasks', 'taskList', 'taskListModel', 'stats'));
    }

    /**
     * Show the form for creating a new task.
     * SRS-004: Membuat tugas
     */
    public function create(int $taskList)
    {
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        $priorities = TaskPriority::cases();

        return view('tasks.create', compact('taskList', 'taskListModel', 'priorities'));
    }

    /**
     * Store a newly created task in storage.
     * SRS-004: Membuat tugas dan memasukkannya ke dalam list/project
     * SRS-005: Menetapkan prioritas pada suatu tugas
     * SRS-006: Menetapkan deadline/tenggat waktu pada tugas
     * SRS-009: User yang login otomatis menjadi pembuat tugas
     */
    public function store(TaskRequest $request, int $taskList)
    {
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        Task::create([
            'task_list_id' => $taskList,
            'user_id' => $request->user()->id,
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
     * SRS-004: Melihat detail tugas
     */
    public function show(int $taskList, Task $task)
    {
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        return view('tasks.show', compact('taskList', 'taskListModel', 'task'));
    }

    /**
     * Show the form for editing the specified task.
     * SRS-004, SRS-005, SRS-006: Edit tugas, prioritas, dan deadline
     */
    public function edit(int $taskList, Task $task)
    {
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        $priorities = TaskPriority::cases();

        return view('tasks.edit', compact('taskList', 'taskListModel', 'task', 'priorities'));
    }

    /**
     * Update the specified task in storage.
     * SRS-004, SRS-005, SRS-006: Update tugas, prioritas, dan deadline
     */
    public function update(TaskRequest $request, int $taskList, Task $task)
    {
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        $task->update($request->validated());

        return redirect()
            ->route('task-lists.tasks.index', $taskList)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Remove the specified task from storage.
     * SRS-004: Hapus tugas
     */
    public function destroy(int $taskList, Task $task)
    {
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

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
        $taskListModel = TaskList::findOrFail($taskList);
        Gate::authorize('manageTasks', $taskListModel);

        $task->toggleStatus();

        $message = $task->status === TaskStatus::Completed
            ? 'Tugas ditandai selesai.'
            : 'Tugas dikembalikan ke pending.';

        return redirect()
            ->route('task-lists.tasks.index', $taskList)
            ->with('success', $message);
    }
}
