@extends($layout)

@section('title', 'Cek via SIMPEG')

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
                        <li class="{{ $result['name_ok'] ?? false ? 'text-green-700' : 'text-yellow-700' }}">
                            <span class="font-medium">Nama sama dengan data lokal:</span>
                            {{ $result['name_ok'] ?? false ? 'Sesuai' : 'Tidak sesuai' }}
                            <div class="text-xs text-gray-600 mt-0.5">
                                Nama SIMPEG: <span class="font-medium">{{ $result['nama'] ?? '—' }}</span>
                                @if (!empty($result['user']))
                                    &middot; Nama Lokal: <span class="font-medium">{{ $result['user']->name }}</span>
                                @endif
                            </div>
                        </li>
                        <li class="{{ $result['phone_ok'] ?? false ? 'text-green-700' : 'text-yellow-700' }}">
                            <span class="font-medium">Nomor HP sesuai SIMPEG:</span>
                            {{ $result['phone_ok'] ?? false ? 'Sesuai' : 'Tidak sesuai' }}
                        </li>
                        <li class="{{ $result['email_ok'] ?? false ? 'text-green-700' : 'text-yellow-700' }}">
                            <span class="font-medium">Email sesuai SIMPEG:</span>
                            {{ $result['email_ok'] ?? false ? 'Sesuai' : 'Tidak sesuai' }}
                        </li>
                    @endif
                </ul>
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
