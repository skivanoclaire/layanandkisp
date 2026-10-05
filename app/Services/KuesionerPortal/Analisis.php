<?php

namespace App\Services\KuesionerPortal;

use App\Models\KuesionerPortalPeriode;
use App\Models\KuesionerPortalResponse;
use Illuminate\Support\Collection;

/**
 * Analisis Subbab 3.3: pra-pemrosesan, uji instrumen, deskriptif-gap-Tk (+ Wilcoxon),
 * IPA (garis rata-rata dan titik tengah skala), Kano (Berger, Fong, better-worse),
 * serta matriks prioritas IPA-Kano (Tabel 3.5).
 */
class Analisis
{
    public const KUADRAN = [
        'I' => 'Kuadran I — Prioritas utama',
        'II' => 'Kuadran II — Pertahankan prestasi',
        'III' => 'Kuadran III — Prioritas rendah',
        'IV' => 'Kuadran IV — Berlebihan',
    ];

    public function jalankan(KuesionerPortalPeriode $periode): array
    {
        $semua = $periode->responses()->with(['answers', 'unitKerja'])->get();
        $selesai = $semua->where('status', KuesionerPortalResponse::STATUS_SELESAI)->values();

        $eksklusi = $this->eksklusi($selesai);
        $dipakai = $selesai->reject(fn ($r) => isset($eksklusi['semua'][$r->kode_responden]))->values();
        $dipakaiKano = $dipakai->reject(fn ($r) => isset($eksklusi['kano'][$r->kode_responden]))->values();

        $jawaban = $this->matriksJawaban($dipakai);
        $atribut = Instrumen::atribut();

        $hasilAtribut = [];
        foreach ($atribut as $kode => $meta) {
            $hasilAtribut[$kode] = ['dimensi' => $meta['dimensi'], 'pernyataan' => $meta['kinerja']]
                + $this->ipaAtribut($jawaban, $kode)
                + ['kano' => $this->kanoAtribut($dipakaiKano, $kode)];
        }

        $garis = $this->garisPotong($hasilAtribut);
        foreach ($hasilAtribut as $kode => &$h) {
            $h['kuadran'] = $this->kuadran($h['kinerja'], $h['kepentingan'], $garis['x'], $garis['y']);
            $h['kuadran_titik_tengah'] = $this->kuadran($h['kinerja'], $h['kepentingan'], 3.0, 3.0);
            $h['pindah_kuadran'] = $h['kuadran'] !== null && $h['kuadran'] !== $h['kuadran_titik_tengah'];
            [$h['prioritas'], $h['tindak_lanjut']] = $this->tindakLanjut($h['kuadran'], $h['kano']['kategori'] ?? null);
        }
        unset($h);

        return [
            'ringkasan' => [
                'masuk' => $semua->count(),
                'menolak' => $semua->where('status', KuesionerPortalResponse::STATUS_MENOLAK)->count(),
                'belum_selesai' => $semua->where('status', KuesionerPortalResponse::STATUS_PERSETUJU)->count(),
                'selesai' => $selesai->count(),
                'dianalisis' => $dipakai->count(),
                'dianalisis_kano' => $dipakaiKano->count(),
            ],
            'eksklusi' => $eksklusi,
            'instrumen' => $this->ujiInstrumen($jawaban),
            'atribut' => $hasilAtribut,
            'urutan_prioritas' => $this->urutkanPrioritas($hasilAtribut),
            'dimensi' => $this->perDimensi($hasilAtribut),
            'garis' => $garis,
            'kepuasan' => $this->kepuasan($jawaban),
            'spearman_kepentingan_worse' => $this->spearmanKepentinganWorse($hasilAtribut),
            'profil' => $this->profil($dipakai),
        ];
    }

    /**
     * Aturan pra-pemrosesan (Subbab 3.3.2). Mengembalikan alasan per kode responden.
     *
     * @return array{semua: array<string, array<string>>, kano: array<string, string>}
     */
    public function eksklusi(Collection $responses): array
    {
        $cfg = config('kuesioner_portal.pra_pemrosesan');
        $kodeAtribut = Instrumen::kodeAtribut();
        $bersyarat = Instrumen::kodeKinerjaBersyarat();
        $kodeKepuasan = Instrumen::kodeKepuasan();

        $semua = [];
        $kano = [];

        foreach ($responses as $r) {
            $alasan = [];
            $a = $r->answers->keyBy('kode');

            if ($r->dikecualikan) {
                $alasan[] = 'Dikecualikan peneliti' . ($r->alasan_dikecualikan ? ': ' . $r->alasan_dikecualikan : '');
            }

            if ($r->durasi_detik !== null && $r->durasi_detik < $cfg['durasi_minimum_detik']) {
                $alasan[] = 'Durasi pengisian tidak wajar (' . gmdate('i:s', $r->durasi_detik) . ')';
            }

            // Item yang seharusnya diisi responden ini
            $harus = 0;
            $kosong = 0;
            $skorLikert = [];
            foreach ($kodeAtribut as $kode) {
                $jw = $a->get($kode);
                $perluKinerja = ! in_array($kode, $bersyarat, true) || $r->pernah_hubungi_petugas;
                foreach (['kepentingan' => true, 'kinerja' => $perluKinerja, 'kano_fungsional' => true, 'kano_disfungsional' => true] as $kolom => $perlu) {
                    if (! $perlu) {
                        continue;
                    }
                    $harus++;
                    $nilai = $jw?->{$kolom};
                    if ($nilai === null) {
                        $kosong++;
                    } elseif (in_array($kolom, ['kepentingan', 'kinerja'], true)) {
                        $skorLikert[] = $nilai;
                    }
                }
            }
            foreach ($kodeKepuasan as $kode) {
                $harus++;
                $nilai = $a->get($kode)?->kinerja;
                $nilai === null ? $kosong++ : $skorLikert[] = $nilai;
            }

            if ($harus && $kosong / $harus > 0.20) {
                $alasan[] = 'Lebih dari 20% item tidak diisi';
            }

            if (count($skorLikert) > 1 && count(array_unique($skorLikert)) === 1) {
                $alasan[] = 'Jawaban seragam pada seluruh item (straight-lining)';
            }

            if ($alasan) {
                $semua[$r->kode_responden] = $alasan;

                continue;
            }

            // Kano: responden dengan Q > batas dikeluarkan dari analisis Kano saja
            $jumlahQ = 0;
            $jumlahKano = 0;
            foreach ($kodeAtribut as $kode) {
                $kat = Kano::kategori($a->get($kode)?->kano_fungsional, $a->get($kode)?->kano_disfungsional);
                if ($kat !== null) {
                    $jumlahKano++;
                    $jumlahQ += $kat === 'Q' ? 1 : 0;
                }
            }
            if ($jumlahKano && $jumlahQ / $jumlahKano > $cfg['batas_questionable']) {
                $kano[$r->kode_responden] = "Jawaban questionable pada {$jumlahQ} dari {$jumlahKano} atribut";
            }
        }

        return ['semua' => $semua, 'kano' => $kano];
    }

    /** kode responden => kode item => [kepentingan, kinerja] */
    private function matriksJawaban(Collection $responses): array
    {
        $m = [];
        foreach ($responses as $r) {
            foreach ($r->answers as $a) {
                $m[$r->kode_responden][$a->kode] = ['kepentingan' => $a->kepentingan, 'kinerja' => $a->kinerja];
            }
        }

        return $m;
    }

    /**
     * Gap, Tk, dan Wilcoxon dihitung pada pasangan lengkap (responden yang mengisi
     * kepentingan dan kinerja atribut tersebut), sehingga X̄ dan Ȳ berbasis n yang sama.
     */
    private function ipaAtribut(array $jawaban, string $kode): array
    {
        $x = [];
        $y = [];
        foreach ($jawaban as $item) {
            $k = $item[$kode] ?? null;
            if ($k && $k['kinerja'] !== null && $k['kepentingan'] !== null) {
                $x[] = $k['kinerja'];
                $y[] = $k['kepentingan'];
            }
        }

        $mx = Statistik::rataRata($x);
        $my = Statistik::rataRata($y);
        $tk = array_sum($y) ? array_sum($x) / array_sum($y) * 100 : null;

        return [
            'n' => count($x),
            'kinerja' => $mx,
            'kepentingan' => $my,
            'kelas_kinerja' => Statistik::kelasRataRata($mx),
            'kelas_kepentingan' => Statistik::kelasRataRata($my),
            'gap' => ($mx !== null && $my !== null) ? $mx - $my : null,
            'tk' => $tk,
            'interpretasi_tk' => Statistik::interpretasiTk($tk),
            'wilcoxon' => Statistik::wilcoxon($x, $y),
        ];
    }

    private function kanoAtribut(Collection $responses, string $kode): array
    {
        $kategori = [];
        foreach ($responses as $r) {
            $a = $r->answers->firstWhere('kode', $kode);
            $kategori[] = Kano::kategori($a?->kano_fungsional, $a?->kano_disfungsional);
        }

        return Kano::ringkas($kategori);
    }

    private function garisPotong(array $hasil): array
    {
        $x = array_values(array_filter(array_column($hasil, 'kinerja'), fn ($v) => $v !== null));
        $y = array_values(array_filter(array_column($hasil, 'kepentingan'), fn ($v) => $v !== null));

        return ['x' => Statistik::rataRata($x), 'y' => Statistik::rataRata($y)];
    }

    private function kuadran(?float $x, ?float $y, ?float $gx, ?float $gy): ?string
    {
        if ($x === null || $y === null || $gx === null || $gy === null) {
            return null;
        }

        return $y >= $gy
            ? ($x < $gx ? 'I' : 'II')
            : ($x < $gx ? 'III' : 'IV');
    }

    /**
     * Tabel 3.5. Kategori campuran dipetakan memakai kategori dominannya.
     *
     * @return array{0:int, 1:string} [peringkat kelompok, tindak lanjut]
     */
    public function tindakLanjut(?string $kuadran, ?string $kano): array
    {
        return match (true) {
            $kuadran === null || $kano === null => [9, 'Data belum cukup'],
            $kuadran === 'I' && $kano === 'M' => [1, 'Prioritas 1 — perbaiki segera'],
            $kuadran === 'I' && $kano === 'O' => [2, 'Prioritas 2 — tingkatkan kinerja'],
            $kuadran === 'III' && $kano === 'M' => [2, 'Naik ke prioritas 2 — kekurangannya menimbulkan ketidakpuasan'],
            $kuadran === 'I' && $kano === 'A' => [3, 'Prioritas 3 — kembangkan bertahap'],
            $kuadran === 'I' && $kano === 'I' => [4, 'Tinjau ulang — gap ada, tidak memengaruhi kepuasan'],
            $kuadran === 'II' && in_array($kano, ['M', 'O'], true) => [5, 'Pertahankan sebagai standar layanan minimum'],
            $kuadran === 'IV' && $kano === 'A' => [5, 'Pertahankan — sumber kepuasan'],
            in_array($kuadran, ['III', 'IV'], true) && $kano === 'I' => [6, 'Prioritas rendah — sumber daya dapat dialihkan'],
            default => [7, 'Di luar Tabel 3.5 — tafsirkan bersama better-worse'],
        };
    }

    /**
     * Pengurutan dalam kelompok: |worse| untuk M/O, better untuk A, lalu gap terbesar.
     */
    private function urutkanPrioritas(array $hasil): array
    {
        $kode = array_keys($hasil);
        usort($kode, function ($a, $b) use ($hasil) {
            $ha = $hasil[$a];
            $hb = $hasil[$b];

            return [$ha['prioritas'], -$this->bobot($ha), $ha['gap'] ?? 0]
                <=> [$hb['prioritas'], -$this->bobot($hb), $hb['gap'] ?? 0];
        });

        return $kode;
    }

    private function bobot(array $h): float
    {
        return match ($h['kano']['kategori'] ?? null) {
            'M', 'O' => abs($h['kano']['worse'] ?? 0),
            'A' => $h['kano']['better'] ?? 0,
            default => 0.0,
        };
    }

    private function perDimensi(array $hasil): array
    {
        $out = [];
        foreach (config('kuesioner_portal.dimensi') as $kd => $d) {
            $baris = array_intersect_key($hasil, $d['atribut']);
            $x = array_values(array_filter(array_column($baris, 'kinerja'), fn ($v) => $v !== null));
            $y = array_values(array_filter(array_column($baris, 'kepentingan'), fn ($v) => $v !== null));
            $mx = Statistik::rataRata($x);
            $my = Statistik::rataRata($y);
            $out[$kd] = [
                'nama' => $d['nama'],
                'kinerja' => $mx,
                'kepentingan' => $my,
                'gap' => ($mx !== null && $my !== null) ? $mx - $my : null,
                'tk' => ($my ? $mx / $my * 100 : null),
            ];
        }

        return $out;
    }

    /**
     * Validitas (corrected item-total vs r tabel) dan reliabilitas (Cronbach's alpha)
     * per blok dan per dimensi, memakai responden dengan jawaban lengkap pada blok tersebut.
     */
    private function ujiInstrumen(array $jawaban): array
    {
        $blok = [
            'kepentingan' => ['kolom' => 'kepentingan', 'kode' => Instrumen::kodeAtribut()],
            'kinerja' => ['kolom' => 'kinerja', 'kode' => Instrumen::kodeAtribut()],
            'kepuasan' => ['kolom' => 'kinerja', 'kode' => Instrumen::kodeKepuasan()],
        ];
        foreach (Instrumen::atributPerDimensi() as $kd => $kodes) {
            $blok["kepentingan_{$kd}"] = ['kolom' => 'kepentingan', 'kode' => $kodes, 'dimensi' => true];
            $blok["kinerja_{$kd}"] = ['kolom' => 'kinerja', 'kode' => $kodes, 'dimensi' => true];
        }

        $hasil = [];
        foreach ($blok as $nama => $b) {
            $matriks = [];
            foreach ($jawaban as $item) {
                $baris = [];
                foreach ($b['kode'] as $kode) {
                    $v = $item[$kode][$b['kolom']] ?? null;
                    if ($v === null) {
                        continue 2;
                    }
                    $baris[] = $v;
                }
                $matriks[] = $baris;
            }

            $n = count($matriks);
            $rTabel = Statistik::rTabel($n);
            $r = Statistik::korelasiItemTotalTerkoreksi($matriks);
            $items = [];
            foreach ($b['kode'] as $i => $kode) {
                $items[$kode] = [
                    'r' => $r[$i] ?? null,
                    'valid' => isset($r[$i], $rTabel) ? $r[$i] > $rTabel : null,
                ];
            }
            $alpha = Statistik::cronbachAlpha($matriks);

            $hasil[$nama] = [
                'n' => $n,
                'r_tabel' => $rTabel,
                'alpha' => $alpha,
                'reliabel' => $alpha !== null ? $alpha >= 0.70 : null,
                'item' => $items,
                'dimensi' => $b['dimensi'] ?? false,
            ];
        }

        return $hasil;
    }

    private function kepuasan(array $jawaban): array
    {
        $out = [];
        foreach (Instrumen::kodeKepuasan() as $kode) {
            $nilai = array_values(array_filter(array_map(fn ($i) => $i[$kode]['kinerja'] ?? null, $jawaban), fn ($v) => $v !== null));
            $m = Statistik::rataRata($nilai);
            $out[$kode] = ['n' => count($nilai), 'rata_rata' => $m, 'kelas' => Statistik::kelasRataRata($m)];
        }

        return $out;
    }

    private function spearmanKepentinganWorse(array $hasil): ?array
    {
        $x = [];
        $y = [];
        foreach ($hasil as $h) {
            if ($h['kepentingan'] !== null && ($h['kano']['worse'] ?? null) !== null) {
                $x[] = $h['kepentingan'];
                $y[] = abs($h['kano']['worse']);
            }
        }
        $rho = Statistik::spearman($x, $y);

        return $rho === null ? null : ['n' => count($x), 'rho' => $rho];
    }

    private function profil(Collection $responses): array
    {
        $opsi = config('kuesioner_portal.profil');
        $out = [];
        foreach (['status_kepegawaian', 'peran', 'lama_penggunaan', 'frekuensi'] as $field) {
            $out[$field] = collect($opsi[$field])->map(fn ($label, $k) => $responses->where($field, $k)->count())->all();
        }
        $out['layanan'] = collect($opsi['layanan'])
            ->map(fn ($label, $k) => $responses->filter(fn ($r) => in_array($k, $r->layanan_diajukan ?? [], true))->count())
            ->all();
        $out['pernah_hubungi_petugas'] = [
            'ya' => $responses->where('pernah_hubungi_petugas', true)->count(),
            'tidak' => $responses->where('pernah_hubungi_petugas', false)->count(),
        ];
        $out['unit_kerja'] = $responses->groupBy(fn ($r) => $r->unitKerja?->nama ?? '(tidak diisi)')
            ->map->count()->sortDesc()->all();

        return $out;
    }
}
