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

            <!-- Unit Kerja -->
            <div class="mb-4">
                <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700">Unit Kerja / Instansi</label>
                <select name="unit_kerja_id" id="unit_kerja_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih Unit Kerja --</option>
                    @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}" {{ old('unit_kerja_id', $user->unit_kerja_id) == $uk->id ? 'selected' : '' }}>
                            {{ $uk->nama }} ({{ $uk->tipe }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Opsional - Pilih untuk user yang merupakan pegawai instansi</p>
            </div>

            <!-- Jabatan (read-only, dikelola via Sinkron SIMPEG) -->
            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-md">
                <div class="flex items-start justify-between gap-4 mb-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Jabatan</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Data jabatan disinkronkan dari SIMPEG — tidak diedit manual.</p>
                    </div>
                    @if(!empty($user->nik))
                        <a href="{{ route('admin.simpeg.index', ['nik' => $user->nik, 'target_user_id' => $user->id, 'return_url' => url()->current()]) }}"
                           class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Sinkron dengan SIMPEG
                        </a>
                    @else
                        <span class="shrink-0 inline-flex items-center px-3 py-1.5 bg-gray-300 text-gray-500 text-xs font-semibold rounded cursor-not-allowed" title="NIK user belum diisi">
                            Sinkron dengan SIMPEG
                        </span>
                    @endif
                </div>

                @if($user->jabatan)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm mt-3">
                        <div>
                            <dt class="text-xs text-gray-500">Nama Jabatan</dt>
                            <dd class="text-gray-800">{{ $user->jabatan->nama_jabatan ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Eselon</dt>
                            <dd class="text-gray-800">{{ $user->jabatan->eselon ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">TMT Jabatan</dt>
                            <dd class="text-gray-800">
                                {{ $user->jabatan->tmt_jabatan ? \Carbon\Carbon::parse($user->jabatan->tmt_jabatan)->format('d M Y') : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Unit Kerja (dari Jabatan)</dt>
                            <dd class="text-gray-800">
                                {{ $user->jabatan->unitKerja->nama ?? ($user->jabatan->unit_kerja_legacy ?: '—') }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-2 text-sm text-gray-600 italic">
                        Belum ada data jabatan.
                        @if(!empty($user->nik))
                            Klik tombol <strong>Sinkron dengan SIMPEG</strong> di atas untuk mengisi otomatis dari SIMPEG.
                        @else
                            Isi NIK user dulu (dan simpan), lalu gunakan tombol Sinkron SIMPEG.
                        @endif
                    </p>
                @endif
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
