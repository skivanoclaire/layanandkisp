<?php

namespace App\Services\TataKelolaKeamanan;

use App\Models\TataKelolaKeamanan\DesignFactor;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\Evaluasi;
use Illuminate\Support\Collection;

/**
 * Rekap lintas tahapan untuk ringkasan, laporan, dan ekspor Excel.
 */
class Laporan
{
    public function __construct(private Kapabilitas $kapabilitas, private ValidasiModel $validasi)
    {
    }

    /** Status setiap tahapan untuk halaman ringkasan evaluasi. */
    public function kemajuan(Evaluasi $evaluasi): array
    {
        $nilaiAkhirDf = $evaluasi->designFactors()->where('responden', DesignFactor::NILAI_AKHIR)->count();
        $respondenDf = $evaluasi->designFactors()->where('responden', '!=', DesignFactor::NILAI_AKHIR)->distinct()->count('responden');
        $skorTerisi = $evaluasi->objektifs()->whereNotNull('skor_kepentingan')->count();
        $dipilih = $evaluasi->objektifs()->where('dipilih', true)->count();
        $pemetaan = $evaluasi->pemetaans()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $sesiFgd = $evaluasi->sesiFgds()->count();
        $verifikasi = $this->validasi->ringkasanVerifikasi($evaluasi);
        $ringkasan = $this->kapabilitas->ringkasanEvaluasi($evaluasi);
        $aktivitasTotal = $ringkasan->sum(fn ($r) => $r['hasil']['jumlah_aktivitas']);
        $cakupan = $this->cakupanKontrol($evaluasi);
        $kontrolDinilai = $evaluasi->kontrols()->whereIn('iso_kode', array_keys($cakupan))->whereNotNull('status')->count();
        $rekomendasi = $evaluasi->rekomendasis()->count();
        $jumlahDf = count(config('tata_kelola_keamanan.design_factors'));

        return [
            [
                'judul' => 'Design factors',
                'route' => 'admin.tata-kelola-keamanan.design-factors',
                'selesai' => $nilaiAkhirDf >= $jumlahDf,
                'keterangan' => "$respondenDf responden; nilai akhir $nilaiAkhirDf dari $jumlahDf DF",
            ],
            [
                'judul' => 'Prioritas objektif dan target',
                'route' => 'admin.tata-kelola-keamanan.objektif',
                'selesai' => $dipilih > 0,
                'keterangan' => "$skorTerisi skor terisi; $dipilih objektif dipilih",
            ],
            [
                'judul' => 'Pemetaan COBIT–ISO–regulasi',
                'route' => 'admin.tata-kelola-keamanan.pemetaan',
                'selesai' => ($pemetaan->sum() > 0),
                'keterangan' => ($pemetaan[Pemetaan::STATUS_DRAF] ?? 0).' draf, '.($pemetaan[Pemetaan::STATUS_DISEPAKATI] ?? 0).' disepakati, '
                    .($pemetaan[Pemetaan::STATUS_DIKELUARKAN] ?? 0).' dikeluarkan',
            ],
            [
                'judul' => 'Validasi model (verifikasi dokumen dan FGD)',
                'route' => 'admin.tata-kelola-keamanan.validasi',
                'selesai' => $pemetaan->sum() > 0 && ($pemetaan[Pemetaan::STATUS_DRAF] ?? 0) === 0 && $sesiFgd > 0,
                'keterangan' => ($verifikasi['didukung'] + $verifikasi['rancangan_peneliti']).' butir lolos verifikasi, '
                    .$verifikasi['belum'].' belum diverifikasi; '.$sesiFgd.' sesi FGD',
            ],
            [
                'judul' => 'Asesmen kapabilitas dan kontrol',
                'route' => 'admin.tata-kelola-keamanan.asesmen',
                'selesai' => $ringkasan->isNotEmpty() && $ringkasan->every(fn ($r) => $r['hasil']['lengkap']) && $kontrolDinilai === count($cakupan) && count($cakupan) > 0,
                'keterangan' => "$aktivitasTotal aktivitas; status kontrol $kontrolDinilai dari ".count($cakupan),
            ],
            [
                'judul' => 'Kesenjangan dan rekomendasi',
                'route' => 'admin.tata-kelola-keamanan.rekomendasi',
                'selesai' => $rekomendasi > 0,
                'keterangan' => "$rekomendasi rekomendasi",
            ],
        ];
    }

    /**
     * Kontrol Annex A yang tercakup dalam pemetaan (kecuali butir yang dikeluarkan dari model),
     * beserta objektif asal dan status kesepakatannya.
     *
     * @return array<string, array{objektif: array, praktik: array, disepakati: bool, baru: bool}>
     */
    public function cakupanKontrol(Evaluasi $evaluasi): array
    {
        $baru = config('tata_kelola_keamanan.iso_kontrol_baru');
        $hasil = [];
        $pemetaans = $evaluasi->pemetaans()->where('status', '!=', Pemetaan::STATUS_DIKELUARKAN)->with('praktik')->get();

        foreach ($pemetaans as $p) {
            foreach ($p->kontrolAnnex() as $kode) {
                $hasil[$kode] ??= ['objektif' => [], 'praktik' => [], 'disepakati' => false, 'baru' => in_array($kode, $baru, true)];
                $hasil[$kode]['objektif'][$p->praktik->objektif_kode] = true;
                $hasil[$kode]['praktik'][$p->praktik->kode] = true;
                $hasil[$kode]['disepakati'] = $hasil[$kode]['disepakati'] || $p->status === Pemetaan::STATUS_DISEPAKATI;
            }
        }
        foreach ($hasil as &$h) {
            $h['objektif'] = array_keys($h['objektif']);
            $h['praktik'] = array_keys($h['praktik']);
        }
        unset($h);

        uksort($hasil, fn ($a, $b) => version_compare(substr($a, 2), substr($b, 2)));

        return $hasil;
    }

    public function kontrolBelumDiterapkan(Evaluasi $evaluasi): Collection
    {
        $cakupan = $this->cakupanKontrol($evaluasi);
        $status = $evaluasi->kontrols()->get()->keyBy('iso_kode');

        return collect($cakupan)
            ->filter(fn ($c, $kode) => in_array($status[$kode]->status ?? null, ['belum', 'sebagian'], true))
            ->map(fn ($c, $kode) => $c + ['kode' => $kode, 'status' => $status[$kode]->status, 'bukti' => $status[$kode]->bukti])
            ->values();
    }

    /** Rekap status kontrol per tema Annex A. */
    public function rekapKontrolPerTema(Evaluasi $evaluasi): array
    {
        $cakupan = $this->cakupanKontrol($evaluasi);
        $status = $evaluasi->kontrols()->get()->keyBy('iso_kode');
        $kategori = array_merge(array_keys(config('tata_kelola_keamanan.status_kontrol')), ['belum_dinilai']);
        $rekap = [];

        foreach (config('tata_kelola_keamanan.iso_tema') as $no => $tema) {
            $rekap[$tema] = array_fill_keys($kategori, 0);
        }
        foreach ($cakupan as $kode => $c) {
            $tema = config('tata_kelola_keamanan.iso_tema')[explode('.', $kode)[1]];
            $rekap[$tema][$status[$kode]->status ?? 'belum_dinilai']++;
        }

        return $rekap;
    }

    public function urutkanKesenjangan(Collection $ringkasan): Collection
    {
        return $ringkasan->sortBy([
            fn ($a, $b) => ($b['gap'] ?? -1) <=> ($a['gap'] ?? -1),
            fn ($a, $b) => ($b['objektif']->skor_kepentingan ?? 0) <=> ($a['objektif']->skor_kepentingan ?? 0),
        ])->values();
    }

    /** Seluruh data untuk halaman laporan dan ekspor. */
    public function lengkap(Evaluasi $evaluasi): array
    {
        $objektifs = $evaluasi->objektifs()->orderByDesc('skor_kepentingan')->orderBy('kode')->get();
        $pemetaans = $evaluasi->pemetaans()->with('praktik')->get()
            ->sortBy(fn ($p) => $p->praktik->kode.'-'.str_pad($p->id, 6, '0', STR_PAD_LEFT))->values();
        $sesiFgds = $evaluasi->sesiFgds()->orderBy('nomor')->get();
        $rekapFgd = $sesiFgds->mapWithKeys(fn ($s) => [$s->nomor => $this->validasi->rekap($s)]);
        $kapabilitas = $this->kapabilitas->ringkasanEvaluasi($evaluasi);
        $cakupan = $this->cakupanKontrol($evaluasi);
        $statusKontrol = $evaluasi->kontrols()->get()->keyBy('iso_kode');

        return [
            'designFactors' => $evaluasi->designFactors()->where('responden', DesignFactor::NILAI_AKHIR)->get()->keyBy('df_kode'),
            'objektifs' => $objektifs,
            'pemetaans' => $pemetaans,
            'sesiFgds' => $sesiFgds,
            'rekapFgd' => $rekapFgd,
            'ringkasanVerifikasi' => $this->validasi->ringkasanVerifikasi($evaluasi),
            'kapabilitas' => $kapabilitas,
            'kesenjangan' => $this->urutkanKesenjangan($kapabilitas),
            'cakupan' => $cakupan,
            'statusKontrol' => $statusKontrol,
            'rekapTema' => $this->rekapKontrolPerTema($evaluasi),
            'rekomendasis' => $evaluasi->rekomendasis()->with('praktik')
                ->orderByRaw("FIELD(horizon, 'pendek', 'menengah', 'panjang')")->orderBy('prioritas')->get()
                ->groupBy('horizon'),
        ];
    }
}
