@extends('layouts.authenticated')

@section('title', '- Prioritas Objektif')
@section('header-title', 'Tata Kelola Keamanan: Prioritas Objektif COBIT 2019')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 2 — Skor kepentingan dan pemilihan objektif',
            'poin' => [
                'Sumber data' => 'Hasil COBIT 2019 Design Toolkit setelah nilai akhir design factors dimasukkan. Ambil nilai <em>relative importance</em> setiap objektif (rentang −100 s.d. 100) dari ringkasan Toolkit.',
                'Cara mengisi' => '1) Salin skor ke kolom <em>Skor</em> untuk ke-40 objektif. 2) Klik <strong>Simpan</strong>. 3) Klik <strong>Terapkan saran pemilihan</strong>. 4) Periksa hasilnya; bila Anda menambah atau mengurangi objektif, tulis alasannya di kolom <em>Alasan</em>.',
                'Kolom Terkait keamanan' => 'Sudah dicentang untuk objektif yang umumnya berkaitan dengan keamanan informasi. Ubah bila menurut kajian Anda berbeda, dan catat alasannya.',
                'Target' => 'Muncul otomatis dari skor. Isi <em>Target manual</em> hanya bila ada dasar kuat, misalnya ketentuan regulasi, dan tulis alasannya.',
                'Contoh' => 'APO13 skor 90 → target level 4; EDM03 skor 70 → target level 3.',
                'Berikutnya' => 'Daftarkan informan RACI untuk objektif terpilih di tab Responden, lalu lanjut ke tab 3. Pemetaan.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Tahap 2 — Skor kepentingan dan pemilihan objektif</h2>
            <ul class="text-sm text-gray-600 mt-2 list-disc list-inside space-y-1">
                <li>Salin skor kepentingan relatif (−100 s.d. 100) dari hasil COBIT 2019 Design Toolkit ke kolom <em>Skor</em>.</li>
                <li>Target dihitung otomatis: skor ≥ 75 → level 4; 50–74 → level 3; 25–49 → level 2; di bawahnya → level 1. Isi kolom <em>Target manual</em> hanya bila ada alasan khusus.</li>
                <li>Objektif yang dinilai: skor ≥ {{ config('tata_kelola_keamanan.aturan.skor_minimal_dipilih') }}, terkait keamanan, paling banyak {{ config('tata_kelola_keamanan.aturan.maksimal_objektif') }}.
                    Saran saat ini: <strong>{{ implode(', ', $saran) ?: 'belum ada (isi skor terlebih dahulu)' }}</strong>.</li>
            </ul>
            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.objektif.saran', $evaluasi) }}" class="mt-3">
                @csrf
                <button class="px-4 py-2 text-sm font-medium rounded-lg border border-green-600 text-green-700 hover:bg-green-50">Terapkan saran pemilihan</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.objektif.update', $evaluasi) }}">
            @csrf @method('PUT')
            <div class="bg-white rounded-lg shadow-sm p-6 mb-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="py-2 pr-3">Kode</th>
                            <th class="py-2 px-3">Objektif</th>
                            <th class="py-2 px-3 w-24">Skor</th>
                            <th class="py-2 px-3 text-center">Terkait keamanan</th>
                            <th class="py-2 px-3 text-center">Target</th>
                            <th class="py-2 px-3 w-24">Target manual</th>
                            <th class="py-2 px-3 text-center">Dinilai</th>
                            <th class="py-2 px-3">Alasan / catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @php $domain = null; @endphp
                        @foreach ($objektifs as $o)
                            @if ($domain !== $o->domain)
                                @php $domain = $o->domain; @endphp
                                <tr class="bg-gray-50">
                                    <td colspan="8" class="py-1 px-2 text-xs font-semibold text-gray-600">{{ $domain }} — {{ config("tata_kelola_keamanan.domain.$domain") }} ({{ \App\Services\TataKelolaKeamanan\Istilah::domain($domain) }})</td>
                                </tr>
                            @endif
                            <tr class="{{ $o->dipilih ? 'bg-green-50' : '' }} {{ in_array($o->kode, $saran, true) ? 'font-medium' : '' }}">
                                <td class="py-1 pr-3 font-mono">{{ $o->kode }}</td>
                                <td class="py-1 px-3">{{ $o->nama }}<br><span class="text-xs text-gray-700">{{ $o->nama_id }}</span><br><span class="text-xs text-gray-400">{{ $o->penjelasan }}</span></td>
                                <td class="py-1 px-3"><input type="number" min="-100" max="100" name="skor[{{ $o->kode }}]" value="{{ old("skor.{$o->kode}", $o->skor_kepentingan) }}" class="w-20 rounded border-gray-300 text-sm"></td>
                                <td class="py-1 px-3 text-center"><input type="checkbox" name="keamanan[{{ $o->kode }}]" value="1" @checked($o->terkait_keamanan)></td>
                                <td class="py-1 px-3 text-center">{{ $o->target_level ?? '-' }}</td>
                                <td class="py-1 px-3">
                                    <select name="target[{{ $o->kode }}]" class="rounded border-gray-300 text-sm">
                                        <option value="">otomatis</option>
                                        @for ($l = 1; $l <= 5; $l++)
                                            <option value="{{ $l }}" @selected($o->target_manual === $l)>{{ $l }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td class="py-1 px-3 text-center"><input type="checkbox" name="dipilih[{{ $o->kode }}]" value="1" @checked($o->dipilih)></td>
                                <td class="py-1 px-3"><input name="alasan[{{ $o->kode }}]" value="{{ $o->alasan }}" class="w-full min-w-[12rem] rounded border-gray-300 text-sm"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="px-5 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan</button>
            <p class="text-xs text-gray-500 mt-2">Saat disimpan, daftar praktik untuk objektif yang dinilai otomatis disiapkan di tahap Pemetaan (bila tersedia di referensi).</p>
        </form>
    </div>
@endsection
