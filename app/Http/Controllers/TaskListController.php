<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskListController extends Controller
{
    /**
     * Display a listing of the user's task lists.
     */
    public function index(Request $request): View
    {
        $taskLists = $request->user()->taskLists()->latest()->get();

        return view('lists.index', compact('taskLists'));
    }

    /**
     * Show the form for creating a new task list.
     */
    public function create(): View
    {
        // Pastikan user punya hak membuat list (via TaskListPolicy::create)
        $this->authorize('create', TaskList::class);

        return view('lists.create');
    }

    /**
     * Store a newly created task list in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Otorisasi create sebelum menyentuh database (FR-03.2)
        $this->authorize('create', TaskList::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->taskLists()->create($validated);

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified task list.
     */
    public function edit(TaskList $list): View
    {
        // Hanya owner yang boleh mengedit (TaskListPolicy::update)
        $this->authorize('update', $list);

        return view('lists.edit', compact('list'));
    }

    /**
     * Update the specified task list in storage.
     */
    public function update(Request $request, TaskList $list): RedirectResponse
    {
        // Hanya owner yang boleh memperbarui (TaskListPolicy::update)
        $this->authorize('update', $list);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $list->update($validated);

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil diperbarui.');
    }

    /**
     * Remove the specified task list from storage (soft delete).
     */
    public function destroy(TaskList $list): RedirectResponse
    {
        // Hanya owner yang boleh menghapus (TaskListPolicy::delete) — FR-03.1
        $this->authorize('delete', $list);

        $list->delete();

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dihapus.');
    }
}

