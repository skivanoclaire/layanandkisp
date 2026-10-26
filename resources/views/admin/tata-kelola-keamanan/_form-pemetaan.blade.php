{{-- Isian butir pemetaan. Variabel: $pemetaan (nullable), $praktikList (koleksi praktik), $praktikId (nullable) --}}
@php
    $isoDipilih = old('iso_items', $pemetaan->iso_items ?? []);
    $regDipilih = old('regulasi_items', $pemetaan->regulasi_items ?? []);
    $baru = config('tata_kelola_keamanan.iso_kontrol_baru');
    $kelompokKontrol = collect(config('tata_kelola_keamanan.iso_kontrol'))->groupBy(fn ($v, $k) => explode('.', $k)[1], true);
@endphp
<div class="space-y-3">
    <div>
        <label class="block text-sm font-medium text-gray-700">Praktik COBIT 2019</label>
        <select name="praktik_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
            @foreach ($praktikList as $pr)
                <option value="{{ $pr->id }}" @selected(($pemetaan->praktik_id ?? $praktikId) == $pr->id)>{{ $pr->kode }} {{ $pr->nama }}{{ $pr->nama_id ? ' — '.$pr->nama_id : '' }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <div>
            <p class="text-sm font-medium text-gray-700">Klausul ISO/IEC 27001:2022</p>
            <div class="mt-1 h-48 overflow-y-auto border rounded-lg p-2 text-xs space-y-0.5">
                @foreach (config('tata_kelola_keamanan.iso_klausul') as $k => $judul)
                    <label class="block"><input type="checkbox" name="iso_items[]" value="{{ $k }}" @checked(in_array($k, $isoDipilih, true))> {{ substr($k, 1) }} {{ $judul }} <span class="text-gray-500">— {{ \App\Services\TataKelolaKeamanan\Istilah::isoId($k) }}</span></label>
                @endforeach
            </div>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-700">Kontrol Annex A <span class="text-xs text-gray-500">(★ = baru di versi 2022)</span></p>
            <div class="mt-1 h-48 overflow-y-auto border rounded-lg p-2 text-xs space-y-0.5">
                @foreach ($kelompokKontrol as $tema => $items)
                    <p class="font-semibold text-gray-600 mt-1">{{ $tema }} — {{ config("tata_kelola_keamanan.iso_tema.$tema") }}</p>
                    @foreach ($items as $k => $judul)
                        <label class="block"><input type="checkbox" name="iso_items[]" value="{{ $k }}" @checked(in_array($k, $isoDipilih, true))> {{ $k }} {{ $judul }} {{ in_array($k, $baru, true) ? '★' : '' }} <span class="text-gray-500">— {{ \App\Services\TataKelolaKeamanan\Istilah::isoId($k) }}</span></label>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    <div>
        <p class="text-sm font-medium text-gray-700">Ketentuan regulasi</p>
        <div class="mt-1 h-40 overflow-y-auto border rounded-lg p-2 text-xs space-y-0.5">
            @foreach (config('tata_kelola_keamanan.regulasi') as $k => $judul)
                <label class="block"><input type="checkbox" name="regulasi_items[]" value="{{ $k }}" @checked(in_array($k, $regDipilih, true))> {{ $judul }}</label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Contoh bukti yang diharapkan</label>
        <textarea name="bukti_diharapkan" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('bukti_diharapkan', $pemetaan->bukti_diharapkan ?? '') }}</textarea>
    </div>

    @if ($pemetaan)
        <div>
            <label class="block text-sm font-medium text-gray-700">Catatan revisi (berdasarkan hasil verifikasi atau saran FGD)</label>
            <textarea name="catatan_revisi" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('catatan_revisi', $pemetaan->catatan_revisi) }}</textarea>
        </div>
    @endif
</div>
