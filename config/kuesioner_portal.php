<?php

/*
|--------------------------------------------------------------------------
| Kuesioner Kualitas Layanan Portal (E-GovQual + IPA + Kano)
|--------------------------------------------------------------------------
|
| Instrumen penelitian tesis "Evaluasi Kualitas Layanan Portal E-Layanan TIK
| Pemerintah Provinsi Kalimantan Utara". Isi berkas ini mengikuti Tabel 3.1,
| Tabel 3.2, Tabel 3.3, dan Lampiran 1 proposal. Kode atribut dipakai sebagai
| kunci jawaban di database, jadi jangan diubah setelah pengumpulan data
| dimulai; ubah teks pernyataan hanya melalui periode baru.
|
*/

return [

    'judul' => 'Kuesioner Evaluasi Kualitas Layanan Portal E-Layanan TIK',

    'pengantar' => 'Kuesioner ini merupakan bagian dari penelitian tesis pada Program Studi S2 Informatika '
        . 'Universitas Amikom Yogyakarta. Penelitian bertujuan mengevaluasi kualitas layanan Portal E-Layanan TIK '
        . '(layanan.diskominfo.kaltaraprov.go.id). Pengisian membutuhkan waktu sekitar 20 menit. Tidak ada jawaban '
        . 'benar atau salah. Identitas akun hanya digunakan untuk memastikan setiap pengguna mengisi satu kali, lalu '
        . 'diganti dengan kode acak. Seluruh jawaban dijaga kerahasiaannya, dianalisis secara agregat, dan hanya '
        . 'digunakan untuk kepentingan penelitian serta perbaikan layanan.',

    // Dimensi E-GovQual beserta atribut. 'pentingnya' = Bagian B, 'kinerja' = Bagian C,
    // 'fungsional' / 'disfungsional' = Bagian D (kuesioner Kano).
    'dimensi' => [
        'EF' => [
            'nama' => 'Efficiency',
            'definisi' => 'Kemudahan menggunakan portal dan kualitas informasi yang disediakan',
            'atribut' => [
                'EF1' => [
                    'kinerja' => 'Struktur menu portal jelas dan mudah diikuti',
                    'fungsional' => 'Struktur menu portal jelas dan mudah diikuti',
                    'disfungsional' => 'Struktur menu portal membingungkan',
                ],
                'EF2' => [
                    'kinerja' => 'Fitur pencarian efektif menemukan layanan atau informasi yang dibutuhkan',
                    'fungsional' => 'Fitur pencarian efektif menemukan layanan atau informasi',
                    'disfungsional' => 'Fitur pencarian tidak menemukan layanan atau informasi yang dicari',
                ],
                'EF3' => [
                    'kinerja' => 'Susunan halaman dan peta layanan tertata dengan baik',
                    'fungsional' => 'Susunan halaman dan peta layanan tertata baik',
                    'disfungsional' => 'Susunan halaman dan peta layanan tidak tertata',
                ],
                'EF4' => [
                    'kinerja' => 'Tampilan dan informasi portal sesuai dengan kebutuhan pengguna, misalnya daftar permohonan milik unit kerja',
                    'fungsional' => 'Tampilan dan informasi portal sesuai kebutuhan Anda',
                    'disfungsional' => 'Tampilan dan informasi portal tidak sesuai kebutuhan Anda',
                ],
                'EF5' => [
                    'kinerja' => 'Informasi persyaratan, alur, dan dokumen layanan disajikan cukup rinci',
                    'fungsional' => 'Persyaratan, alur, dan dokumen layanan dijelaskan rinci',
                    'disfungsional' => 'Persyaratan, alur, dan dokumen layanan kurang jelas',
                ],
                'EF6' => [
                    'kinerja' => 'Informasi pada portal mutakhir',
                    'fungsional' => 'Informasi pada portal selalu mutakhir',
                    'disfungsional' => 'Informasi pada portal sudah usang',
                ],
                'EF7' => [
                    'kinerja' => 'Petunjuk pengisian formulir permohonan memadai',
                    'fungsional' => 'Petunjuk pengisian formulir memadai',
                    'disfungsional' => 'Petunjuk pengisian formulir tidak tersedia atau kurang',
                ],
            ],
        ],
        'TR' => [
            'nama' => 'Trust',
            'definisi' => 'Keyakinan pengguna bahwa portal aman dan melindungi data',
            'atribut' => [
                'TR1' => [
                    'kinerja' => 'Proses login dan pengelolaan akun SSO aman',
                    'fungsional' => 'Login dan akun SSO aman',
                    'disfungsional' => 'Login dan akun SSO kurang aman',
                ],
                'TR2' => [
                    'kinerja' => 'Portal hanya meminta data pribadi yang diperlukan',
                    'fungsional' => 'Portal hanya meminta data pribadi yang diperlukan',
                    'disfungsional' => 'Portal meminta data pribadi yang tidak diperlukan',
                ],
                'TR3' => [
                    'kinerja' => 'Data dan dokumen yang diunggah disimpan secara aman',
                    'fungsional' => 'Data dan dokumen yang diunggah disimpan aman',
                    'disfungsional' => 'Data dan dokumen yang diunggah tidak terjamin keamanannya',
                ],
                'TR4' => [
                    'kinerja' => 'Data yang diberikan hanya digunakan untuk keperluan permohonan',
                    'fungsional' => 'Data hanya digunakan untuk keperluan permohonan',
                    'disfungsional' => 'Data digunakan untuk keperluan lain',
                ],
            ],
        ],
        'RL' => [
            'nama' => 'Reliability',
            'definisi' => 'Kelayakan dan kecepatan mengakses serta menerima layanan',
            'atribut' => [
                'RL1' => [
                    'kinerja' => 'Formulir, templat, dan dokumen layanan dapat diunduh dengan cepat',
                    'fungsional' => 'Formulir dan dokumen dapat diunduh dengan cepat',
                    'disfungsional' => 'Formulir dan dokumen lambat diunduh',
                ],
                'RL2' => [
                    'kinerja' => 'Portal tersedia dan dapat diakses kapan pun dibutuhkan',
                    'fungsional' => 'Portal dapat diakses kapan pun dibutuhkan',
                    'disfungsional' => 'Portal sering tidak dapat diakses',
                ],
                'RL3' => [
                    'kinerja' => 'Permohonan berhasil dikirim dan diproses pada percobaan pertama',
                    'fungsional' => 'Permohonan berhasil dikirim pada percobaan pertama',
                    'disfungsional' => 'Permohonan harus dikirim berulang kali',
                ],
                'RL4' => [
                    'kinerja' => 'Permohonan diselesaikan tepat waktu sesuai waktu layanan yang dijanjikan',
                    'fungsional' => 'Permohonan selesai tepat waktu sesuai janji layanan',
                    'disfungsional' => 'Permohonan selesai melewati waktu yang dijanjikan',
                ],
                'RL5' => [
                    'kinerja' => 'Halaman portal termuat dengan cepat',
                    'fungsional' => 'Halaman portal termuat dengan cepat',
                    'disfungsional' => 'Halaman portal lambat termuat',
                ],
                'RL6' => [
                    'kinerja' => 'Portal berfungsi dengan baik pada peramban dan perangkat yang digunakan',
                    'fungsional' => 'Portal berfungsi baik di peramban dan perangkat Anda',
                    'disfungsional' => 'Portal bermasalah di peramban atau perangkat Anda',
                ],
            ],
        ],
        'CS' => [
            'nama' => 'Citizen Support',
            'definisi' => 'Kemampuan memperoleh bantuan petugas ketika dibutuhkan',
            // Kinerja atribut petugas hanya dinilai responden yang pernah menghubungi petugas
            // (Papadomichelaki & Mentzas, 2012). Kepentingan dan Kano tetap ditanyakan.
            'kinerja_hanya_jika_pernah_hubungi_petugas' => true,
            'atribut' => [
                'CS1' => [
                    'kinerja' => 'Petugas menunjukkan kesungguhan dalam menyelesaikan masalah pengguna',
                    'fungsional' => 'Petugas sungguh-sungguh menyelesaikan masalah Anda',
                    'disfungsional' => 'Petugas kurang sungguh-sungguh menyelesaikan masalah Anda',
                ],
                'CS2' => [
                    'kinerja' => 'Petugas memberikan tanggapan cepat atas pertanyaan atau kendala',
                    'fungsional' => 'Petugas cepat menanggapi pertanyaan atau kendala',
                    'disfungsional' => 'Petugas lambat menanggapi pertanyaan atau kendala',
                ],
                'CS3' => [
                    'kinerja' => 'Petugas memiliki pengetahuan yang memadai untuk menjawab pertanyaan',
                    'fungsional' => 'Petugas menguasai jawaban atas pertanyaan Anda',
                    'disfungsional' => 'Petugas kurang menguasai jawaban atas pertanyaan Anda',
                ],
                'CS4' => [
                    'kinerja' => 'Petugas mampu menumbuhkan rasa percaya dan yakin kepada pengguna',
                    'fungsional' => 'Petugas membuat Anda merasa yakin dan percaya',
                    'disfungsional' => 'Petugas membuat Anda ragu',
                ],
            ],
        ],
    ],

    // Konstruk kepuasan pengguna, hanya dinilai pada Bagian C.
    'kepuasan' => [
        'KPS1' => 'Secara keseluruhan pengguna puas dengan layanan portal',
        'KPS2' => 'Layanan portal sesuai dengan harapan pengguna',
        'KPS3' => 'Portal memenuhi kebutuhan layanan TIK unit kerja pengguna',
    ],

    // Tabel 3.2
    'skala_kepentingan' => [
        1 => 'Sangat tidak penting',
        2 => 'Tidak penting',
        3 => 'Cukup penting',
        4 => 'Penting',
        5 => 'Sangat penting',
    ],
    'skala_kinerja' => [
        1 => 'Sangat tidak setuju',
        2 => 'Tidak setuju',
        3 => 'Netral',
        4 => 'Setuju',
        5 => 'Sangat setuju',
    ],

    // Tabel 3.3
    'skala_kano' => [
        1 => 'Saya suka',
        2 => 'Memang seharusnya begitu',
        3 => 'Netral',
        4 => 'Masih dapat menerima',
        5 => 'Tidak suka',
    ],

    // Bagian A. Kunci disimpan di database; label hanya untuk tampilan.
    'profil' => [
        'status_kepegawaian' => [
            'pns' => 'PNS',
            'pppk' => 'PPPK',
            'non_asn' => 'Non-ASN/operator',
        ],
        'peran' => [
            'operator' => 'Operator/pengelola TIK OPD',
            'pejabat' => 'Pejabat struktural',
            'staf' => 'Staf pengguna',
        ],
        'lama_penggunaan' => [
            'lt6b' => '< 6 bulan',
            '6_12b' => '6–12 bulan',
            '1_2t' => '1–2 tahun',
            'gt2t' => '> 2 tahun',
        ],
        'frekuensi' => [
            'mingguan' => 'Hampir setiap minggu',
            'bulanan' => '1–3 kali per bulan',
            'tahunan' => 'Beberapa kali per tahun',
        ],
        'layanan' => [
            'email' => 'Email dinas',
            'subdomain' => 'Subdomain',
            'pse' => 'PSE',
            'tte' => 'TTE',
            'vidcon' => 'Video conference',
            'jaringan' => 'VPN/jaringan intra',
            'pusat_data' => 'Pusat data',
            'shortlink' => 'Pemendek tautan',
            'rekomendasi' => 'Rekomendasi aplikasi',
            'splp' => 'SPLP',
            'konsultasi' => 'Konsultasi SPBE',
            'lainnya' => 'Lainnya',
        ],
    ],

    /*
    | Kriteria inklusi: akun yang pernah mengajukan minimal satu permohonan.
    | Setiap entri memetakan tabel permohonan ke kelompok layanan Bagian A6.
    | 'kecuali' berisi kondisi kolom => nilai yang TIDAK dihitung (mis. draf).
    | Tabel yang tidak ada atau tidak punya kolom user_id dilewati.
    */
    'tabel_permohonan' => [
        ['tabel' => 'email_requests', 'layanan' => 'email'],
        ['tabel' => 'email_password_reset_requests', 'layanan' => 'email'],
        ['tabel' => 'subdomain_requests', 'layanan' => 'subdomain'],
        ['tabel' => 'subdomain_ip_change_requests', 'layanan' => 'subdomain'],
        ['tabel' => 'subdomain_name_change_requests', 'layanan' => 'subdomain'],
        ['tabel' => 'subdomain_data_update_requests', 'layanan' => 'subdomain'],
        ['tabel' => 'pse_update_requests', 'layanan' => 'pse'],
        ['tabel' => 'tte_assistance_requests', 'layanan' => 'tte'],
        ['tabel' => 'tte_registration_requests', 'layanan' => 'tte'],
        ['tabel' => 'tte_passphrase_reset_requests', 'layanan' => 'tte'],
        ['tabel' => 'tte_certificate_update_requests', 'layanan' => 'tte'],
        ['tabel' => 'vidcon_requests', 'layanan' => 'vidcon'],
        ['tabel' => 'vpn_registrations', 'layanan' => 'jaringan'],
        ['tabel' => 'vpn_resets', 'layanan' => 'jaringan'],
        ['tabel' => 'jip_pdns_requests', 'layanan' => 'jaringan'],
        ['tabel' => 'laporan_gangguan', 'layanan' => 'jaringan'],
        ['tabel' => 'starlink_requests', 'layanan' => 'jaringan'],
        ['tabel' => 'visitations', 'layanan' => 'pusat_data'],
        ['tabel' => 'vps_requests', 'layanan' => 'pusat_data'],
        ['tabel' => 'backup_requests', 'layanan' => 'pusat_data'],
        ['tabel' => 'cloud_storage_requests', 'layanan' => 'pusat_data'],
        ['tabel' => 'shortlink_requests', 'layanan' => 'shortlink'],
        ['tabel' => 'rekomendasi_aplikasi_forms', 'layanan' => 'rekomendasi', 'kecuali' => ['status' => 'draft']],
        ['tabel' => 'splp_provider_requests', 'layanan' => 'splp'],
        ['tabel' => 'splp_consumer_requests', 'layanan' => 'splp'],
        ['tabel' => 'splp_sandbox_requests', 'layanan' => 'splp'],
        ['tabel' => 'splp_change_requests', 'layanan' => 'splp'],
        ['tabel' => 'splp_deactivation_requests', 'layanan' => 'splp'],
        ['tabel' => 'dtsen_account_requests', 'layanan' => 'lainnya'],
        ['tabel' => 'dtsen_data_requests', 'layanan' => 'lainnya'],
        ['tabel' => 'requests', 'layanan' => 'lainnya'],
    ],

    // Pengelola portal di DKISP bukan responden (Batasan Masalah 2).
    'role_dikecualikan' => ['Admin'],

    // Pra-pemrosesan (Subbab 3.3.2)
    'pra_pemrosesan' => [
        // Respons yang selesai lebih cepat dari ini dianggap tidak wajar.
        'durasi_minimum_detik' => 300,
        // Responden dengan proporsi jawaban Kano questionable di atas ini dikeluarkan dari analisis Kano.
        'batas_questionable' => 0.20,
    ],
];
