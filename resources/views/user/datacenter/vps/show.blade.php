@extends('layouts.authenticated')
@section('title', '- Detail VPS/VM')
@section('header-title', 'Detail VPS/VM')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan VPS/VM</h1>
        <a href="{{ route('user.datacenter.vps.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
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
                    @if($vpsRequest->ip_public)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">IP Public</label>
                        <p class="text-lg font-bold text-purple-700 font-mono">{{ $vpsRequest->ip_public }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $vpsRequest->keterangan }}</p>
            </div>
        </div>

        <!-- Status Timeline -->
        @if($vpsRequest->status !== 'menunggu')
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h2>
            <div class="space-y-3">
                @if($vpsRequest->processing_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $vpsRequest->processing_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Sedang Diproses</p>
                        @if($vpsRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $vpsRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($vpsRequest->completed_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $vpsRequest->completed_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800">Selesai - VPS/VM Tersedia</p>
                    </div>
                </div>
                @endif

                @if($vpsRequest->rejected_at)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm text-gray-600">
                        {{ $vpsRequest->rejected_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex-shrink-0 w-3 h-3 bg-red-500 rounded-full mt-1 mx-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-800">Ditolak</p>
                        @if($vpsRequest->processedBy)
                        <p class="text-sm text-gray-600">oleh {{ $vpsRequest->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Kredensial VPS (jika sudah selesai) -->
        @if($vpsRequest->status === 'selesai' && $vpsRequest->username_vps)
        @php
            $plainUsernameVps = $vpsRequest->getPlainUsernameVps();
            $plainPasswordVps = $vpsRequest->getPlainPasswordVps();
        @endphp
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-lg font-semibold text-green-800 mb-3">Kredensial VPS Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Username VPS:</label>
                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-green-300">
                        <span id="vps-username-display" class="text-green-900 font-mono flex-1 select-all break-all">{{ $plainUsernameVps }}</span>
                        <button type="button" id="vps-copy-username" class="text-green-700 hover:text-green-900 flex-shrink-0" title="Salin username">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Password VPS:</label>
                    <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-green-300">
                        <span id="vps-password-display" class="text-green-900 font-mono flex-1 select-all break-all">••••••••••••</span>
                        <button type="button" id="vps-toggle-password" class="text-green-700 hover:text-green-900 flex-shrink-0" title="Tampilkan/Sembunyikan">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <button type="button" id="vps-copy-password" class="text-green-700 hover:text-green-900 flex-shrink-0" title="Salin password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @if($vpsRequest->ip_public)
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">IP Public:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpsRequest->ip_public }}</p>
                </div>
                @endif
                @if($vpsRequest->os_vps)
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">Operating System:</label>
                    <p class="text-green-900 font-mono bg-white px-3 py-2 rounded border border-green-300">{{ $vpsRequest->os_vps }}</p>
                </div>
                @endif
            </div>
            @if($vpsRequest->keterangan_admin)
            <div class="mt-4">
                <label class="block text-sm font-semibold text-green-700 mb-1">Keterangan Admin:</label>
                <div class="bg-white p-3 rounded border border-green-300">
                    <p class="text-green-900 whitespace-pre-wrap">{{ $vpsRequest->keterangan_admin }}</p>
                </div>
            </div>
            @endif

            <div class="mt-4 bg-amber-50 border border-amber-300 rounded p-3 text-sm text-amber-900">
                <p class="font-semibold mb-1">Tanggung Jawab Pengguna</p>
                <p>Dengan menggunakan layanan ini, pengguna bertanggung jawab penuh untuk menjaga kerahasiaan dan keamanan akun serta dilarang menyalahgunakan akses VPS. Segala konsekuensi hukum akibat penyalahgunaan menjadi tanggung jawab pengguna.</p>
            </div>
        </div>
        @elseif($vpsRequest->keterangan_admin)
        <!-- Keterangan Admin (fallback untuk data lama tanpa kredensial terstruktur) -->
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-sm font-semibold text-green-700 mb-2">Keterangan Admin:</h3>
            <p class="text-green-900 whitespace-pre-wrap">{{ $vpsRequest->keterangan_admin }}</p>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($vpsRequest->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $vpsRequest->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>

@if($vpsRequest->status === 'selesai' && $vpsRequest->username_vps)
<script>
(function () {
    const username = @json($plainUsernameVps);
    const password = @json($plainPasswordVps);
    const pwDisplay = document.getElementById('vps-password-display');
    const toggleBtn = document.getElementById('vps-toggle-password');
    const copyPwBtn = document.getElementById('vps-copy-password');
    const copyUserBtn = document.getElementById('vps-copy-username');
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
})();
</script>
@endif
@endsection
