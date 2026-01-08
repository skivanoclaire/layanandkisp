@extends('layouts.authenticated')

@section('title', '- Kelola Perubahan Nama Subdomain')
@section('header-title', 'Kelola Perubahan Nama Subdomain')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Kelola Permohonan Perubahan Nama Subdomain</h1>
            <p class="text-gray-600 mt-2">Kelola dan proses permohonan perubahan nama subdomain dari pengguna</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="text-yellow-600 text-sm font-semibold">Menunggu</div>
                <div class="text-2xl font-bold text-yellow-800">{{ $stats['pending'] }}</div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="text-blue-600 text-sm font-semibold">Disetujui</div>
                <div class="text-2xl font-bold text-blue-800">{{ $stats['approved'] }}</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="text-green-600 text-sm font-semibold">Selesai</div>
                <div class="text-2xl font-bold text-green-800">{{ $stats['completed'] }}</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-red-600 text-sm font-semibold">Ditolak</div>
                <div class="text-2xl font-bold text-red-800">{{ $stats['rejected'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="mb-6 flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Cari tiket, subdomain, atau pemohon..."
                    value="{{ request('search') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                Filter
            </button>
            <a href="{{ route('admin.subdomain.name-change.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                Reset
            </a>
        </form>

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

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tiket</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Pemohon</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Lama</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Baru</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($requests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono">{{ $request->ticket_number }}</td>
                            <td class="px-4 py-3 text-sm">{{ $request->user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $request->old_subdomain_name }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-purple-700">{{ $request->new_subdomain_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if ($request->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                                    @elseif($request->status == 'completed') bg-green-100 text-green-800
                                    @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $request->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.subdomain.name-change.show', $request->id) }}"
                                    class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data permohonan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    </div>
@endsection
