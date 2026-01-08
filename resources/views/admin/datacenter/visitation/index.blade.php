@extends('layouts.authenticated')
@section('title', '- Kelola Kunjungan/Colocation')
@section('header-title', 'Kelola Kunjungan/Colocation')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-purple-700">Kelola Permohonan Kunjungan/Colocation</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-md p-4">
        <form method="GET" action="{{ route('admin.datacenter.visitation.index') }}" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
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
            <a href="{{ route('admin.datacenter.visitation.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
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
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Pemohon</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tujuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal</th>
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
                        {{ $item->nama }}<br>
                        <span class="text-xs text-gray-500">{{ $item->unitKerja->nama ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $item->tujuan_kunjungan }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->tanggal_kunjungan->format('d/m/Y') }}<br>
                        <span class="text-xs text-gray-500">{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item->status == 'menunggu')
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                        @elseif($item->status == 'disetujui')
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Disetujui</span>
                        @elseif($item->status == 'selesai')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Selesai</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Ditolak</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.datacenter.visitation.show', $item) }}"
                           class="text-purple-600 hover:text-purple-800 font-semibold">
                            Detail →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        Tidak ada permohonan kunjungan.
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
