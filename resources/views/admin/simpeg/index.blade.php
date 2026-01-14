@extends('layouts.authenticated')

@section('title', '- Cek via SIMPEG')
@section('header-title', 'Cek via SIMPEG')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-semibold mb-4">Cek via SIMPEG</h1>

        {{-- Flash: error merah (gangguan layanan) --}}
        @if (session('flash_error'))
            <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                {{ session('flash_error') }}
            </div>
        @endif

        {{-- Flash: warning kuning (data tidak ditemukan) --}}
        @if (session('flash_warning'))
            <div class="mb-4 rounded border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                {{ session('flash_warning') }}
            </div>
        @endif

        {{-- Flash: success hijau (opsional kalau dipakai) --}}
        @if (session('flash_success'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('flash_success') }}
            </div>
        @endif

        {{-- Form input NIK --}}
        <form method="POST" action="{{ route('admin.simpeg.check') }}" class="bg-white rounded shadow p-4 mb-6">
            @csrf
            <label class="block text-sm font-medium mb-1" for="nik">NIK</label>
            <div class="flex gap-2">
                <input id="nik" type="text" name="nik" value="{{ old('nik', $input_nik ?? '') }}"
                    class="w-full border rounded px-3 py-2" inputmode="numeric" pattern="\d{16}"
                    placeholder="Masukkan 16 digit NIK" required>
                <button type="button"
                    onclick="document.getElementById('nik').value=''; document.getElementById('nik').focus();"
                    class="px-3 py-2 border rounded text-sm hover:bg-gray-50">
                    Bersihkan
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Contoh: 6471xxxxxxxxxxxx (16 digit angka)</p>
            @error('nik')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            <button class="mt-3 inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded">
                Cek
            </button>
        </form>

        {{-- Hasil cek --}}
        @if (!is_null($result))
            @php
                $isValid = (bool) ($result['is_valid'] ?? false);
            @endphp

            <div class="bg-white rounded shadow p-4 mb-6">
                <h2 class="text-lg font-semibold mb-3">Hasil</h2>

                {{-- Alert bila NIK tidak terdata --}}
                @unless ($isValid)
                    <div class="mb-3 rounded border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                        NIK tidak terdata di SIMPEG.
                    </div>
                @endunless

                {{-- Alert bila NIP berhasil disimpan --}}
                @if ($isValid && ($result['nip_saved'] ?? false))
                    <div class="mb-3 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        <strong>✓ NIP berhasil disimpan</strong> ke data user ({{ $result['user']->name ?? 'N/A' }}) dengan NIP: <strong>{{ $result['nip'] }}</strong>
                    </div>
                @endif

                <ul class="space-y-2">
                    <li class="flex items-center gap-2">
                        <span class="font-medium">NIK valid di SIMPEG:</span>
                        @if ($isValid)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-green-800 bg-green-100 border border-green-200 text-xs font-semibold">Ya</span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-red-800 bg-red-100 border border-red-200 text-xs font-semibold">Tidak</span>
                        @endif
                    </li>
                    <li>
                        <span class="font-medium">NIP (dari SIMPEG):</span>
                        <span class="text-gray-800">{{ $result['nip'] ?? '—' }}</span>
                    </li>

                    {{-- Detail kecocokan hanya relevan kalau valid --}}
                    @if ($isValid)
                        <li>
                            <span class="font-medium">Nama (dari SIMPEG):</span>
                            <span class="text-gray-800">{{ $result['nama'] ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="font-medium">Nomor HP (dari SIMPEG):</span>
                            <span class="text-gray-800">{{ $result['telepon'] ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="font-medium">Email (dari SIMPEG):</span>
                            <span class="text-gray-800">{{ $result['email'] ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="font-medium">Jabatan (dari SIMPEG):</span>
                            <span class="text-gray-800">{{ $result['jabatan'] ?? '—' }}</span>
                        </li>
                        @if(!empty($result['golongan']))
                        <li>
                            <span class="font-medium">Golongan (dari SIMPEG):</span>
                            <span class="text-gray-800">{{ $result['golongan'] }}</span>
                        </li>
                        @endif
                        @if(!empty($result['instansi']))
                        <li>
                            <span class="font-medium">Instansi (dari SIMPEG):</span>
                            <span class="text-gray-800">{{ $result['instansi'] }}</span>
                            @if(!empty($result['matched_unit_kerja']))
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-green-800 bg-green-100 border border-green-200 text-xs font-semibold">
                                    ✓ Cocok: {{ $result['matched_unit_kerja']->nama }}
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-yellow-800 bg-yellow-100 border border-yellow-200 text-xs font-semibold">
                                    ⚠ Tidak ditemukan di master
                                </span>
                            @endif
                        </li>
                        @endif

                        <li class="pt-3 border-t border-gray-200">
                            <span class="font-medium">Kecocokan dengan Data Lokal:</span>
                        </li>
                        <li class="{{ $result['name_ok'] ?? false ? 'text-green-700' : 'text-yellow-700' }} pl-4">
                            <span class="font-medium">Nama:</span>
                            {{ $result['name_ok'] ?? false ? '✓ Sesuai' : '⚠ Tidak sesuai' }}
                            @if (!empty($result['user']))
                                <div class="text-xs text-gray-600 mt-0.5">
                                    Nama Lokal: <span class="font-medium">{{ $result['user']->name }}</span>
                                </div>
                            @endif
                        </li>
                        <li class="{{ $result['phone_ok'] ?? false ? 'text-green-700' : 'text-yellow-700' }} pl-4">
                            <span class="font-medium">Nomor HP:</span>
                            {{ $result['phone_ok'] ?? false ? '✓ Sesuai' : '⚠ Tidak sesuai' }}
                            @if (!empty($result['user']) && !empty($result['user']->phone))
                                <div class="text-xs text-gray-600 mt-0.5">
                                    HP Lokal: <span class="font-medium">{{ $result['user']->phone }}</span>
                                </div>
                            @endif
                        </li>
                        <li class="{{ $result['email_ok'] ?? false ? 'text-green-700' : 'text-yellow-700' }} pl-4">
                            <span class="font-medium">Email:</span>
                            {{ $result['email_ok'] ?? false ? '✓ Sesuai' : '⚠ Tidak sesuai' }}
                            @if (!empty($result['user']))
                                <div class="text-xs text-gray-600 mt-0.5">
                                    Email Lokal: <span class="font-medium">{{ $result['user']->email }}</span>
                                </div>
                            @endif
                        </li>
                    @endif
                </ul>

                {{-- Form untuk simpan manual ke user --}}
                @if ($isValid && !empty($result['user']))
                    <div class="mt-6 pt-4 border-t border-gray-300">
                        <h3 class="text-md font-semibold mb-3">Simpan Data ke User</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            Pilih data yang ingin disimpan ke akun: <strong>{{ $result['user']->name }}</strong> ({{ $result['user']->email }})
                        </p>

                        <form method="POST" action="{{ route('admin.simpeg.saveToUser') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $result['user']->id }}">
                            <input type="hidden" name="nik" value="{{ $input_nik ?? '' }}">
                            @if(!empty($result['instansi']))
                                <input type="hidden" name="instansi_simpeg" value="{{ $result['instansi'] }}">
                            @endif

                            <div class="space-y-2 mb-4">
                                @if(!empty($result['nip']))
                                <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="fields[]" value="nip" class="mt-1">
                                    <div class="flex-1">
                                        <span class="font-medium">NIP:</span>
                                        <span class="text-gray-700">{{ $result['nip'] }}</span>
                                        <input type="hidden" name="nip" value="{{ $result['nip'] }}">
                                        @if(!empty($result['user']->nip))
                                            <span class="text-xs text-gray-500 block">(Saat ini: {{ $result['user']->nip }})</span>
                                        @endif
                                    </div>
                                </label>
                                @endif

                                @if(!empty($result['nama']))
                                <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="fields[]" value="name" class="mt-1">
                                    <div class="flex-1">
                                        <span class="font-medium">Nama:</span>
                                        <span class="text-gray-700">{{ $result['nama'] }}</span>
                                        <input type="hidden" name="nama" value="{{ $result['nama'] }}">
                                        <span class="text-xs text-gray-500 block">(Saat ini: {{ $result['user']->name }})</span>
                                    </div>
                                </label>
                                @endif

                                @if(!empty($result['telepon']))
                                <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="fields[]" value="phone" class="mt-1">
                                    <div class="flex-1">
                                        <span class="font-medium">Nomor HP:</span>
                                        <span class="text-gray-700">{{ $result['telepon'] }}</span>
                                        <input type="hidden" name="telepon" value="{{ $result['telepon'] }}">
                                        @if(!empty($result['user']->phone))
                                            <span class="text-xs text-gray-500 block">(Saat ini: {{ $result['user']->phone }})</span>
                                        @endif
                                    </div>
                                </label>
                                @endif

                                @if(!empty($result['email']))
                                <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="fields[]" value="email" class="mt-1">
                                    <div class="flex-1">
                                        <span class="font-medium">Email:</span>
                                        <span class="text-gray-700">{{ $result['email'] }}</span>
                                        <input type="hidden" name="email_simpeg" value="{{ $result['email'] }}">
                                        <span class="text-xs text-gray-500 block">(Saat ini: {{ $result['user']->email }})</span>
                                    </div>
                                </label>
                                @endif

                                @if(!empty($result['jabatan']))
                                <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="fields[]" value="jabatan" class="mt-1">
                                    <div class="flex-1">
                                        <span class="font-medium">Jabatan:</span>
                                        <span class="text-gray-700">{{ $result['jabatan'] }}</span>
                                        <input type="hidden" name="jabatan" value="{{ $result['jabatan'] }}">
                                        @if(!empty($result['user']->jabatan))
                                            <span class="text-xs text-gray-500 block">(Saat ini: {{ $result['user']->jabatan }})</span>
                                        @endif
                                    </div>
                                </label>
                                @endif
                            </div>

                            {{-- Unit Kerja Selection --}}
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                <label class="flex items-start gap-2">
                                    <input type="checkbox" name="fields[]" value="unit_kerja" class="mt-1" id="unit_kerja_checkbox">
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">Unit Kerja / Instansi</span>
                                        @if(!empty($result['instansi']))
                                            <div class="text-sm text-gray-600 mt-1">
                                                Dari SIMPEG: <strong>{{ $result['instansi'] }}</strong>
                                            </div>
                                        @endif
                                        @if(!empty($result['user']->unitKerja))
                                            <div class="text-xs text-gray-500 mt-1">
                                                Saat ini: {{ $result['user']->unitKerja->nama }}
                                            </div>
                                        @endif
                                    </div>
                                </label>

                                <div class="mt-3">
                                    <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Pilih Unit Kerja
                                    </label>
                                    <select name="unit_kerja_id" id="unit_kerja_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Pilih Unit Kerja --</option>
                                        @foreach($unitKerjas as $uk)
                                            <option value="{{ $uk->id }}"
                                                    {{ (!empty($result['matched_unit_kerja']) && $result['matched_unit_kerja']->id == $uk->id) ? 'selected' : '' }}>
                                                {{ $uk->nama }} ({{ $uk->tipe }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @if(!empty($result['matched_unit_kerja']))
                                            ✓ Auto-selected berdasarkan data SIMPEG. Anda dapat mengubahnya jika perlu.
                                        @else
                                            Pilih unit kerja yang sesuai untuk user ini.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium">
                                Simpan ke User
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        {{-- 10 log terakhir --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Riwayat (10 terakhir)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2">Waktu</th>
                            <th>NIK</th>
                            <th>NIP</th>
                            <th>Nama (SIMPEG)</th>
                            <th>Nama</th>
                            <th>HP</th>
                            <th>Email</th>
                            <th>Pencetak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="border-b">
                                <td class="py-2">{{ $log->created_at }}</td>
                                <td class="font-mono">{{ $log->nik }}</td>
                                <td class="font-mono">{{ $log->nip ?? '—' }}</td>
                                <td>{{ $log->name_from_simpeg ?? '—' }}</td>
                                <td>
                                    {!! $log->name_match
                                        ? '<span class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-800 border border-green-200 text-xs font-semibold">✓</span>'
                                        : '<span class="inline-block px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 border border-yellow-200 text-xs font-semibold">⚠</span>' !!}
                                </td>
                                <td>
                                    {!! $log->phone_match
                                        ? '<span class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-800 border border-green-200 text-xs font-semibold">✓</span>'
                                        : '<span class="inline-block px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 border border-yellow-200 text-xs font-semibold">⚠</span>' !!}
                                </td>
                                <td>
                                    {!! $log->email_match
                                        ? '<span class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-800 border border-green-200 text-xs font-semibold">✓</span>'
                                        : '<span class="inline-block px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 border border-yellow-200 text-xs font-semibold">⚠</span>' !!}
                                </td>
                                <td>{{ optional($log->createdBy)->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-3 text-center text-gray-500">Belum ada log</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-check unit_kerja checkbox if there's a matched unit kerja
        @if(!is_null($result ?? null) && !empty($result['matched_unit_kerja']))
            const unitKerjaCheckbox = document.getElementById('unit_kerja_checkbox');
            if (unitKerjaCheckbox) {
                unitKerjaCheckbox.checked = true;
            }
        @endif
    });
</script>
@endpush
