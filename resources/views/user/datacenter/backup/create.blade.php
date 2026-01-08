@extends('layouts.authenticated')
@section('title', '- Ajukan Backup')
@section('header-title', 'Backup')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Permohonan Backup</h1>
        <a href="{{ route('user.datacenter.backup.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <!-- Operating Hours Info -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-sm text-blue-800">
            <strong>Jam Layanan:</strong><br>
            Senin - Kamis: 07.30 - 16.00 WITA<br>
            Jumat: 07.30 - 16.30 WITA
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3">
            <ul class="list-disc list-inside text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.datacenter.backup.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf

        <div class="space-y-4">
            <!-- Data Pemohon -->
            <div class="pb-3 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Data Pemohon</h2>
            </div>

            <!-- Nama -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="{{ old('nama', Auth::user()->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- NIP -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    NIP <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nip" value="{{ Auth::user()->nip ?? '' }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed">
            </div>

            <!-- Instansi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Instansi <span class="text-red-500">*</span>
                </label>
                <select name="unit_kerja_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Instansi --</option>
                    @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                            {{ $uk->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Konfigurasi Backup -->
            <div class="pt-4 pb-3 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Konfigurasi Backup</h2>
            </div>

            <!-- Tipe Backup (Checkbox) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Tipe Backup <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="backup_virtual_machine" value="1"
                               {{ old('backup_virtual_machine') ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-gray-700">Backup Virtual Machine</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="backup_aplikasi" value="1"
                               {{ old('backup_aplikasi') ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-gray-700">Backup Aplikasi</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="backup_database" value="1"
                               {{ old('backup_database') ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-gray-700">Backup Database</span>
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Pilih minimal satu tipe backup</p>
            </div>

            <!-- Jadwal Backup -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Jadwal Backup <span class="text-red-500">*</span>
                </label>
                <select name="jadwal_backup" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Jadwal Backup --</option>
                    <option value="Harian" {{ old('jadwal_backup') == 'Harian' ? 'selected' : '' }}>Harian</option>
                    <option value="Mingguan" {{ old('jadwal_backup') == 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="Bulanan" {{ old('jadwal_backup') == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                </select>
            </div>

            <!-- Retensi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Retensi (Hari) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="retensi_hari" value="{{ old('retensi_hari') }}" required min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                <p class="mt-1 text-xs text-gray-500">Berapa hari backup akan disimpan. Contoh: 7, 14, 30 hari</p>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Jelaskan detail sistem yang akan dibackup, ukuran data, kebutuhan khusus, dll...">{{ old('keterangan') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-2 pt-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.datacenter.backup.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
