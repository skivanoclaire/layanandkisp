@extends('layouts.authenticated')

@section('title', '- Ajukan Perubahan IP Subdomain')
@section('header-title', 'Ajukan Perubahan IP Subdomain')

@section('content')
    <div class="bg-blue-100 p-6 rounded-lg shadow border border-blue-200 mb-6">
        <h2 class="text-2xl font-bold text-blue-800 mb-2">Form Perubahan IP Subdomain</h2>
        <p>Silakan isi form di bawah ini untuk mengajukan permohonan perubahan IP address subdomain Anda.</p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('user.subdomain.ip-change.store') }}" method="POST" id="ipChangeForm">
            @csrf

            <!-- Subdomain Name -->
            <div class="mb-6">
                <label for="subdomain_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Subdomain <span class="text-red-500">*</span>
                </label>
                <input type="text" name="subdomain_name" id="subdomain_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subdomain_name') border-red-500 @enderror"
                    value="{{ old('subdomain_name') }}" placeholder="contoh: subdomain.example.com" required>
                @error('subdomain_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Masukkan nama subdomain lengkap yang akan diubah IP-nya</p>
            </div>

            <!-- Old IP Address -->
            <div class="mb-6">
                <label for="old_ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                    IP Address Lama <span class="text-red-500">*</span>
                </label>
                <input type="text" name="old_ip_address" id="old_ip_address"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('old_ip_address') border-red-500 @enderror"
                    value="{{ old('old_ip_address') }}" placeholder="contoh: 192.168.1.1" required
                    pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$">
                @error('old_ip_address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">IP address yang saat ini digunakan oleh subdomain</p>
            </div>

            <!-- New IP Address -->
            <div class="mb-6">
                <label for="new_ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                    IP Address Baru <span class="text-red-500">*</span>
                </label>
                <input type="text" name="new_ip_address" id="new_ip_address"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('new_ip_address') border-red-500 @enderror"
                    value="{{ old('new_ip_address') }}" placeholder="contoh: 192.168.1.2" required
                    pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$">
                @error('new_ip_address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">IP address baru yang akan digunakan (harus berbeda dari IP lama)</p>
            </div>

            <!-- Reason -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Perubahan <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="5"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror"
                    placeholder="Jelaskan alasan perubahan IP address (minimal 10 karakter)"
                    required minlength="10">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    <span id="charCount">0</span>/10 karakter minimum
                </p>
            </div>

            <!-- Information Box -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">Catatan Penting:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Pastikan IP address baru sudah siap dan dapat digunakan</li>
                            <li>Perubahan IP akan diproses setelah mendapat persetujuan admin</li>
                            <li>Anda hanya dapat mengajukan satu permohonan dalam satu waktu</li>
                            <li>Proses perubahan IP dapat memakan waktu beberapa saat</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.subdomain.ip-change.index') }}"
                    class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- JavaScript for character count and validation -->
    <script>
        const reasonTextarea = document.getElementById('reason');
        const charCountSpan = document.getElementById('charCount');
        const oldIpInput = document.getElementById('old_ip_address');
        const newIpInput = document.getElementById('new_ip_address');
        const form = document.getElementById('ipChangeForm');

        // Update character count
        reasonTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCountSpan.textContent = length;

            if (length < 10) {
                charCountSpan.classList.add('text-red-600');
                charCountSpan.classList.remove('text-green-600');
            } else {
                charCountSpan.classList.add('text-green-600');
                charCountSpan.classList.remove('text-red-600');
            }
        });

        // Initialize character count
        charCountSpan.textContent = reasonTextarea.value.length;

        // Form validation before submit
        form.addEventListener('submit', function(e) {
            const oldIp = oldIpInput.value.trim();
            const newIp = newIpInput.value.trim();

            // Check if IPs are the same
            if (oldIp === newIp) {
                e.preventDefault();
                alert('IP address baru harus berbeda dengan IP address lama!');
                newIpInput.focus();
                return false;
            }

            // Check reason length
            if (reasonTextarea.value.trim().length < 10) {
                e.preventDefault();
                alert('Alasan perubahan harus minimal 10 karakter!');
                reasonTextarea.focus();
                return false;
            }
        });

        // IP address format validation feedback
        function validateIpFormat(input) {
            const ipPattern = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;

            if (input.value && !ipPattern.test(input.value)) {
                input.classList.add('border-red-500');
            } else if (input.value) {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
            } else {
                input.classList.remove('border-red-500', 'border-green-500');
            }
        }

        oldIpInput.addEventListener('blur', function() {
            validateIpFormat(this);
        });

        newIpInput.addEventListener('blur', function() {
            validateIpFormat(this);
        });
    </script>
@endsection
