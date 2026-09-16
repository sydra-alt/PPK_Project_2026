<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskListController extends Controller
{
    /**
     * Display a listing of the user's owned and shared task lists.
     * SRS-002: Membuat & melihat list/project
     * SRS-008: Melihat list yang dibagikan
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $ownedLists = $user->taskLists()->withCount('tasks')->latest()->get();
        $sharedLists = $user->sharedTaskLists()->withCount('tasks')->latest()->get();

        return view('lists.index', compact('ownedLists', 'sharedLists'));
    }

    /**
     * Show the form for creating a new task list.
     * Otorisasi via TaskListPolicy::create (dari branch Autheticator)
     */
    public function create(): View
    {
        $this->authorize('create', TaskList::class);

        return view('lists.create');
    }

    /**
     * Store a newly created task list in storage.
     * SRS-002: Pengguna otomatis menjadi pemiliknya
     * Otorisasi create sebelum menyentuh database (dari branch Autheticator)
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TaskList::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $list = DB::transaction(function () use ($request, $validated) {
            return $request->user()->taskLists()->create($validated);
        });

        return redirect()->route('lists.index')
            ->with('success', 'List "' . $list->name . '" berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified task list.
     * Hanya owner yang boleh mengedit (TaskListPolicy::update)
     */
    public function edit(TaskList $list): View
    {
        $this->authorize('update', $list);

        return view('lists.edit', compact('list'));
    }

    /**
     * Update the specified task list in storage.
     * Hanya owner yang boleh memperbarui (TaskListPolicy::update)
     */
    public function update(Request $request, TaskList $list): RedirectResponse
    {
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
     * Remove the specified task list from storage atomically.
     * SRS-003 & Tambahan Fitur: Menghapus daftar beserta seluruh tugas dan
     * keanggotaan di dalamnya secara atomik (DB::transaction).
     * Hanya owner yang boleh menghapus (TaskListPolicy::delete)
     */
    public function destroy(TaskList $list): RedirectResponse
    {
        $this->authorize('delete', $list);

        DB::transaction(function () use ($list) {
            // 1. Hapus seluruh tugas yang ada di dalam list ini
            $list->tasks()->delete();

            // 2. Hapus seluruh keanggotaan kolaborasi dalam list ini
            $list->members()->detach();

            // 3. Hapus list itu sendiri
            $list->forceDelete();
        });

        return redirect()->route('lists.index')
            ->with('success', 'List beserta seluruh tugas dan keanggotaan di dalamnya berhasil dihapus secara atomik.');
    }
}
