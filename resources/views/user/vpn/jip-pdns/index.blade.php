@extends('layouts.authenticated')
@section('title', '- Akses JIP PDNS')
@section('header-title', 'Akses JIP PDNS')

@section('content')
<div class="container mx-auto px-4 max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Daftar Permohonan Akses JIP PDNS</h1>
        <a href="{{ route('user.vpn.jip-pdns.create') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
            + Ajukan Permohonan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal Ajuan</th>
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
                                @if($req->is_kabupaten_kota)
                                    <span class="text-sm">Kab/Kota: {{ $req->kabupaten_kota }}</span>
                                @else
                                    <span class="text-sm">Provinsi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $req->created_at->format('d/m/Y H:i') }} WITA</td>
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
                                <a href="{{ route('user.vpn.jip-pdns.show', $req->id) }}"
                                   class="text-purple-600 hover:text-purple-900 font-semibold">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Belum ada permohonan akses JIP PDNS.
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
