@extends('layouts.authenticated')
@section('title', '- Permohonan VPS/VM')
@section('header-title', 'VPS/VM')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Riwayat Permohonan VPS/VM</h1>
        <a href="{{ route('user.datacenter.vps.create') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
            + Ajukan Permohonan
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-md p-4">
        <form method="GET" action="{{ route('user.datacenter.vps.index') }}" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Sedang Diproses</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor tiket atau nama..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                Filter
            </button>
            <a href="{{ route('user.datacenter.vps.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
                Reset
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-purple-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No. Tiket</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Spesifikasi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">IP Public</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($requests as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">
                        {{ $item->ticket_no }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <span class="font-semibold">{{ $item->vcpu }} vCPU</span>,
                        {{ $item->ram_gb }} GB RAM,
                        {{ $item->storage_gb }} GB Storage
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->ip_public ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item->status == 'menunggu')
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                        @elseif($item->status == 'proses')
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                        @elseif($item->status == 'selesai')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Selesai</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('user.datacenter.vps.show', $item) }}"
                           class="text-purple-600 hover:text-purple-800 font-semibold">
                            Detail →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        Belum ada permohonan VPS/VM.
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
