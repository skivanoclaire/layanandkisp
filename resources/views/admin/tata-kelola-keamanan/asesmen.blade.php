@extends('layouts.authenticated')

@section('title', '- Asesmen Kapabilitas')
@section('header-title', 'Tata Kelola Keamanan: Asesmen Tingkat Kapabilitas')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 5a — Asesmen tingkat kapabilitas',
            'poin' => [
                'Langkah' => '1) Masukkan aktivitas COBIT per praktik dan per tingkat (2–5) dari buku <em>Governance and Management Objectives</em>, satu per satu atau lewat kotak impor. 2) Informan yang ditugaskan menjawab dari akunnya (ya/belum/tidak tahu). 3) Wawancarai informan dan periksa bukti. 4) Tetapkan setiap aktivitas <em>Terpenuhi</em> atau <em>Belum</em>, catat bukti dan sumbernya, unggah lampiran bila perlu.',
                'Format impor' => 'Satu baris satu aktivitas: <code>KODE_PRAKTIK | LEVEL | uraian | bukti diharapkan</code>, contoh <code>APO13.01 | 2 | Menetapkan ruang lingkup SMKI | Dokumen ruang lingkup</code>.',
                'Kapan "Terpenuhi"' => 'Hanya bila ada bukti yang dapat ditunjukkan (dokumen, rekaman sistem, atau hasil observasi), bukan sekadar pengakuan lisan. Jawaban informan "ya" tanpa bukti tetap ditetapkan <em>Belum</em>; penilaian mandiri cenderung lebih tinggi daripada hasil validasi bukti (Afifah dkk, 2025).',
                'Berkas informan' => 'Informan dapat melampirkan berkas bukti saat menjawab. Buka berkasnya melalui tautan 📎 di bagian <em>Jawaban informan</em> setiap aktivitas, periksa keasliannya, lalu tetapkan terpenuhi/belum.',
                'Arti rating' => 'N = hampir tidak ada aktivitas terpenuhi (≤ 15%), P = sebagian (≤ 50%), L = sebagian besar (≤ 85%), F = hampir semua (> 85%). Sebuah tingkat tercapai bila ratingnya F; aplikasi menghitungnya otomatis.',
                'Berikutnya' => 'Setelah semua objektif lengkap, isi tab 5b. Status Kontrol.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Tahap 5a — Asesmen tingkat kapabilitas</h2>
            <p class="text-sm text-gray-600 mt-1">Masukkan aktivitas COBIT 2019 per tingkat (dari buku Governance and Management Objectives), lalu nyatakan setiap aktivitas
                <em>terpenuhi</em> atau <em>belum</em> berdasarkan bukti dokumen, wawancara, dan observasi. Aplikasi menghitung pencapaian dan rating N/P/L/F secara otomatis.
                Suatu tingkat tercapai bila ratingnya F; penilaian berhenti pada tingkat pertama yang belum F.</p>
        </div>

        @if ($objektifs->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-600">Belum ada objektif yang dipilih.</div>
        @else
            {{-- Ringkasan semua objektif --}}
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="py-2 pr-3">Objektif</th>
                            <th class="py-2 px-3 text-center">Aktivitas</th>
                            <th class="py-2 px-3 text-center">Target</th>
                            <th class="py-2 px-3 text-center">Saat ini</th>
                            <th class="py-2 px-3 text-center">Kesenjangan</th>
                            <th class="py-2 px-3 text-center">Asesmen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($objektifs as $o)
                            @php $r = $ringkasan[$o->kode] ?? null; @endphp
                            <tr class="{{ $objektif && $objektif->kode === $o->kode ? 'bg-green-50' : '' }}">
                                <td class="py-2 pr-3"><a href="{{ route('admin.tata-kelola-keamanan.asesmen', [$evaluasi, 'objektif' => $o->kode]) }}" class="text-green-700 hover:underline font-mono">{{ $o->kode }}</a> {{ $o->nama }}<br><span class="text-xs text-gray-600">{{ $o->nama_id }}</span></td>
                                <td class="py-2 px-3 text-center">{{ $r['hasil']['jumlah_aktivitas'] ?? 0 }}</td>
                                <td class="py-2 px-3 text-center">{{ $r['target'] ?? '-' }}</td>
                                <td class="py-2 px-3 text-center font-semibold">{{ $r['hasil']['level'] ?? '-' }}</td>
                                <td class="py-2 px-3 text-center">{{ $r['gap'] ?? '-' }}</td>
                                <td class="py-2 px-3 text-center text-xs">{{ ($r['hasil']['lengkap'] ?? false) ? 'lengkap' : 'belum lengkap' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($objektif)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $objektif->kode }} {{ $objektif->nama }} <span class="font-normal text-gray-600">({{ $objektif->nama_id }})</span>:
                        tingkat saat ini <span class="text-green-700">{{ $hasil['level'] ?? '-' }}</span>, target {{ $objektif->target_level ?? '-' }}
                        @unless ($hasil['lengkap'])
                            <span class="text-xs font-normal text-yellow-700">(masih ada aktivitas yang belum dinilai)</span>
                        @endunless
                    </h3>
                    @include('admin.tata-kelola-keamanan._ringkasan-level', ['hasil' => $hasil])
                </div>

                @foreach ($praktiks as $pr)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
                        <h4 class="font-semibold text-gray-900"><span class="font-mono">{{ $pr->kode }}</span> {{ $pr->nama }}<br><span class="text-sm font-normal text-gray-600">{{ $pr->nama_id }}</span></h4>
                        @forelse ($pr->aktivitas->groupBy('level') as $level => $items)
                            <p class="mt-3 text-xs font-semibold text-gray-500">LEVEL {{ $level }}</p>
                            @foreach ($items as $a)
                                <div class="mt-2 p-3 rounded-lg border {{ $a->terpenuhi === true ? 'border-green-300 bg-green-50' : ($a->terpenuhi === false ? 'border-red-200 bg-red-50' : '') }}">
                                    <div class="flex justify-between gap-2">
                                        <p class="text-sm text-gray-900">{{ $a->uraian }}</p>
                                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.aktivitas.destroy', [$evaluasi, $a]) }}" onsubmit="return confirm('Hapus aktivitas ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs text-red-600 hover:underline">hapus</button>
                                        </form>
                                    </div>
                                    @if ($a->bukti_diharapkan)
                                        <p class="text-xs text-gray-500 mt-1">Bukti diharapkan: {{ $a->bukti_diharapkan }}</p>
                                    @endif
                                    @if ($a->jawabans->isNotEmpty())
                                        @php $jw = $a->jawabans->countBy('jawaban'); @endphp
                                        <details class="mt-2 text-xs">
                                            <summary class="cursor-pointer text-blue-800">
                                                Jawaban informan: {{ $jw['ya'] ?? 0 }} ya, {{ $jw['tidak'] ?? 0 }} belum, {{ $jw['tidak_tahu'] ?? 0 }} tidak tahu{{ ($nb = $a->jawabans->sum(fn ($x) => $x->lampirans->count())) ? ', '.$nb.' berkas' : '' }}
                                                <span class="text-gray-500">(bahan wawancara dan pemeriksaan bukti, bukan hasil akhir)</span>
                                            </summary>
                                            <ul class="mt-1 ml-4 list-disc">
                                                @foreach ($a->jawabans as $j)
                                                    <li><strong>{{ $j->responden->jabatan ?? '' }}</strong> ({{ $j->responden->user->name ?? '' }}):
                                                        {{ \App\Models\TataKelolaKeamanan\JawabanAktivitas::JAWABAN[$j->jawaban] ?? $j->jawaban }}{{ $j->keterangan ? ' — '.$j->keterangan : '' }}
                                                        @foreach ($j->lampirans as $l)
                                                            <br>📎 <a href="{{ route('admin.tata-kelola-keamanan.lampiran-informan', [$evaluasi, $l]) }}" class="text-green-700 hover:underline">{{ $l->nama_asli }}</a>
                                                            <span class="text-gray-400">({{ $l->ukuranTerbaca() }})</span>
                                                        @endforeach
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </details>
                                    @endif
                                    <form method="POST" action="{{ route('admin.tata-kelola-keamanan.aktivitas.nilai', [$evaluasi, $a]) }}" enctype="multipart/form-data" class="mt-2 grid grid-cols-1 md:grid-cols-6 gap-2 text-sm">
                                        @csrf
                                        <div class="md:col-span-1">
                                            <label class="block"><input type="radio" name="terpenuhi" value="1" @checked($a->terpenuhi === true)> Terpenuhi</label>
                                            <label class="block"><input type="radio" name="terpenuhi" value="0" @checked($a->terpenuhi === false)> Belum</label>
                                            <label class="block text-gray-500"><input type="radio" name="terpenuhi" value="" @checked($a->terpenuhi === null)> Belum dinilai</label>
                                        </div>
                                        <textarea name="bukti_ditemukan" rows="2" placeholder="Bukti yang ditemukan" class="md:col-span-2 rounded-lg border-gray-300 text-sm">{{ $a->bukti_ditemukan }}</textarea>
                                        <div class="md:col-span-1 text-xs">
                                            @foreach (config('tata_kelola_keamanan.sumber_bukti') as $k => $label)
                                                <label class="block"><input type="checkbox" name="sumber_bukti[]" value="{{ $k }}" @checked(in_array($k, $a->sumber_bukti ?? [], true))> {{ $label }}</label>
                                            @endforeach
                                        </div>
                                        <div class="md:col-span-1 space-y-1">
                                            <input name="informan" value="{{ $a->informan }}" placeholder="Informan" class="w-full rounded-lg border-gray-300 text-sm">
                                            <input type="file" name="lampiran" class="w-full text-xs">
                                            @if ($a->lampiran_path)
                                                <a href="{{ route('admin.tata-kelola-keamanan.aktivitas.lampiran', [$evaluasi, $a]) }}" class="text-xs text-green-700 hover:underline">unduh lampiran</a>
                                            @endif
                                        </div>
                                        <div class="md:col-span-1">
                                            <button class="px-3 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        @empty
                            <p class="text-sm text-gray-400 mt-2">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                @endforeach

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Tambah aktivitas</h4>
                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.aktivitas.store', $evaluasi) }}" class="space-y-2">
                            @csrf
                            <select name="praktik_id" class="w-full rounded-lg border-gray-300 text-sm">
                                @foreach ($praktiks as $pr)
                                    <option value="{{ $pr->id }}">{{ $pr->kode }} {{ $pr->nama }}{{ $pr->nama_id ? ' — '.$pr->nama_id : '' }}</option>
                                @endforeach
                            </select>
                            <select name="level" class="w-full rounded-lg border-gray-300 text-sm">
                                @foreach (config('tata_kelola_keamanan.aturan.level_dinilai') as $l)
                                    <option value="{{ $l }}">Level {{ $l }}</option>
                                @endforeach
                            </select>
                            <textarea name="uraian" rows="2" required placeholder="Uraian aktivitas (dari COBIT 2019 G&MO)" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                            <input name="bukti_diharapkan" placeholder="Bukti yang diharapkan (opsional)" class="w-full rounded-lg border-gray-300 text-sm">
                            <button class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Tambah</button>
                        </form>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Impor banyak aktivitas</h4>
                        <p class="text-xs text-gray-500 mb-2">Satu baris per aktivitas: <code>KODE_PRAKTIK | LEVEL | uraian | bukti diharapkan (opsional)</code></p>
                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.aktivitas.impor', $evaluasi) }}" class="space-y-2">
                            @csrf
                            <textarea name="teks" rows="6" required class="w-full rounded-lg border-gray-300 text-xs font-mono"
                                      placeholder="APO13.01 | 2 | Menetapkan ruang lingkup dan batasan SMKI | Dokumen ruang lingkup SMKI"></textarea>
                            <button class="px-4 py-2 text-sm font-medium rounded-lg border border-green-600 text-green-700 hover:bg-green-50">Impor</button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
