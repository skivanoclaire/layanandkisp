<?php

namespace App\Exports;

use App\Models\KuesionerPortalPeriode;
use App\Models\KuesionerPortalResponse;
use App\Services\KuesionerPortal\Analisis;
use App\Services\KuesionerPortal\Instrumen;
use App\Services\KuesionerPortal\Kano;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Ekspor data kuesioner untuk analisis di Jupyter (pandas.read_excel).
 * Sheet "data": satu baris per respons selesai (format lebar), tanpa identitas akun.
 * Sheet "kodebook": arti setiap kolom dan kode jawaban.
 */
class KuesionerPortalExport implements WithMultipleSheets
{
    public function __construct(private KuesionerPortalPeriode $periode)
    {
    }

    public function sheets(): array
    {
        return [
            new KuesionerPortalSheet('data', $this->data()),
            new KuesionerPortalSheet('kodebook', $this->kodebook()),
        ];
    }

    private function data(): array
    {
        $responses = $this->periode->responses()
            ->where('status', KuesionerPortalResponse::STATUS_SELESAI)
            ->with(['answers', 'unitKerja'])
            ->orderBy('submitted_at')
            ->get();

        $eksklusi = app(Analisis::class)->eksklusi($responses);
        $kodeAtribut = Instrumen::kodeAtribut();
        $kodeKepuasan = Instrumen::kodeKepuasan();
        $layanan = array_keys(config('kuesioner_portal.profil.layanan'));

        $header = [
            'kode_responden', 'submitted_at', 'durasi_detik',
            'eksklusi', 'alasan_eksklusi', 'eksklusi_kano',
            'unit_kerja', 'status_kepegawaian', 'peran', 'lama_penggunaan', 'frekuensi',
        ];
        foreach ($layanan as $l) {
            $header[] = "layanan_$l";
        }
        $header[] = 'layanan_lainnya';
        $header[] = 'pernah_hubungi_petugas';
        foreach ($kodeAtribut as $k) {
            $header[] = "I_$k";
        }
        foreach ($kodeAtribut as $k) {
            $header[] = "P_$k";
        }
        foreach ($kodeKepuasan as $k) {
            $header[] = $k;
        }
        foreach ($kodeAtribut as $k) {
            array_push($header, "F_$k", "D_$k", "K_$k");
        }
        array_push($header, 'kelebihan', 'kekurangan', 'saran');

        $rows = [$header];
        foreach ($responses as $r) {
            $a = $r->answers->keyBy('kode');
            $alasan = $eksklusi['semua'][$r->kode_responden] ?? [];

            $row = [
                $r->kode_responden,
                $r->submitted_at?->format('Y-m-d H:i:s'),
                $r->durasi_detik,
                $alasan ? 1 : 0,
                implode('; ', $alasan),
                isset($eksklusi['kano'][$r->kode_responden]) ? 1 : 0,
                $r->unitKerja?->nama,
                $r->status_kepegawaian,
                $r->peran,
                $r->lama_penggunaan,
                $r->frekuensi,
            ];
            foreach ($layanan as $l) {
                $row[] = in_array($l, $r->layanan_diajukan ?? [], true) ? 1 : 0;
            }
            $row[] = $r->layanan_lainnya;
            $row[] = $r->pernah_hubungi_petugas ? 1 : 0;
            foreach ($kodeAtribut as $k) {
                $row[] = $a->get($k)?->kepentingan;
            }
            foreach ($kodeAtribut as $k) {
                $row[] = $a->get($k)?->kinerja;
            }
            foreach ($kodeKepuasan as $k) {
                $row[] = $a->get($k)?->kinerja;
            }
            foreach ($kodeAtribut as $k) {
                $f = $a->get($k)?->kano_fungsional;
                $d = $a->get($k)?->kano_disfungsional;
                array_push($row, $f, $d, Kano::kategori($f, $d));
            }
            array_push($row, $r->kelebihan, $r->kekurangan, $r->saran);

            $rows[] = $row;
        }

        return $rows;
    }

    private function kodebook(): array
    {
        $rows = [['kolom', 'keterangan']];
        $rows[] = ['kode_responden', 'Kode acak pengganti identitas akun'];
        $rows[] = ['durasi_detik', 'Selisih waktu persetujuan dan pengiriman'];
        $rows[] = ['eksklusi', '1 = dikeluarkan dari seluruh analisis menurut aturan pra-pemrosesan atau keputusan peneliti'];
        $rows[] = ['eksklusi_kano', '1 = dikeluarkan dari analisis Kano saja (questionable > batas)'];
        foreach (['status_kepegawaian', 'peran', 'lama_penggunaan', 'frekuensi'] as $f) {
            $rows[] = [$f, collect(config("kuesioner_portal.profil.$f"))->map(fn ($v, $k) => "$k = $v")->implode('; ')];
        }
        foreach (config('kuesioner_portal.profil.layanan') as $k => $v) {
            $rows[] = ["layanan_$k", "1 = pernah mengajukan $v"];
        }
        $rows[] = ['pernah_hubungi_petugas', '1 = ya, 0 = tidak (bila 0, P_CS1–P_CS4 kosong)'];
        $rows[] = ['I_<kode>', 'Kepentingan (Bagian B), 1 = sangat tidak penting … 5 = sangat penting'];
        $rows[] = ['P_<kode>', 'Kinerja (Bagian C), 1 = sangat tidak setuju … 5 = sangat setuju'];
        $rows[] = ['F_<kode>, D_<kode>', 'Kano fungsional/disfungsional: ' . collect(config('kuesioner_portal.skala_kano'))->map(fn ($v, $k) => "$k = $v")->implode('; ')];
        $rows[] = ['K_<kode>', 'Kategori Kano hasil Tabel 2.3: A, O, M, I, R, Q'];
        foreach (Instrumen::atribut() as $kode => $a) {
            $rows[] = [$kode, "{$a['nama_dimensi']} — {$a['kinerja']}"];
        }
        foreach (config('kuesioner_portal.kepuasan') as $kode => $teks) {
            $rows[] = [$kode, "Kepuasan pengguna — $teks"];
        }

        return $rows;
    }
}
