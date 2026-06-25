@extends('layouts.authenticated')

@section('title', '- Master Data Layanan SPLP')
@section('header-title', 'Master Data Integrasi (SPLP) — Layanan')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Registry Layanan SPLP</h1>
            <p class="text-gray-600 mt-1">Daftar endpoint/layanan penyedia yang terdaftar di SPLP.</p>
        </div>
        <a href="{{ route('admin.splp.services.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Tambah Layanan</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-4 flex flex-wrap items-end gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/kode layanan" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Semua Status</option>
            @foreach (['aktif', 'nonaktif', 'dicabut'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <select name="environment" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Semua Env</option>
            @foreach (['produksi', 'sandbox'] as $e)<option value="{{ $e }}" @selected(request('environment') === $e)>{{ ucfirst($e) }}</option>@endforeach
        </select>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Layanan</th>
                    <th class="px-4 py-3 text-left">OPD Pemilik</th>
                    <th class="px-4 py-3 text-left">Env</th>
                    <th class="px-4 py-3 text-left">Klasifikasi</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($services as $svc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $svc->kode_layanan }}</td>
                        <td class="px-4 py-3 font-medium">{{ $svc->nama_layanan }}</td>
                        <td class="px-4 py-3">{{ $svc->opdPemilik->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ ucfirst($svc->environment) }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $svc->klasifikasi_badge_class }}">{{ ucfirst($svc->klasifikasi_data) }}</span></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                @class(['bg-green-100 text-green-800' => $svc->status === 'aktif', 'bg-gray-100 text-gray-700' => $svc->status === 'nonaktif', 'bg-red-100 text-red-800' => $svc->status === 'dicabut'])">
                                {{ ucfirst($svc->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.splp.services.show', $svc) }}" class="text-gray-600 hover:underline">Lihat</a>
                            <a href="{{ route('admin.splp.services.edit', $svc) }}" class="text-blue-600 hover:underline ml-2">Edit</a>
                            <form action="{{ route('admin.splp.services.destroy', $svc) }}" method="POST" class="inline" onsubmit="return confirm('Hapus layanan ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada layanan terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $services->links() }}</div>
</div>
@endsection
