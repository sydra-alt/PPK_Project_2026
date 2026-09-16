<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Edit List') }}: {{ $list->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Perbarui informasi nama atau deskripsi list tugas Anda.') }}
                </p>
            </div>
            <a href="{{ route('lists.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Kembali ke Daftar List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6 sm:p-8">
                    <form method="POST" action="{{ route('lists.update', $list) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Nama List / Proyek *')" class="font-semibold text-gray-700" />
                            <x-text-input id="name" name="name" type="text"
                                          class="mt-1.5 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm"
                                          :value="old('name', $list->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Deskripsi Singkat (Opsional)')" class="font-semibold text-gray-700" />
                            <textarea id="description" name="description" rows="3"
                                      class="mt-1.5 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm"
                                      placeholder="{{ __('A brief description of this list...') }}">{{ old('description', $list->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <a href="{{ route('lists.index') }}"
                               class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                                {{ __('Batal') }}
                            </a>

                            <x-primary-button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm font-semibold text-xs uppercase tracking-widest">
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
