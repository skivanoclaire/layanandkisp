@extends('layouts.authenticated')

@section('title', '- Laporan Pemanfaatan DTSEN')
@section('header-title', 'Laporan Pemanfaatan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Pemanfaatan Data</h1>
            <p class="text-gray-600 mt-1">Tahap 5 — pelaporan periodik minimal satu kali per enam bulan.</p>
        </div>
        @if ($reportable->isNotEmpty())
            <a href="{{ route('user.dtsen.pemanfaatan.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Buat Laporan</a>
        @endif
    </div>

    @include('partials.dtsen.errors')

    {{-- Pengingat jatuh tempo pelaporan per permohonan --}}
    @php
        $jatuhTempo = $reportable->map(function ($r) {
            return ['request' => $r, 'due' => \App\Models\DtsenUtilizationReport::nextDueFor($r)];
        })->filter(fn ($row) => $row['due'] && $row['due']->isPast());
    @endphp
    @if ($jatuhTempo->isNotEmpty())
        <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 p-4 text-sm text-orange-900">
            <p class="font-semibold">Pelaporan jatuh tempo</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach ($jatuhTempo as $row)
                    <li>
                        <span class="font-mono">{{ $row['request']->ticket_no }}</span> — {{ $row['request']->nama_program }}
                        (sejak {{ $row['due']->format('d/m/Y') }})
                        <a href="{{ route('user.dtsen.pemanfaatan.create', ['permohonan' => $row['request']->id]) }}" class="underline font-semibold ml-1">Lapor sekarang</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($reportable->isEmpty())
        <div class="mb-6 rounded-lg border border-gray-300 bg-gray-50 p-4 text-sm text-gray-700">
            Belum ada permohonan yang datanya telah diterima, sehingga belum ada yang perlu dilaporkan.
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Permohonan</th>
                    <th class="px-4 py-3 text-left">Periode</th>
                    <th class="px-4 py-3 text-left">Program/Kegiatan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->dataRequest->ticket_no ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->periode_mulai->format('d/m/Y') }} — {{ $item->periode_akhir->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $item->nama_program }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                            @if ($item->catatan_review)
                                <p class="text-xs text-gray-500 mt-1">{{ $item->catatan_review }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->file_path)
                                <a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">Lampiran</a>
                            @endif
                            @if ($item->status === \App\Models\DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN)
                                <a href="{{ route('user.dtsen.pemanfaatan.edit', $item->id) }}" class="text-orange-600 hover:underline ml-2">Perbaiki</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada laporan pemanfaatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
