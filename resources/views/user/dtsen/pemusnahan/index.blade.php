@extends('layouts.authenticated')

@section('title', '- Pemusnahan Data DTSEN')
@section('header-title', 'Pemusnahan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Berita Acara Pemusnahan Data</h1>
            <p class="text-gray-600 mt-1">Tahap 5 — pemusnahan data setelah masa retensi berakhir.</p>
        </div>
        @if ($reportable->isNotEmpty())
            <a href="{{ route('user.dtsen.pemusnahan.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Buat Berita Acara</a>
        @endif
    </div>

    @include('partials.dtsen.errors')

    @if ($jumlahTerlambat > 0)
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-900">
            <p class="font-semibold">Batas penyampaian terlampaui</p>
            <p class="mt-1">
                {{ $jumlahTerlambat }} berita acara masih berstatus draft padahal batas
                {{ \App\Models\DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI }} hari kalender sejak pemusnahan sudah lewat.
                Segera sampaikan ke DKISP.
            </p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Permohonan</th>
                    <th class="px-4 py-3 text-left">Waktu Pemusnahan</th>
                    <th class="px-4 py-3 text-left">Dasar</th>
                    <th class="px-4 py-3 text-left">Batas Penyampaian</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->dataRequest->ticket_no ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->waktu_pelaksanaan->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $item->dasar_label }}</td>
                        <td class="px-4 py-3">
                            {{ $item->batas_penyampaian?->format('d/m/Y') ?? '-' }}
                            @php($sisa = $item->daysUntilDeadline())
                            @if ($item->status === \App\Models\DtsenDestructionReport::STATUS_DRAFT && $sisa !== null)
                                <p class="text-xs {{ $sisa < 0 ? 'text-red-600 font-semibold' : 'text-orange-600' }}">
                                    {{ $sisa < 0 ? 'Terlambat ' . abs($sisa) . ' hari' : 'Sisa ' . $sisa . ' hari' }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                            @if ($item->catatan_verifikasi)
                                <p class="text-xs text-gray-500 mt-1">{{ $item->catatan_verifikasi }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if ($item->file_path)
                                <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">Berkas</a>
                            @endif
                            @if (in_array($item->status, [\App\Models\DtsenDestructionReport::STATUS_DRAFT, \App\Models\DtsenDestructionReport::STATUS_PERLU_PERBAIKAN], true))
                                <a href="{{ route('user.dtsen.pemusnahan.edit', $item->id) }}" class="text-orange-600 hover:underline ml-2">Ubah</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada berita acara pemusnahan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
