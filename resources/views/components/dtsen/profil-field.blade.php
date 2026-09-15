@props([
    'profil',       // App\Services\Dtsen\ProfilPemohon
    'field',        // nama field di profil: nama|nip|jabatan|telepon|email
    'name',         // nama input pada form
    'label',
    'type' => 'text',
    'required' => false,
    'value' => null,    // nilai tersimpan, dipakai bila profil belum terisi
    'placeholder' => null,
])

@php
    $terkunci = $profil->terisi($field);
    // Nilai profil menang: kolom terkunci ditimpa juga di sisi server saat validasi,
    // jadi tampilan dan data tersimpan tidak mungkin berbeda.
    $isi = $terkunci ? $profil->nilai($field) : old($name, $value);
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if ($required && ! $terkunci)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if ($terkunci)
        <div class="relative">
            <input type="{{ $type }}" name="{{ $name }}" value="{{ $isi }}" readonly
                   class="w-full px-4 py-2 pr-9 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400" aria-hidden="true">
                {{-- Gembok: menandai kolom yang mengikuti profil --}}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </span>
        </div>
        <p class="text-xs text-gray-500 mt-1">
            Terisi dari profil Anda.
            <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:underline">Ubah di Profil</a>
        </p>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $isi }}" @required($required)
               @if ($placeholder) placeholder="{{ $placeholder }}" @endif
               class="w-full px-4 py-2 border border-gray-300 rounded-lg @error($name) border-red-500 @enderror">
        <p class="text-xs text-amber-600 mt-1">
            Belum ada di profil Anda — isi di sini, lalu lengkapi
            <a href="{{ route('profile.edit') }}" class="underline">Profil</a> agar tidak perlu diketik lagi.
        </p>
    @endif

    @error($name)<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
</div>
