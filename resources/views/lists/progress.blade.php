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
                    <span>📊</span> {{ __('Pemantauan Progres Proyek') }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('task-lists.tasks.index', $list->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 rounded-lg text-xs font-semibold text-white hover:bg-indigo-700 shadow-sm transition">
                    📋 Buka Daftar Tugas
                </a>
                <a href="{{ route('lists.members.index', $list) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-purple-50 border border-purple-200 rounded-lg text-xs font-semibold text-purple-700 hover:bg-purple-100 shadow-sm transition">
                    👥 Anggota ({{ 1 + $list->members->count() }})
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Main Progress Overview Card --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">
                            Status Proyek
                        </span>
                        <h3 class="text-2xl font-extrabold text-gray-900 mt-2">
                            {{ $list->name }}
                        </h3>
                        @if ($list->description)
                            <p class="text-sm text-gray-500 mt-1 max-w-2xl">
                                {{ $list->description }}
                            </p>
                        @endif
                    </div>

                    {{-- Percentage Big Indicator --}}
                    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div class="text-right">
                            <div class="text-3xl font-black text-indigo-600">
                                {{ $percentage }}%
                            </div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Selesai
                            </div>
                        </div>
                        <div class="w-16 h-16 rounded-full border-4 border-indigo-200 flex items-center justify-center font-bold text-indigo-700">
                            {{ $completed }}/{{ $total }}
                        </div>
                    </div>
                </div>

                {{-- Full Progress Bar --}}
                <div class="mt-6">
                    <div class="w-full bg-gray-100 rounded-full h-3.5 overflow-hidden flex">
                        <div class="bg-emerald-500 h-3.5 transition-all duration-500"
                             style="width: {{ $percentage }}%"
                             title="Selesai: {{ $percentage }}%"></div>
                        @if ($total > 0 && $pending > 0)
                            <div class="bg-amber-400 h-3.5 transition-all duration-500"
                                 style="width: {{ round(($pending / $total) * 100) }}%"
                                 title="Pending: {{ round(($pending / $total) * 100) }}%"></div>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai ({{ $completed }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Pending ({{ $pending }})</span>
                            @if ($overdue > 0)
                                <span class="flex items-center gap-1.5 text-red-600 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Terlambat ({{ $overdue }})</span>
                            @endif
                        </div>
                        <span>Total: {{ $total }} Tugas</span>
                    </div>
                </div>
            </div>

            {{-- 4 Quick Stats Grid --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        📋
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $total }}</div>
                        <div class="text-xs text-gray-500 font-medium">Total Tugas</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        ✅
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $completed }}</div>
                        <div class="text-xs text-gray-500 font-medium">Tugas Selesai</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        ⏳
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-amber-600">{{ $pending }}</div>
                        <div class="text-xs text-gray-500 font-medium">Dalam Proses</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        ⚠️
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-600">{{ $overdue }}</div>
                        <div class="text-xs text-gray-500 font-medium">Melewati Deadline</div>
                    </div>
                </div>
            </div>

            {{-- Priority Breakdown & Tasks Summary --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Priority Breakdown Cards --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h4 class="text-base font-bold text-gray-900 mb-4">
                        🎯 Rincian Berdasarkan Prioritas
                    </h4>

                    <div class="space-y-4">
                        {{-- Urgent --}}
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-red-600">🚨 Urgent</span>
                                <span class="text-gray-600">{{ $priorityStats['urgent']['completed'] }} / {{ $priorityStats['urgent']['total'] }} selesai</span>
                            </div>
                            @php
                                $urgPct = $priorityStats['urgent']['total'] > 0 ? round(($priorityStats['urgent']['completed'] / $priorityStats['urgent']['total']) * 100) : 0;
                            @endphp
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $urgPct }}%"></div>
                            </div>
                        </div>

                        {{-- High --}}
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-amber-600">⚡ High</span>
                                <span class="text-gray-600">{{ $priorityStats['high']['completed'] }} / {{ $priorityStats['high']['total'] }} selesai</span>
                            </div>
                            @php
                                $highPct = $priorityStats['high']['total'] > 0 ? round(($priorityStats['high']['completed'] / $priorityStats['high']['total']) * 100) : 0;
                            @endphp
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $highPct }}%"></div>
                            </div>
                        </div>

                        {{-- Medium --}}
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-blue-600">📌 Medium</span>
                                <span class="text-gray-600">{{ $priorityStats['medium']['completed'] }} / {{ $priorityStats['medium']['total'] }} selesai</span>
                            </div>
                            @php
                                $medPct = $priorityStats['medium']['total'] > 0 ? round(($priorityStats['medium']['completed'] / $priorityStats['medium']['total']) * 100) : 0;
                            @endphp
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $medPct }}%"></div>
                            </div>
                        </div>

                        {{-- Low --}}
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-gray-600">☕ Low</span>
                                <span class="text-gray-600">{{ $priorityStats['low']['completed'] }} / {{ $priorityStats['low']['total'] }} selesai</span>
                            </div>
                            @php
                                $lowPct = $priorityStats['low']['total'] > 0 ? round(($priorityStats['low']['completed'] / $priorityStats['low']['total']) * 100) : 0;
                            @endphp
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-gray-400 h-2 rounded-full" style="width: {{ $lowPct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Task Breakdown Table / List --}}
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-base font-bold text-gray-900">
                            📋 Daftar Tugas dalam Proyek
                        </h4>
                        <a href="{{ route('task-lists.tasks.create', $list->id) }}"
                           class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            + Tambah Tugas
                        </a>
                    </div>

                    @if ($tasks->isEmpty())
                        <div class="text-center py-8 text-gray-400 text-sm">
                            Belum ada tugas di dalam list ini.
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            @foreach ($tasks as $task)
                                <div class="py-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="text-base">
                                            {{ $task->status->value === 'completed' ? '✅' : '⏳' }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate {{ $task->status->value === 'completed' ? 'line-through text-gray-400' : '' }}">
                                                {{ $task->title }}
                                            </p>
                                            <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                                                <span>Dibuat oleh: {{ $task->user?->name ?? 'User' }}</span>
                                                @if ($task->due_date)
                                                    <span>• Tenggat: {{ $task->due_date->format('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                            {{ $task->priority->value === 'urgent' ? 'bg-red-100 text-red-800' :
                                               ($task->priority->value === 'high' ? 'bg-amber-100 text-amber-800' :
                                               ($task->priority->value === 'medium' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst($task->priority->value) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
