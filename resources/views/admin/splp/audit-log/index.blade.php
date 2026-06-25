@extends('layouts.authenticated')

@section('title', '- Audit Log SPLP')
@section('header-title', 'Master Data Integrasi (SPLP) — Audit Log')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Riwayat Konfigurasi SPLP</h1>
    <p class="text-gray-600 mb-6">Audit trail perubahan layanan & konsumen (read-only).</p>

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-4 flex flex-wrap items-end gap-3">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filter</button>
        <a href="{{ route('admin.splp.audit.index') }}" class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">Reset</a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Layanan</th>
                    <th class="px-4 py-3 text-left">Konsumen</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-left">Aktor</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-3">{{ $log->service->nama_layanan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $log->consumer->nama_konsumen ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $log->keterangan }}</td>
                        <td class="px-4 py-3">{{ $log->actor->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
