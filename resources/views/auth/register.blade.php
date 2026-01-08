<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-lg mt-10">
        @csrf

        <h2 class="text-2xl font-bold text-green-700 text-center mb-6">Registrasi Akun</h2>

        <!-- Name -->
        <div class="mb-4">
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="name"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400"
                type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- ID Type Selection -->
        <div class="mb-4">
            <x-input-label for="id_type" :value="__('Jenis Identitas')" class="text-lg font-semibold text-gray-700" />
            <select id="id_type" name="id_type"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400"
                required>
                <option value="">-- Pilih Jenis Identitas --</option>
                <option value="nik" {{ old('id_type') == 'nik' ? 'selected' : '' }}>NIK (16 Digit)</option>
                <option value="nip" {{ old('id_type') == 'nip' ? 'selected' : '' }}>NIP (18 Digit)</option>
            </select>
            <x-input-error :messages="$errors->get('id_type')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- NIK Field -->
        <div class="mb-4" id="nik-field" style="display: none;">
            <x-input-label for="nik" :value="__('NIK (16 Digit)')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="nik"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400 font-mono"
                type="text" name="nik" :value="old('nik')" maxlength="16" pattern="\d{16}"
                placeholder="Contoh: 1234567890123456" />
            <p class="text-xs text-gray-500 mt-1">NIK harus tepat 16 digit angka</p>
            <x-input-error :messages="$errors->get('nik')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- NIP Field -->
        <div class="mb-4" id="nip-field" style="display: none;">
            <x-input-label for="nip" :value="__('NIP (18 Digit)')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="nip"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400 font-mono"
                type="text" name="nip" :value="old('nip')" maxlength="18" pattern="\d{18}"
                placeholder="Contoh: 123456789012345678" />
            <p class="text-xs text-gray-500 mt-1">NIP harus tepat 18 digit angka</p>
            <x-input-error :messages="$errors->get('nip')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Nomor HP / WhatsApp -->
        <div class="mb-4">
            <x-input-label for="phone" :value="__('Nomor HP / WhatsApp')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="phone"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400"
                type="text" name="phone" :value="old('phone')" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="email"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400"
                type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password (Gunakan huruf besar, kecil, angka & simbol)')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="password"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400"
                type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="password_confirmation"
                class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400"
                type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- reCAPTCHA -->
        <div class="mb-4">
            {!! NoCaptcha::display() !!}
            @error('g-recaptcha-response')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-base text-gray-600 hover:text-gray-900 underline" href="{{ route('login') }}">
                {{ __('Sudah punya akun? Login di sini') }}
            </a>

            <x-primary-button
                class="text-base px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-md shadow">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
    {!! NoCaptcha::renderJs() !!}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idTypeSelect = document.getElementById('id_type');
            const nikField = document.getElementById('nik-field');
            const nipField = document.getElementById('nip-field');
            const nikInput = document.getElementById('nik');
            const nipInput = document.getElementById('nip');

            function toggleFields() {
                const selectedType = idTypeSelect.value;

                if (selectedType === 'nik') {
                    nikField.style.display = 'block';
                    nipField.style.display = 'none';
                    nikInput.required = true;
                    nipInput.required = false;
                    nipInput.value = '';
                } else if (selectedType === 'nip') {
                    nikField.style.display = 'none';
                    nipField.style.display = 'block';
                    nikInput.required = false;
                    nikInput.value = '';
                    nipInput.required = true;
                } else {
                    nikField.style.display = 'none';
                    nipField.style.display = 'none';
                    nikInput.required = false;
                    nipInput.required = false;
                }
            }

            // Initial state on page load
            toggleFields();

            // Update on change
            idTypeSelect.addEventListener('change', toggleFields);

            // Validate digits only
            nikInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
            });

            nipInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
            });
        });
    </script>
</x-guest-layout>
