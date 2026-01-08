@extends('layouts.authenticated')

@section('title', '- Data Software Aset TIK')
@section('header-title', 'Data Software Aset TIK')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Data Software Aset TIK</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.google-aset-tik.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Dashboard</a>
            <a href="{{ route('admin.google-aset-tik.sync.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">Sinkronisasi</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.google-aset-tik.software.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">OPD</label>
                <select name="opd" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Semua OPD --</option>
                    @foreach($opdList as $opd)
                        <option value="{{ $opd }}" {{ request('opd') == $opd ? 'selected' : '' }}>{{ $opd }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Aktif</label>
                <select name="is_aktif" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif" {{ request('is_aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak Aktif" {{ request('is_aktif') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                <select name="jenis" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Semua Jenis --</option>
                    @foreach($jenisList as $jenis)
                        <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Judul/URL/Kode...">
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
                <a href="{{ route('admin.google-aset-tik.software.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama OPD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Database</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($software as $index => $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $software->firstItem() + $index }}</td>
                    <td class="px-6 py-4 text-sm">{{ $item->nama_opd }}</td>
                    <td class="px-6 py-4 text-sm">{{ Str::limit($item->judul, 40) }}</td>
                    <td class="px-6 py-4 text-sm"><a href="{{ $item->url }}" target="_blank" class="text-blue-600 hover:underline">{{ Str::limit($item->url, 30) }}</a></td>
                    <td class="px-6 py-4 text-sm">{{ $item->platform ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $item->database ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $item->is_aktif == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $item->is_aktif ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                        <a href="{{ route('admin.google-aset-tik.software.show', $item->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">Belum ada data software</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $software->links() }}</div>
</div>
@endsection
