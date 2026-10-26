{{-- Isian data sesi FGD. Variabel: $s (SesiFgd nullable), $respondens (koleksi responden evaluasi) --}}
@php $hadir = array_map('intval', old('peserta', $s->peserta ?? [])); @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
        <input type="date" name="tanggal" value="{{ old('tanggal', $s?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Tempat</label>
        <input name="tempat" value="{{ old('tempat', $s->tempat ?? '') }}" placeholder="mis. Ruang rapat DKISP" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
    </div>
</div>
<div>
    <p class="text-sm font-medium text-gray-700">Informan yang hadir</p>
    @forelse ($respondens as $r)
        <label class="block text-sm"><input type="checkbox" name="peserta[]" value="{{ $r->id }}" @checked(in_array($r->id, $hadir, true))>
            {{ $r->user->name ?? '-' }} <span class="text-gray-500">— {{ $r->jabatan }}{{ $r->peran_raci ? ' ('.$r->peran_raci.')' : '' }}</span></label>
    @empty
        <p class="text-xs text-gray-500">Belum ada responden. Daftarkan informan di tab Responden, atau tuliskan peserta di bawah.</p>
    @endforelse
</div>
<div>
    <label class="block text-sm font-medium text-gray-700">Peserta lain <span class="text-xs text-gray-500">(nama dan jabatan, bila tidak terdaftar sebagai responden)</span></label>
    <textarea name="peserta_lain" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('peserta_lain', $s->peserta_lain ?? '') }}</textarea>
</div>
