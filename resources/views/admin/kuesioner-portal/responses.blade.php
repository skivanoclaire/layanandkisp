@extends('layouts.authenticated')

@section('title', '- Respons Kuesioner Portal')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@php
    $labelStatus = [
        \App\Models\KuesionerPortalResponse::STATUS_SELESAI => ['Selesai', 'bg-green-100 text-green-800'],
        \App\Models\KuesionerPortalResponse::STATUS_PERSETUJU => ['Belum selesai', 'bg-yellow-100 text-yellow-800'],
        \App\Models\KuesionerPortalResponse::STATUS_MENOLAK => ['Menolak', 'bg-gray-100 text-gray-700'],
    ];
@endphp

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')

        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <a href="{{ route('admin.kuesioner-portal.index') }}" class="text-sm text-green-700 hover:underline">← Kembali</a>
                <h1 class="text-2xl font-semibold text-gray-900 mt-1">Respons — {{ $periode->nama }}</h1>
                <p class="text-sm text-gray-600 mt-1">Respons bertanda merah dikeluarkan otomatis menurut aturan pra-pemrosesan (Subbab 3.3.2) atau oleh peneliti.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.kuesioner-portal.analisis', $periode) }}" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Analisis</a>
                <a href="{{ route('admin.kuesioner-portal.export', $periode) }}" class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Ekspor Excel</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs text-gray-500">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Perangkat daerah</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Dikirim</th>
                        <th class="py-3 px-4 text-right">Durasi</th>
                        <th class="py-3 px-4">Pra-pemrosesan</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($responses as $r)
                        @php
                            $alasan = $eksklusi['semua'][$r->kode_responden] ?? [];
                            $alasanKano = $eksklusi['kano'][$r->kode_responden] ?? null;
                        @endphp
                        <tr class="{{ $alasan ? 'bg-red-50' : '' }}">
                            <td class="py-3 px-4 font-mono">{{ $r->kode_responden }}</td>
                            <td class="py-3 px-4">{{ $r->unitKerja?->nama ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $labelStatus[$r->status][1] ?? '' }}">{{ $labelStatus[$r->status][0] ?? $r->status }}</span>
                            </td>
                            <td class="py-3 px-4">{{ $r->submitted_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                            <td class="py-3 px-4 text-right">{{ $r->durasi_detik !== null ? gmdate('H:i:s', $r->durasi_detik) : '-' }}</td>
                            <td class="py-3 px-4 text-xs">
                                @foreach ($alasan as $a)
                                    <p class="text-red-700">{{ $a }}</p>
                                @endforeach
                                @if ($alasanKano)
                                    <p class="text-yellow-700">Kano: {{ $alasanKano }}</p>
                                @endif
                                @if (! $alasan && ! $alasanKano && $r->status === \App\Models\KuesionerPortalResponse::STATUS_SELESAI)
                                    <span class="text-green-700">Dipakai</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($r->status === \App\Models\KuesionerPortalResponse::STATUS_SELESAI)
                                    <a href="{{ route('admin.kuesioner-portal.show', $r) }}" class="text-green-700 hover:underline">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 px-4 text-center text-gray-500">Belum ada respons pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
