<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users for admin management.
     * SRS-011: Manajemen akun oleh admin
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $usersQuery = User::query()->withCount(['taskLists', 'tasks']);

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'regular' => User::where('role', 'user')->count(),
        ];

        return view('admin.users.index', compact('users', 'search', 'stats'));
    }

    /**
     * Store a newly created user in storage by admin.
     * SRS-011: Admin dapat menambah akun pengguna
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        DB::transaction(function () use ($validated) {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'email_verified_at' => now(),
            ]);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun pengguna "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Remove the specified user from storage by admin.
     * SRS-011: Admin dapat menghapus akun pengguna
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // 1. Cegah admin menghapus akunnya sendiri
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // 2. Cegah menghapus admin terakhir di sistem
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus admin terakhir di sistem.');
        }

        // 3. Hapus seluruh data user (task list, tasks, keanggotaan) secara atomik
        DB::transaction(function () use ($user) {
            // Hapus list milik user beserta tasks dan members
            foreach ($user->taskLists as $list) {
                $list->tasks()->delete();
                $list->members()->detach();
                $list->forceDelete();
            }

            // Lepaskan user dari keanggotaan di list lain
            $user->sharedTaskLists()->detach();

            // Hapus tasks yang dibuat user di list lain
            $user->tasks()->delete();

            // Hapus user
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna "' . $user->name . '" beserta seluruh datanya berhasil dihapus dari sistem.');
    }
}
