<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>⚙️</span> {{ __('Manajemen Akun Pengguna (Admin Panel)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Tambah akun baru, cari, dan kelola pengguna sistem.
                </p>
            </div>
            <a href="{{ route('lists.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                ← Kembali ke Daftar List
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-3">
                    <span class="text-green-600 text-lg">✅</span>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg bg-red-50 border border-red-200 p-4 flex items-center gap-3">
                    <span class="text-red-600 text-lg">❌</span>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Stats Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        👥
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                        <div class="text-xs text-gray-500 font-medium">Total Pengguna</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                        👑
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-purple-600">{{ $stats['admins'] }}</div>
                        <div class="text-xs text-gray-500 font-medium">Administrator</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        🙋
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['regular'] }}</div>
                        <div class="text-xs text-gray-500 font-medium">Pengguna Biasa</div>
                    </div>
                </div>
            </div>

            {{-- Grid: Add User Form & User Table --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Add User Form --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm h-fit">
                    <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span>➕</span> Tambah Akun Pengguna
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Buat akun pengguna atau admin baru langsung ke sistem.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3">
                            <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1" for="name">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                   placeholder="Contoh: Budi Pratama"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1" for="email">
                                Alamat Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                   placeholder="budi@example.com"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1" for="password">
                                Kata Sandi <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password" required
                                   placeholder="Minimal 8 karakter"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1" for="role">
                                Peran (Role) <span class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white">
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User (Pengguna Biasa)</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator)</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                            Simpan Pengguna Baru
                        </button>
                    </form>
                </div>

                {{-- Right: User List Table & Search --}}
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between">
                    <div>
                        {{-- Search Bar Header --}}
                        <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <h3 class="text-base font-bold text-gray-900">
                                Daftar Seluruh Pengguna
                            </h3>

                            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                                <input type="text"
                                       name="search"
                                       value="{{ $search }}"
                                       placeholder="Cari nama, email, role..."
                                       class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-gray-800 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition">
                                    Cari
                                </button>
                                @if ($search)
                                    <a href="{{ route('admin.users.index') }}" class="text-xs text-gray-500 hover:text-gray-700">
                                        Reset
                                    </a>
                                @endif
                            </form>
                        </div>

                        {{-- Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Pengguna</th>
                                        <th class="px-6 py-3">Peran</th>
                                        <th class="px-6 py-3">Proyek & Tugas</th>
                                        <th class="px-6 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($users as $user)
                                        @php
                                            $isSelf = $user->id === Auth::id();
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition {{ $isSelf ? 'bg-indigo-50/40' : '' }}">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                                                            <span>{{ $user->name }}</span>
                                                            @if ($isSelf)
                                                                <span class="text-[10px] bg-indigo-600 text-white px-1.5 py-0.2 rounded font-semibold">Anda</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                @if ($user->isAdmin())
                                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        👑 Admin
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                        🙋 User
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 text-xs text-gray-500">
                                                <div>📁 {{ $user->task_lists_count }} list</div>
                                                <div>📋 {{ $user->tasks_count }} tugas dibuat</div>
                                            </td>

                                            <td class="px-6 py-4 text-right">
                                                @if (! $isSelf)
                                                    <form method="POST"
                                                          action="{{ route('admin.users.destroy', $user) }}"
                                                          onsubmit="return confirm('Hapus pengguna {{ $user->name }} beserta seluruh data proyek dan tugasnya secara permanen?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="px-2.5 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded font-medium transition">
                                                            🗑️ Hapus
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Akun aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                                Tidak ada pengguna yang sesuai dengan pencarian.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    @if ($users->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
