<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitKerja;

class UnitKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitKerjas = [
            'BADAN KESATUAN BANGSA DAN POLITIK',
            'SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH',
            'INSPEKTORAT',
            'DINAS KESEHATAN',
            'BADAN PENANGGULANGAN BENCANA DAERAH',
            'DINAS KELAUTAN DAN PERIKANAN',
            'SEKRETARIAT DAERAH',
            'DINAS PENDIDIKAN DAN KEBUDAYAAN',
            'SATUAN POLISI PAMONG PRAJA',
            'DINAS SOSIAL',
            'DINAS PEMBERDAYAAN PEREMPUAN, PERLINDUNGAN ANAK, PENGENDALIAN PENDUDUK DAN KELUARGA BERENCANA',
            'DINAS PERTANIAN DAN KETAHANAN PANGAN',
            'DINAS LINGKUNGAN HIDUP',
            'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL',
            'DINAS PEMBERDAYAAN MASYARAKAT DAN DESA',
            'DINAS PERHUBUNGAN',
            'DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU',
            'DINAS PERPUSTAKAAN DAN KEARSIPAN',
            'DINAS PARIWISATA',
            'DINAS KEHUTANAN',
            'DINAS ENERGI DAN SUMBER DAYA MINERAL',
            'DINAS PERINDUSTRIAN, PERDAGANGAN, KOPERASI, USAHA KECIL DAN MENENGAH',
            'DINAS TENAGA KERJA DAN TRANSMIGRASI',
            'BADAN PERENCANAAN PEMBANGUNAN DAERAH DAN LITBANG',
            'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH',
            'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
            'BADAN PENGHUBUNG',
            'BADAN KEPEGAWAIAN DAERAH',
            'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN BULUNGAN',
            'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN TANA TIDUNG',
            'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN MALINAU',
            'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN NUNUKAN',
            'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KOTA TARAKAN',
            'UPT PANTI SOSIAL TRISNA WERDHA MARGA RAHAYU',
            'UPT TEKNOLOGI, INFORMASI DAN KOMUNIKASI PENDIDIKAN',
            'UPT TAMAN BUDAYA KALTARA',
            'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH NUNUKAN',
            'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH MALINAU',
            'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH TARAKAN',
            'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI MALINAU',
            'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI NUNUKAN',
            'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI BULUNGAN',
            'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI TARAKAN',
            'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI TIDENG PALE',
            'DINAS PEKERJAAN UMUM, PENATAAN RUANG, PERUMAHAN DAN KAWASAN PERMUKIMAN',
            'BIRO HUKUM',
            'BIRO KESEJAHTERAAN RAKYAT',
            'BIRO PEREKONOMIAN',
            'BIRO ORGANISASI',
            'SMA NEGERI 1 TARAKAN',
            'SMA NEGERI 2 TARAKAN',
            'SMA NEGERI 3 TARAKAN',
            'SMK NEGERI 1 TARAKAN',
            'SMK NEGERI 2 TARAKAN',
            'SMK NEGERI 3 TARAKAN',
            'SMA NEGERI 1 BUNYU',
            'SMA NEGERI 1 PESO',
            'SMA NEGERI 1 SEKATAK',
            'SMA NEGERI 1 TANJUNG PALAS',
            'SMA NEGERI 1 TANJUNG PALAS BARAT',
            'SMA NEGERI 1 TANJUNG PALAS TIMUR',
            'SMA NEGERI 1 TANJUNG PALAS UTARA',
            'SMA NEGERI 1 TANJUNG SELOR',
            'SMA NEGERI 2 TANJUNG SELOR',
            'SMK NEGERI 1 TANJUNG PALAS',
            'SMK NEGERI 1 TANJUNG PALAS UTARA',
            'SMK NEGERI 1 TANJUNG SELOR',
            'SMK NEGERI 2 TANJUNG SELOR',
            'SMK NEGERI 3 TANJUNG SELOR',
            'SMA NEGERI 1 NUNUKAN SELATAN',
            'SMA NEGERI 2 NUNUKAN',
            'SMK NEGERI 1 NUNUKAN',
            'SMA NEGERI TERPADU UNGGULAN 1 TANA TIDUNG',
            'SMA NEGERI 2 TANA TIDUNG',
            'SMA NEGERI 1 TANA TIDUNG',
            'SMA NEGERI 1 MALINAU',
            'SMA NEGERI 2 MALINAU',
            'SMA NEGERI 3 MALINAU',
            'SMA NEGERI 4 MALINAU',
            'SMA NEGERI 5 MALINAU',
            'SMA NEGERI 6 MALINAU',
            'SMA NEGERI 7 MALINAU',
            'SMA NEGERI 8 MALINAU',
            'SMA NEGERI 9 MALINAU',
            'SMA NEGERI 10 MALINAU',
            'SMA NEGERI 11 MALINAU',
            'SMA NEGERI 12 MALINAU',
            'SMA NEGERI 13 MALINAU',
            'SMA NEGERI 14 MALINAU',
            'SMA NEGERI 15 MALINAU',
            'SMA NEGERI 16 MALINAU',
            'SLB NEGERI TARAKAN',
            'SMK NEGERI 1 MALINAU',
            'SMK NEGERI 2 MALINAU',
            'SMK SPP MALINAU',
            'SMA NEGERI 1 SEBUKU',
            'SMA NEGERI 1 SEMBAKUNG',
            'SMA NEGERI 1 LUMBIS',
            'SMK NEGERI 1 TULIN ONSOI',
            'SMK NEGERI 1 SEI MENGGARIS',
            'SMA NEGERI 1 SEBATIK TENGAH',
            'SMA NEGERI 1 SEBATIK',
            'SMK NEGERI 1 SEBATIK BARAT',
            'SMA NEGERI 1 KRAYAN SELATAN',
            'SMA NEGERI 1 KRAYAN',
            'SMK NEGERI 1 KRAYAN',
            'SLB NEGERI NUNUKAN',
            'SLB NEGERI MALINAU',
            'SLB NEGERI TANJUNG SELOR',
            'SMK NEGERI 1 BUNYU',
            'SMA NEGERI 1 NUNUKAN',
            'UPT PENERAPAN MUTU HASIL PERIKANAN',
            'UPT PERIKANAN BUDIDAYA LAUT DAN PAYAU',
            'UPT PELABUHAN PERIKANAN TENGKAYU II',
            'UPTD LABORATORIUM LINGKUNGAN HIDUP',
            'UPT LABORATORIUM KESEHATAN HEWAN DAN KESEHATAN MASYARAKAT VETERINER',
            'UPT INSTALASI FARMASI',
            'UPTD PERLINDUNGAN PEREMPUAN DAN ANAK',
            'SMA NEGERI 1 TANJUNG PALAS TENGAH',
            'SMK NEGERI 1 TANA TIDUNG',
            'SLB NEGERI TANA TIDUNG',
            'SMK NEGERI 1 TANJUNG PALAS TIMUR',
            'BADAN PENGELOLA PERBATASAN DAERAH',
            'SMK NEGERI 1 SEMBAKUNG ATULAI',
            'SMK NEGERI 4 TARAKAN',
            'SMA NEGERI 4 TARAKAN',
            'SMA NEGERI 2 NUNUKAN SELATAN',
            'UPTD PELABUHAN TENGKAYU I',
            'UPTD PELABUHAN LIEM HIE DJUNG NUNUKAN',
            'BADAN KEUANGAN DAN ASET DAERAH',
            'BADAN PENDAPATAN DAERAH',
            'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
            'BIRO PEMERINTAHAN DAN OTONOMI DAERAH',
            'BIRO PENGADAAN BARANG DAN JASA',
            'BIRO UMUM',
            'BIRO ADMINISTRASI PEMBANGUNAN',
            'BIRO ADMINISTRASI PIMPINAN',
            'DINAS PEMUDA DAN OLAHRAGA',
            'SMA NEGERI 2 TANJUNG PALAS TIMUR',
            'SLB NEGERI BUNYU',
            'SMK NEGERI 1 LUMBIS OGONG',
            'SMA NEGERI 3 NUNUKAN',
            'RSUD dr. H. JUSUF S.K. TARAKAN',
            'SMA NEGERI 5 TARAKAN',
            'SMA NEGERI 3 TANA TIDUNG',
            'SMA NEGERI 1 SEI MENGGARIS',
            'UPTD BADAN PENDAPATAN DAERAH KELAS A DI WILAYAH BULUNGAN',
            'UPTD BADAN PENDAPATAN DAERAH KELAS A DI WILAYAH TARAKAN',
            'UPTD BADAN PENDAPATAN DAERAH KELAS A DI WILAYAH MALINAU',
            'UPTD BADAN PENDAPATAN DAERAH KELAS A DI WILAYAH NUNUKAN',
            'UPTD BADAN PENDAPATAN DAERAH KELAS A DI WILAYAH TIDENG PALE',
        ];

        foreach ($unitKerjas as $nama) {
            UnitKerja::create([
                'nama' => $nama,
                'singkatan' => $this->generateSingkatan($nama),
                'tipe' => $this->determineTipe($nama),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Determine tipe based on nama
     * - Induk: Badan, Dinas, Satpol PP, Inspektorat, Sekretariat, RSUD, Biro
     * - Cabang: UPT, UPTD, Cabang Dinas
     * - Sekolah: SMA, SMK, SLB
     */
    private function determineTipe(string $nama): string
    {
        // Sekolah
        if (str_contains($nama, 'SMA') || str_contains($nama, 'SMK') || str_contains($nama, 'SLB')) {
            return UnitKerja::TIPE_SEKOLAH;
        }

        // Cabang Perangkat Daerah
        if (str_starts_with($nama, 'UPT') ||
            str_starts_with($nama, 'UPTD') ||
            str_starts_with($nama, 'CABANG')) {
            return UnitKerja::TIPE_CABANG;
        }

        // Induk Perangkat Daerah (default for Badan, Dinas, Sekretariat, Inspektorat, Satpol PP, RSUD, Biro)
        return UnitKerja::TIPE_INDUK;
    }

    /**
     * Generate singkatan from nama
     */
    private function generateSingkatan(string $nama): string
    {
        // For schools, return as is (already short)
        if (str_contains($nama, 'SMA') || str_contains($nama, 'SMK') || str_contains($nama, 'SLB')) {
            // Extract the school type and number
            preg_match('/(SM[AK]|SLB)\s+(NEGERI\s+)?(\d+|TERPADU UNGGULAN \d+|SPP)?\s*(.*)/', $nama, $matches);
            if (!empty($matches)) {
                return $matches[1] . (isset($matches[3]) && $matches[3] ? ' ' . $matches[3] : '');
            }
            return substr($nama, 0, 20);
        }

        // For regular institutions, take first letters of each word
        $words = explode(' ', $nama);
        $singkatan = '';
        foreach ($words as $word) {
            // Skip common words
            if (in_array($word, ['DAN', 'DAERAH', 'PROVINSI', 'KELAS', 'DI', 'WILAYAH', 'KABUPATEN', 'KOTA'])) {
                continue;
            }
            $singkatan .= substr($word, 0, 1);
        }

        return substr($singkatan, 0, 20);
    }
}
