@extends('layouts.authenticated')

@section('title', '- Pembaruan Data Subdomain')
@section('header-title', 'Pembaruan Data Subdomain')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
                <p class="text-sm text-yellow-800">Menunggu</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-orange-700">{{ $stats['revisi'] }}</p>
                <p class="text-sm text-orange-800">Revisi</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-700">{{ $stats['disetujui'] }}</p>
                <p class="text-sm text-green-800">Disetujui</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-red-700">{{ $stats['ditolak'] }}</p>
                <p class="text-sm text-red-800">Ditolak</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua</option>
                    @foreach (['pending' => 'Menunggu', 'revisi' => 'Revisi', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tiket / subdomain / pemohon"
                       class="px-3 py-2 border border-gray-300 rounded-lg w-64">
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg">Filter</button>
            @if (request('status') || request('search'))
                <a href="{{ route('admin.subdomain.data-update.index') }}" class="text-gray-600 hover:text-gray-800">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-left">Nomor Tiket</th>
                        <th class="px-4 py-2 text-left">Subdomain</th>
                        <th class="px-4 py-2 text-left">Pemohon</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-sm">{{ $req->ticket_number }}</td>
                            <td class="px-4 py-2">{{ $req->webMonitor->subdomain ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $req->user->name ?? '-' }}</td>
                            <td class="px-4 py-2">@include('user.subdomain.data-update._status', ['status' => $req->status])</td>
                            <td class="px-4 py-2 text-sm">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2 space-x-3">
                                <a href="{{ route('admin.subdomain.data-update.show', $req->id) }}"
                                    class="text-green-600 hover:text-green-800 font-medium">Detail</a>
                                @if ($req->hasBeritaAcara())
                                    <a href="{{ route('admin.subdomain.data-update.berita-acara.download', $req->id) }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium">Berita Acara</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada permohonan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $requests->links() }}</div>
    </div>
@endsection
