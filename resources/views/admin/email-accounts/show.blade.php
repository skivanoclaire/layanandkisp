@extends('layouts.authenticated')

@section('title', '- Detail Email Account')
@section('header-title', 'Detail Email Account')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Email Account</h1>
        <a href="{{ request()->query('return_url', route('admin.email-accounts.index')) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
            Kembali
        </a>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <p class="text-lg font-semibold text-gray-900">{{ $emailAccount->email }}</p>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                @if($emailAccount->isSuspended())
                    <span class="inline-block px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">
                        Suspended
                    </span>
                @else
                    <span class="inline-block px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">
                        Aktif
                    </span>
                @endif
            </div>

            <!-- Domain -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                <p class="text-gray-900">{{ $emailAccount->domain }}</p>
            </div>

            <!-- User -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CPanel User</label>
                <p class="text-gray-900">{{ $emailAccount->user }}</p>
            </div>

            <!-- Disk Used -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Disk Used</label>
                <p class="text-gray-900">{{ $emailAccount->diskused_readable ?? '-' }}</p>
                @if($emailAccount->disk_used)
                    <p class="text-sm text-gray-500">{{ number_format($emailAccount->disk_used) }} bytes</p>
                @endif
            </div>

            <!-- Disk Quota -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Disk Quota</label>
                <p class="text-gray-900">{{ $emailAccount->diskquota_readable ?? '-' }}</p>
                @if($emailAccount->disk_quota)
                    <p class="text-sm text-gray-500">{{ number_format($emailAccount->disk_quota) }} bytes</p>
                @endif
            </div>

            <!-- Disk Usage Percentage -->
            @if($emailAccount->disk_usage_percentage > 0)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Disk Usage Percentage</label>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-blue-600 h-4 rounded-full flex items-center justify-center text-xs text-white font-semibold"
                         style="width: {{ min($emailAccount->disk_usage_percentage, 100) }}%">
                        {{ $emailAccount->disk_usage_percentage }}%
                    </div>
                </div>
            </div>
            @endif

            <!-- Last Synced At -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Terakhir Disinkronkan</label>
                <p class="text-gray-900">
                    {{ $emailAccount->last_synced_at ? $emailAccount->last_synced_at->format('d F Y H:i:s') : '-' }}
                </p>
                @if($emailAccount->last_synced_at)
                    <p class="text-sm text-gray-500">{{ $emailAccount->last_synced_at->diffForHumans() }}</p>
                @endif
            </div>

            <!-- Created At -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dibuat di Database</label>
                <p class="text-gray-900">{{ $emailAccount->created_at->format('d F Y H:i:s') }}</p>
                <p class="text-sm text-gray-500">{{ $emailAccount->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <!-- User Information Section -->
        <div class="mt-8 pt-6 border-t-2 border-gray-300">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Informasi Pemohon</h2>
                @if(auth()->user()->hasRole('Admin'))
                    <button
                        onclick="document.getElementById('userInfoForm').classList.toggle('hidden'); document.getElementById('userInfoDisplay').classList.toggle('hidden')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold">
                        {{ $requestingUser ? 'Edit Informasi' : 'Tambah Informasi' }}
                    </button>
                @endif
            </div>

            <!-- Display Mode -->
            <div id="userInfoDisplay" class="{{ $requestingUser ? 'bg-blue-50' : 'bg-yellow-50' }} rounded-lg p-6">
                @if($requestingUser)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemohon</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $requestingUser->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                            <p class="text-gray-900">{{ $requestingUser->nip ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instansi</label>
                            <p class="text-gray-900">{{ $requestingUser->instansi ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Pribadi Pemohon</label>
                            <p class="text-gray-900">{{ $requestingUser->email ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                            <p class="text-gray-900">{{ $requestingUser->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <p class="text-gray-900">
                                @if($requestingUser->roles->isNotEmpty())
                                    {{ $requestingUser->roles->pluck('display_name')->join(', ') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Tidak ditemukan data pemohon.</span><br>
                        Email ini mungkin dibuat secara manual di CPanel atau data permohonan sudah dihapus.
                        <br>Klik "Tambah Informasi" untuk menambahkan data pemohon secara manual.
                    </p>
                @endif
            </div>

            <!-- Edit/Add Form -->
            @if(auth()->user()->hasRole('Admin'))
            <form
                id="userInfoForm"
                action="{{ route('admin.email-accounts.update-requester-info', $emailAccount) }}"
                method="POST"
                class="hidden bg-gray-50 rounded-lg p-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_url" value="{{ request()->query('return_url', route('admin.email-accounts.index')) }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemohon <span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            name="requester_name"
                            value="{{ old('requester_name', $requestingUser->name ?? '') }}"
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('requester_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                        <input
                            type="text"
                            name="requester_nip"
                            value="{{ old('requester_nip', $requestingUser->nip ?? '') }}"
                            maxlength="18"
                            pattern="\d{18}"
                            placeholder="18 digit"
                            class="w-full border border-gray-300 rounded px-3 py-2 font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Harus tepat 18 digit angka (opsional)</p>
                        @error('requester_nip')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Instansi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instansi</label>
                        <select
                            name="requester_instansi"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Instansi --</option>
                            @foreach($unitKerjaList as $unitKerja)
                                <option value="{{ $unitKerja->nama }}"
                                    {{ old('requester_instansi', $requestingUser->instansi ?? '') == $unitKerja->nama ? 'selected' : '' }}>
                                    {{ $unitKerja->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('requester_instansi')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Pribadi Pemohon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Pribadi Pemohon</label>
                        <input
                            type="email"
                            name="requester_email"
                            value="{{ old('requester_email', $requestingUser->email ?? '') }}"
                            placeholder="email@example.com"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('requester_email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No. HP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                        <input
                            type="text"
                            name="requester_phone"
                            value="{{ old('requester_phone', $requestingUser->phone ?? '') }}"
                            placeholder="08xxxxxxxxxx"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('requester_phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                        Simpan
                    </button>
                    <button
                        type="button"
                        onclick="document.getElementById('userInfoForm').classList.add('hidden'); document.getElementById('userInfoDisplay').classList.remove('hidden')"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded font-semibold">
                        Batal
                    </button>
                </div>
            </form>
            @endif
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between">
            <form action="{{ route('admin.email-accounts.destroy', $emailAccount) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-semibold"
                    onclick="return confirm('Hapus {{ $emailAccount->email }} dari database lokal? Data di server WHM tidak akan terpengaruh.')">
                    Hapus dari Database Lokal
                </button>
            </form>

            <a href="{{ request()->query('return_url', route('admin.email-accounts.index')) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
                Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection
