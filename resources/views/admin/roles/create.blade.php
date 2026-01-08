@extends('layouts.authenticated')

@section('title', '- Tambah Role Baru')
@section('header-title', 'Tambah Role Baru')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.roles.index') }}"
            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Role
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-6">Form Tambah Role</h2>

        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf

            <!-- Name (System Identifier) -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Role (Identifier) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                    value="{{ old('name') }}"
                    placeholder="contoh: User-OPD, Manager, Supervisor"
                    required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Nama unik untuk sistem. Hanya huruf, angka, underscore (_), dan dash (-). Contoh: User-OPD, Team-Leader
                </p>
            </div>

            <!-- Display Name -->
            <div class="mb-6">
                <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Tampilan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="display_name" id="display_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('display_name') border-red-500 @enderror"
                    value="{{ old('display_name') }}"
                    placeholder="contoh: User OPD, Manager TIK"
                    required>
                @error('display_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Nama yang akan ditampilkan di interface (boleh menggunakan spasi dan karakter khusus)
                </p>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi
                </label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Deskripsi singkat tentang role ini dan kewenangannya...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Deskripsi opsional untuk menjelaskan tujuan dan lingkup role ini
                </p>
            </div>

            <!-- Info Box -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">Langkah Selanjutnya:</p>
                        <p>Setelah role dibuat, jangan lupa untuk:</p>
                        <ol class="list-decimal list-inside space-y-1 mt-2">
                            <li>Assign permissions di menu "Kelola Kewenangan"</li>
                            <li>Assign role ini ke user yang sesuai di menu "User Management"</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Simpan Role
                </button>
                <a href="{{ route('admin.roles.index') }}"
                    class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
