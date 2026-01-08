@extends('layouts.authenticated')
@section('title', '- Kelola Starlink Jelajah')
@section('header-title', 'Kelola Starlink Jelajah')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-purple-700">Kelola Permohonan Starlink Jelajah</h1>

        <!-- Service Toggle Button -->
        <button onclick="document.getElementById('toggleServiceModal').classList.remove('hidden')"
                class="px-4 py-2 rounded-lg font-semibold flex items-center gap-2 {{ $serviceSetting && $serviceSetting->is_active ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Layanan: {{ $serviceSetting && $serviceSetting->is_active ? 'AKTIF' : 'NONAKTIF' }}
        </button>
    </div>

    <!-- Service Status Banner -->
    @if($serviceSetting && !$serviceSetting->is_active)
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-red-700 font-semibold">Layanan Tidak Tersedia Sementara</p>
                <p class="text-red-700 text-sm mt-1">{{ $serviceSetting->inactive_reason ?? 'Layanan sedang dalam perbaikan atau digunakan untuk keperluan internal.' }}</p>
                @if($serviceSetting->updatedBy)
                <p class="text-red-600 text-xs mt-1">Dinonaktifkan oleh {{ $serviceSetting->updatedBy->name }} pada {{ $serviceSetting->updated_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Tabs -->
    <div class="mb-4 flex gap-2 flex-wrap">
        <a href="{{ route('admin.internet.starlink.index') }}"
           class="px-4 py-2 rounded-lg font-semibold {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Semua ({{ \App\Models\StarlinkRequest::count() }})
        </a>
        <a href="{{ route('admin.internet.starlink.index', ['status' => 'menunggu']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'menunggu' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Menunggu ({{ \App\Models\StarlinkRequest::where('status', 'menunggu')->count() }})
        </a>
        <a href="{{ route('admin.internet.starlink.index', ['status' => 'proses']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'proses' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Diproses ({{ \App\Models\StarlinkRequest::where('status', 'proses')->count() }})
        </a>
        <a href="{{ route('admin.internet.starlink.index', ['status' => 'selesai']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Disetujui ({{ \App\Models\StarlinkRequest::where('status', 'selesai')->count() }})
        </a>
        <a href="{{ route('admin.internet.starlink.index', ['status' => 'ditolak']) }}"
           class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'ditolak' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Ditolak ({{ \App\Models\StarlinkRequest::where('status', 'ditolak')->count() }})
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">Tidak ada permohonan Starlink{{ $status ? ' dengan status ' . $status : '' }}.</p>
        </div>
    @else
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Tanggal Pengajuan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Periode Kegiatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm font-medium text-purple-600">{{ $request->ticket_no }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            {{ $request->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $request->nama }}</div>
                            <div class="text-gray-500 text-xs">{{ $request->nip }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $request->unitKerja->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $request->tanggal_mulai->format('d/m/Y') }} - {{ $request->tanggal_selesai->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($request->status === 'menunggu')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                            @elseif($request->status === 'proses')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>
                            @elseif($request->status === 'selesai')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.internet.starlink.show', $request->id) }}"
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
            {{ $requests->links() }}
        </div>
    @endif
</div>

<!-- Toggle Service Modal -->
<div id="toggleServiceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            {{ $serviceSetting && $serviceSetting->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Layanan Starlink
        </h3>

        <form action="{{ route('admin.internet.starlink.toggle-service') }}" method="POST">
            @csrf
            <input type="hidden" name="is_active" value="{{ $serviceSetting && $serviceSetting->is_active ? '0' : '1' }}">

            @if(!$serviceSetting || !$serviceSetting->is_active)
                <p class="text-gray-700 mb-4">
                    Dengan mengaktifkan layanan, user akan dapat mengajukan permohonan Starlink Jelajah.
                </p>
                <input type="hidden" name="inactive_reason" value="">
            @else
                <p class="text-gray-700 mb-4">
                    Dengan menonaktifkan layanan, user tidak akan dapat mengajukan permohonan baru. Silakan berikan alasan penonaktifan:
                </p>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Alasan Penonaktifan: <span class="text-red-500">*</span>
                </label>
                <textarea name="inactive_reason"
                          rows="3"
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                          placeholder="Contoh: Unit sedang digunakan untuk kegiatan internal sampai tanggal..."></textarea>
            @endif

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="px-4 py-2 rounded-lg font-semibold {{ $serviceSetting && $serviceSetting->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white">
                    {{ $serviceSetting && $serviceSetting->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Layanan
                </button>
                <button type="button"
                        onclick="document.getElementById('toggleServiceModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg font-semibold bg-gray-300 hover:bg-gray-400 text-gray-800">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
