@extends('layouts.authenticated')
@section('title', '- Kelola Pasang Baru Internet')
@section('header-title', 'Kelola Pasang Baru Internet')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-purple-700">Kelola Permohonan Pasang Baru Internet</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <!-- Filter Tabs -->
    <div class="mb-4 flex gap-2 flex-wrap">
        <a href="{{ route('admin.internet.pasang-baru.index') }}"
           class="px-4 py-2 rounded-lg font-semibold {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Semua ({{ \App\Models\InternetInstallationRequest::count() }})
        </a>
        <a href="{{ route('admin.internet.pasang-baru.index', ['status' => 'menunggu']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'menunggu' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Menunggu ({{ \App\Models\InternetInstallationRequest::where('status', 'menunggu')->count() }})
        </a>
        <a href="{{ route('admin.internet.pasang-baru.index', ['status' => 'proses']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'proses' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Diproses ({{ \App\Models\InternetInstallationRequest::where('status', 'proses')->count() }})
        </a>
        <a href="{{ route('admin.internet.pasang-baru.index', ['status' => 'selesai']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Terpasang ({{ \App\Models\InternetInstallationRequest::where('status', 'selesai')->count() }})
        </a>
        <a href="{{ route('admin.internet.pasang-baru.index', ['status' => 'ditolak']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'ditolak' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Ditolak ({{ \App\Models\InternetInstallationRequest::where('status', 'ditolak')->count() }})
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">Tidak ada permohonan pasang baru{{ $status ? ' dengan status ' . $status : '' }}.</p>
        </div>
    @else
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Tanggal Pengajuan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Lokasi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Titik</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-purple-600">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $item->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $item->nama }}</div>
                            <div class="text-gray-500 text-xs">{{ $item->nip }} · {{ $item->unitKerja->nama ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ \Illuminate\Support\Str::limit($item->lokasi_pemasangan, 60) }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->jumlah_titik }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @switch($item->status)
                                @case('menunggu')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @break
                                @case('proses')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>
                                    @break
                                @case('selesai')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Terpasang</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.internet.pasang-baru.show', $item->id) }}"
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
