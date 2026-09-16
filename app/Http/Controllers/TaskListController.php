<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        return view('lists.create');
    }

    /**
     * Store a newly created task list in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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
        Gate::authorize('update', $list);

        return view('lists.edit', compact('list'));
    }

    /**
     * Update the specified task list in storage.
     */
    public function update(Request $request, TaskList $list): RedirectResponse
    {
        Gate::authorize('update', $list);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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
        Gate::authorize('delete', $list);

        $list->delete();

        return redirect()->route('lists.index')
            ->with('success', 'List berhasil dihapus.');
    }
}
