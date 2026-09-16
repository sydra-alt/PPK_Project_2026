<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskListMemberController extends Controller
{
    /**
     * Display list of members in the task list.
     * SRS-008: Kolaborasi dalam list/project
     */
    public function index(TaskList $list): View
    {
        $this->authorize('view', $list);

        $list->load(['owner', 'members']);

        return view('lists.members', compact('list'));
    }

    /**
     * Add a new member to the task list.
     * SRS-008: Pemilik list/project dapat menambahkan pengguna lain
     */
    public function store(Request $request, TaskList $list): RedirectResponse
    {
        $this->authorize('manageMembers', $list);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Pengguna dengan email tersebut tidak ditemukan dalam sistem.',
        ]);

        $userToAdd = User::where('email', $validated['email'])->firstOrFail();

        // 1. Cek jika menambahkan diri sendiri (pemilik)
        if ($list->isOwner($userToAdd)) {
            return back()->with('error', 'Anda sudah menjadi pemilik dari list ini.');
        }

        // 2. Cek jika user sudah menjadi anggota
        if ($list->hasMember($userToAdd)) {
            return back()->with('error', 'Pengguna tersebut sudah terdaftar sebagai anggota list.');
        }

        // 3. Tambahkan ke tabel pivot secara aman
        DB::transaction(function () use ($list, $userToAdd) {
            $list->members()->attach($userToAdd->id, ['role' => 'member']);
        });

        return back()->with('success', 'Pengguna "' . $userToAdd->name . '" berhasil ditambahkan sebagai kolaborator.');
    }

    /**
     * Remove a member from the task list.
     * SRS-008: Pemilik dapat menghapus anggota dari list
     */
    public function destroy(TaskList $list, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $list);

        if ($list->isOwner($user)) {
            return back()->with('error', 'Tidak dapat menghapus pemilik list.');
        }

        DB::transaction(function () use ($list, $user) {
            $list->members()->detach($user->id);
        });

        return back()->with('success', 'Anggota "' . $user->name . '" berhasil dihapus dari list.');
    }
}
