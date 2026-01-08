@extends('layouts.authenticated')
@section('title', '- Kelola Permohonan Video Conference')
@section('header-title', 'Kelola Permohonan Video Conference')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-purple-700">Permohonan Video Conference</h1>
        <a href="{{ route('admin.vidcon-data.index') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Ke Data Fasilitasi Vidcon
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Status Filter -->
    <div class="mb-4 flex gap-2">
        <a href="{{ route('admin.vidcon.index') }}"
           class="px-4 py-2 rounded {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Semua
        </a>
        <a href="{{ route('admin.vidcon.index', ['status' => 'menunggu']) }}"
           class="px-4 py-2 rounded {{ request('status') == 'menunggu' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Menunggu
        </a>
        <a href="{{ route('admin.vidcon.index', ['status' => 'proses']) }}"
           class="px-4 py-2 rounded {{ request('status') == 'proses' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Diproses
        </a>
        <a href="{{ route('admin.vidcon.index', ['status' => 'selesai']) }}"
           class="px-4 py-2 rounded {{ request('status') == 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Selesai
        </a>
        <a href="{{ route('admin.vidcon.index', ['status' => 'ditolak']) }}"
           class="px-4 py-2 rounded {{ request('status') == 'ditolak' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Ditolak
        </a>
    </div>

    <!-- Date Filter & Export -->
    <div class="bg-white rounded shadow p-4 mb-4">
        <form method="GET" action="{{ route('admin.vidcon.index') }}" class="space-y-4">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="dari_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" id="dari_tanggal" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div>
                    <label for="sampai_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" id="sampai_tanggal" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-md">
                        Filter
                    </button>
                    <a href="{{ route('admin.vidcon.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-md">
                        Reset
                    </a>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.vidcon.export-excel', request()->query()) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </a>
                    <a href="{{ route('admin.vidcon.export-pdf', request()->query()) }}"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if($items->count() === 0)
        <div class="bg-white rounded shadow p-6 text-center text-gray-500">
            Tidak ada permohonan video conference
            @if($status)
                dengan status <strong>{{ $status }}</strong>
            @endif
        </div>
    @else
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Tiket</th>
                        <th class="px-3 py-2 text-left">Pemohon</th>
                        <th class="px-3 py-2 text-left">Judul Kegiatan</th>
                        <th class="px-3 py-2 text-left">Tanggal</th>
                        <th class="px-3 py-2 text-left">Platform</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Diajukan</th>
                        <th class="px-3 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono">{{ $it->ticket_no }}</td>
                        <td class="px-3 py-2">
                            {{ $it->nama }}<br>
                            <span class="text-xs text-gray-600">{{ $it->user->email }}</span>
                        </td>
                        <td class="px-3 py-2">{{ $it->judul_kegiatan }}</td>
                        <td class="px-3 py-2">
                            {{ $it->tanggal_mulai->format('d/m/Y') }}
                            @if($it->tanggal_mulai->format('Y-m-d') !== $it->tanggal_selesai->format('Y-m-d'))
                                <br>- {{ $it->tanggal_selesai->format('d/m/Y') }}
                            @endif
                            <br><span class="text-xs text-gray-600">{{ $it->jam_mulai }} - {{ $it->jam_selesai }}</span>
                        </td>
                        <td class="px-3 py-2">{{ $it->platform_display }}</td>
                        <td class="px-3 py-2">
                            {!! $it->status_badge !!}
                            @if($it->status === 'proses' && $it->isStale())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-1">
                                    {{ $it->daysSinceLastUpdate() }}d
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-sm">{{ $it->submitted_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('admin.vidcon.show', $it->id) }}"
                               class="px-3 py-1 rounded bg-purple-600 hover:bg-purple-700 text-white text-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
