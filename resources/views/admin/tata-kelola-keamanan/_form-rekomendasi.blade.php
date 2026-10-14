{{-- Variabel: $rk (nullable), $praktiks --}}
<div class="grid grid-cols-1 md:grid-cols-6 gap-2">
    <select name="praktik_id" class="md:col-span-2 rounded-lg border-gray-300 text-sm">
        <option value="">— praktik (opsional) —</option>
        @foreach ($praktiks as $pr)
            <option value="{{ $pr->id }}" @selected(($rk->praktik_id ?? null) == $pr->id)>{{ $pr->kode }} {{ \Illuminate\Support\Str::limit($pr->nama_id ?? $pr->nama, 60) }}</option>
        @endforeach
    </select>
    <select name="iso_kode" class="md:col-span-2 rounded-lg border-gray-300 text-sm">
        <option value="">— kontrol ISO (opsional) —</option>
        @foreach (config('tata_kelola_keamanan.iso_kontrol') as $k => $judul)
            <option value="{{ $k }}" @selected(($rk->iso_kode ?? null) === $k)>{{ $k }} {{ $judul }} — {{ \App\Services\TataKelolaKeamanan\Istilah::isoId($k) }}</option>
        @endforeach
    </select>
    <select name="horizon" class="rounded-lg border-gray-300 text-sm">
        @foreach (config('tata_kelola_keamanan.horizon') as $k => $label)
            <option value="{{ $k }}" @selected(($rk->horizon ?? 'pendek') === $k)>{{ $label }}</option>
        @endforeach
    </select>
    <input type="number" name="prioritas" min="1" max="999" value="{{ $rk->prioritas ?? 1 }}" class="rounded-lg border-gray-300 text-sm" title="Prioritas (1 = tertinggi)">
    <textarea name="uraian" rows="2" required placeholder="Uraian rekomendasi" class="md:col-span-4 rounded-lg border-gray-300 text-sm">{{ $rk->uraian ?? '' }}</textarea>
    <input name="penanggung_jawab" value="{{ $rk->penanggung_jawab ?? '' }}" placeholder="Penanggung jawab" class="md:col-span-2 rounded-lg border-gray-300 text-sm">
</div>
