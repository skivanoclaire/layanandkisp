@extends('layouts.user')

@section('title', 'Edit Profile')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow border border-gray-200">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Edit Profil Pengguna</h2>

        @if (session('status'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <!-- Nama -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    required>
            </div>

            <!-- NIK / NIP -->
            <div class="mb-4">
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK / NIP</label>
                <input id="nik" type="text" name="nik" value="{{ old('nik', auth()->user()->nik) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>

            <!-- Nomor HP / WA -->
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Nomor HP / WA</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>



            <!-- Current Password -->
            <div class="mt-4">
                <x-input-label for="current_password" :value="__('Password Sekarang')" />
                <x-text-input id="current_password" class="block mt-1 w-full" type="password" name="current_password"
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>

            <!-- New Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password Baru')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm New Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection