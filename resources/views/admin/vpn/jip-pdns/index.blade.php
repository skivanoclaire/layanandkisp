@extends('layouts.authenticated')
@section('title', '- Admin: Akses JIP PDNS')
@section('header-title', 'Admin: Akses JIP PDNS')

@section('content')
<div class="container mx-auto px-4 max-w-7xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-purple-700">Kelola Akses JIP PDNS</h1>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.vpn.jip-pdns.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari no. tiket, nama, atau NIP..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="min-w-[150px]">
                <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Proses</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.vpn.jip-pdns.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-mono text-purple-600 font-semibold">{{ $req->ticket_no }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $req->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $req->nip }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($req->is_kabupaten_kota)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-semibold">Kab/Kota</span>
                                    <div class="text-xs text-gray-600 mt-1">{{ $req->kabupaten_kota }}</div>
                                @else
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">Provinsi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $req->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($req->status === 'menunggu')
                                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                @elseif($req->status === 'proses')
                                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Proses</span>
                                @elseif($req->status === 'selesai')
                                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a href="{{ route('admin.vpn.jip-pdns.show', $req->id) }}"
                                   class="text-purple-600 hover:text-purple-900 font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada permohonan akses JIP PDNS.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
