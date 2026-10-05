@extends('layouts.authenticated')

@section('title', '- Kuesioner Kualitas Layanan Portal')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')

        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Kuesioner Kualitas Layanan Portal</h1>
                <p class="text-sm text-gray-600 mt-1">Instrumen E-GovQual (21 atribut) + 3 item kepuasan + kuesioner Kano. Responden dikenali hanya dari kode acak.</p>
            </div>
            <a href="{{ route('admin.kuesioner-portal.pratinjau') }}"
               class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                Pratinjau kuesioner
            </a>
        </div>

        {{-- Populasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-5">
                <p class="text-sm text-gray-500">Total akun (di luar pengelola)</p>
                <p class="text-3xl font-semibold text-gray-900 mt-1">{{ $populasi['total_akun'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5">
                <p class="text-sm text-gray-500">Populasi: akun pernah mengajukan permohonan</p>
                <p class="text-3xl font-semibold text-green-700 mt-1">{{ $populasi['akun_layak'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5">
                <p class="text-sm text-gray-500">Rekap per tanggal</p>
                <p class="text-lg font-semibold text-gray-900 mt-2">{{ now()->translatedFormat('d F Y H:i') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Pemakaian layanan (dasar penetapan populasi)</h2>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4">Layanan</th>
                        <th class="py-2 px-4 text-right">Jumlah permohonan</th>
                        <th class="py-2 px-4 text-right">Jumlah akun pemohon</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($populasi['per_layanan'] as $k => $v)
                        <tr>
                            <td class="py-2 pr-4">{{ $labelLayanan[$k] ?? $k }}</td>
                            <td class="py-2 px-4 text-right">{{ number_format($v['permohonan']) }}</td>
                            <td class="py-2 px-4 text-right">{{ number_format($v['akun']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-gray-500 mt-3">Satu akun dapat mengajukan lebih dari satu layanan, sehingga jumlah akun per layanan tidak dijumlahkan.
                Usulan rekomendasi aplikasi berstatus draf tidak dihitung.</p>
        </div>

        {{-- Periode --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Periode pengumpulan data</h2>
            @if ($periodes->isEmpty())
                <p class="text-sm text-gray-600">Belum ada periode. Buat periode di bawah ini.</p>
            @else
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="py-2 pr-4">Periode</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3 text-right">Selesai</th>
                            <th class="py-2 px-3 text-right">Belum selesai</th>
                            <th class="py-2 px-3 text-right">Menolak</th>
                            <th class="py-2 px-3 text-right">Tingkat respons</th>
                            <th class="py-2 pl-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($periodes as $p)
                            <tr>
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-gray-900">{{ $p->nama }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $p->dibuka_at?->translatedFormat('d M Y') ?? 'belum dibuka' }}
                                        @if ($p->ditutup_at) – {{ $p->ditutup_at->translatedFormat('d M Y') }} @endif
                                    </p>
                                </td>
                                <td class="py-3 px-3">
                                    @if ($p->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Dibuka</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">Ditutup</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">{{ $p->jumlah_selesai }}</td>
                                <td class="py-3 px-3 text-right">{{ $p->jumlah_belum_selesai }}</td>
                                <td class="py-3 px-3 text-right">{{ $p->jumlah_menolak }}</td>
                                <td class="py-3 px-3 text-right">
                                    {{ $populasi['akun_layak'] ? number_format($p->jumlah_selesai / $populasi['akun_layak'] * 100, 1, ',', '.') . '%' : '-' }}
                                </td>
                                <td class="py-3 pl-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.kuesioner-portal.responses', $p) }}" class="px-3 py-1.5 text-xs rounded border border-gray-300 hover:bg-gray-50">Respons</a>
                                        <a href="{{ route('admin.kuesioner-portal.analisis', $p) }}" class="px-3 py-1.5 text-xs rounded border border-gray-300 hover:bg-gray-50">Analisis</a>
                                        <a href="{{ route('admin.kuesioner-portal.export', $p) }}" class="px-3 py-1.5 text-xs rounded border border-gray-300 hover:bg-gray-50">Ekspor Excel</a>
                                        @if ($p->is_active)
                                            <form method="POST" action="{{ route('admin.kuesioner-portal.periode.tutup', $p) }}">
                                                @csrf
                                                <button class="px-3 py-1.5 text-xs rounded bg-red-600 text-white hover:bg-red-700">Tutup</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.kuesioner-portal.periode.buka', $p) }}">
                                                @csrf
                                                <button class="px-3 py-1.5 text-xs rounded bg-green-600 text-white hover:bg-green-700">Buka</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-xs text-gray-500 mt-3">Tingkat respons = respons selesai ÷ populasi saat ini. Hanya satu periode yang dapat dibuka sekaligus.</p>
            @endif

            <form method="POST" action="{{ route('admin.kuesioner-portal.periode.store') }}" class="mt-6 border-t pt-5 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama periode baru</label>
                    <input id="nama" name="nama" required maxlength="255" value="{{ old('nama') }}" placeholder="mis. Pengumpulan Data Tesis 2026"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @error('nama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (opsional)</label>
                    <input id="keterangan" name="keterangan" maxlength="2000" value="{{ old('keterangan') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Buat periode</button>
                </div>
            </form>
        </div>
    </div>
@endsection
