<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('lists.index') }}" class="hover:text-indigo-600 transition">← Daftar List</a>
                    <span>/</span>
                    <span>{{ $list->name }}</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>👥</span> {{ __('Kelola Anggota Kolaborasi') }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('task-lists.tasks.index', $list->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    📋 Buka Tugas
                </a>
                <a href="{{ route('lists.progress', $list) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-xs font-semibold text-blue-700 hover:bg-blue-100 shadow-sm transition">
                    📊 Lihat Progres
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Add Member Form (Only Owner or Admin) --}}
            @can('manageMembers', $list)
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-1">
                        ➕ Undang Pengguna ke List Ini
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Masukkan alamat email pengguna terdaftar yang ingin Anda ajak berkolaborasi.
                    </p>

                    <form method="POST" action="{{ route('lists.members.store', $list) }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <input type="email"
                                   name="email"
                                   id="email"
                                   placeholder="contoh: user@example.com"
                                   required
                                   value="{{ old('email') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>
                        <button type="submit"
                                class="inline-flex justify-center items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Undang Anggota
                        </button>
                    </form>
                </div>
            @endcan

            {{-- Member List Table --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900">
                        Daftar Anggota & Kolaborator
                    </h3>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-semibold">
                        Total: {{ 1 + $list->members->count() }} orang
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    {{-- Owner Row --}}
                    <div class="p-4 sm:px-6 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm">
                                {{ strtoupper(substr($list->owner?->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                    <span>{{ $list->owner?->name ?? 'Pemilik' }}</span>
                                    @if ($list->isOwner(Auth::user()))
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Anda</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $list->owner?->email ?? '-' }}</div>
                            </div>
                        </div>
                        <div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                👑 Pemilik
                            </span>
                        </div>
                    </div>

                    {{-- Collaborators Rows --}}
                    @forelse ($list->members as $member)
                        <div class="p-4 sm:px-6 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-sm">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <span>{{ $member->name }}</span>
                                        @if ($member->id === Auth::id())
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Anda</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $member->email }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    🤝 Anggota
                                </span>

                                @can('manageMembers', $list)
                                    <form method="POST"
                                          action="{{ route('lists.members.destroy', [$list, $member]) }}"
                                          onsubmit="return confirm('Keluarkan {{ $member->name }} dari list ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-2.5 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            Belum ada kolaborator yang ditambahkan ke list ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
