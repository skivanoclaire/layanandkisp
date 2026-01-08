@extends('layouts.authenticated')

@section('title', '- Kelola Data Vidcon')
@section('header-title', 'Kelola Data Vidcon')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Data Fasilitasi Video Konferensi</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.vidcon.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Ke Permohonan Vidcon
            </a>
            <a href="{{ route('admin.operators.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Kelola Data Operator
            </a>
            <a href="{{ route('admin.vidcon-data.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Tambah Data
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.vidcon-data.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instansi</label>
                <select name="unit_kerja_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Semua Instansi --</option>
                    @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}" {{ request('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                            {{ $uk->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
                <input type="text" name="platform" value="{{ request('platform') }}" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Zoom, YT, dll...">
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
                <a href="{{ route('admin.vidcon-data.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Reset</a>
                <a href="{{ route('admin.vidcon-data.export-excel', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Export Excel</a>
                <a href="{{ route('admin.vidcon-data.export-pdf', request()->query()) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Export PDF</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instansi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Kegiatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akun Zoom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operator</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($vidconData as $data)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $data->no }}</td>
                    <td class="px-6 py-4 text-sm">{{ $data->unitKerja->nama ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">{{ Str::limit($data->judul_kegiatan, 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        {{ $data->tanggal_mulai ? $data->tanggal_mulai->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $data->platform }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        @if($data->platform === 'Zoom' && $data->akun_zoom)
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">
                                Akun {{ $data->akun_zoom }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($data->operators->count() > 0)
                            @foreach($data->operators as $op)
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 mr-1 mb-1">
                                    {{ $op->name }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.vidcon-data.show', $data) }}" class="text-blue-600 hover:text-blue-900 mr-3">Lihat</a>
                        <a href="{{ route('admin.vidcon-data.edit', $data) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <form action="{{ route('admin.vidcon-data.destroy', $data) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $vidconData->links() }}
    </div>
</div>
@endsection
