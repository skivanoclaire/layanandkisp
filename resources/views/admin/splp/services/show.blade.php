@extends('layouts.authenticated')

@section('title', '- Detail Layanan SPLP')
@section('header-title', 'Detail Layanan SPLP')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <a href="{{ route('admin.splp.services.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali</a>

    <div class="bg-white rounded-lg shadow-sm border p-6 mt-4">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $service->nama_layanan }}</h1>
                <p class="font-mono text-sm text-gray-500">{{ $service->kode_layanan }}</p>
            </div>
            <a href="{{ route('admin.splp.services.edit', $service) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Edit</a>
        </div>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div><dt class="text-gray-500">OPD Pemilik</dt><dd class="font-medium">{{ $service->opdPemilik->nama ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ ucfirst($service->status) }}</dd></div>
            <div><dt class="text-gray-500">Environment</dt><dd class="font-medium">{{ ucfirst($service->environment) }}</dd></div>
            <div><dt class="text-gray-500">Klasifikasi</dt><dd class="font-medium">{{ ucfirst($service->klasifikasi_data) }}</dd></div>
            <div class="md:col-span-2"><dt class="text-gray-500">Backend URL</dt><dd class="font-mono break-all">{{ $service->backend_url ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Route Path</dt><dd class="font-mono">{{ $service->route_path ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Autentikasi</dt><dd class="font-medium">{{ strtoupper($service->auth_type) }}</dd></div>
            <div><dt class="text-gray-500">Gateway Service ID</dt><dd class="font-mono">{{ $service->gateway_service_id ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Gateway Route ID</dt><dd class="font-mono">{{ $service->gateway_route_id ?? '-' }}</dd></div>
        </dl>
    </div>

    {{-- Konsumen --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
        <h2 class="font-bold text-gray-800 mb-3">Konsumen Terdaftar ({{ $service->consumers->count() }})</h2>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100"><tr><th class="px-3 py-2 text-left">Konsumen</th><th class="px-3 py-2 text-left">Kredensial</th><th class="px-3 py-2 text-left">Berlaku s.d.</th><th class="px-3 py-2 text-left">Status</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($service->consumers as $c)
                    <tr><td class="px-3 py-2">{{ $c->nama_konsumen }}</td><td class="px-3 py-2">{{ strtoupper($c->credential_type) }}</td><td class="px-3 py-2">{{ optional($c->expires_at)->format('d/m/Y') ?? '-' }}</td><td class="px-3 py-2">{{ ucfirst($c->status) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Belum ada konsumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Audit --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
        <h2 class="font-bold text-gray-800 mb-3">Riwayat Konfigurasi</h2>
        <ul class="space-y-2 text-sm">
            @forelse ($service->logs->sortByDesc('created_at') as $log)
                <li class="flex gap-3 border-l-2 border-gray-200 pl-3">
                    <span class="text-gray-400 whitespace-nowrap">{{ optional($log->created_at)->format('d/m/Y H:i') }}</span>
                    <span class="font-medium">{{ $log->action }}</span>
                    <span class="text-gray-600">{{ $log->keterangan }} @if($log->actor)— {{ $log->actor->name }}@endif</span>
                </li>
            @empty
                <li class="text-gray-500">Belum ada riwayat.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
