@extends('layouts.authenticated')

@section('title', '- Rekap Pemusnahan DTSEN')
@section('header-title', 'Berita Acara Pemusnahan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Berita Acara Pemusnahan Data</h1>
        <p class="text-gray-600 mt-1">
            Form 5.2 — salinan berita acara wajib diterima DKISP maksimal
            {{ \App\Models\DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI }} hari kalender sejak pemusnahan.
        </p>
    </div>

    @include('partials.dtsen.errors')

    @if ($terlambat->isNotEmpty())
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-900">
            <p class="font-semibold">Melewati batas penyampaian ({{ $terlambat->count() }})</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach ($terlambat as $t)
                    <li>
                        <span class="font-mono">{{ $t->dataRequest->ticket_no ?? '-' }}</span> —
                        dimusnahkan {{ $t->waktu_pelaksanaan->format('d/m/Y') }},
                        batas {{ $t->batas_penyampaian?->format('d/m/Y') }} (masih berstatus draft di sisi OPD)
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenDestructionReport::statusLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.laporan.pemusnahan') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="space-y-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">
                            <span class="font-mono text-blue-600">{{ $item->dataRequest->ticket_no ?? '-' }}</span>
                            — {{ $item->dataRequest->nama_program ?? '' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $item->dataRequest->unitKerja->nama ?? '-' }} ·
                            Dimusnahkan {{ $item->waktu_pelaksanaan->format('d/m/Y H:i') }} ·
                            Batas penyampaian {{ $item->batas_penyampaian?->format('d/m/Y') ?? '-' }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                <dl class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div><dt class="text-gray-500">Dasar Pemusnahan</dt><dd>{{ $item->dasar_label }}</dd></div>
                    <div><dt class="text-gray-500">Petugas Pelaksana</dt><dd>{{ $item->petugas_nama }} @if($item->petugas_nip)({{ $item->petugas_nip }})@endif</dd></div>
                    <div><dt class="text-gray-500">Saksi</dt><dd>{{ $item->saksi_nama ?: '-' }} @if($item->saksi_unit)— {{ $item->saksi_unit }}@endif</dd></div>
                    <div><dt class="text-gray-500">Level Data</dt><dd>{{ $item->dataRequest?->level_label ?? '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Metode Pemusnahan</dt><dd class="whitespace-pre-line">{{ $item->metode_pemusnahan }}</dd></div>
                </dl>

                @if ($item->file_path)
                    <p class="mt-3 text-sm"><a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Berita acara bertanda tangan</a></p>
                @else
                    <p class="mt-3 text-sm text-orange-600">Berkas berita acara belum diunggah.</p>
                @endif

                @if ($item->status !== \App\Models\DtsenDestructionReport::STATUS_DRAFT)
                    <form action="{{ route('admin.dtsen.laporan.pemusnahan.verify', $item->id) }}" method="POST"
                          class="mt-4 flex flex-wrap items-end gap-2 border-t pt-4">
                        @csrf
                        <div class="flex-1 min-w-[240px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Catatan Verifikasi</label>
                            <input type="text" name="catatan_verifikasi" value="{{ $item->catatan_verifikasi }}"
                                   placeholder="Wajib bila dikembalikan"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <button name="status" value="diverifikasi"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Verifikasi</button>
                        <button name="status" value="perlu_perbaikan"
                                class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Kembalikan</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center text-gray-500">Belum ada berita acara pemusnahan.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
