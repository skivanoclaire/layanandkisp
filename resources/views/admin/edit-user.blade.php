@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow border border-gray-200">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Edit Data Pengguna</h2>

        @if(session('success'))
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

            <!-- Role -->
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" id="role"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <!-- NIK -->
            <div class="mb-4">
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK / NIP</label>
                <input type="text" name="nik" id="nik" value="{{ old('nik', $user->nik) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
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
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection