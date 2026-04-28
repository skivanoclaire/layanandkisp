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
                    @if($vpnRegistration->is_kabupaten_kota)
                        <p class="text-gray-800">{{ $vpnRegistration->unit_kerja_manual ?? '-' }}</p>
                        <p class="text-xs mt-1">
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded font-semibold">Kab/Kota: {{ $vpnRegistration->kabupaten_kota ?? '-' }}</span>
                        </p>
                    @else
                        <p class="text-gray-800">{{ $vpnRegistration->unitKerja->nama ?? '-' }}</p>
                    @endif
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

        @php
            $bandwidthLogs = $vpnRegistration->logs->where('action', 'bandwidth_revised');
        @endphp
        @if($bandwidthLogs->count() > 0)
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Riwayat Revisi Bandwidth oleh Admin</h3>
            <div class="bg-gray-50 rounded border divide-y divide-gray-200">
                @foreach($bandwidthLogs as $log)
                <div class="p-3 text-sm">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <span class="font-mono text-gray-500 line-through">{{ $log->old_value ?? '-' }}</span>
                            <span class="mx-2 text-gray-400">→</span>
                            <span class="font-mono text-purple-700 font-semibold">{{ $log->new_value }}</span>
                        </div>
                        <div class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($log->note)
                    <p class="text-xs text-gray-600 mt-1 italic">{{ $log->note }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Informasi Kredensial VPN (jika sudah selesai) -->
        @if($vpnRegistration->status === 'selesai' && $vpnRegistration->username_vpn)
        @php
            $plainUsername = $vpnRegistration->getPlainUsernameVpn();
            $plainPassword = $vpnRegistration->getPlainPasswordVpn();
        @endphp
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-lg font-semibold text-green-800 mb-3">Kredensial VPN Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Username VPN:</label>
                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-green-300">
                        <span id="vpn-username-display" class="text-green-900 font-mono flex-1 select-all break-all">{{ $plainUsername }}</span>
                        <button type="button" id="vpn-copy-username" class="text-green-700 hover:text-green-900 flex-shrink-0" title="Salin username">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Password VPN:</label>
                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-green-300">
                        <span id="vpn-password-display" class="text-green-900 font-mono flex-1 select-all break-all">••••••••••••</span>
                        <button type="button" id="vpn-toggle-password" class="text-green-700 hover:text-green-900 flex-shrink-0" title="Tampilkan/Sembunyikan">
                            <svg id="vpn-eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <button type="button" id="vpn-copy-password" class="text-green-700 hover:text-green-900 flex-shrink-0" title="Salin password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
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

            <div class="mt-4 bg-amber-50 border border-amber-300 rounded p-3 text-sm text-amber-900">
                <p class="font-semibold mb-1">Tanggung Jawab Pengguna</p>
                <p>Dengan menggunakan layanan ini, pengguna bertanggung jawab penuh untuk menjaga kerahasiaan dan keamanan akun serta dilarang mengakses situs yang tidak diizinkan. Segala konsekuensi hukum akibat penyalahgunaan menjadi tanggung jawab pengguna.</p>
            </div>
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

@if($vpnRegistration->status === 'selesai' && $vpnRegistration->username_vpn)
<script>
(function () {
    const username = @json($plainUsername);
    const password = @json($plainPassword);
    const pwDisplay = document.getElementById('vpn-password-display');
    const toggleBtn = document.getElementById('vpn-toggle-password');
    const copyPwBtn = document.getElementById('vpn-copy-password');
    const copyUserBtn = document.getElementById('vpn-copy-username');
    let visible = false;
    const masked = '••••••••••••';

    toggleBtn.addEventListener('click', function () {
        visible = !visible;
        pwDisplay.textContent = visible ? password : masked;
    });

    function copy(text, btn) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(function () {
            const original = btn.getAttribute('title');
            btn.setAttribute('title', 'Tersalin!');
            btn.classList.add('text-green-600');
            setTimeout(function () {
                btn.setAttribute('title', original);
                btn.classList.remove('text-green-600');
            }, 1500);
        });
    }

    copyPwBtn.addEventListener('click', function () { copy(password, copyPwBtn); });
    copyUserBtn.addEventListener('click', function () { copy(username, copyUserBtn); });
})();
</script>
@endif
@endsection
