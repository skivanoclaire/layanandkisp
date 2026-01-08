@extends('layouts.authenticated')

@section('title', '- Edit User')
@section('header-title', 'Edit User')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow border border-gray-200">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Edit Data Pengguna</h2>

        @if (session('success'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <!-- Nama -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                    required>
            </div>

            <!-- Role (Multi-select) -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <div class="space-y-2">
                    @foreach($roles as $role)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ $role->display_name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-sm text-gray-500">User bisa memiliki lebih dari satu role</p>
            </div>

            <!-- NIP -->
            <div class="mb-4">
                <label for="nip" class="block text-sm font-medium text-gray-700">NIP (Nomor Induk Pegawai)</label>
                <input type="text" name="nip" id="nip" value="{{ old('nip', $user->nip) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                    placeholder="Masukkan NIP jika pegawai ASN">
                <p class="mt-1 text-xs text-gray-500">Opsional - hanya untuk pegawai ASN</p>
            </div>

            <!-- NIK -->
            <div class="mb-4">
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" name="nik" id="nik" value="{{ old('nik', $user->nik) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                    placeholder="16 digit NIK">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Nomor HP / WA</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="mt-4">
                <label class="block font-semibold mb-1">Password Baru</label>
                <input type="password" name="password" class="form-input w-full border border-gray-300 rounded-md p-2"
                    placeholder="Kosongkan jika tidak ingin mengubah">
            </div>

            <div class="mt-2">
                <label class="block font-semibold mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                    class="form-input w-full border border-gray-300 rounded-md p-2">
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
