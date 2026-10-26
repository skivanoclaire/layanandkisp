@extends('layouts.authenticated')

@section('title', '- Permohonan Pembaruan Data Arsitektur')
@section('header-title', 'Permohonan Pembaruan Data Arsitektur')

@php
    $kelasPermohonan = \App\Models\MasterArsitektur\Permohonan::class;
    $tab = $kelasPermohonan::LABEL_STATUS + ['semua' => 'Semua'];
@endphp

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Permohonan Pembaruan Data Arsitektur</h1>
        <p class="text-gray-600 mt-1">Usulan penambahan dan perubahan Master Data Arsitektur dari instansi. Data baru berubah setelah usulan disetujui.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="mb-6 border-b border-gray-200 overflow-x-auto">
        <nav class="flex gap-1 min-w-max">
            @foreach ($tab as $kunci => $label)
                <a href="{{ route('admin.arsitektur-permohonan.index', ['status' => $kunci]) }}"
                   class="px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 whitespace-nowrap
                          {{ $kunci === $status ? 'border-green-600 text-green-700 bg-green-50' : 'border-transparent text-gray-600 hover:text-green-700 hover:bg-gray-50' }}">
                    {{ $label }}
                    <span class="ml-1 inline-block px-2 py-0.5 rounded-full text-xs {{ $kunci === $status ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">{{ $kunci === 'semua' ? $jumlah->sum() : ($jumlah[$kunci] ?? 0) }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-3 py-2 text-left">Tanggal</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Unit Kerja</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Pemohon</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Usulan</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Entri</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Status</th>
                        <th class="border border-gray-300 px-3 py-2 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $p)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="border border-gray-300 px-3 py-2 whitespace-nowrap">{{ $p->created_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $p->unitKerja?->nama ?? '-' }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $p->user?->name ?? '-' }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $kelasPermohonan::LABEL_AKSI[$p->aksi] ?? $p->aksi }}<div class="text-xs text-gray-500">{{ $p->label_jenis }}</div></td>
                            <td class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">{{ $p->nama_entri }}</td>
                            <td class="border border-gray-300 px-3 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $kelasPermohonan::WARNA_STATUS[$p->status] ?? 'bg-gray-100 text-gray-800' }}">{{ $kelasPermohonan::LABEL_STATUS[$p->status] ?? $p->status }}</span>
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <a href="{{ route('admin.arsitektur-permohonan.show', $p) }}" class="text-green-700 hover:underline font-semibold">{{ $p->menunggu() ? 'Proses' : 'Lihat' }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="border border-gray-300 px-3 py-8 text-center text-gray-500">Tidak ada permohonan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $daftar->links() }}</div>
    </div>
</div>
@endsection
