<?php

namespace App\Exports;

use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Services\TataKelolaKeamanan\Aturan;
use App\Services\TataKelolaKeamanan\Istilah;
use App\Services\TataKelolaKeamanan\Laporan;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Ekspor seluruh hasil evaluasi ke Excel (satu sheet per tabel hasil).
 */
class TataKelolaKeamananExport implements WithMultipleSheets
{
    public function __construct(private Evaluasi $evaluasi)
    {
    }

    public function sheets(): array
    {
        $d = app(Laporan::class)->lengkap($this->evaluasi);

        return [
            new KuesionerPortalSheet('design_factors', $this->designFactors($d)),
            new KuesionerPortalSheet('objektif', $this->objektif($d)),
            new KuesionerPortalSheet('pemetaan', $this->pemetaan($d)),
            new KuesionerPortalSheet('validasi_model', $this->validasiModel($d)),
            new KuesionerPortalSheet('fgd', $this->fgd($d)),
            new KuesionerPortalSheet('kapabilitas', $this->kapabilitas($d)),
            new KuesionerPortalSheet('kontrol', $this->kontrol($d)),
            new KuesionerPortalSheet('rekomendasi', $this->rekomendasi($d)),
        ];
    }

    private function designFactors(array $d): array
    {
        $rows = [['DF', 'Nama', 'Butir', 'Nilai']];
        foreach (config('tata_kelola_keamanan.design_factors') as $kode => $df) {
            $nilai = $d['designFactors'][$kode]->nilai ?? [];
            foreach ($df['butir'] as $i => $butir) {
                $v = match ($df['tipe']) {
                    'risiko' => isset($nilai['dampak'][$i]) ? 'dampak '.$nilai['dampak'][$i].', kemungkinan '.($nilai['kemungkinan'][$i] ?? '-') : '',
                    'pilihan' => (($nilai['pilihan'] ?? null) === $i) ? 'dipilih' : '',
                    default => $nilai[$i] ?? '',
                };
                $rows[] = [$kode, $df['nama'], $butir, $v];
            }
        }

        return $rows;
    }

    private function objektif(array $d): array
    {
        $rows = [['Kode', 'Nama', 'Skor kepentingan', 'Terkait keamanan', 'Dipilih', 'Target level', 'Alasan']];
        foreach ($d['objektifs'] as $o) {
            $rows[] = [$o->kode, $o->nama, $o->skor_kepentingan, $o->terkait_keamanan ? 'ya' : 'tidak', $o->dipilih ? 'ya' : 'tidak', $o->target_level, $o->alasan];
        }

        return $rows;
    }

    private function pemetaan(array $d): array
    {
        $rows = [['Objektif', 'Praktik', 'Nama praktik', 'Klausul/kontrol ISO 27001:2022', 'Ketentuan regulasi', 'Bukti diharapkan', 'Status', 'Sesi FGD disepakati', 'Alasan dikeluarkan']];
        foreach ($d['pemetaans'] as $p) {
            $rows[] = [
                $p->praktik->objektif_kode, $p->praktik->kode, $p->praktik->nama,
                implode('; ', array_map([Pemetaan::class, 'labelIso'], $p->iso_items ?? [])),
                implode('; ', array_map(fn ($k) => Istilah::regulasi($k), $p->regulasi_items ?? [])),
                $p->bukti_diharapkan, $p->status, $p->sesi_disepakati, $p->alasan_dikeluarkan,
            ];
        }

        return $rows;
    }

    private function validasiModel(array $d): array
    {
        $dokumenAcuan = config('tata_kelola_keamanan.dokumen_acuan');
        $rows = [['Praktik', 'Klausul/kontrol', 'Verifikasi', 'Dokumen pendukung', 'Catatan verifikasi', 'Status']];
        foreach ($d['pemetaans'] as $p) {
            $rows[] = [
                $p->praktik->kode, implode(', ', $p->iso_items ?? []), Pemetaan::labelVerifikasi($p->verifikasi),
                collect($p->dokumen_acuan ?? [])->map(fn ($rujukan, $k) => ($dokumenAcuan[$k] ?? $k).($rujukan ? " ($rujukan)" : ''))->implode(' | '),
                $p->catatan_verifikasi, $p->status,
            ];
        }

        return $rows;
    }

    private function fgd(array $d): array
    {
        $rows = [['Sesi', 'Tanggal', 'Praktik', 'Klausul/kontrol', 'Keputusan', 'Saran']];
        foreach ($d['sesiFgds'] as $s) {
            foreach ($d['rekapFgd'][$s->nomor] as $r) {
                $rows[] = [
                    $s->nomor, $s->tanggal?->format('Y-m-d'), $r['pemetaan']->praktik->kode,
                    implode(', ', $r['pemetaan']->iso_items ?? []), $r['keputusan'] ?? 'belum dibahas', $r['saran'],
                ];
            }
        }

        return $rows;
    }

    private function kapabilitas(array $d): array
    {
        $levels = config('tata_kelola_keamanan.aturan.level_dinilai');
        $head = ['Objektif', 'Nama', 'Target'];
        foreach ($levels as $l) {
            $head[] = "Level $l (terpenuhi/total)";
            $head[] = "Rating L$l";
        }
        $rows = [array_merge($head, ['Level saat ini', 'Gap', 'Asesmen lengkap'])];

        foreach ($d['kapabilitas'] as $r) {
            $row = [$r['objektif']->kode, $r['objektif']->nama, $r['target']];
            foreach ($levels as $l) {
                $pl = $r['hasil']['per_level'][$l];
                $row[] = $pl['total'] ? "{$pl['terpenuhi']}/{$pl['total']}" : '-';
                $row[] = $pl['rating'] ? $pl['rating'].' ('.Aturan::labelRating($pl['rating']).')' : '-';
            }
            $rows[] = array_merge($row, [$r['hasil']['level'], $r['gap'], $r['hasil']['lengkap'] ? 'ya' : 'belum']);
        }

        return $rows;
    }

    private function kontrol(array $d): array
    {
        $rows = [['Kontrol', 'Judul', 'Baru 2022', 'Objektif', 'Praktik', 'Disepakati FGD', 'Status', 'Bukti']];
        foreach ($d['cakupan'] as $kode => $c) {
            $s = $d['statusKontrol'][$kode] ?? null;
            $rows[] = [
                $kode, Istilah::isoJudul($kode).' / '.Istilah::isoId($kode), $c['baru'] ? 'ya' : '', implode(', ', $c['objektif']),
                implode(', ', $c['praktik']), $c['disepakati'] ? 'ya' : 'belum',
                config('tata_kelola_keamanan.status_kontrol.'.($s->status ?? ''), 'belum dinilai'), $s->bukti ?? '',
            ];
        }

        return $rows;
    }

    private function rekomendasi(array $d): array
    {
        $rows = [['Horizon', 'Prioritas', 'Objektif', 'Praktik', 'Kontrol', 'Rekomendasi', 'Penanggung jawab']];
        foreach ($d['rekomendasis'] as $horizon => $items) {
            foreach ($items as $r) {
                $rows[] = [config("tata_kelola_keamanan.horizon.$horizon"), $r->prioritas, $r->objektif_kode, $r->praktik?->kode, $r->iso_kode, $r->uraian, $r->penanggung_jawab];
            }
        }

        return $rows;
    }
}
