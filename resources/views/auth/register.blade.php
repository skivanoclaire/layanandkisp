<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-lg mt-10">
        @csrf

        <h2 class="text-2xl font-bold text-green-700 text-center mb-6">Registrasi Akun</h2>

        <!-- Name -->
        <div class="mb-4">
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="name" class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- NIK/NIP -->
        <div class="mb-4">
            <x-input-label for="nik" :value="__('NIK / NIP')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="nik" class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400" type="text" name="nik" :value="old('nik')" required />
            <x-input-error :messages="$errors->get('nik')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Nomor HP / WhatsApp -->
        <div class="mb-4">
            <x-input-label for="phone" :value="__('Nomor HP / WhatsApp')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="phone" class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400" type="text" name="phone" :value="old('phone')" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="email" class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password (Gunakan huruf besar, kecil, angka & simbol)')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="password" class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-600 text-sm" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-lg font-semibold text-gray-700" />
            <x-text-input id="password_confirmation" class="mt-2 w-full text-base p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-400" type="password" name="password_confirmation" required autocomplete="new-password" />
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

            <x-primary-button class="text-base px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-md shadow">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
    {!! NoCaptcha::renderJs() !!}
</x-guest-layout>
