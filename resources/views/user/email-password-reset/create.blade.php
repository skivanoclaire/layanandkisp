@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Reset Password Email</h1>
            <p class="text-gray-600 mt-2">Ajukan permintaan reset password untuk email kaltaraprov Anda</p>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Info Box -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Informasi Penting:</strong>
                    </p>
                    <ul class="list-disc list-inside text-sm text-blue-700 mt-2 space-y-1">
                        <li>Password harus memenuhi standar keamanan minimal</li>
                        <li>Permintaan akan diproses oleh admin</li>
                        <li>Anda akan menerima notifikasi setelah permintaan diproses</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">Form Permintaan Reset Password</h2>
            </div>

            <form action="{{ route('user.email-password-reset.store') }}" method="POST" class="p-6 space-y-6" x-data="passwordStrength()">
                @csrf

                <!-- NIP Field (Read Only) -->
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700 mb-2">
                        NIP (18 Digit)
                    </label>
                    <div class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                        <p class="text-gray-800 font-mono text-lg font-semibold">{{ $user->nip }}</p>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">NIP dari data profil Anda</p>
                </div>

                <!-- Email Preview (Read Only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email yang akan direset
                    </label>
                    <div class="bg-blue-50 px-4 py-3 rounded-lg border-2 border-blue-200 flex items-center">
                        <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="text-blue-900 font-mono text-lg font-semibold">{{ $emailAddress }}</p>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Email yang terdaftar di sistem</p>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               name="password"
                               id="password"
                               placeholder="Masukkan password baru"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('password') border-red-500 @enderror"
                               required
                               x-on:input="checkStrength($event.target.value)">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Password Strength Indicator -->
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700">Kekuatan Password:</span>
                            <span class="text-xs font-semibold" :class="strengthColor" x-text="strengthText"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300"
                                 :class="strengthColor"
                                 :style="`width: ${strength}%`"></div>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="mt-3 space-y-1">
                        <p class="text-xs font-medium text-gray-700 mb-2">Password harus memenuhi:</p>
                        <div class="space-y-1">
                            <div class="flex items-center text-xs">
                                <svg class="h-4 w-4 mr-2" :class="requirements.length ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span :class="requirements.length ? 'text-green-700' : 'text-gray-600'">Minimal 10 karakter</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <svg class="h-4 w-4 mr-2" :class="requirements.uppercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span :class="requirements.uppercase ? 'text-green-700' : 'text-gray-600'">Minimal 1 huruf besar (A-Z)</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <svg class="h-4 w-4 mr-2" :class="requirements.lowercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span :class="requirements.lowercase ? 'text-green-700' : 'text-gray-600'">Minimal 1 huruf kecil (a-z)</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <svg class="h-4 w-4 mr-2" :class="requirements.number ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span :class="requirements.number ? 'text-green-700' : 'text-gray-600'">Minimal 1 angka (0-9)</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <svg class="h-4 w-4 mr-2" :class="requirements.symbol ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span :class="requirements.symbol ? 'text-green-700' : 'text-gray-600'">Minimal 1 simbol (!@#$%^&*)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Confirmation Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPasswordConfirm ? 'text' : 'password'"
                               name="password_confirmation"
                               id="password_confirmation"
                               placeholder="Masukkan ulang password baru"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               required>
                        <button type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg x-show="!showPasswordConfirm" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPasswordConfirm" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <a href="{{ route('user.email-password-reset.index') }}"
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Kembali
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Ajukan Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function passwordStrength() {
    return {
        showPassword: false,
        showPasswordConfirm: false,
        strength: 0,
        strengthText: 'Sangat Lemah',
        strengthColor: 'text-gray-400',
        requirements: {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            symbol: false
        },

        checkStrength(password) {
            let score = 0;

            // Check requirements
            this.requirements.length = password.length >= 10;
            this.requirements.uppercase = /[A-Z]/.test(password);
            this.requirements.lowercase = /[a-z]/.test(password);
            this.requirements.number = /[0-9]/.test(password);
            this.requirements.symbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

            // Calculate score
            if (this.requirements.length) score += 20;
            if (this.requirements.uppercase) score += 20;
            if (this.requirements.lowercase) score += 20;
            if (this.requirements.number) score += 20;
            if (this.requirements.symbol) score += 20;

            this.strength = score;

            // Set text and color
            if (score === 0) {
                this.strengthText = 'Sangat Lemah';
                this.strengthColor = 'text-gray-400 bg-gray-400';
            } else if (score <= 40) {
                this.strengthText = 'Lemah';
                this.strengthColor = 'text-red-500 bg-red-500';
            } else if (score <= 60) {
                this.strengthText = 'Sedang';
                this.strengthColor = 'text-orange-500 bg-orange-500';
            } else if (score <= 80) {
                this.strengthText = 'Kuat';
                this.strengthColor = 'text-yellow-500 bg-yellow-500';
            } else {
                this.strengthText = 'Sangat Kuat';
                this.strengthColor = 'text-green-500 bg-green-500';
            }
        }
    }
}
</script>
@endsection
