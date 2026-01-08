@extends('layouts.authenticated')

@section('title', '- Tambah Web Monitor')
@section('header-title', 'Tambah Web Monitor')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Website Monitoring</h1>
        <p class="text-gray-600 mt-2">Tambahkan website baru untuk dimonitor</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.web-monitor.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="nama_instansi" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Instansi / Sistem
                </label>
                <input type="text"
                       id="nama_instansi"
                       name="nama_instansi"
                       value="{{ old('nama_instansi') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama_instansi') border-red-500 @enderror"
                       list="unit-kerja-list"
                       placeholder="Ketik atau pilih nama instansi/sistem">
                <datalist id="unit-kerja-list">
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->nama }}">{{ $unit->tipe }}</option>
                    @endforeach
                </datalist>
                @error('nama_instansi')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Opsional: Ketik manual atau pilih dari daftar unit kerja</p>
            </div>

            <div class="mb-4">
                <label for="subdomain" class="block text-sm font-semibold text-gray-700 mb-2">
                    Subdomain
                </label>
                <input type="text"
                       id="subdomain"
                       name="subdomain"
                       value="{{ old('subdomain') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subdomain') border-red-500 @enderror"
                       placeholder="Contoh: diskominfo.kaltaraprov.go.id">
                @error('subdomain')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Opsional: Kosongkan jika IP hanya untuk VM/server tanpa domain</p>
            </div>

            <div class="mb-4">
                <label for="ip_address" class="block text-sm font-semibold text-gray-700 mb-2">
                    IP Address <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text"
                           id="ip_address"
                           name="ip_address"
                           value="{{ old('ip_address') }}"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ip_address') border-red-500 @enderror"
                           required
                           placeholder="Contoh: 103.156.110.1">
                    <a href="{{ route('admin.web-monitor.check-ip-publik') }}"
                       target="_blank"
                       class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center whitespace-nowrap"
                       title="Lihat IP yang tersedia">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Cek IP
                    </a>
                </div>
                @error('ip_address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <div id="ip-warning" class="hidden mt-2 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold">IP Address Sudah Digunakan!</p>
                            <p id="ip-warning-text" class="text-sm mt-1"></p>
                        </div>
                    </div>
                </div>
                <p class="text-gray-500 text-xs mt-1">Gunakan IP dari range 103.156.110.0/24</p>
            </div>

            <div class="mb-4">
                <label for="jenis" class="block text-sm font-semibold text-gray-700 mb-2">
                    Jenis <span class="text-red-500">*</span>
                </label>
                <select id="jenis"
                        name="jenis"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('jenis') border-red-500 @enderror"
                        required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($jenisOptions as $option)
                        <option value="{{ $option }}" {{ old('jenis') === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                @error('jenis')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-2">
                    Keterangan / Deskripsi
                </label>
                <textarea id="keterangan"
                          name="keterangan"
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('keterangan') border-red-500 @enderror"
                          placeholder="Jelaskan penggunaan IP ini (contoh: VM Database Server, Server Backup, Mailserver Internal, dll)">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Disarankan diisi untuk memudahkan identifikasi penggunaan IP</p>
            </div>

            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <label class="flex items-start gap-3">
                    <input type="checkbox"
                           name="is_proxied"
                           class="mt-1"
                           {{ old('is_proxied') ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-gray-800">Aktifkan Cloudflare Proxy</span>
                        <p class="text-sm text-gray-600 mt-1">
                            Jika dicentang, traffic akan melalui Cloudflare untuk keamanan dan performa lebih baik
                        </p>
                    </div>
                </label>
            </div>

            <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <label class="flex items-start gap-3">
                    <input type="checkbox"
                           name="create_in_cloudflare"
                           class="mt-1"
                           {{ old('create_in_cloudflare') ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-orange-800">Buat DNS Record di Cloudflare</span>
                        <p class="text-sm text-orange-700 mt-1">
                            Jika dicentang, sistem akan otomatis membuat A record di Cloudflare dengan data yang diinput
                        </p>
                    </div>
                </label>
            </div>

            <!-- Hidden field to track return URL -->
            <input type="hidden" name="return_to" value="{{ request()->query('from') }}">

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Simpan
                </button>
                @php
                    $backUrl = request()->query('from') === 'check-ip'
                        ? route('admin.web-monitor.check-ip-publik')
                        : route('admin.web-monitor.index');
                @endphp
                <a href="{{ $backUrl }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ipInput = document.getElementById('ip_address');
    const ipWarning = document.getElementById('ip-warning');
    const ipWarningText = document.getElementById('ip-warning-text');
    const submitButton = document.querySelector('button[type="submit"]');
    let typingTimer;
    const doneTypingInterval = 500; // 500ms after user stops typing

    // Check IP immediately on page load if there's a value
    if (ipInput.value.trim()) {
        checkIpAvailability();
    }

    ipInput.addEventListener('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(checkIpAvailability, doneTypingInterval);
    });

    ipInput.addEventListener('keydown', function() {
        clearTimeout(typingTimer);
    });

    // Also check on blur (when user leaves the input field)
    ipInput.addEventListener('blur', function() {
        if (ipInput.value.trim()) {
            checkIpAvailability();
        }
    });

    function checkIpAvailability() {
        const ip = ipInput.value.trim();

        if (!ip) {
            hideWarning();
            return;
        }

        // Validate IP format
        const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
        if (!ipPattern.test(ip)) {
            hideWarning();
            return;
        }

        // Check if IP is already used via AJAX
        const url = `{{ url('admin/web-monitor/check-ip-availability') }}?ip=${encodeURIComponent(ip)}`;
        console.log('Checking IP:', ip, 'URL:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.used) {
                    showWarning(`IP ini sudah digunakan oleh <strong>${data.instance}</strong>`);
                } else {
                    hideWarning();
                }
            })
            .catch(error => {
                console.error('Error checking IP:', error);
                hideWarning();
            });
    }

    function showWarning(message) {
        ipWarningText.innerHTML = message;
        ipWarning.classList.remove('hidden');
        ipInput.classList.add('border-red-500');
    }

    function hideWarning() {
        ipWarning.classList.add('hidden');
        ipInput.classList.remove('border-red-500');
    }
});
</script>
@endsection
