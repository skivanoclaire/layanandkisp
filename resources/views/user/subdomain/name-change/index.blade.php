@extends('layouts.authenticated')

@section('title', '- Perubahan Nama Subdomain')
@section('header-title', 'Perubahan Nama Subdomain')

@section('content')
    <div class="bg-purple-100 p-6 rounded-lg shadow border border-purple-200 mb-6">
        <h2 class="text-2xl font-bold text-purple-800 mb-2">Permohonan Perubahan Nama Subdomain</h2>
        <p class="text-purple-700">Kelola permohonan perubahan nama subdomain Anda yang sudah terdaftar.</p>
        <p class="text-sm text-purple-600 mt-2">
            <strong>Perhatian:</strong> Perubahan nama subdomain akan mempengaruhi semua link dan akses ke website Anda.
            Pastikan Anda telah menginformasikan perubahan ini kepada semua pengguna.
        </p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Action Button -->
        <div class="mb-6">
            <a href="{{ route('user.subdomain.name-change.create') }}"
                class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Perubahan Nama Baru
            </a>
        </div>

        <!-- Requests Table -->
        <div class="bg-gray-50 p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Daftar Permohonan Anda</h2>

            @if ($requests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2 text-left">Nomor Tiket</th>
                                <th class="px-4 py-2 text-left">Nama Lama</th>
                                <th class="px-4 py-2 text-left">Nama Baru</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono text-sm">{{ $request->ticket_number }}</td>
                                    <td class="px-4 py-2">
                                        <span class="text-gray-600">{{ $request->old_subdomain_name }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="font-semibold text-purple-700">{{ $request->new_subdomain_name }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                                            @elseif($request->status == 'completed') bg-green-100 text-green-800
                                            @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if ($request->status == 'pending') Menunggu
                                            @elseif($request->status == 'approved') Disetujui
                                            @elseif($request->status == 'completed') Selesai
                                            @elseif($request->status == 'rejected') Ditolak
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $request->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('user.subdomain.name-change.show', $request->id) }}"
                                            class="text-purple-600 hover:text-purple-800 font-medium">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg font-semibold">Belum ada permohonan perubahan nama subdomain</p>
                    <p class="mt-2">Klik tombol di atas untuk mengajukan permohonan baru</p>
                </div>
            @endif
        </div>
    </div>
@endsection
