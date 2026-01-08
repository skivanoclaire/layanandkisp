@extends('layouts.authenticated')

@section('title', '- Perubahan IP Subdomain')
@section('header-title', 'Perubahan IP Subdomain')

@section('content')
    <div class="bg-blue-100 p-6 rounded-lg shadow border border-blue-200 mb-6">
        <h2 class="text-2xl font-bold text-blue-800 mb-2">Permohonan Perubahan IP Subdomain</h2>
        <p>Kelola permohonan perubahan IP address untuk subdomain Anda yang sudah terdaftar.</p>
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
            <a href="{{ route('user.subdomain.ip-change.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Perubahan IP Baru
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
                                <th class="px-4 py-2 text-left">Subdomain</th>
                                <th class="px-4 py-2 text-left">IP Lama</th>
                                <th class="px-4 py-2 text-left">IP Baru</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono text-sm">{{ $request->ticket_number }}</td>
                                    <td class="px-4 py-2">{{ $request->subdomain_name }}</td>
                                    <td class="px-4 py-2 font-mono text-sm">{{ $request->old_ip_address }}</td>
                                    <td class="px-4 py-2 font-mono text-sm">{{ $request->new_ip_address }}</td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium
                                            @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                                            @elseif($request->status == 'completed') bg-green-100 text-green-800
                                            @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if ($request->status == 'pending')
                                                Menunggu
                                            @elseif($request->status == 'approved')
                                                Disetujui
                                            @elseif($request->status == 'completed')
                                                Selesai
                                            @elseif($request->status == 'rejected')
                                                Ditolak
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $request->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('user.subdomain.ip-change.show', $request->id) }}"
                                            class="text-blue-600 hover:text-blue-800 underline text-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada permohonan</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan mengajukan permohonan perubahan IP pertama Anda.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- JavaScript untuk auto hide alert -->
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100.border-green-400, .bg-red-100.border-red-400');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
@endsection
