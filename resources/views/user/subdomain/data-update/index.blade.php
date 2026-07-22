@extends('layouts.authenticated')

@section('title', '- Pembaruan Data Subdomain')
@section('header-title', 'Pembaruan Data Subdomain')

@section('content')
    <div class="bg-green-100 p-6 rounded-lg shadow border border-green-200 mb-6">
        <h2 class="text-2xl font-bold text-green-800 mb-2">Permohonan Pembaruan Data Subdomain</h2>
        <p class="text-green-700">Perbarui data aplikasi (informasi aplikasi, teknologi, dan server) dari subdomain milik unit kerja Anda. Perubahan akan diterapkan setelah disetujui admin.</p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <div class="mb-6">
            <a href="{{ route('user.subdomain.data-update.create') }}"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Pembaruan Data
            </a>
        </div>

        <div class="bg-gray-50 p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Daftar Permohonan Anda</h2>

            @if ($requests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2 text-left">Nomor Tiket</th>
                                <th class="px-4 py-2 text-left">Subdomain</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $req)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono text-sm">{{ $req->ticket_number }}</td>
                                    <td class="px-4 py-2">{{ $req->webMonitor->subdomain ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @include('user.subdomain.data-update._status', ['status' => $req->status])
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 space-x-3">
                                        <a href="{{ route('user.subdomain.data-update.show', $req->id) }}"
                                            class="text-green-600 hover:text-green-800 font-medium">Detail</a>
                                        @if ($req->isEditable())
                                            <a href="{{ route('user.subdomain.data-update.edit', $req->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                        @endif
                                        @if ($req->hasBeritaAcara())
                                            <a href="{{ route('user.subdomain.data-update.berita-acara.download', $req->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium">Berita Acara</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="text-lg font-semibold">Belum ada permohonan pembaruan data</p>
                    <p class="mt-2">Klik tombol di atas untuk mengajukan permohonan baru</p>
                </div>
            @endif
        </div>
    </div>
@endsection
