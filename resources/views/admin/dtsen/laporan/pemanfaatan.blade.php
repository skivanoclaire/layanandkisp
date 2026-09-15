@extends('layouts.authenticated')

@section('title', '- Rekap Pemanfaatan DTSEN')
@section('header-title', 'Rekapitulasi Laporan Pemanfaatan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan Pemanfaatan Data</h1>
        <p class="text-gray-600 mt-1">Form 5.1 — rekapitulasi berjenjang oleh Bapperida (koordinator Forum SDD) &amp; DKISP (prosesor).</p>
    </div>

    @include('partials.dtsen.errors')

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        @foreach ([
            'Total Laporan' => $ringkasan['total'],
            'Menunggu Telaah' => $ringkasan['terkirim'],
            'Sudah Diverifikasi' => $ringkasan['diverifikasi'],
        ] as $label => $value)
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenUtilizationReport::statusLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Perangkat Daerah</label>
            <select name="unit_kerja_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach ($unitKerjaList as $uk)
                    <option value="{{ $uk->id }}" @selected((string) request('unit_kerja_id') === (string) $uk->id)>{{ $uk->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Periode Akhir Dari</label>
            <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
            <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.laporan.pemanfaatan') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="space-y-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">
                            <span class="font-mono text-blue-600">{{ $item->dataRequest->ticket_no ?? '-' }}</span>
                            — {{ $item->nama_program }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $item->dataRequest->unitKerja->nama ?? '-' }} ·
                            Periode {{ $item->periode_mulai->format('d/m/Y') }} — {{ $item->periode_akhir->format('d/m/Y') }} ·
                            Dilaporkan {{ $item->user->name ?? '-' }}, {{ $item->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                <div class="mt-3 text-sm space-y-2">
                    <div>
                        <p class="text-gray-500">Variabel yang dimanfaatkan</p>
                        @forelse ($item->variabelNames() as $nama)
                            <span class="inline-block bg-gray-100 rounded px-2 py-0.5 text-xs mr-1 mb-1">{{ $nama }}</span>
                        @empty
                            <span class="text-gray-400">-</span>
                        @endforelse
                    </div>
                    <div><p class="text-gray-500">Hasil Pemanfaatan</p><p class="whitespace-pre-line">{{ $item->hasil_pemanfaatan }}</p></div>
                    @if ($item->kendala)
                        <div><p class="text-gray-500">Kendala</p><p class="whitespace-pre-line">{{ $item->kendala }}</p></div>
                    @endif
                    @if ($item->file_path)
                        <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Dokumentasi pendukung</a>
                    @endif
                </div>

                <form action="{{ route('admin.dtsen.laporan.pemanfaatan.review', $item->id) }}" method="POST"
                      class="mt-4 flex flex-wrap items-end gap-2 border-t pt-4">
                    @csrf
                    <div class="flex-1 min-w-[240px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan Telaah</label>
                        <input type="text" name="catatan_review" value="{{ $item->catatan_review }}"
                               placeholder="Wajib bila dikembalikan untuk perbaikan"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <button name="status" value="diverifikasi"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Verifikasi</button>
                    <button name="status" value="perlu_perbaikan"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Kembalikan</button>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center text-gray-500">Belum ada laporan pemanfaatan.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
