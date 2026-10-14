<?php

namespace App\Services\MasterArsitektur;

use App\Models\MasterArsitektur\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

/**
 * Impor Master Data Arsitektur dari Excel (.xlsx/.xls) atau CSV.
 *
 * - Workbook lengkap: setiap sheet yang namanya dikenali (config "sheet") diimpor.
 * - Satu jenis data: sheet bernama sesuai config dipakai; bila tidak ada, sheet pertama.
 * - Kolom dicocokkan lewat judulnya, bukan posisinya, sehingga urutan kolom bebas.
 * - Rumus dibaca dari nilai terakhir yang tersimpan di berkas (sumbernya Google
 *   Sheets, rumusnya tidak bisa dihitung ulang di sini).
 */
class Importer
{
    public const MODE_PERBARUI = 'perbarui';
    public const MODE_GANTI = 'ganti';

    /** Baris teratas yang diperiksa untuk menemukan baris judul kolom. */
    private const BATAS_BARIS_JUDUL = 6;

    /** Panjang maksimum kolom inti pada tabel. */
    private const PANJANG = ['kode' => 150, 'nama' => 1000, 'status' => 100, 'ref_1' => 255, 'ref_2' => 255, 'ref_3' => 255, 'tahun' => 20];

    public function __construct(private PencocokInstansi $pencocok)
    {
    }

    /**
     * @param  string       $path      lokasi berkas
     * @param  string       $ekstensi  xlsx, xls, atau csv
     * @param  string|null  $jenis     null = semua sheet yang dikenali
     * @return array<string, array> hasil per jenis
     */
    public function impor(string $path, string $ekstensi, ?string $jenis = null, string $mode = self::MODE_PERBARUI): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $tipe = match (strtolower($ekstensi)) {
            'xlsx' => 'Xlsx',
            'xls' => 'Xls',
            'csv', 'txt' => 'Csv',
            default => throw new RuntimeException('Format berkas tidak didukung. Gunakan .xlsx, .xls, atau .csv.'),
        };

        if ($jenis !== null && ! Skema::adaJenis($jenis)) {
            throw new RuntimeException("Jenis data [$jenis] tidak dikenal.");
        }

        $reader = IOFactory::createReader($tipe);
        $reader->setReadDataOnly(true);

        // sheet yang dibaca => jenis data
        $target = [];

        if ($tipe === 'Csv') {
            if ($jenis === null) {
                throw new RuntimeException('Berkas CSV hanya memuat satu jenis data. Pilih jenis datanya terlebih dahulu.');
            }
            $target[0] = $jenis;
        } else {
            $namaSheet = $reader->listWorksheetNames($path);

            if ($jenis !== null) {
                $sheet = $this->cariSheet($namaSheet, $jenis) ?? $namaSheet[0];
                $target[$sheet] = $jenis;
            } else {
                foreach (array_keys(Skema::jenis()) as $j) {
                    if ($sheet = $this->cariSheet($namaSheet, $j)) {
                        $target[$sheet] = $j;
                    }
                }
                if (! $target) {
                    throw new RuntimeException('Tidak ada sheet yang dikenali pada berkas ini. Pilih jenis data secara manual atau samakan nama sheet dengan templat ekspor.');
                }
            }

            $reader->setLoadSheetsOnly(array_keys($target));
        }

        $workbook = $reader->load($path);
        $hasil = [];

        foreach ($target as $sheet => $j) {
            $worksheet = is_int($sheet) ? $workbook->getSheet($sheet) : $workbook->getSheetByName($sheet);
            $hasil[$j] = $this->imporSheet($worksheet, $j, $mode);
        }

        $workbook->disconnectWorksheets();

        return $hasil;
    }

    private function cariSheet(array $namaSheet, string $jenis): ?string
    {
        $dicari = [Skema::jenis($jenis)['sheet'], Skema::label($jenis)];

        foreach ($namaSheet as $nama) {
            foreach ($dicari as $d) {
                if (mb_strtolower(trim($nama)) === mb_strtolower($d)) {
                    return $nama;
                }
            }
        }

        return null;
    }

    private function imporSheet(Worksheet $worksheet, string $jenis, string $mode): array
    {
        $hasil = [
            'label' => Skema::label($jenis),
            'sheet' => $worksheet->getTitle(),
            'dibaca' => 0, 'ditambah' => 0, 'diperbarui' => 0, 'dihapus' => 0, 'duplikat' => 0,
            'tanpa_instansi' => [],
            'galat' => null,
        ];

        $baris = $this->bacaBaris($worksheet);
        [$barisJudul, $kolom] = $this->temukanJudul($baris, $jenis);

        if ($barisJudul === null) {
            $hasil['galat'] = 'Baris judul kolom tidak ditemukan (minimal harus ada kolom "' . Skema::kolom($jenis)['nama']['label'] . '").';

            return $hasil;
        }

        $definisi = Skema::kolom($jenis);
        $entri = [];

        foreach ($baris as $nomor => $sel) {
            if ($nomor <= $barisJudul) {
                continue;
            }

            $nilai = [];
            foreach ($kolom as $huruf => $kunci) {
                $nilai[$kunci] = $this->bersihkan($sel[$huruf] ?? null, $definisi[$kunci]['tipe'] ?? 'text');
            }

            if (($nilai['nama'] ?? null) === null) {
                continue;
            }

            $hasil['dibaca']++;
            $kunci = Item::hitungKunci($jenis, $nilai);

            if (isset($entri[$kunci])) {
                $hasil['duplikat']++;
            }
            $entri[$kunci] = $nilai;
        }

        DB::transaction(function () use ($jenis, $mode, $entri, $kolom, &$hasil) {
            if ($mode === self::MODE_GANTI) {
                $hasil['dihapus'] = Item::where('jenis', $jenis)->delete();
                $lama = collect();
            } else {
                $lama = Item::where('jenis', $jenis)->get()->keyBy('kunci');
            }

            $kolomBerkas = array_values($kolom);

            foreach ($entri as $kunci => $nilai) {
                $item = $lama->get($kunci) ?? new Item(['jenis' => $jenis]);
                $this->isi($item, $nilai, $kolomBerkas, $hasil);

                if (! $item->exists) {
                    $item->save();
                    $hasil['ditambah']++;
                } elseif ($item->isDirty()) {
                    $item->save();
                    $hasil['diperbarui']++;
                }
            }
        });

        arsort($hasil['tanpa_instansi']);

        return $hasil;
    }

    /**
     * Hanya kolom yang ada pada berkas yang ditimpa; kolom lain pada data lama dibiarkan.
     */
    private function isi(Item $item, array $nilai, array $kolomBerkas, array &$hasil): void
    {
        $atribut = $item->atribut ?? [];

        foreach ($kolomBerkas as $kunci) {
            $v = $nilai[$kunci] ?? null;

            if ($kunci === 'unit_kerja') {
                $v = $v === null ? null : mb_substr($v, 0, 255);
                $item->unit_kerja_teks = $v;
                $id = $this->pencocok->cari($v);

                // Pemetaan manual untuk nama yang tidak dikenali dipertahankan
                if ($id !== null || $v === null) {
                    $item->unit_kerja_id = $id;
                } elseif ($item->unit_kerja_id === null) {
                    $hasil['tanpa_instansi'][$v] = ($hasil['tanpa_instansi'][$v] ?? 0) + 1;
                }
            } elseif (in_array($kunci, Skema::INTI, true)) {
                $item->setAttribute($kunci, $v === null ? null : mb_substr($v, 0, self::PANJANG[$kunci]));
            } elseif ($v === null) {
                unset($atribut[$kunci]);
            } else {
                $atribut[$kunci] = $v;
            }
        }

        $item->atribut = $atribut ?: null;
    }

    /** @return array<int, array<string, mixed>> nomor baris => [huruf kolom => nilai] */
    private function bacaBaris(Worksheet $worksheet): array
    {
        $baris = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $sel = [];
            $iterator = $row->getCellIterator();
            $iterator->setIterateOnlyExistingCells(true);

            foreach ($iterator as $cell) {
                $v = $cell->isFormula() ? $cell->getOldCalculatedValue() : $cell->getValue();
                if ($v !== null && $v !== '') {
                    $sel[$cell->getColumn()] = $v;
                }
            }

            if ($sel) {
                $baris[$row->getRowIndex()] = $sel;
            }
        }

        return $baris;
    }

    /**
     * Baris judul = baris (di antara baris-baris teratas) dengan judul kolom
     * dikenali terbanyak, dan wajib memuat kolom nama.
     *
     * @return array{0: int|null, 1: array<string, string>} [nomor baris, huruf kolom => kunci kolom]
     */
    private function temukanJudul(array $baris, string $jenis): array
    {
        $peta = Skema::petaJudul($jenis);
        $terbaik = [null, []];

        foreach (array_slice($baris, 0, self::BATAS_BARIS_JUDUL, true) as $nomor => $sel) {
            $kolom = [];
            foreach ($sel as $huruf => $judul) {
                $kunci = Skema::cocokkanJudul($peta, $judul);
                if ($kunci !== null && ! in_array($kunci, $kolom, true)) {
                    $kolom[$huruf] = $kunci;
                }
            }

            if (in_array('nama', $kolom, true) && count($kolom) > count($terbaik[1])) {
                $terbaik = [$nomor, $kolom];
            }
        }

        return $terbaik;
    }

    private function bersihkan(mixed $nilai, string $tipe): ?string
    {
        if ($nilai === null || is_bool($nilai)) {
            return null;
        }

        // Tanggal yang tersimpan sebagai nomor seri Excel
        if ($tipe === 'tanggal' && is_numeric($nilai) && $nilai > 20000 && $nilai < 80000) {
            return Carbon::instance(Date::excelToDateTimeObject((float) $nilai))->locale('id')->translatedFormat('j F Y');
        }

        if (is_float($nilai)) {
            // 2025.0 => "2025", 3.3 => "3.3"
            $nilai = rtrim(rtrim(number_format($nilai, 6, '.', ''), '0'), '.');
        }

        $nilai = trim(str_replace(["\r\n", "\r"], "\n", (string) $nilai));

        // Sisa galat rumus dari Excel/Google Sheets
        if ($nilai === '' || preg_match('/^#(N\/A|REF!|VALUE!|NAME\?|DIV\/0!|ERROR!|NULL!|NUM!)$/i', $nilai)) {
            return null;
        }

        return $nilai;
    }
}
