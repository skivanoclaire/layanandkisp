@extends('layouts.authenticated')
@section('title', '- Detail VPS/VM')
@section('header-title', 'Detail VPS/VM')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan VPS/VM</h1>
        <a href="{{ route('admin.datacenter.vps.index') }}"
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
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $vpsRequest->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($vpsRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($vpsRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($vpsRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $vpsRequest->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $vpsRequest->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $vpsRequest->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $vpsRequest->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Spesifikasi VPS -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Spesifikasi VPS/VM</h2>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Total vCPU</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->vcpu }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Socket</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->jumlah_socket }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">vCPU per Socket</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->vcpu_per_socket }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">RAM</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->ram_gb }} GB</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Storage</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $vpsRequest->storage_gb }} GB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $vpsRequest->keterangan }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons: Process/Reject -->
    @if($vpsRequest->status === 'menunggu')
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">Tindakan untuk Permohonan Ini</h3>
        <div class="flex gap-3">
            <button onclick="document.getElementById('formProcess').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Proses Permohonan
            </button>
            <button onclick="document.getElementById('formReject').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                Tolak Permohonan
            </button>
        </div>

        <!-- Form Process -->
        <form id="formProcess" action="{{ route('admin.datacenter.vps.process', $vpsRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan (opsional):</label>
            <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-blue-300 rounded-lg"></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Proses</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>

        <!-- Form Reject -->
        <form id="formReject" action="{{ route('admin.datacenter.vps.reject', $vpsRequest->id) }}" method="POST" style="display:none;" class="mt-4">
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

    <!-- Action: Complete with IP Assignment -->
    @if($vpsRequest->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Selesaikan Permohonan VPS/VM</h3>
        <form action="{{ route('admin.datacenter.vps.complete', $vpsRequest->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">IP Public: <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="ip_public" id="ip_public" required
                           class="flex-1 px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Contoh: 103.xx.xx.xx">
                    <button type="button" onclick="autopickIP()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                        Auto-pick IP
                    </button>
                </div>
                <p class="mt-1 text-xs text-blue-600">Klik "Auto-pick IP" untuk otomatis memilih IP tersedia dari sistem monitoring</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Username VPS: <span class="text-red-500">*</span></label>
                    <input type="text" name="username_vps" required
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Username akses VPS">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Password VPS: <span class="text-red-500">*</span></label>
                    <input type="text" name="password_vps" required
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Password akses VPS">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Operating System (OS):</label>
                    <input type="text" name="os_vps"
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Contoh: Ubuntu 22.04 LTS, CentOS 7, Windows Server 2019">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">Keterangan untuk User:</label>
                <textarea name="keterangan_admin" rows="3"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Informasi tambahan untuk user (opsional)">{{ $vpsRequest->keterangan_admin }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Internal (opsional):</label>
                <textarea name="admin_notes" rows="2"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Catatan untuk admin">{{ $vpsRequest->admin_notes }}</textarea>
            </div>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Selesaikan & Berikan Akses VPS
            </button>
        </form>
    </div>
    @endif

    <!-- Credentials Display Section -->
    @if($vpsRequest->status === 'selesai' && $vpsRequest->username_vps)
    @php
        $plainUsernameVps = $vpsRequest->getPlainUsernameVps();
        $plainPasswordVps = $vpsRequest->getPlainPasswordVps();
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
            @if($vpsRequest->completed_at)
            <span class="text-xs text-indigo-600 whitespace-nowrap">
                Diselesaikan: {{ $vpsRequest->completed_at->format('d/m/Y H:i') }}
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">Username VPS:</label>
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-indigo-300">
                    <span id="admin-vps-username-display" class="text-indigo-900 font-mono flex-1 select-all break-all">{{ $plainUsernameVps }}</span>
                    <button type="button" id="admin-vps-copy-username" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Salin username">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">Password VPS:</label>
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-indigo-300">
                    <span id="admin-vps-password-display" class="text-indigo-900 font-mono flex-1 select-all break-all">••••••••••••</span>
                    <button type="button" id="admin-vps-toggle-password" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Tampilkan/Sembunyikan">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <button type="button" id="admin-vps-copy-password" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Salin password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            @if($vpsRequest->ip_public)
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">IP Public:</label>
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-indigo-300">
                    <span id="admin-vps-ip-display" class="text-indigo-900 font-mono flex-1 select-all break-all">{{ $vpsRequest->ip_public }}</span>
                    <button type="button" id="admin-vps-copy-ip" class="text-indigo-700 hover:text-indigo-900 flex-shrink-0" title="Salin IP">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif
            @if($vpsRequest->os_vps)
            <div>
                <label class="block text-sm font-semibold text-indigo-700 mb-1">Operating System:</label>
                <p class="text-indigo-900 font-mono bg-white px-3 py-2 rounded border border-indigo-300">{{ $vpsRequest->os_vps }}</p>
            </div>
            @endif
        </div>

        @if($vpsRequest->keterangan_admin)
        <div class="mt-4">
            <label class="block text-sm font-semibold text-indigo-700 mb-1">Keterangan untuk User:</label>
            <div class="bg-white p-3 rounded border border-indigo-300">
                <p class="text-indigo-900 whitespace-pre-wrap">{{ $vpsRequest->keterangan_admin }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Update Notes Section -->
    @if(in_array($vpsRequest->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Admin</h3>
        <form action="{{ route('admin.datacenter.vps.update-notes', $vpsRequest->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $vpsRequest->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($vpsRequest->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $vpsRequest->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
async function autopickIP() {
    const ipInput = document.getElementById('ip_public');
    try {
        const response = await fetch('{{ route("admin.datacenter.vps.api.autopick-ip") }}');
        const data = await response.json();

        if (data.success && data.ip) {
            ipInput.value = data.ip;
            alert('IP berhasil dipilih: ' + data.ip);
        } else {
            alert('Gagal mendapatkan IP: ' + (data.message || 'Tidak ada IP tersedia'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>

@if($vpsRequest->status === 'selesai' && $vpsRequest->username_vps)
<script>
(function () {
    const username = @json($plainUsernameVps);
    const password = @json($plainPasswordVps);
    const ipPublic = @json($vpsRequest->ip_public);
    const pwDisplay = document.getElementById('admin-vps-password-display');
    const toggleBtn = document.getElementById('admin-vps-toggle-password');
    const copyPwBtn = document.getElementById('admin-vps-copy-password');
    const copyUserBtn = document.getElementById('admin-vps-copy-username');
    const copyIpBtn = document.getElementById('admin-vps-copy-ip');
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
        copyIpBtn.addEventListener('click', function () { copy(ipPublic, copyIpBtn); });
    }
})();
</script>
@endif
@endsection
