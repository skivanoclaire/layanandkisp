@extends('layouts.authenticated')

@section('title', '- Ajukan Perubahan Nama Subdomain')
@section('header-title', 'Ajukan Perubahan Nama Subdomain')

@section('content')
    <div class="bg-purple-100 p-6 rounded-lg shadow border border-purple-200 mb-6">
        <h2 class="text-2xl font-bold text-purple-800 mb-2">Form Perubahan Nama Subdomain</h2>
        <p class="text-purple-700">Silakan isi form di bawah ini untuk mengajukan permohonan perubahan nama subdomain Anda.</p>
        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-300 rounded">
            <p class="text-sm text-yellow-800">
                <strong>⚠️ Perhatian Penting:</strong>
            </p>
            <ul class="list-disc list-inside text-sm text-yellow-700 mt-2 space-y-1">
                <li>Perubahan nama subdomain akan mempengaruhi semua link dan akses ke website Anda</li>
                <li>Semua bookmark dan tautan eksternal akan menjadi tidak valid</li>
                <li>DNS propagation dapat memakan waktu hingga 24-48 jam</li>
                <li>Pastikan menginformasikan perubahan ini kepada semua pengguna Anda</li>
            </ul>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('user.subdomain.name-change.store') }}" method="POST" id="nameChangeForm">
            @csrf

            <!-- Select Subdomain -->
            <div class="mb-6">
                <label for="web_monitor_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih Subdomain <span class="text-red-500">*</span>
                </label>
                <select name="web_monitor_id" id="web_monitor_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('web_monitor_id') border-red-500 @enderror"
                    required>
                    <option value="">-- Pilih Subdomain yang Akan Diubah --</option>
                    @foreach ($userSubdomains as $subdomain)
                        <option value="{{ $subdomain->id }}"
                                data-name="{{ $subdomain->subdomain }}"
                                {{ old('web_monitor_id') == $subdomain->id ? 'selected' : '' }}>
                            {{ $subdomain->subdomain }} ({{ $subdomain->nama_instansi }})
                        </option>
                    @endforeach
                </select>
                @error('web_monitor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Pilih subdomain yang sudah terdaftar milik Anda</p>
            </div>

            <!-- Old Subdomain Name (Auto-filled) -->
            <div class="mb-6">
                <label for="old_subdomain_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Subdomain Saat Ini
                </label>
                <input type="text" name="old_subdomain_name" id="old_subdomain_name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 @error('old_subdomain_name') border-red-500 @enderror"
                    value="{{ old('old_subdomain_name') }}" readonly placeholder="Pilih subdomain terlebih dahulu">
                @error('old_subdomain_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Subdomain Name -->
            <div class="mb-6">
                <label for="new_subdomain_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Subdomain Baru <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1 flex items-center">
                        <input type="text" name="new_subdomain_name" id="new_subdomain_name"
                            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('new_subdomain_name') border-red-500 @enderror"
                            value="{{ old('new_subdomain_name') }}" placeholder="contoh: subdomain-baru" required
                            pattern="^[a-zA-Z0-9\-_]+$" maxlength="63">
                        <div id="availability-indicator" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="w-6 h-6 text-green-500 hidden" id="available-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <svg class="w-6 h-6 text-red-500 hidden" id="unavailable-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-r-lg text-gray-500 font-mono text-sm whitespace-nowrap">
                        .kaltaraprov.go.id
                    </div>
                </div>
                <p id="availability-message" class="mt-1 text-sm"></p>
                @error('new_subdomain_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Hanya huruf, angka, dash (-) dan underscore (_). Maksimal 63 karakter</p>
            </div>

            <!-- Reason -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Perubahan <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="5"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('reason') border-red-500 @enderror"
                    required minlength="20" maxlength="1000" placeholder="Jelaskan alasan perubahan nama subdomain (minimal 20 karakter)">{{ old('reason') }}</textarea>
                <div class="flex justify-between items-center mt-1">
                    <span id="char-count" class="text-xs text-gray-500">0 / 1000 karakter</span>
                    @error('reason')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <p class="mt-1 text-xs text-gray-500">Jelaskan dengan detail mengapa perlu dilakukan perubahan nama subdomain</p>
            </div>

            <!-- Acknowledgment Checkbox -->
            <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="dns_propagation_acknowledged" id="dns_propagation_acknowledged"
                        class="mt-1 w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 @error('dns_propagation_acknowledged') border-red-500 @enderror"
                        value="1" {{ old('dns_propagation_acknowledged') ? 'checked' : '' }} required>
                    <div class="text-sm">
                        <span class="font-semibold text-orange-800">Saya memahami dan menyetujui bahwa:</span>
                        <ul class="list-disc list-inside mt-2 space-y-1 text-orange-700">
                            <li>Perubahan nama subdomain akan mempengaruhi seluruh akses ke website</li>
                            <li>Semua link dan bookmark yang ada akan menjadi tidak valid</li>
                            <li>DNS propagation dapat memakan waktu hingga 24-48 jam</li>
                            <li>Saya akan menginformasikan perubahan ini kepada seluruh pengguna</li>
                            <li>Konfigurasi DNS akan diatur ke DNS Only (tidak di-proxy Cloudflare)</li>
                        </ul>
                    </div>
                </label>
                @error('dns_propagation_acknowledged')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" id="submitBtn"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.subdomain.name-change.index') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-6 py-3 rounded-lg shadow-md hover:shadow-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-fill old subdomain name when selection changes
    document.getElementById('web_monitor_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const subdomainName = selectedOption.getAttribute('data-name');
        document.getElementById('old_subdomain_name').value = subdomainName || '';
    });

    // Character counter for reason
    const reasonTextarea = document.getElementById('reason');
    const charCount = document.getElementById('char-count');
    reasonTextarea.addEventListener('input', function() {
        charCount.textContent = `${this.value.length} / 1000 karakter`;
    });

    // Real-time availability check
    let checkTimeout;
    const newSubdomainInput = document.getElementById('new_subdomain_name');
    const availabilityIndicator = document.getElementById('availability-indicator');
    const availableIcon = document.getElementById('available-icon');
    const unavailableIcon = document.getElementById('unavailable-icon');
    const availabilityMessage = document.getElementById('availability-message');
    const submitBtn = document.getElementById('submitBtn');
    const acknowledgeCheckbox = document.getElementById('dns_propagation_acknowledged');

    function updateSubmitButton() {
        const isAvailable = !availableIcon.classList.contains('hidden');
        const isAcknowledged = acknowledgeCheckbox.checked;
        const hasReason = reasonTextarea.value.length >= 20;
        const hasSelected = document.getElementById('web_monitor_id').value !== '';

        submitBtn.disabled = !(isAvailable && isAcknowledged && hasReason && hasSelected);
    }

    newSubdomainInput.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        const value = this.value.trim();

        if (value.length < 3) {
            availabilityIndicator.classList.add('hidden');
            availabilityMessage.textContent = '';
            updateSubmitButton();
            return;
        }

        checkTimeout = setTimeout(() => {
            fetch('{{ route("user.subdomain.name-change.check-availability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ subdomain_name: value })
            })
            .then(response => response.json())
            .then(data => {
                availabilityIndicator.classList.remove('hidden');

                if (data.available) {
                    availableIcon.classList.remove('hidden');
                    availableIcon.classList.add('block');
                    unavailableIcon.classList.add('hidden');
                    unavailableIcon.classList.remove('block');
                    availabilityMessage.textContent = '✓ ' + data.message;
                    availabilityMessage.className = 'mt-1 text-sm text-green-600';
                } else {
                    availableIcon.classList.add('hidden');
                    availableIcon.classList.remove('block');
                    unavailableIcon.classList.remove('hidden');
                    unavailableIcon.classList.add('block');
                    availabilityMessage.textContent = '✗ ' + data.message;
                    availabilityMessage.className = 'mt-1 text-sm text-red-600';
                }
                updateSubmitButton();
            });
        }, 500);
    });

    acknowledgeCheckbox.addEventListener('change', updateSubmitButton);
    reasonTextarea.addEventListener('input', updateSubmitButton);
    document.getElementById('web_monitor_id').addEventListener('change', updateSubmitButton);
</script>
@endpush
