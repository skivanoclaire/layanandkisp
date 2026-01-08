@extends('layouts.authenticated')
@section('title', '- Pendampingan TTE')
@section('header-title', 'Pendampingan TTE')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Permohonan Pendampingan Aktivasi dan Penggunaan TTE</h1>
        <a href="{{ route('user.tte.assistance.create') }}"
            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
            + Ajukan Permohonan
        </a>
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
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-purple-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No. Tiket</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Waktu Pendampingan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal Pengajuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($requests as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">
                        {{ $item->ticket_no }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->waktu_pendampingan->format('d/m/Y H:i') }} WITA
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $item->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('user.tte.assistance.show', $item) }}"
                            class="text-purple-600 hover:text-purple-800 font-semibold">
                            Detail →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        Belum ada permohonan pendampingan TTE.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $requests->links() }}
    </div>
</div>
@endsection
