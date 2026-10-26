<?php

/*
|--------------------------------------------------------------------------
| Master Data Arsitektur (Arsitektur SPBE)
|--------------------------------------------------------------------------
| Sumber: MasterDataArsitektur.xlsx. Seluruh modul (dasbor, form CRUD, impor,
| ekspor) digerakkan oleh berkas ini.
|
| - "kategori": 7 tab dasbor. Tiap kategori memuat satu atau lebih "jenis".
| - "jenis": satu sheet pada Excel = satu jenis data.
| - "kolom": kunci kolom => ['label' => ..., 'header' => ..., 'tipe' => ...].
|     * label  : judul kolom di form/detail, sekaligus judul kolom Excel bila
|                "header" tidak diisi.
|     * header : judul kolom pada Excel (boleh array untuk beberapa variasi).
|                Pencocokan mengabaikan huruf besar/kecil, tanda panah, dan
|                keterangan dalam kurung, mis. "→ RAB Level 2 (Dependency)".
|     * tipe   : text (default), textarea, tanggal.
|   Kunci kolom berikut disimpan sebagai kolom tabel (bisa difilter):
|     kode, nama, status, unit_kerja, ref_1, ref_2, ref_3, tahun.
|   Kolom lain disimpan pada kolom JSON "atribut".
| - "kunci": kolom penentu baris yang sama saat impor mode "perbarui".
| - "indikator" (pada kategori): kartu ringkasan berisi jumlah dan persentase
|   entri yang suatu kolomnya bernilai tertentu; ikut menyesuaikan filter.
*/

$rab = [
    'ref_1' => ['label' => 'RAB Level 1', 'header' => ['RAB Level 1 Nasional', 'RAB Level 1']],
    'ref_2' => ['label' => 'RAB Level 2'],
    'ref_3' => ['label' => 'RAB Level 3'],
];

$rai = [
    'ref_1' => ['label' => 'RAI Level 1'],
    'ref_2' => ['label' => 'RAI Level 2'],
    'ref_3' => ['label' => 'RAI Level 3'],
    'instansi' => ['label' => 'Instansi'],
];

$rak = [
    'ref_1' => ['label' => 'RAK Level 1'],
    'ref_2' => ['label' => 'RAK Level 2'],
];

$kinerja = [
    'visi' => ['label' => 'Visi', 'tipe' => 'textarea'],
    'misi' => ['label' => 'Misi', 'tipe' => 'textarea'],
    'tujuan' => ['label' => 'Tujuan', 'tipe' => 'textarea'],
    'sasaran_strategis' => ['label' => 'Sasaran Strategis', 'tipe' => 'textarea'],
    'iku' => ['label' => 'Indikator Kinerja Utama (IKU)', 'tipe' => 'textarea'],
    'iku_target' => ['label' => 'Nilai IKU Target'],
    'iku_realisasi' => ['label' => 'Nilai IKU Realisasi'],
];

return [

    /*
    | Halaman publik /arsitektur (hanya lihat, tanpa login).
    | - "kategori": tab yang ditampilkan ke publik. Infrastruktur dan Keamanan
    |   tidak dibuka karena memuat inventaris server/perangkat serta hasil audit,
    |   kerentanan, dan insiden; tambahkan kuncinya di sini bila memang akan dibuka.
    | - "kolom_tersembunyi": jenis => kolom yang tidak ditampilkan pada detail publik.
    */
    'publik' => [
        'kategori' => ['proses-bisnis', 'layanan', 'data', 'aplikasi', 'peta-rencana'],
        'kolom_tersembunyi' => [
            'aplikasi' => [
                'basis_data', 'bahasa_pemrograman', 'basis_aplikasi', 'framework', 'nama_server', 'lokasi_server',
                'standar_keamanan', 'audit_keamanan', 'peningkatan_keamanan', 'insiden_keamanan',
                'edukasi_keamanan', 'kelaikan_keamanan', 'kerentanan_keamanan',
            ],
        ],
    ],

    'kategori' => [
        'proses-bisnis' => [
            'label' => 'Proses Bisnis',
            'jenis' => ['proses-bisnis'],
            'filter' => ['ref_1' => 'RAB 1 - Sektor', 'ref_2' => 'RAB 2 - Urusan', 'ref_3' => 'RAB 3 - Fungsi'],
            'panel' => ['opd' => 'Daftar OPD', 'ref_1' => 'Sektor Pemerintahan', 'ref_2' => 'Urusan Pemerintahan', 'katalog' => 'Katalog Proses Bisnis'],
            'katalog' => ['kode' => 'ID Proses Bisnis', 'nama' => 'Nama Proses Bisnis', 'unit_kerja' => 'OPD', 'ref_3' => 'RAB 3 - Fungsi', 'status' => 'Status'],
        ],
        'layanan' => [
            'label' => 'Layanan',
            'jenis' => ['layanan'],
            'filter' => ['ref_1' => 'RAL 1', 'ref_2' => 'RAL 2', 'ref_3' => 'RAL 3', 'status' => 'Status Layanan'],
            'panel' => ['opd' => 'Daftar OPD', 'ref_1' => 'RAL Level 1', 'ref_2' => 'RAL Level 2', 'katalog' => 'Katalog Layanan'],
            'katalog' => ['kode' => 'ID Layanan', 'nama' => 'Nama Layanan', 'unit_kerja' => 'OPD', 'ref_3' => 'RAL 3', 'status' => 'Status'],
            // Kartu ringkasan: jumlah & persentase entri yang kolomnya bernilai tertentu
            'indikator' => [
                ['label' => 'Layanan Terdigitalisasi', 'kolom' => 'metode_layanan', 'nilai' => 'Elektronik', 'keterangan' => 'Metode layanan: Elektronik'],
            ],
        ],
        'data' => [
            'label' => 'Data',
            'jenis' => ['data'],
            'filter' => ['ref_1' => 'RAD 1', 'ref_2' => 'RAD 2', 'ref_3' => 'RAD 3', 'status' => 'Status Data'],
            'panel' => ['opd' => 'Daftar OPD (Wali Data)', 'ref_1' => 'RAD Level 1', 'ref_2' => 'RAD Level 2', 'katalog' => 'Katalog Data dan Informasi'],
            'katalog' => ['kode' => 'ID Data', 'nama' => 'Nama Data', 'unit_kerja' => 'Wali Data', 'ref_3' => 'RAD 3', 'status' => 'Status'],
        ],
        'aplikasi' => [
            'label' => 'Aplikasi',
            'jenis' => ['aplikasi'],
            'filter' => ['ref_1' => 'RAA 1', 'ref_2' => 'RAA 2', 'status' => 'Status'],
            'panel' => ['opd' => 'Daftar OPD', 'ref_1' => 'RAA Level 1', 'ref_2' => 'RAA Level 2', 'katalog' => 'Katalog Aplikasi'],
            'katalog' => ['kode' => 'ID Aplikasi', 'nama' => 'Nama Aplikasi', 'unit_kerja' => 'OPD', 'ref_2' => 'RAA 2', 'status' => 'Status'],
        ],
        'infrastruktur' => [
            'label' => 'Infrastruktur',
            'jenis' => [
                'pusat-komputasi', 'jip', 'spl', 'server', 'storage',
                'perangkat-jaringan', 'perangkat-lunak', 'perangkat-keamanan', 'perangkat-periferal',
            ],
            'filter' => ['ref_1' => 'RAI 1', 'ref_2' => 'RAI 2', 'ref_3' => 'RAI 3'],
            'panel' => ['opd' => 'Daftar OPD Pengelola', 'ref_1' => 'RAI Level 1', 'ref_2' => 'RAI Level 2', 'katalog' => 'Katalog Infrastruktur'],
            'katalog' => ['jenis' => 'Jenis', 'kode' => 'ID', 'nama' => 'Nama', 'unit_kerja' => 'OPD Pengelola', 'ref_3' => 'RAI 3'],
        ],
        'keamanan' => [
            'label' => 'Keamanan',
            'jenis' => ['standar-keamanan', 'edukasi', 'kerentanan', 'peningkatan', 'insiden', 'audit', 'kelaikan'],
            'filter' => ['ref_1' => 'RAK 1', 'ref_2' => 'RAK 2'],
            'panel' => ['ref_1' => 'RAK Level 1', 'ref_2' => 'RAK Level 2', 'katalog' => 'Katalog Keamanan'],
            'katalog' => ['jenis' => 'Jenis', 'kode' => 'ID', 'nama' => 'Nama', 'ref_2' => 'RAK 2'],
        ],
        'peta-rencana' => [
            'label' => 'Peta Rencana',
            'jenis' => ['peta-rencana'],
            'filter' => ['tahun' => 'Tahun Pelaksanaan', 'status' => 'Status', 'ref_1' => 'RAB 1 - Sektor', 'ref_2' => 'RAB 2 - Urusan'],
            'panel' => ['opd' => 'Daftar OPD Pelaksana', 'ref_1' => 'Sektor Pemerintahan', 'ref_2' => 'Urusan Pemerintahan', 'katalog' => 'Katalog Peta Rencana'],
            'katalog' => ['kode' => 'ID Proses Bisnis', 'nama' => 'Kegiatan', 'unit_kerja' => 'OPD Pelaksana', 'tahun' => 'Tahun', 'status' => 'Status'],
        ],
    ],

    'jenis' => [

        // ------------------------------------------------------------------
        // Proses Bisnis
        // ------------------------------------------------------------------
        'proses-bisnis' => [
            'label' => 'Proses Bisnis',
            'sheet' => 'Proses_Bisnis',
            'kunci' => ['kode'],
            'kolom' => [
                'kode' => ['label' => 'ID Proses Bisnis'],
                'nama' => ['label' => 'Nama Bisnis/Urusan'],
                'status' => ['label' => 'Status'],
                'unit_kerja' => ['label' => 'Unit Kerja'],
                'kode_proses_bisnis' => ['label' => 'Kode Proses Bisnis'],
                'sumber' => ['label' => 'Sumber Proses Bisnis'],
                'program' => ['label' => 'Program'],
                'uraian' => ['label' => 'Uraian Bisnis Proses', 'tipe' => 'textarea'],
            ] + $kinerja + $rab + [
                'instansi' => ['label' => 'Instansi'],
            ],
        ],

        // ------------------------------------------------------------------
        // Layanan
        // ------------------------------------------------------------------
        'layanan' => [
            'label' => 'Layanan',
            'sheet' => 'Layanan',
            'kolom' => [
                'kode' => ['label' => 'ID Layanan'],
                'nama' => ['label' => 'Nama Layanan'],
                'status' => ['label' => 'Status Layanan'],
                'unit_kerja' => ['label' => 'Unit Kerja'],
                'kode_layanan' => ['label' => 'Kode Layanan'],
                'status_reviu' => ['label' => 'Status Reviu Arsitektur'],
                'tujuan' => ['label' => 'Tujuan', 'tipe' => 'textarea'],
                'fungsi' => ['label' => 'Fungsi', 'tipe' => 'textarea'],
                'penanggung_jawab' => ['label' => 'Penanggung Jawab Pelayanan'],
                'kl_terkait' => ['label' => 'Kementerian/Lembaga Terkait'],
                'target_layanan' => ['label' => 'Target Layanan'],
                'metode_layanan' => ['label' => 'Metode Layanan'],
                'potensi_manfaat' => ['label' => 'Potensi Manfaat', 'tipe' => 'textarea'],
                'potensi_ekonomi' => ['label' => 'Potensi Ekonomi', 'tipe' => 'textarea'],
                'potensi_resiko' => ['label' => 'Potensi Resiko', 'tipe' => 'textarea'],
                'mitigasi_resiko' => ['label' => 'Mitigasi Resiko', 'tipe' => 'textarea'],
                'data' => ['label' => 'Data', 'tipe' => 'textarea'],
                'aplikasi' => ['label' => 'Aplikasi'],
                'status_probis' => ['label' => 'Status Probis'],
                'proses_bisnis' => ['label' => 'Proses Bisnis'],
                'rab_level_2' => ['label' => 'Urusan Pemerintahan (RAB Level 2)', 'header' => ['Urusan Pemerintahan → RAB Level 2', 'RAB Level 2']],
                'ref_1' => ['label' => 'RAL Level 1'],
                'ref_2' => ['label' => 'RAL Level 2'],
                'ref_3' => ['label' => 'RAL Level 3'],
                'rab_level_3' => ['label' => 'RAB Level 3'],
                'url' => ['label' => 'URL'],
                'penyedia_layanan' => ['label' => 'Penyedia Layanan'],
                'pengelola_layanan' => ['label' => 'Pengelola Layanan'],
                'cara_akses' => ['label' => 'Cara Akses Layanan'],
                'klasifikasi_sistem' => ['label' => 'Klasifikasi Sistem'],
                'kontak_bantuan' => ['label' => 'Kontak Permintaan Bantuan', 'tipe' => 'textarea'],
                'waktu_bantuan' => ['label' => 'Waktu Pelayanan Bantuan'],
                'target_pemenuhan' => ['label' => 'Target Pemenuhan Layanan'],
                'target_ketersediaan' => ['label' => 'Target Ketersediaan Layanan'],
                'instansi' => ['label' => 'Instansi'],
            ],
        ],

        // ------------------------------------------------------------------
        // Data dan Informasi
        // ------------------------------------------------------------------
        'data' => [
            'label' => 'Data dan Informasi',
            'sheet' => 'Data_Informasi',
            'kolom' => [
                'kode' => ['label' => 'ID Data'],
                'nama' => ['label' => 'Nama Data'],
                'status' => ['label' => 'Status Data'],
                'unit_kerja' => ['label' => 'Penanggung Jawab Data/Wali Data', 'header' => ['Penanggung Jawab Data/ Wali Data', 'Wali Data']],
                'kode_data' => ['label' => 'Kode Data'],
                'status_reviu' => ['label' => 'Status Reviu Data'],
                'uraian' => ['label' => 'Uraian Data', 'tipe' => 'textarea'],
                'tujuan' => ['label' => 'Tujuan Data', 'header' => ['Tujuan dari', 'Tujuan']],
                'aplikasi' => ['label' => 'Aplikasi'],
                'sifat_data' => ['label' => 'Sifat Data'],
                'jenis_data' => ['label' => 'Jenis Data'],
                'validitas_data' => ['label' => 'Validitas Data'],
                'produsen_data' => ['label' => 'Penghasil Data/Produsen Data'],
                'informasi_input' => ['label' => 'Informasi yang Terkait (Input)', 'tipe' => 'textarea'],
                'informasi_output' => ['label' => 'Informasi yang Terkait (Output)', 'tipe' => 'textarea'],
                'interoperabilitas' => ['label' => 'Interoperabilitas'],
                'proses_bisnis' => ['label' => 'Proses Bisnis'],
                'layanan' => ['label' => 'Layanan', 'tipe' => 'textarea'],
                'ref_1' => ['label' => 'RAD Level 1'],
                'ref_2' => ['label' => 'RAD Level 2'],
                'ref_3' => ['label' => 'RAD Level 3'],
                'instansi' => ['label' => 'Instansi'],
            ],
        ],

        // ------------------------------------------------------------------
        // Aplikasi
        // ------------------------------------------------------------------
        'aplikasi' => [
            'label' => 'Aplikasi',
            'sheet' => 'Aplikasi',
            'kolom' => [
                'kode' => ['label' => 'ID Aplikasi'],
                'nama' => ['label' => 'Nama Aplikasi'],
                'status' => ['label' => 'Status'],
                'unit_kerja' => ['label' => 'Unit Operasional Teknologi'],
                'tahun' => ['label' => 'Tahun Pengembangan'],
                'kode_aplikasi' => ['label' => 'Kode Aplikasi'],
                'status_reviu' => ['label' => 'Status Reviu Aplikasi'],
                'kebutuhan_fitur' => ['label' => 'Kebutuhan Fitur', 'tipe' => 'textarea'],
                'uraian' => ['label' => 'Uraian Aplikasi', 'tipe' => 'textarea'],
                'fungsi' => ['label' => 'Fungsi Aplikasi', 'tipe' => 'textarea'],
                'url' => ['label' => 'URL'],
                'layanan' => ['label' => 'Layanan', 'tipe' => 'textarea'],
                'proses_bisnis' => ['label' => 'Proses Bisnis', 'tipe' => 'textarea'],
                'data_digunakan' => ['label' => 'Data yang Digunakan', 'tipe' => 'textarea'],
                'luaran' => ['label' => 'Luaran', 'tipe' => 'textarea'],
                'inputan_data' => ['label' => 'Inputan Data', 'tipe' => 'textarea'],
                'supplier_data' => ['label' => 'Supplier Data'],
                'luaran_data' => ['label' => 'Luaran Data', 'tipe' => 'textarea'],
                'customer_data' => ['label' => 'Customer Data'],
                'tipe_lisensi' => ['label' => 'Tipe Lisensi Aplikasi'],
                'basis_data' => ['label' => 'Nama Basis Data'],
                'bahasa_pemrograman' => ['label' => 'Bahasa Pemrograman'],
                'basis_aplikasi' => ['label' => 'Basis Aplikasi'],
                'framework' => ['label' => 'Framework Pembangunan Aplikasi'],
                'nama_server' => ['label' => 'Nama Server'],
                'lokasi_server' => ['label' => 'Lokasi Server'],
                'unit_pengembang' => ['label' => 'Unit Pengembang'],
                'kepemilikan' => ['label' => 'Kepemilikan Aplikasi'],
                'ref_1' => ['label' => 'RAA Level 1'],
                'ref_2' => ['label' => 'RAA Level 2'],
                'komputasi_awan' => ['label' => 'Komputasi Awan'],
                'spl' => ['label' => 'Sistem Penghubung Layanan'],
                'jip' => ['label' => 'Jaringan Intra Pemerintah'],
                'standar_keamanan' => ['label' => 'Standar Teknis dan Prosedur Keamanan SPBE'],
                'audit_keamanan' => ['label' => 'Audit Keamanan SPBE'],
                'peningkatan_keamanan' => ['label' => 'Peningkatan Keamanan SPBE'],
                'insiden_keamanan' => ['label' => 'Penanganan Insiden Keamanan SPBE'],
                'edukasi_keamanan' => ['label' => 'Edukasi Kesadaran Keamanan SPBE'],
                'kelaikan_keamanan' => ['label' => 'Kelaikan Keamanan SPBE'],
                'kerentanan_keamanan' => ['label' => 'Identifikasi Kerentanan Keamanan SPBE'],
                'instansi' => ['label' => 'Instansi'],
            ],
        ],

        // ------------------------------------------------------------------
        // Infrastruktur
        // ------------------------------------------------------------------
        'pusat-komputasi' => [
            'label' => 'Pusat Komputasi',
            'sheet' => 'PusatKomputasi',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Fasilitas'],
                'unit_kerja' => ['label' => 'Unit Kerja Penanggung Jawab'],
                'kode_referensi' => ['label' => 'Kode Model Referensi SPBE'],
                'bandwidth_intranet' => ['label' => 'Bandwidth Intranet'],
                'bandwidth_internet' => ['label' => 'Bandwidth Internet'],
                'lokasi' => ['label' => 'Lokasi', 'tipe' => 'textarea'],
                'kepemilikan' => ['label' => 'Kepemilikan'],
                'klasifikasi_tier' => ['label' => 'Klasifikasi Tier Fasilitas'],
                'sistem_pengamanan' => ['label' => 'Sistem Pengamanan Fasilitas'],
            ] + $rai,
        ],
        'jip' => [
            'label' => 'Jaringan Intra Pemerintah',
            'sheet' => 'JIP',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Jaringan Intra Pemerintah'],
                'unit_kerja' => ['label' => 'Unit Kerja Pengelola'],
                'deskripsi' => ['label' => 'Deskripsi Jaringan', 'tipe' => 'textarea'],
                'jenis_jaringan' => ['label' => 'Jenis Jaringan'],
                'kepemilikan' => ['label' => 'Kepemilikan'],
                'nama_pemilik' => ['label' => 'Nama Pemilik'],
                'bandwidth' => ['label' => 'Bandwidth'],
                'tipe_media' => ['label' => 'Tipe Media Jaringan'],
                'media_lainnya' => ['label' => 'Nama Media Lainnya'],
            ] + $rai,
        ],
        'spl' => [
            'label' => 'Sistem Penghubung Layanan',
            'sheet' => 'SPL',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Sistem Penghubung Layanan Pemerintah'],
                'deskripsi' => ['label' => 'Deskripsi Sistem Penghubung', 'tipe' => 'textarea'],
                'jenis_spl' => ['label' => 'Jenis SPL'],
                'kepemilikan' => ['label' => 'Kepemilikan'],
                'nama_pemilik' => ['label' => 'Nama Pemilik'],
                'jaringan_intra' => ['label' => 'Nama Jaringan Intra yang Digunakan'],
            ] + $rai,
        ],
        'server' => [
            'label' => 'Server',
            'sheet' => 'Server',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Server'],
                'unit_kerja' => ['label' => 'Unit Pengelola'],
                'deskripsi' => ['label' => 'Deskripsi Server', 'tipe' => 'textarea'],
                'jenis_penggunaan' => ['label' => 'Jenis Penggunaan Server'],
                'daftar_aplikasi' => ['label' => 'Daftar Aplikasi', 'tipe' => 'textarea'],
                'status_kepemilikan' => ['label' => 'Status Kepemilikan', 'header' => ['Status Kepemilikkan', 'Status Kepemilikan']],
                'nama_pemilik' => ['label' => 'Nama Pemilik Server'],
                'lokasi' => ['label' => 'Lokasi Server'],
                'perangkat_lunak' => ['label' => 'Perangkat Lunak yang Digunakan'],
                'kapasitas_memori' => ['label' => 'Kapasitas Memori (RAM)'],
                'teknologi_prosesor' => ['label' => 'Jenis Teknologi Prosesor'],
                'kapasitas_penyimpanan' => ['label' => 'Jumlah Kapasitas Penyimpanan'],
                'kode_referensi' => ['label' => 'Kode Referensi Infrastruktur'],
                'teknik_penyimpanan' => ['label' => 'Teknik Penyimpanan'],
            ] + $rai,
        ],
        'storage' => [
            'label' => 'Media Penyimpanan (Storage)',
            'sheet' => 'Storage',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Media Storage'],
                'unit_kerja' => ['label' => 'Unit Pengelola'],
                'deskripsi' => ['label' => 'Deskripsi Data Storage', 'header' => ['Deskrpsi Data Storage', 'Deskripsi Data Storage'], 'tipe' => 'textarea'],
                'data_digunakan' => ['label' => 'Data yang Digunakan'],
                'kepemilikan' => ['label' => 'Kepemilikan'],
                'nama_pemilik' => ['label' => 'Nama Pemilik'],
                'lokasi' => ['label' => 'Lokasi Storage'],
                'perangkat_lunak' => ['label' => 'Perangkat Lunak yang Digunakan'],
                'metode_akses' => ['label' => 'Metode Akses Data Sharing'],
            ] + $rai,
        ],
        'perangkat-jaringan' => [
            'label' => 'Perangkat Jaringan',
            'sheet' => 'PerangkatJaringan',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Perangkat Jaringan'],
                'unit_kerja' => ['label' => 'Unit Pengelola'],
                'deskripsi' => ['label' => 'Deskripsi Perangkat Jaringan', 'tipe' => 'textarea'],
                'tipe_device' => ['label' => 'Tipe Device'],
                'kepemilikan' => ['label' => 'Kepemilikan'],
                'nama_pemilik' => ['label' => 'Nama Pemilik'],
            ] + $rai,
        ],
        'perangkat-lunak' => [
            'label' => 'Perangkat Lunak Platform',
            'sheet' => 'PerangkatLunak',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Perangkat Lunak'],
                'deskripsi' => ['label' => 'Deskripsi Perangkat Lunak', 'tipe' => 'textarea'],
                'tipe_perangkat_lunak' => ['label' => 'Tipe Perangkat Lunak'],
                'sistem_operasi' => ['label' => 'Jenis Perangkat Sistem Operasi'],
                'sistem_utilitas' => ['label' => 'Jenis Perangkat Sistem Utilitas'],
                'sistem_database' => ['label' => 'Jenis Perangkat Sistem Database'],
                'jenis_lisensi' => ['label' => 'Jenis Lisensi'],
                'pemilik_lisensi' => ['label' => 'Nama Pemilik Lisensi'],
                'validitas_lisensi' => ['label' => 'Validitas Lisensi'],
            ] + $rai,
        ],
        'perangkat-keamanan' => [
            'label' => 'Perangkat Keamanan',
            'sheet' => 'PerangkatKeamanan',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Perangkat Keamanan'],
                'unit_kerja' => ['label' => 'Unit Pengelola'],
                'deskripsi' => ['label' => 'Deskripsi Security Device', 'tipe' => 'textarea'],
                'tipe_keamanan' => ['label' => 'Tipe Keamanan Device'],
                'nama_pemilik' => ['label' => 'Nama Pemilik'],
            ] + $rai,
        ],
        'perangkat-periferal' => [
            'label' => 'Perangkat Periferal',
            'sheet' => 'PerangkatPeriferal',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Perangkat Periferal'],
                'unit_kerja' => ['label' => 'Unit Pengelola'],
                'deskripsi' => ['label' => 'Deskripsi Periferal', 'tipe' => 'textarea'],
                'tipe' => ['label' => 'Tipe'],
                'lokasi' => ['label' => 'Lokasi'],
            ] + $rai,
        ],

        // ------------------------------------------------------------------
        // Keamanan
        // ------------------------------------------------------------------
        'standar-keamanan' => [
            'label' => 'Standar Keamanan',
            'sheet' => 'StandarKeamanan',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Standar Keamanan'],
                'jenis_standar' => ['label' => 'Jenis Standar Keamanan'],
                'deskripsi' => ['label' => 'Deskripsi', 'tipe' => 'textarea'],
                'tanggal_mulai' => ['label' => 'Tanggal Mulai Penerapan', 'tipe' => 'tanggal'],
                'tanggal_akhir' => ['label' => 'Tanggal Akhir Penerapan', 'tipe' => 'tanggal'],
            ] + $rak + [
                'data_informasi' => ['label' => 'Data dan Informasi'],
                'aplikasi' => ['label' => 'Aplikasi'],
            ],
        ],
        'edukasi' => [
            'label' => 'Edukasi Kesadaran Keamanan',
            'sheet' => 'Edukasi',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Kegiatan', 'header' => 'Nama Kegiatan Edukasi Kesadaran Keamanan SPBE'],
                'deskripsi' => ['label' => 'Deskripsi Kegiatan', 'header' => 'Deskripsi Kegiatan Edukasi Kesadaran Keamanan SPBE', 'tipe' => 'textarea'],
                'tanggal' => ['label' => 'Tanggal Kegiatan', 'header' => 'Tanggal Kegiatan Edukasi Kesadaran Keamanan SPBE', 'tipe' => 'tanggal'],
            ] + $rak + [
                'aplikasi' => ['label' => 'Aplikasi'],
                'data_informasi' => ['label' => 'Data dan Informasi'],
            ],
        ],
        'kerentanan' => [
            'label' => 'Identifikasi Kerentanan',
            'sheet' => 'Kerentanan',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Kegiatan', 'header' => 'Nama Kegiatan Identifikasi Kerentanan Keamanan SPBE'],
                'deskripsi' => ['label' => 'Deskripsi Kegiatan', 'header' => 'Deskripsi Kegiatan Identifikasi Kerentanan Keamanan SPBE', 'tipe' => 'textarea'],
                'tanggal' => ['label' => 'Tanggal Kegiatan', 'header' => ['Tanggal Kegiatan Kegiatan Identifikasi Kerentanan Keamanan SPBE', 'Tanggal Kegiatan Identifikasi Kerentanan Keamanan SPBE'], 'tipe' => 'tanggal'],
            ] + $rak + [
                'aplikasi' => ['label' => 'Aplikasi'],
                'data_informasi' => ['label' => 'Data dan Informasi'],
            ],
        ],
        'peningkatan' => [
            'label' => 'Peningkatan Keamanan',
            'sheet' => 'Peningkatan',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Kegiatan', 'header' => 'Nama Kegiatan Peningkatan Keamanan SPBE'],
                'deskripsi' => ['label' => 'Deskripsi Kegiatan', 'header' => 'Deskripsi Kegiatan Peningkatan Keamanan SPBE', 'tipe' => 'textarea'],
                'tanggal' => ['label' => 'Tanggal Kegiatan', 'header' => 'Tanggal Kegiatan Peningkatan Keamanan SPBE', 'tipe' => 'tanggal'],
                'nilai_kematangan' => ['label' => 'Nilai Kematangan Keamanan Siber'],
            ] + $rak,
        ],
        'insiden' => [
            'label' => 'Penanganan Insiden',
            'sheet' => 'Insiden',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Kegiatan', 'header' => 'Nama Kegiatan Penanganan Insiden Keamanan SPBE'],
                'deskripsi' => ['label' => 'Deskripsi Kegiatan', 'header' => 'Deskripsi Kegiatan Penanganan Insiden Keamanan SPBE', 'tipe' => 'textarea'],
                'tanggal' => ['label' => 'Tanggal Kegiatan', 'header' => 'Tanggal Kegiatan Penanganan Insiden Keamanan SPBE', 'tipe' => 'tanggal'],
                'nilai_kematangan' => ['label' => 'Nilai Kematangan Penanganan Insiden Siber', 'header' => ['Nilai Kematangan Penangan Insiden Siber', 'Nilai Kematangan Penanganan Insiden Siber']],
                'aplikasi' => ['label' => 'Aplikasi'],
            ] + $rak + [
                'data_informasi' => ['label' => 'Data dan Informasi'],
            ],
        ],
        'audit' => [
            'label' => 'Audit Keamanan',
            'sheet' => 'Audit',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Kegiatan', 'header' => 'Nama Kegiatan Audit Keamanan SPBE'],
                'hasil_audit' => ['label' => 'Hasil Audit Keamanan SPBE'],
                'jenis_audit' => ['label' => 'Jenis Audit Keamanan'],
                'tanggal' => ['label' => 'Tanggal Kegiatan', 'header' => 'Tanggal Kegiatan Audit Keamanan', 'tipe' => 'tanggal'],
                'tindak_lanjut' => ['label' => 'Tindak Lanjut Audit Keamanan'],
            ] + $rak + [
                'aplikasi' => ['label' => 'Aplikasi'],
                'infrastruktur' => ['label' => 'Infrastruktur'],
            ],
        ],
        'kelaikan' => [
            'label' => 'Kelaikan Keamanan',
            'sheet' => 'Kelaikan',
            'kolom' => [
                'kode' => ['label' => 'ID'],
                'nama' => ['label' => 'Nama Kegiatan', 'header' => 'Nama Kegiatan Kelaikan Keamanan SPBE'],
                'tanggal' => ['label' => 'Tanggal Kelaikan', 'header' => 'Tanggal Kelaikan Keamanan SPBE', 'tipe' => 'tanggal'],
            ] + $rak + [
                'ref_3' => ['label' => 'RAK Level 3'],
                'aplikasi' => ['label' => 'Aplikasi'],
                'data_informasi' => ['label' => 'Data dan Informasi'],
            ],
        ],

        // ------------------------------------------------------------------
        // Peta Rencana
        // ------------------------------------------------------------------
        'peta-rencana' => [
            'label' => 'Peta Rencana',
            'sheet' => 'Peta_Rencana',
            'kunci' => ['kode', 'nama', 'unit_kerja', 'tahun', 'opd_pemilik_probis', 'inisiatif_strategis', 'gap_analisis', 'muatan', 'target'],
            'kolom' => [
                'kode' => ['label' => 'ID Proses Bisnis'],
                'nama_proses_bisnis' => ['label' => 'Nama Bisnis/Urusan'],
                'opd_pemilik_probis' => ['label' => 'OPD Pemilik Probis'],
                'inisiatif_strategis' => ['label' => 'Inisiatif Strategis', 'tipe' => 'textarea'],
                'program_permendagri' => ['label' => 'Program (Permendagri)', 'header' => ['PROGRAM (Permedagri)', 'PROGRAM (Permendagri)']],
                'program' => ['label' => 'Program'],
                'gap_analisis' => ['label' => 'Gap Analisis', 'tipe' => 'textarea'],
                'nama' => ['label' => 'Kegiatan'],
                'unit_kerja' => ['label' => 'OPD Peta Rencana'],
                'muatan' => ['label' => 'Muatan PR'],
                'target' => ['label' => 'Target'],
                'tahun' => ['label' => 'Tahun Pelaksanaan'],
                'domain_aplikasi' => ['label' => 'Nama Domain Aplikasi'],
                'pagu_anggaran' => ['label' => 'Pagu Anggaran'],
                'sub_kegiatan' => ['label' => 'Sub Kegiatan (Renstra)'],
                'catatan' => ['label' => 'Catatan/Capaian', 'tipe' => 'textarea'],
            ] + $kinerja + $rab + [
                'status' => ['label' => 'Status'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Padanan nama perangkat daerah
    |--------------------------------------------------------------------------
    | Nama pada Excel yang berbeda dengan nama di Master Data Unit Kerja.
    | Kiri: nama pada Excel, kanan: nama pada Master Data Unit Kerja.
    | Pencocokan mengabaikan huruf besar/kecil dan tanda kutip.
    */
    'alias_instansi' => [
        'Badan Perencanaan Pembangunan Daerah dan Litbang' => 'BADAN PERENCANAAN PEMBANGUNAN, RISET DAN INOVASI DAERAH',
        'Rumah Sakit Umum Daerah Tarakan' => 'RSUD DR. H. JUSUF S.K. TARAKAN',
        'Sekretariat DPRD' => 'SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH',
        'Dinas Pekerjaan Umum, Penataan Ruang, Perumahan dan Kawasan Pemukiman' => 'DINAS PEKERJAAN UMUM, PENATAAN RUANG, PERUMAHAN DAN KAWASAN PERMUKIMAN',
        'DKISP' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
        'DKISP Provinsi Kalimantan Utara' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
        'DKISP Prov Kaltara' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
        'Dinas Komunikasi dan Informatika' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
    ],
];
