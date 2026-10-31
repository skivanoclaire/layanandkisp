<?php

namespace App\Exports;

use App\Models\PresensiKegiatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// StringValueBinder: NIP 18 digit tetap teks, tidak berubah jadi notasi ilmiah.
class PresensiKegiatanExport extends StringValueBinder implements WithCustomValueBinder, FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    private int $no = 0;

    public function __construct(private PresensiKegiatan $kegiatan)
    {
    }

    public function collection()
    {
        return $this->kegiatan->peserta()->orderBy('instansi')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'NIP', 'Jabatan', 'Instansi', 'No. HP', 'Email', 'Konfirmasi', 'Waktu Konfirmasi', 'Catatan', 'Hadir', 'Waktu Hadir', 'Sumber Data'];
    }

    public function map($p): array
    {
        return [
            ++$this->no,
            $p->nama,
            $p->nip,
            $p->jabatan,
            $p->instansi,
            $p->no_hp,
            $p->email,
            $p->konfirmasi_label ?? '-',
            $p->konfirmasi_at?->format('d/m/Y H:i'),
            $p->catatan,
            $p->hadir_at ? 'Ya' : 'Tidak',
            $p->hadir_at?->format('d/m/Y H:i'),
            ['manual' => 'Isi manual', 'akun' => 'Akun E-Layanan', 'admin' => 'Ditambahkan panitia'][$p->sumber] ?? $p->sumber,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Peserta';
    }
}
