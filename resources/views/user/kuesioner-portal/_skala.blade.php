{{-- Satu baris pilihan 1–5. Parameter: $nama, $pilihan, $nilai, $pratinjau --}}
<div class="grid grid-cols-5 gap-1.5" data-grup="{{ $nama }}">
    @foreach ($pilihan as $skor => $label)
        <label class="flex flex-col items-center justify-start gap-0.5 px-1 py-2 rounded-md border border-gray-300 text-center cursor-pointer select-none transition
                      hover:border-green-500 has-checked:bg-green-600 has-checked:border-green-600 has-checked:text-white">
            <input type="radio" class="sr-only" name="{{ $nama }}" value="{{ $skor }}" data-wajib
                   @checked((string) $nilai === (string) $skor) @disabled($pratinjau ?? false)>
            <span class="text-sm font-semibold">{{ $skor }}</span>
            <span class="text-[10px] leading-tight hidden sm:block">{{ $label }}</span>
        </label>
    @endforeach
</div>
