@extends('layouts.authenticated')
@section('title', '- Pasang Baru Internet')
@section('header-title', 'Pasang Baru Internet')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-purple-700 mb-2">Pasang Baru Internet</h1>
            <p class="text-gray-600">Ajukan pemasangan jaringan internet baru untuk gedung, ruangan, atau titik kerja di lingkungan kantor.</p>
        </div>
        <a href="{{ route('user.internet.pasang-baru.create') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Permohonan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif

    @if($requests->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-600 mb-4">Belum ada permohonan pasang baru internet.</p>
            <a href="{{ route('user.internet.pasang-baru.create') }}"
               class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                Ajukan Permohonan Pertama
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No. Tiket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal Pengajuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Titik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">{{ $request->ticket_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ \Illuminate\Support\Str::limit($request->lokasi_pemasangan, 60) }}
                            @if($request->ruangan)
                                <div class="text-xs text-gray-500">Ruangan: {{ $request->ruangan }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->jumlah_titik }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($request->status)
                                @case('menunggu')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @break
                                @case('proses')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                                    @break
                                @case('selesai')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Terpasang</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('user.internet.pasang-baru.show', $request->id) }}"
                               class="text-purple-600 hover:text-purple-900 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
