@extends('layouts.authenticated')

@section('title', '- Master Data Konsumen SPLP')
@section('header-title', 'Master Data Integrasi (SPLP) — Konsumen')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Registry Konsumen SPLP</h1>
            <p class="text-gray-600 mt-1">Daftar konsumen & metadata kredensial akses layanan SPLP.</p>
        </div>
        <a href="{{ route('admin.splp.consumers.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Tambah Konsumen</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-4 flex flex-wrap items-end gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama konsumen" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <select name="splp_service_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Semua Layanan</option>
            @foreach ($services as $svc)<option value="{{ $svc->id }}" @selected(request('splp_service_id') == $svc->id)>{{ $svc->nama_layanan }}</option>@endforeach
        </select>
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Semua Status</option>
            @foreach (['aktif', 'nonaktif', 'dicabut', 'kadaluarsa'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Konsumen</th>
                    <th class="px-4 py-3 text-left">Layanan</th>
                    <th class="px-4 py-3 text-left">Kredensial</th>
                    <th class="px-4 py-3 text-left">Berlaku s.d.</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($consumers as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $c->nama_konsumen }}</td>
                        <td class="px-4 py-3">{{ $c->service->nama_layanan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ strtoupper($c->credential_type) }}</td>
                        <td class="px-4 py-3">{{ optional($c->expires_at)->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $c->status_badge_class }}">{{ ucfirst($c->status) }}</span></td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.splp.consumers.edit', $c) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.splp.consumers.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Hapus konsumen ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada konsumen terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $consumers->links() }}</div>
</div>
@endsection
