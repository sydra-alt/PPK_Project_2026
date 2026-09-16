<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Selamat Datang, ') }} {{ Auth::user()->name }}! 👋
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Kelola aktivitas, daftar tugas, dan kolaborasi tim Anda dengan JARA.') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('lists.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('+ Buat List Baru') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Quick action cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Card 1: My Lists --}}
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-2xl mb-4">
                        📁
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Daftar Tugas & Proyek</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Lihat seluruh daftar tugas pribadi maupun yang dibagikan oleh rekan tim Anda.
                    </p>
                    <a href="{{ route('lists.index') }}"
                       class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        Buka Daftar List &rarr;
                    </a>
                </div>

                {{-- Card 2: Create List --}}
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-2xl mb-4">
                        ✨
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Buat List Baru</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Mulai kelompokkan tugas baru dengan otomatis menjadi pemilik (owner) daftar.
                    </p>
                    <a href="{{ route('lists.create') }}"
                       class="inline-flex items-center text-sm font-semibold text-emerald-600 hover:text-emerald-800">
                        Mulai Sekarang &rarr;
                    </a>
                </div>

                {{-- Card 3: Admin / Collaboration --}}
                @if (Auth::user()->isAdmin())
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-2xl mb-4">
                            ⚙️
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Panel Administrator</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Kelola pengguna sistem, tambah akun baru, atau hapus akun pengguna.
                        </p>
                        <a href="{{ route('admin.users.index') }}"
                           class="inline-flex items-center text-sm font-semibold text-purple-600 hover:text-purple-800">
                            Kelola Pengguna &rarr;
                        </a>
                    </div>
                @else
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-2xl mb-4">
                            🤝
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Kolaborasi Tim</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Bekerja sama dalam satu daftar tugas dengan mengundang anggota lain.
                        </p>
                        <a href="{{ route('lists.index') }}"
                           class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                            Lihat Kolaborasi &rarr;
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
