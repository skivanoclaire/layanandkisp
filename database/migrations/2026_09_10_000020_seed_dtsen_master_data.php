<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data awal master DTSEN: wilayah Provinsi Kalimantan Utara (provinsi s.d. kecamatan),
     * rilis perdana, dan katalog variabel awal.
     *
     * Katalog variabel di sini adalah titik mulai; kurasi lanjutan (penambahan,
     * penonaktifan, penyesuaian level akses per rilis) dilakukan Bapperida lewat
     * menu Master Data > Variabel DTSEN.
     */
    private array $kecamatan = [
        'Bulungan' => [
            'Tanjung Selor', 'Tanjung Palas', 'Tanjung Palas Barat', 'Tanjung Palas Utara',
            'Tanjung Palas Timur', 'Tanjung Palas Tengah', 'Sekatak', 'Peso', 'Peso Hilir', 'Bunyu',
        ],
        'Malinau' => [
            'Malinau Kota', 'Malinau Barat', 'Malinau Utara', 'Malinau Selatan',
            'Malinau Selatan Hilir', 'Malinau Selatan Hulu', 'Mentarang', 'Mentarang Hulu',
            'Pujungan', 'Kayan Hulu', 'Kayan Hilir', 'Kayan Selatan', 'Bahau Hulu',
            'Sungai Boh', 'Sungai Tubu',
        ],
        'Nunukan' => [
            'Nunukan', 'Nunukan Selatan', 'Sebatik', 'Sebatik Barat', 'Sebatik Timur',
            'Sebatik Tengah', 'Sebatik Utara', 'Sebuku', 'Sembakung', 'Sembakung Atulai',
            'Lumbis', 'Lumbis Ogong', 'Lumbis Pansiangan', 'Lumbis Hulu', 'Krayan',
            'Krayan Selatan', 'Krayan Barat', 'Krayan Timur', 'Krayan Tengah',
            'Tulin Onsoi', 'Seimenggaris',
        ],
        'Tana Tidung' => [
            'Sesayap', 'Sesayap Hilir', 'Tana Lia', 'Betayau', 'Muruk Rian',
        ],
        'Tarakan' => [
            'Tarakan Barat', 'Tarakan Tengah', 'Tarakan Timur', 'Tarakan Utara',
        ],
    ];

    /** [kode, nama, kategori, level_minimal, deskripsi] */
    private array $variables = [
        ['DTSEN-001', 'Nomor Induk Kependudukan (NIK)', 'Identitas', 4, 'Identitas tunggal penduduk; hanya tersedia pada level BNBA.'],
        ['DTSEN-002', 'Nama Lengkap Kepala Keluarga', 'Identitas', 4, 'Nama kepala keluarga sesuai dokumen kependudukan.'],
        ['DTSEN-003', 'Nama Lengkap Anggota Keluarga', 'Identitas', 4, 'Nama anggota keluarga dalam satu kartu keluarga.'],
        ['DTSEN-004', 'Nomor Kartu Keluarga', 'Identitas', 4, 'Nomor KK sebagai kunci relasi antar anggota keluarga.'],
        ['DTSEN-005', 'Alamat Tempat Tinggal', 'Identitas', 4, 'Alamat lengkap termasuk RT/RW.'],
        ['DTSEN-006', 'Titik Koordinat Rumah Tangga', 'Identitas', 4, 'Koordinat geospasial hasil pendataan lapangan.'],
        ['DTSEN-010', 'Jenis Kelamin', 'Demografi', 3, 'Jenis kelamin anggota keluarga.'],
        ['DTSEN-011', 'Tanggal Lahir / Umur', 'Demografi', 3, 'Umur dalam tahun atau tanggal lahir.'],
        ['DTSEN-012', 'Status Perkawinan', 'Demografi', 3, 'Belum kawin, kawin, cerai hidup, cerai mati.'],
        ['DTSEN-013', 'Hubungan dengan Kepala Keluarga', 'Demografi', 3, 'Posisi anggota dalam keluarga.'],
        ['DTSEN-014', 'Jumlah Anggota Keluarga', 'Demografi', 2, 'Banyaknya anggota dalam satu keluarga.'],
        ['DTSEN-020', 'Desil Kesejahteraan', 'Kesejahteraan', 3, 'Peringkat kesejahteraan hasil pemeringkatan DTSEN.'],
        ['DTSEN-021', 'Kelompok Sasaran Program', 'Kesejahteraan', 3, 'Penetapan kelompok sasaran hasil pemeringkatan.'],
        ['DTSEN-030', 'Pendidikan Tertinggi yang Ditamatkan', 'Pendidikan', 3, 'Jenjang pendidikan terakhir yang ditamatkan.'],
        ['DTSEN-031', 'Status Sekolah', 'Pendidikan', 3, 'Tidak/belum pernah sekolah, masih sekolah, tidak bersekolah lagi.'],
        ['DTSEN-040', 'Status Pekerjaan Utama', 'Ketenagakerjaan', 3, 'Status dalam pekerjaan utama.'],
        ['DTSEN-041', 'Lapangan Usaha Pekerjaan Utama', 'Ketenagakerjaan', 3, 'Sektor lapangan usaha pekerjaan utama.'],
        ['DTSEN-050', 'Status Kepemilikan Bangunan Tempat Tinggal', 'Perumahan', 3, 'Milik sendiri, kontrak/sewa, bebas sewa, dinas, lainnya.'],
        ['DTSEN-051', 'Jenis Lantai Terluas', 'Perumahan', 3, 'Material lantai bangunan tempat tinggal.'],
        ['DTSEN-052', 'Jenis Dinding Terluas', 'Perumahan', 3, 'Material dinding bangunan tempat tinggal.'],
        ['DTSEN-053', 'Jenis Atap Terluas', 'Perumahan', 3, 'Material atap bangunan tempat tinggal.'],
        ['DTSEN-054', 'Sumber Air Minum Utama', 'Perumahan', 3, 'Sumber utama air minum rumah tangga.'],
        ['DTSEN-055', 'Sumber Penerangan Utama', 'Perumahan', 3, 'Listrik PLN, non-PLN, atau bukan listrik.'],
        ['DTSEN-056', 'Bahan Bakar Utama Memasak', 'Perumahan', 3, 'Jenis bahan bakar utama untuk memasak.'],
        ['DTSEN-057', 'Fasilitas Buang Air Besar', 'Perumahan', 3, 'Ketersediaan dan jenis fasilitas sanitasi.'],
        ['DTSEN-060', 'Kepemilikan Aset Bergerak', 'Aset', 3, 'Kepemilikan kendaraan dan aset bergerak lainnya.'],
        ['DTSEN-061', 'Kepemilikan Aset Tidak Bergerak', 'Aset', 3, 'Kepemilikan tanah, bangunan lain, dan aset tidak bergerak.'],
        ['DTSEN-070', 'Penyandang Disabilitas', 'Kesehatan', 3, 'Status dan jenis disabilitas anggota keluarga.'],
        ['DTSEN-071', 'Penyakit Kronis / Menahun', 'Kesehatan', 3, 'Riwayat penyakit kronis anggota keluarga.'],
        ['DTSEN-072', 'Status Gizi Balita', 'Kesehatan', 3, 'Indikator status gizi balita dalam keluarga.'],
        ['DTSEN-080', 'Kepesertaan Jaminan Kesehatan (PBI-JKN)', 'Program Bantuan', 3, 'Status kepesertaan penerima bantuan iuran JKN.'],
        ['DTSEN-081', 'Penerima Program Keluarga Harapan (PKH)', 'Program Bantuan', 3, 'Status penerima manfaat PKH.'],
        ['DTSEN-082', 'Penerima Bantuan Pangan (BPNT/Sembako)', 'Program Bantuan', 3, 'Status penerima bantuan pangan non-tunai.'],
        ['DTSEN-090', 'Rekapitulasi Jumlah Keluarga per Wilayah', 'Agregat', 2, 'Agregasi jumlah keluarga menurut wilayah administratif.'],
        ['DTSEN-091', 'Rekapitulasi Penduduk menurut Desil', 'Agregat', 2, 'Agregasi jumlah penduduk menurut desil kesejahteraan.'],
    ];

    public function up(): void
    {
        $now = now();

        // --- Wilayah ---
        if (DB::table('dtsen_wilayahs')->count() === 0) {
            $provinsiId = DB::table('dtsen_wilayahs')->insertGetId([
                'parent_id' => null,
                'tingkat' => 'provinsi',
                'kode' => '65',
                'nama' => 'Kalimantan Utara',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $kodeKab = [
                'Bulungan' => '6501',
                'Malinau' => '6502',
                'Nunukan' => '6503',
                'Tana Tidung' => '6504',
                'Tarakan' => '6571',
            ];

            foreach ($this->kecamatan as $kab => $daftarKecamatan) {
                $kabId = DB::table('dtsen_wilayahs')->insertGetId([
                    'parent_id' => $provinsiId,
                    'tingkat' => 'kabupaten_kota',
                    'kode' => $kodeKab[$kab] ?? null,
                    'nama' => $kab === 'Tarakan' ? 'Kota Tarakan' : 'Kabupaten ' . $kab,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $rows = [];
                foreach ($daftarKecamatan as $i => $nama) {
                    $rows[] = [
                        'parent_id' => $kabId,
                        'tingkat' => 'kecamatan',
                        'kode' => ($kodeKab[$kab] ?? '00') . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                        'nama' => $nama,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('dtsen_wilayahs')->insert($rows);
            }
        }

        // --- Rilis perdana ---
        $releaseId = DB::table('dtsen_releases')->where('nomor_rilis', 'DTSEN-2025-01')->value('id');
        if (! $releaseId) {
            $releaseId = DB::table('dtsen_releases')->insertGetId([
                'nomor_rilis' => 'DTSEN-2025-01',
                'tanggal_rilis' => '2025-01-01',
                'keterangan' => 'Rilis awal katalog variabel DTSEN pada portal. Sesuaikan nomor & tanggal dengan rilis resmi yang berlaku.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // --- Katalog variabel ---
        foreach ($this->variables as $i => [$kode, $nama, $kategori, $level, $deskripsi]) {
            if (DB::table('dtsen_variables')->where('kode', $kode)->exists()) {
                continue;
            }
            DB::table('dtsen_variables')->insert([
                'kode' => $kode,
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'kategori' => $kategori,
                'level_minimal' => $level,
                'dtsen_release_id' => $releaseId,
                'is_active' => true,
                'urutan' => ($i + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('dtsen_variables')->whereIn('kode', array_column($this->variables, 0))->delete();
        DB::table('dtsen_releases')->where('nomor_rilis', 'DTSEN-2025-01')->delete();
        DB::table('dtsen_wilayahs')->delete();
    }
};
