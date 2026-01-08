@extends('layouts.authenticated')
@section('title', '- Detail Pendaftaran VPN')
@section('header-title', 'Detail Pendaftaran VPN')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Pendaftaran VPN</h1>
        <a href="{{ route('user.vpn.registration.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Ticket & Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Tiket:</label>
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $vpnRegistration->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($vpnRegistration->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($vpnRegistration->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($vpnRegistration->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $vpnRegistration->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $vpnRegistration->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $vpnRegistration->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $vpnRegistration->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Detail Permohonan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Permohonan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe VPN:</label>
                    <p class="text-gray-800">{{ $vpnRegistration->tipe }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bandwidth:</label>
                    <p class="text-gray-800">{{ $vpnRegistration->bandwidth ?? '-' }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Uraian Kebutuhan:</label>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $vpnRegistration->uraian_kebutuhan }}</p>
                </div>
            </div>
        </div>

        <!-- Informasi Kredensial VPN (jika sudah selesai) -->
        @if($vpnRegistration->status === 'selesai' && $vpnRegistration->username_vpn)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-lg font-semibold text-green-800 mb-3">Kredensial VPN Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Username VPN:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpnRegistration->username_vpn }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Password VPN:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpnRegistration->password_vpn }}</p>
                </div>
                @if($vpnRegistration->ip_vpn)
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">IP VPN:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpnRegistration->ip_vpn }}</p>
                </div>
                @endif
            </div>
            @if($vpnRegistration->keterangan_admin)
            <div class="mt-4">
                <label class="block text-sm font-semibold text-green-700 mb-1">Keterangan Admin:</label>
                <div class="bg-white p-3 rounded border border-green-300">
                    <p class="text-green-900 whitespace-pre-wrap">{{ $vpnRegistration->keterangan_admin }}</p>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Admin Notes -->
        @if($vpnRegistration->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $vpnRegistration->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
