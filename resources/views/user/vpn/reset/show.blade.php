@extends('layouts.authenticated')
@section('title', '- Detail Reset Akun VPN')
@section('header-title', 'Detail Reset Akun VPN')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Reset Akun VPN</h1>
        <a href="{{ route('user.vpn.reset.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Ticket & Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Tiket:</label>
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $vpnReset->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($vpnReset->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($vpnReset->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($vpnReset->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $vpnReset->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $vpnReset->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $vpnReset->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $vpnReset->unitKerja->nama ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Username VPN Lama:</label>
                    <p class="text-gray-800">{{ $vpnReset->username_vpn_lama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Alasan Reset -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Alasan Reset</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $vpnReset->alasan }}</p>
            </div>
        </div>

        <!-- Kredensial VPN Baru (jika sudah selesai) -->
        @if($vpnReset->status === 'selesai' && $vpnReset->username_vpn_baru)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-lg font-semibold text-green-800 mb-3">Kredensial VPN Baru Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Username VPN Baru:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpnReset->username_vpn_baru }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Password VPN Baru:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpnReset->password_vpn_baru }}</p>
                </div>
            </div>
            @if($vpnReset->keterangan_admin)
            <div class="mt-4">
                <label class="block text-sm font-semibold text-green-700 mb-1">Keterangan Admin:</label>
                <div class="bg-white p-3 rounded border border-green-300">
                    <p class="text-green-900 whitespace-pre-wrap">{{ $vpnReset->keterangan_admin }}</p>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Admin Notes -->
        @if($vpnReset->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $vpnReset->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
