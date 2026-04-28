@extends('layouts.authenticated')
@section('title', '- Detail Pendaftaran VPN')
@section('header-title', 'Detail Pendaftaran VPN')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Pendaftaran VPN</h1>
        <a href="{{ route('admin.vpn.registration.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
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

        @if(!in_array($vpnRegistration->status, ['selesai', 'ditolak']))
        <div class="mb-6 bg-purple-50 border-l-4 border-purple-400 p-4 rounded">
            <h3 class="text-sm font-semibold text-purple-800 mb-2">Revisi Bandwidth</h3>
            <form action="{{ route('admin.vpn.registration.revise-bandwidth', $vpnRegistration->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="text" name="bandwidth" required value="{{ $vpnRegistration->bandwidth }}"
                       placeholder="Contoh: 50 Mbps"
                       class="px-3 py-2 border border-purple-300 rounded-lg text-sm">
                <input type="text" name="revision_note" maxlength="500"
                       placeholder="Catatan revisi (opsional)"
                       class="px-3 py-2 border border-purple-300 rounded-lg text-sm">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold text-sm">
                    Simpan Revisi
                </button>
            </form>
        </div>
        @endif

        @php
            $bandwidthLogs = $vpnRegistration->logs->where('action', 'bandwidth_revised');
        @endphp
        @if($bandwidthLogs->count() > 0)
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Riwayat Revisi Bandwidth</h3>
            <div class="bg-gray-50 rounded border divide-y divide-gray-200">
                @foreach($bandwidthLogs as $log)
                <div class="p-3 text-sm">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <span class="font-mono text-gray-500 line-through">{{ $log->old_value ?? '-' }}</span>
                            <span class="mx-2 text-gray-400">→</span>
                            <span class="font-mono text-purple-700 font-semibold">{{ $log->new_value }}</span>
                        </div>
                        <div class="text-xs text-gray-500 text-right whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i') }}<br>
                            <span>oleh {{ $log->actor->name ?? '—' }}</span>
                        </div>
                    </div>
                    @if($log->note)
                    <p class="text-xs text-gray-600 mt-1 italic">{{ $log->note }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Action Buttons -->
    @if($vpnRegistration->status === 'menunggu')
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">Tindakan untuk Permohonan Ini</h3>
        <div class="flex gap-3">
            <button onclick="document.getElementById('formProses').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Proses Permohonan
            </button>
            <button onclick="document.getElementById('formTolak').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                Tolak Permohonan
            </button>
        </div>

        <!-- Form Proses -->
        <form id="formProses" action="{{ route('admin.vpn.registration.process', $vpnRegistration->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <p class="text-sm text-blue-800">Lanjutkan memproses permohonan ini? Anda akan diarahkan untuk mengisi kredensial VPN.</p>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Proses</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>

        <!-- Form Tolak -->
        <form id="formTolak" action="{{ route('admin.vpn.registration.reject', $vpnRegistration->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-red-700 mb-1">Alasan Penolakan: <span class="text-red-500">*</span></label>
            <textarea name="admin_notes" rows="3" required class="w-full px-3 py-2 border border-red-300 rounded-lg" placeholder="Jelaskan alasan penolakan..."></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Tolak</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>
    </div>
    @endif

    @if($vpnRegistration->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Selesaikan Permohonan</h3>
        <form action="{{ route('admin.vpn.registration.complete', $vpnRegistration->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Username VPN: <span class="text-red-500">*</span></label>
                    <input type="text" name="username_vpn" required
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Username VPN yang diberikan">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Password VPN: <span class="text-red-500">*</span></label>
                    <input type="text" name="password_vpn" required
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Password VPN yang diberikan">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">IP VPN:</label>
                    <input type="text" name="ip_vpn"
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="IP VPN (opsional)">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">Keterangan untuk User:</label>
                <textarea name="keterangan_admin" rows="3"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Informasi tambahan untuk user (opsional)">{{ $vpnRegistration->keterangan_admin }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Internal (opsional):</label>
                <textarea name="admin_notes" rows="2"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Catatan untuk admin">{{ $vpnRegistration->admin_notes }}</textarea>
            </div>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Selesaikan & Berikan Kredensial
            </button>
        </form>
    </div>
    @endif

    @if($vpnRegistration->status === 'selesai' && $vpnRegistration->username_vpn)
    @php
        $plainUsername = $vpnRegistration->getPlainUsernameVpn();
        $plainPassword = $vpnRegistration->getPlainPasswordVpn();
    @endphp
    <div class="bg-indigo-50 border-l-4 border-indigo-500 rounded-lg p-6 mb-6">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h3 class="text-lg font-bold text-indigo-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Kredensial yang Diberikan ke User
                </h3>
                <p class="text-xs text-indigo-700 mt-1">Gunakan informasi ini untuk verifikasi atau bantuan ke user. Jaga kerahasiaan kredensial.</p>
            </div>
            @if($vpnRegistration->completed_at)
            <span class="text-xs text-indigo-600 whitespace-nowrap">
                Diselesaikan: {{ $vpnRegistration->completed_at->format('d/m/Y H:i') }}
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">Username VPN:</label>
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-indigo-300">
                    <span id="admin-vpn-username-display" class="text-indigo-900 font-mono flex-1 select-all break-all">{{ $plainUsername }}</span>
                    <button type="button" id="admin-vpn-copy-username" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Salin username">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">Password VPN:</label>
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-indigo-300">
                    <span id="admin-vpn-password-display" class="text-indigo-900 font-mono flex-1 select-all break-all">••••••••••••</span>
                    <button type="button" id="admin-vpn-toggle-password" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Tampilkan/Sembunyikan">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <button type="button" id="admin-vpn-copy-password" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Salin password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            @if($vpnRegistration->ip_vpn)
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">IP VPN:</label>
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-indigo-300">
                    <span id="admin-vpn-ip-display" class="text-indigo-900 font-mono flex-1 select-all break-all">{{ $vpnRegistration->ip_vpn }}</span>
                    <button type="button" id="admin-vpn-copy-ip" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Salin IP">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif
        </div>

        @if($vpnRegistration->keterangan_admin)
        <div class="mt-4">
            <label class="block text-sm font-semibold text-indigo-700 mb-1">Keterangan untuk User:</label>
            <div class="bg-white p-3 rounded border border-indigo-300">
                <p class="text-indigo-900 whitespace-pre-wrap">{{ $vpnRegistration->keterangan_admin }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($vpnRegistration->status === 'selesai')
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Admin</h3>
        <form action="{{ route('admin.vpn.registration.update-notes', $vpnRegistration->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $vpnRegistration->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($vpnRegistration->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $vpnRegistration->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>

@if($vpnRegistration->status === 'selesai' && $vpnRegistration->username_vpn)
<script>
(function () {
    const username = @json($plainUsername);
    const password = @json($plainPassword);
    const ipVpn = @json($vpnRegistration->ip_vpn);
    const pwDisplay = document.getElementById('admin-vpn-password-display');
    const toggleBtn = document.getElementById('admin-vpn-toggle-password');
    const copyPwBtn = document.getElementById('admin-vpn-copy-password');
    const copyUserBtn = document.getElementById('admin-vpn-copy-username');
    const copyIpBtn = document.getElementById('admin-vpn-copy-ip');
    let visible = false;
    const masked = '••••••••••••';

    toggleBtn.addEventListener('click', function () {
        visible = !visible;
        pwDisplay.textContent = visible ? password : masked;
    });

    function copy(text, btn) {
        if (!navigator.clipboard || !text) return;
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
    if (copyIpBtn) {
        copyIpBtn.addEventListener('click', function () { copy(ipVpn, copyIpBtn); });
    }
})();
</script>
@endif
@endsection
