{{-- Dropdown kolom bertipe "pilihan". Butuh: $kunci, $definisi, $name, $nilai, $kelasInput --}}
@php
    $opsi = $definisi['opsi'] ?? [];
    $nilaiLama = $nilai !== null && $nilai !== '' && ! in_array($nilai, $opsi, true);
    $otomatis = $definisi['otomatis'] ?? null;
@endphp
<select name="{{ $name }}" id="f-{{ $kunci }}" class="{{ $kelasInput }}"
        @if ($otomatis)
            data-otomatis-dari="f-{{ $otomatis['dari'] }}"
            data-otomatis-terisi="{{ $otomatis['terisi'] }}"
            data-otomatis-kosong="{{ $otomatis['kosong'] }}"
        @endif>
    <option value="">— Pilih —</option>
    @foreach ($opsi as $o)
        <option value="{{ $o }}" @selected($nilai === $o)>{{ $o }}</option>
    @endforeach
    @if ($nilaiLama)
        <option value="{{ $nilai }}" selected>{{ $nilai }} (nilai lama)</option>
    @endif
</select>
@if ($nilaiLama)
    <p class="text-xs text-orange-600 mt-1">Nilai tersimpan "{{ $nilai }}" tidak termasuk pilihan baku. Pilih salah satu opsi sebelum menyimpan.</p>
@elseif ($otomatis)
    <p class="text-xs text-gray-500 mt-1">Terisi otomatis dari kolom URL; tetap dapat diubah.</p>
@endif

@once
    @push('scripts')
        <script>
            // Kolom pilihan yang mengikuti kolom lain (mis. Metode Layanan mengikuti URL),
            // berhenti mengikuti begitu pengguna memilih sendiri.
            document.querySelectorAll('select[data-otomatis-dari]').forEach(function (pilihan) {
                const sumber = document.getElementById(pilihan.dataset.otomatisDari);
                if (! sumber) return;

                let manual = pilihan.value !== '';
                pilihan.addEventListener('change', function () { manual = true; });

                const ikuti = function () {
                    if (manual) return;
                    pilihan.value = sumber.value.trim() !== '' ? pilihan.dataset.otomatisTerisi : pilihan.dataset.otomatisKosong;
                };
                sumber.addEventListener('input', ikuti);
            });
        </script>
    @endpush
@endonce
