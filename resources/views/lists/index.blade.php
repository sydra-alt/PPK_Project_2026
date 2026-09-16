<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Daftar Tugas & Proyek') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola list tugas pribadi maupun kolaborasi bersama tim Anda.
                </p>
            </div>
            <a href="{{ route('lists.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('+ Buat Daftar Baru') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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

            {{-- SECTION 1: Owned Lists (SRS-002, SRS-003) --}}
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span>📁</span> {{ __('List Milik Saya') }}
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full font-semibold">
                            {{ $ownedLists->count() }}
                        </span>
                    </h3>
                </div>

                @if ($ownedLists->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 shadow-sm">
                        <div class="text-4xl mb-2">📭</div>
                        <h4 class="text-base font-semibold text-gray-900">{{ __('Belum ada list pribadi') }}</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Mulai dengan membuat list tugas atau proyek pertama Anda.') }}</p>
                        <div class="mt-4">
                            <a href="{{ route('lists.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-lg text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm">
                                {{ __('+ Buat List Sekarang') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($ownedLists as $list)
                            @php
                                $stats = $list->progressStats();
                            @endphp
                            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="text-lg font-bold text-gray-900 truncate">
                                            {{ $list->name }}
                                        </h4>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 shrink-0">
                                            Pemilik
                                        </span>
                                    </div>

                                    @if ($list->description)
                                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                            {{ $list->description }}
                                        </p>
                                    @endif

                                    {{-- Mini Progress Bar (SRS-010) --}}
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <div class="flex justify-between text-xs text-gray-500 font-medium mb-1">
                                            <span>Progres Tugas</span>
                                            <span class="font-bold text-gray-700">{{ $stats['percentage'] }}% ({{ $stats['completed'] }}/{{ $stats['total'] }})</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Links & Buttons --}}
                                <div class="mt-6 pt-4 border-t border-gray-100 space-y-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('task-lists.tasks.index', $list->id) }}"
                                           class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-semibold transition">
                                            <span>📋</span> Buka Tugas
                                        </a>
                                        <a href="{{ route('lists.progress', $list) }}"
                                           class="inline-flex items-center gap-1 px-3 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-semibold transition"
                                           title="Lihat Progres">
                                            <span>📊</span> Progres
                                        </a>
                                        <a href="{{ route('lists.members.index', $list) }}"
                                           class="inline-flex items-center gap-1 px-3 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-semibold transition"
                                           title="Kelola Kolaborator">
                                            <span>👥</span> Anggota
                                        </a>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <a href="{{ route('lists.edit', $list) }}"
                                           class="text-xs text-gray-600 hover:text-gray-900 font-medium px-2 py-1">
                                            ✏️ Edit
                                        </a>
                                        <form method="POST" action="{{ route('lists.destroy', $list) }}"
                                              onsubmit="return confirm('Hapus list ini beserta seluruh tugas dan keanggotaan di dalamnya secara atomik?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- SECTION 2: Shared Lists / Collaboration (SRS-008, SRS-009) --}}
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span>🤝</span> {{ __('Dibagikan ke Saya (Kolaborasi)') }}
                        <span class="text-xs bg-purple-100 text-purple-700 px-2.5 py-0.5 rounded-full font-semibold">
                            {{ $sharedLists->count() }}
                        </span>
                    </h3>
                </div>

                @if ($sharedLists->isEmpty())
                    <div class="bg-white rounded-xl border border-dashed border-gray-300 p-6 text-center text-gray-500">
                        <p class="text-sm">{{ __('Belum ada list yang dibagikan oleh pengguna lain kepada Anda.') }}</p>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($sharedLists as $list)
                            @php
                                $stats = $list->progressStats();
                            @endphp
                            <div class="bg-white rounded-xl border border-purple-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="text-lg font-bold text-gray-900 truncate">
                                            {{ $list->name }}
                                        </h4>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 shrink-0">
                                            Kolaborator
                                        </span>
                                    </div>

                                    <p class="text-xs text-gray-400 mt-1">
                                        Pemilik: <span class="font-medium text-gray-700">{{ $list->owner?->name ?? 'User' }}</span>
                                    </p>

                                    @if ($list->description)
                                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                            {{ $list->description }}
                                        </p>
                                    @endif

                                    {{-- Mini Progress Bar --}}
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <div class="flex justify-between text-xs text-gray-500 font-medium mb-1">
                                            <span>Progres Tugas</span>
                                            <span class="font-bold text-gray-700">{{ $stats['percentage'] }}% ({{ $stats['completed'] }}/{{ $stats['total'] }})</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Links --}}
                                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center gap-2">
                                    <a href="{{ route('task-lists.tasks.index', $list->id) }}"
                                       class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-semibold transition">
                                        <span>📋</span> Buka Tugas
                                    </a>
                                    <a href="{{ route('lists.progress', $list) }}"
                                       class="inline-flex items-center gap-1 px-3 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-semibold transition"
                                       title="Lihat Progres">
                                        <span>📊</span> Progres
                                    </a>
                                    <a href="{{ route('lists.members.index', $list) }}"
                                       class="inline-flex items-center gap-1 px-3 py-2 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition"
                                       title="Daftar Anggota">
                                        <span>👥</span> Anggota
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
