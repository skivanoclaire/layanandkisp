@extends('layouts.authenticated')

@section('title', '- Lembar Validasi Model')
@section('header-title', 'Tata Kelola Keamanan: Lembar Validasi Model')

@push('styles')
    <style>
        @media print {
            aside, nav, header, .no-print { display: none !important; }
            .print-full { margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
        .lap th, .lap td { border: 1px solid #9ca3af; padding: 4px 6px; vertical-align: top; }
        .lap th { background: #f3f4f6; }
    </style>
@endpush

@section('content')
    @php $dokumenAcuan = config('tata_kelola_keamanan.dokumen_acuan'); @endphp
    <div class="container mx-auto px-4 py-6 print-full">
        <div class="no-print flex gap-2 mb-4">
            <a href="{{ route('admin.tata-kelola-keamanan.validasi', [$evaluasi] + ($sesi ? ['sesi' => $sesi->nomor] : [])) }}" class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">&larr; Kembali</a>
            <button onclick="window.print()" class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Cetak / simpan PDF</button>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 print-full text-sm">
            <h1 class="text-xl font-semibold text-gray-900">Lembar Validasi Model</h1>
            <p class="text-gray-600">{{ $evaluasi->nama }} · {{ $evaluasi->instansi }}
                @if ($sesi) · FGD sesi {{ $sesi->nomor }}{{ $sesi->tanggal ? ', '.$sesi->tanggal->translatedFormat('d F Y') : '' }}@endif
                @if ($evaluasi->is_simulasi) · <strong class="text-yellow-700">DATA SIMULASI</strong> @endif</p>

            <p class="mt-3 text-gray-800">Petunjuk: kolom Dokumen Pendukung diisi peneliti berdasarkan hasil verifikasi silang. Dalam FGD, peserta memberi tanda
                setuju (S) atau tidak setuju (TS) bahwa butir pada baris tersebut relevan dan dapat diterapkan di DKISP, lalu menuliskan saran bila ada.</p>

            <table class="lap w-full mt-3">
                <thead>
                    <tr><th>No</th><th>Praktik COBIT 2019</th><th>Klausul/Kontrol ISO/IEC 27001:2022</th><th>Ketentuan Regulasi</th><th>Dokumen Pendukung</th><th class="w-12">S/TS</th><th class="w-48">Saran</th></tr>
                </thead>
                <tbody>
                    @forelse ($butir as $i => $p)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $p->praktik->kode }} {{ $p->praktik->nama }}</td>
                            <td>{{ implode('; ', array_map([\App\Models\TataKelolaKeamanan\Pemetaan::class, 'labelIso'], $p->iso_items ?? [])) }}</td>
                            <td>{{ implode('; ', array_map(fn ($k) => \App\Services\TataKelolaKeamanan\Istilah::regulasi($k), $p->regulasi_items ?? [])) ?: '-' }}</td>
                            <td>
                                @forelse ($p->dokumen_acuan ?? [] as $k => $rujukan)
                                    <p>{{ $dokumenAcuan[$k] ?? $k }}{{ $rujukan ? ' ('.$rujukan.')' : '' }}</p>
                                @empty
                                    <p>Rancangan peneliti: {{ $p->catatan_verifikasi }}</p>
                                @endforelse
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-gray-500">Belum ada butir yang lolos verifikasi dokumen.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <p class="mt-6">Peserta FGD:</p>
            <table class="lap w-full mt-1">
                <thead><tr><th class="w-10">No</th><th>Nama</th><th>Jabatan</th><th class="w-40">Tanda tangan</th></tr></thead>
                <tbody>
                    @php $hadir = $sesi ? $sesi->respondenHadir() : collect(); @endphp
                    @foreach (range(1, max(5, $hadir->count())) as $n)
                        @php $r = $hadir->values()[$n - 1] ?? null; @endphp
                        <tr><td class="text-center">{{ $n }}</td><td>{{ $r?->user?->name }}</td><td>{{ $r?->jabatan }}</td><td class="h-8"></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
