@extends('layouts.authenticated')
@section('title', '- Kelola Laporan Gangguan Internet')
@section('header-title', 'Kelola Laporan Gangguan Internet')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-purple-700">Kelola Laporan Gangguan Internet</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Tabs -->
    <div class="mb-4 flex gap-2 flex-wrap">
        <a href="{{ route('admin.internet.laporan-gangguan.index') }}"
           class="px-4 py-2 rounded-lg font-semibold {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Semua ({{ \App\Models\LaporanGangguan::count() }})
        </a>
        <a href="{{ route('admin.internet.laporan-gangguan.index', ['status' => 'menunggu']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'menunggu' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Menunggu ({{ \App\Models\LaporanGangguan::where('status', 'menunggu')->count() }})
        </a>
        <a href="{{ route('admin.internet.laporan-gangguan.index', ['status' => 'proses']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'proses' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Diproses ({{ \App\Models\LaporanGangguan::where('status', 'proses')->count() }})
        </a>
        <a href="{{ route('admin.internet.laporan-gangguan.index', ['status' => 'selesai']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Selesai ({{ \App\Models\LaporanGangguan::where('status', 'selesai')->count() }})
        </a>
        <a href="{{ route('admin.internet.laporan-gangguan.index', ['status' => 'ditolak']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'ditolak' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Ditolak ({{ \App\Models\LaporanGangguan::where('status', 'ditolak')->count() }})
        </a>
    </div>

    @if($laporans->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">Tidak ada laporan gangguan{{ $status ? ' dengan status ' . $status : '' }}.</p>
        </div>
    @else
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Pelapor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Permasalahan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($laporans as $laporan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm font-medium text-purple-600">{{ $laporan->ticket_no }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            {{ $laporan->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $laporan->nama }}</div>
                            <div class="text-gray-500 text-xs">{{ $laporan->nip }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $laporan->unitKerja->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ Str::limit($laporan->uraian_permasalahan, 50) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($laporan->status === 'menunggu')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                            @elseif($laporan->status === 'proses')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>
                            @elseif($laporan->status === 'selesai')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.internet.laporan-gangguan.show', $laporan->id) }}"
                               class="text-purple-600 hover:text-purple-900 font-medium">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $laporans->links() }}
        </div>
    @endif
</div>
@endsection
