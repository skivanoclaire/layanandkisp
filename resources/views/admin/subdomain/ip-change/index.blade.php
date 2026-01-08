@extends('layouts.authenticated')

@section('title', '- Kelola Perubahan IP Subdomain')
@section('header-title', 'Kelola Perubahan IP Subdomain')

@section('content')
    <!-- Header with Stats -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Permohonan Perubahan IP Subdomain</h1>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Menunggu</p>
                        <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="bg-yellow-200 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Disetujui</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $stats['approved'] }}</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Selesai</p>
                        <p class="text-2xl font-bold text-green-700">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="bg-green-200 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Ditolak</p>
                        <p class="text-2xl font-bold text-red-700">{{ $stats['rejected'] }}</p>
                    </div>
                    <div class="bg-red-200 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-700" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.subdomain.ip-change.index') }}" class="flex flex-wrap gap-3">
            <!-- Status Filter -->
            <div class="flex gap-2">
                <a href="{{ route('admin.subdomain.ip-change.index') }}"
                    class="px-4 py-2 border rounded-lg {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Semua
                </a>
                <a href="{{ route('admin.subdomain.ip-change.index', ['status' => 'pending']) }}"
                    class="px-4 py-2 border rounded-lg {{ request('status') == 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Menunggu
                </a>
                <a href="{{ route('admin.subdomain.ip-change.index', ['status' => 'approved']) }}"
                    class="px-4 py-2 border rounded-lg {{ request('status') == 'approved' ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Disetujui
                </a>
                <a href="{{ route('admin.subdomain.ip-change.index', ['status' => 'completed']) }}"
                    class="px-4 py-2 border rounded-lg {{ request('status') == 'completed' ? 'bg-green-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Selesai
                </a>
                <a href="{{ route('admin.subdomain.ip-change.index', ['status' => 'rejected']) }}"
                    class="px-4 py-2 border rounded-lg {{ request('status') == 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Ditolak
                </a>
            </div>

            <!-- Search -->
            <div class="flex-1 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari tiket, subdomain, IP, atau nama pemohon..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Cari
                </button>
                @if (request('search') || request('status'))
                    <a href="{{ route('admin.subdomain.ip-change.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

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

    <!-- Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if ($requests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">No. Tiket</th>
                            <th class="px-4 py-3 text-left font-semibold">Pemohon</th>
                            <th class="px-4 py-3 text-left font-semibold">Subdomain</th>
                            <th class="px-4 py-3 text-left font-semibold">IP Lama</th>
                            <th class="px-4 py-3 text-left font-semibold">IP Baru</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-sm">{{ $req->ticket_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $req->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $req->user->email }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $req->subdomain_name }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono text-sm text-red-600">{{ $req->old_ip_address }}</td>
                                <td class="px-4 py-3 font-mono text-sm text-green-600">{{ $req->new_ip_address }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                        @if ($req->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($req->status == 'approved') bg-blue-100 text-blue-800
                                        @elseif($req->status == 'completed') bg-green-100 text-green-800
                                        @elseif($req->status == 'rejected') bg-red-100 text-red-800 @endif">
                                        @if ($req->status == 'pending')
                                            Menunggu
                                        @elseif($req->status == 'approved')
                                            Disetujui
                                        @elseif($req->status == 'completed')
                                            Selesai
                                        @elseif($req->status == 'rejected')
                                            Ditolak
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $req->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.subdomain.ip-change.show', $req->id) }}"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada permohonan</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Tidak ada permohonan perubahan IP{{ request('status') ? ' dengan status ' . request('status') : '' }}.
                </p>
            </div>
        @endif
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
