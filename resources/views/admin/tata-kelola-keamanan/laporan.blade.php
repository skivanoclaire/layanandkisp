@extends('layouts.authenticated')

@section('title', '- Laporan Tata Kelola Keamanan')
@section('header-title', 'Tata Kelola Keamanan: Laporan Hasil')

@push('styles')
    <style>
        @media print {
            aside, nav, header, .no-print { display: none !important; }
            .print-full { margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
        .lap th, .lap td { border: 1px solid #d1d5db; padding: 4px 6px; vertical-align: top; }
        .lap th { background: #f3f4f6; }
    </style>
@endpush

@section('content')
    <div class="container mx-auto px-4 py-6 print-full">
        <div class="no-print">
            @include('admin.tata-kelola-keamanan._nav')
            <button onclick="window.print()" class="mb-4 px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cetak / simpan PDF</button>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 print-full text-sm">
            <h1 class="text-xl font-semibold text-gray-900">Laporan Hasil — {{ $evaluasi->nama }}</h1>
            <p class="text-gray-600">{{ $evaluasi->instansi }} · dicetak {{ now()->translatedFormat('d F Y H:i') }}
                @if ($evaluasi->is_simulasi) · <strong class="text-yellow-700">DATA SIMULASI</strong> @endif</p>

            {{-- A. Design factors --}}
            <h2 class="mt-6 text-lg font-semibold">A. Nilai akhir design factors</h2>
            <table class="lap w-full mt-2">
                <thead><tr><th>DF</th><th>Nama</th><th>Ringkasan nilai</th></tr></thead>
                <tbody>
                    @foreach (config('tata_kelola_keamanan.design_factors') as $kode => $df)
                        @php $nilai = $designFactors[$kode]->nilai ?? null; @endphp
                        <tr>
                            <td>{{ $kode }}</td>
                            <td>{{ $df['nama'] }}</td>
                            <td>
                                @if (! $nilai)
                                    <span class="text-gray-400">belum diisi</span>
                                @elseif ($df['tipe'] === 'pilihan')
                                    {{ $df['butir'][$nilai['pilihan'] ?? -1] ?? '-' }}
                                @elseif ($df['tipe'] === 'risiko')
                                    @php
                                        $skor = collect($df['butir'])->map(fn ($b, $i) => ['b' => $b, 's' => ($nilai['dampak'][$i] ?? 0) * ($nilai['kemungkinan'][$i] ?? 0)])
                                            ->sortByDesc('s')->take(5);
                                    @endphp
                                    Risiko tertinggi (dampak × kemungkinan):
                                    {{ $skor->map(fn ($x) => $x['b'].' ('.$x['s'].')')->implode('; ') }}
                                @else
                                    {{ collect($df['butir'])->map(fn ($b, $i) => $b.': '.($nilai[$i] ?? '-'))->implode('; ') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- B. Objektif --}}
            <h2 class="mt-6 text-lg font-semibold">B. Skor kepentingan, target, dan objektif yang dinilai</h2>
            <table class="lap w-full mt-2">
                <thead><tr><th>Kode</th><th>Objektif</th><th>Skor</th><th>Target</th><th>Terkait keamanan</th><th>Dinilai</th></tr></thead>
                <tbody>
                    @foreach ($objektifs->filter(fn ($o) => $o->skor_kepentingan !== null || $o->dipilih) as $o)
                        <tr class="{{ $o->dipilih ? 'font-semibold' : '' }}">
                            <td>{{ $o->kode }}</td><td>{{ $o->nama }}<br><span class="text-xs text-gray-600">{{ $o->nama_id }}</span></td><td class="text-center">{{ $o->skor_kepentingan ?? '-' }}</td>
                            <td class="text-center">{{ $o->target_level ?? '-' }}</td><td class="text-center">{{ $o->terkait_keamanan ? 'ya' : '' }}</td>
                            <td class="text-center">{{ $o->dipilih ? 'ya' : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- C. Model integrasi --}}
            <h2 class="mt-6 text-lg font-semibold">C. Model integrasi (butir pemetaan)</h2>
            <table class="lap w-full mt-2">
                <thead><tr><th>Praktik COBIT 2019</th><th>Klausul / kontrol ISO/IEC 27001:2022</th><th>Ketentuan regulasi</th><th>Bukti diharapkan</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($pemetaans as $p)
                        <tr class="{{ $p->status === 'dikeluarkan' ? 'text-gray-400 line-through' : '' }}">
                            <td>{{ $p->praktik->kode }} {{ $p->praktik->nama }}<br><span class="text-xs text-gray-600">{{ $p->praktik->nama_id }}</span></td>
                            <td>{{ implode('; ', array_map([\App\Models\TataKelolaKeamanan\Pemetaan::class, 'labelIso'], $p->iso_items ?? [])) }}</td>
                            <td>{{ implode('; ', array_map(fn ($k) => \App\Services\TataKelolaKeamanan\Istilah::regulasi($k), $p->regulasi_items ?? [])) }}</td>
                            <td>{{ $p->bukti_diharapkan }}</td>
                            <td>{{ \App\Http\Controllers\Admin\TataKelolaKeamanan\EvaluasiController::labelStatusPemetaan($p->status) }}{{ $p->putaran_disepakati ? ' (putaran '.$p->putaran_disepakati.')' : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-gray-400">Belum ada pemetaan.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- D. Delphi --}}
            <h2 class="mt-6 text-lg font-semibold">D. Hasil validasi pakar ({{ $pakarCount }} pakar, {{ $putarans->count() }} putaran)</h2>
            @foreach ($rekapDelphi as $nomor => $rekap)
                <p class="mt-2 font-medium">Putaran {{ $nomor }}: {{ $rekap->where('disepakati', true)->count() }} dari {{ $rekap->count() }} butir disepakati</p>
                <table class="lap w-full mt-1">
                    <thead><tr><th>Praktik</th><th>Klausul/kontrol</th><th>Setuju</th><th>Tidak</th><th>Keputusan</th></tr></thead>
                    <tbody>
                        @foreach ($rekap as $r)
                            <tr>
                                <td>{{ $r['pemetaan']->praktik->kode }}</td><td>{{ implode(', ', $r['pemetaan']->iso_items ?? []) }}</td>
                                <td class="text-center">{{ $r['setuju'] }}</td><td class="text-center">{{ $r['tidak_setuju'] }}</td>
                                <td>{{ $r['jumlah_penilai'] ? ($r['disepakati'] ? 'disepakati' : 'belum disepakati') : 'belum dinilai' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            {{-- E. Kapabilitas --}}
            <h2 class="mt-6 text-lg font-semibold">E. Tingkat kapabilitas dan kesenjangan</h2>
            <table class="lap w-full mt-2">
                <thead>
                    <tr>
                        <th>Objektif</th>
                        @foreach (config('tata_kelola_keamanan.aturan.level_dinilai') as $l)
                            <th>Level {{ $l }}</th>
                        @endforeach
                        <th>Saat ini</th><th>Target</th><th>Gap</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kesenjangan as $r)
                        <tr>
                            <td>{{ $r['objektif']->kode }} {{ $r['objektif']->nama }}<br><span class="text-xs text-gray-600">{{ $r['objektif']->nama_id }}</span></td>
                            @foreach ($r['hasil']['per_level'] as $pl)
                                <td class="text-center">{{ $pl['total'] ? $pl['terpenuhi'].'/'.$pl['total'].' ('.$pl['rating'].')' : '-' }}</td>
                            @endforeach
                            <td class="text-center font-semibold">{{ $r['hasil']['level'] ?? '-' }}{{ $r['hasil']['lengkap'] ? '' : '*' }}</td>
                            <td class="text-center">{{ $r['target'] ?? '-' }}</td>
                            <td class="text-center">{{ $r['gap'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-gray-500 mt-1">Isi sel: aktivitas terpenuhi/total (rating). * = masih ada aktivitas yang belum dinilai.</p>

            {{-- F. Kontrol --}}
            <h2 class="mt-6 text-lg font-semibold">F. Status penerapan kontrol Annex A ({{ count($cakupan) }} kontrol terpetakan)</h2>
            <table class="lap w-full mt-2">
                <thead>
                    <tr><th>Tema</th>
                        @foreach (config('tata_kelola_keamanan.status_kontrol') as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th>Belum dinilai</th></tr>
                </thead>
                <tbody>
                    @foreach ($rekapTema as $tema => $n)
                        <tr><td>{{ $tema }}</td>
                            @foreach (array_keys(config('tata_kelola_keamanan.status_kontrol')) as $k)
                                <td class="text-center">{{ $n[$k] }}</td>
                            @endforeach
                            <td class="text-center">{{ $n['belum_dinilai'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <table class="lap w-full mt-3">
                <thead><tr><th>Kontrol</th><th>Objektif</th><th>Status</th><th>Bukti</th></tr></thead>
                <tbody>
                    @foreach ($cakupan as $kode => $c)
                        <tr>
                            <td>{{ \App\Services\TataKelolaKeamanan\Istilah::isoRingkas($kode) }} {{ $c['baru'] ? '★' : '' }}</td>
                            <td>{{ implode(', ', $c['objektif']) }}</td>
                            <td>{{ config('tata_kelola_keamanan.status_kontrol.'.($statusKontrol[$kode]->status ?? ''), 'belum dinilai') }}</td>
                            <td>{{ $statusKontrol[$kode]->bukti ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- G. Peta jalan --}}
            <h2 class="mt-6 text-lg font-semibold">G. Rekomendasi dan peta jalan</h2>
            @foreach (config('tata_kelola_keamanan.horizon') as $h => $labelH)
                <p class="mt-2 font-medium">{{ $labelH }}</p>
                <table class="lap w-full mt-1">
                    <thead><tr><th>No</th><th>Praktik / kontrol</th><th>Rekomendasi</th><th>Penanggung jawab</th></tr></thead>
                    <tbody>
                        @forelse ($rekomendasis[$h] ?? [] as $rk)
                            <tr><td class="text-center">{{ $rk->prioritas }}</td><td>{{ $rk->praktik?->kode ?? $rk->objektif_kode }} {{ $rk->iso_kode }}</td>
                                <td>{{ $rk->uraian }}</td><td>{{ $rk->penanggung_jawab }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-gray-400">-</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
@endsection
