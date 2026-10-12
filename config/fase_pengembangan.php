<?php

/*
|--------------------------------------------------------------------------
| Tahap Fase Pengembangan Aplikasi SPBE
|--------------------------------------------------------------------------
|
| Dipakai bersama oleh halaman pemohon dan admin: judul tab, teks
| "Petunjuk Pengisian", dan berkas format dokumen (.docx) tiap tahap.
| Berkas format berada di public/{folder_format}/.
|
*/

return [

    'folder_format' => 'templates/fase-pengembangan',

    'tahap' => [

        'rancang_bangun' => [
            'judul' => 'Rancang Bangun',
            'format' => 'Format-Dokumen-Rancang-Bangun.docx',
            'petunjuk' => "Dokumentasi Rancang bangun sebagaimana paling kurang meliputi minimal memuat:\na. pemodelan rancang bangun;\nb. alur proses Aplikasi SPBE;\nc. pemetaan dan keterhubungan fungsi dan basis data dalam Aplikasi SPBE;\nd. pemetaan hak akses dan peran untuk pengguna aplikasi;\ne. rancangan antarmuka pengguna dan navigasi dari layar ke layar sesuai dengan tingkatan pengguna;\nf. rancangan kendali internal yang diperlukan dalam proses validasi, otorisasi, dan pencatatan aktivitas; dan\ng. rancangan integrasi antara aplikasi dengan aplikasi lain",
        ],

        'implementasi' => [
            'judul' => 'Implementasi',
            'format' => 'Format-Dokumen-Implementasi.docx',
            'petunjuk' => "Dokumentasi Implementasi sebagaimana dimaksud minimal memuat:\na. menerjemahkan bentuk rancang bangun menjadi kode atau bentuk bahasa pemrograman;\nb. melakukan pengkodean Aplikasi SPBE dan basis data sesuai dengan rancang bangun yang telah disetujui;\nc. melakukan kendali mutu melalui serangkaian uji kelaikan atas Aplikasi SPBE yang dibangun dan dikembangkan;\nd. melaksanakan instalasi dan konfigurasi Aplikasi SPBE;\ne. menyediakan antarmuka pemrograman aplikasi sebagai fasilitas interoperabilitas data;\nf. menyusun dokumentasi atas pembangunan dan pengembangan Aplikasi SPBE;\ng. melaksanakan alih pengetahuan dan teknologi melalui sosialisasi, bimbingan teknis, konsultasi, dan/atau pendampingan; dan\nh. menyusun dokumen serah terima pekerjaan dalam hal pembangunan dan pengembangan Aplikasi SPBE tidak dilakukan secara swakelola.",
        ],

        'uji_kelaikan' => [
            'judul' => 'Uji Kelaikan',
            'format' => 'Format-Dokumen-Uji-Kelaikan.docx',
            'petunjuk' => "Dokumentasi Pelaksanaan uji kelaikan sebagaimana dimaksud meliputi aspek:\na. uji fungsi, meliputi pengujian yang memastikan Aplikasi SPBE yang dibangun dan dikembangkan sudah memenuhi fungsi Aplikasi SPBE sesuai dengan dokumentasi terkait;\nb. uji integrasi, meliputi pengujian yang memastikan Aplikasi SPBE yang dibangun dan dikembangkan sudah memenuhi kebutuhan dan persyaratan integrasi dengan Aplikasi SPBE, data, serta komponen lain yang terkait;\nc. uji beban, meliputi pengujian yang memastikan Aplikasi SPBE dapat berfungsi sebagaimana mestinya menghadapi beban kerja yang dikenakan terhadapnya; dan\nd. uji keamanan, meliputi pengujian yang memastikan Aplikasi SPBE dapat berfungsi sebagaimana mestinya dengan menjaga keamanan data dan informasi yang terkait dengannya sesuai dengan ketentuan peraturan perundang-undangan.\n\nUji kelaikan sebagaimana dimaksud dilakukan dengan tahapan:\na. menyusun perencanaan uji kelaikan yang terdiri atas penentuan jadwal pelaksanaan uji kelaikan, penyiapan lingkungan dan sumber daya;\nb. mengidentifikasi uji kelaikan yang terdiri atas penentuan ruang lingkup dan kriteria uji kelaikan;\nc. menyusun rancangan uji kelaikan yang terdiri atas penyiapan alur proses uji kelaikan;\nd. menetapkan skenario uji kelaikan yang terdiri atas penentuan uji kelaikan dengan menggunakan berbagai skenario yang berbeda;\ne. melaksanakan uji kelaikan; dan\nf. melakukan evaluasi uji kelaikan.\n\nUji kelaikan sebagaimana dimaksud dilakukan dengan menggunakan metode dan pendekatan pengujian yang berlaku, meliputi:\na. pengujian kotak putih, yang merupakan uji kelaikan terhadap fungsi fungsionalitas sebuah Aplikasi SPBE dengan mengetahui struktur program; dan/atau\nb. pengujian kotak hitam, yang merupakan uji kelaikan terhadap fungsi fungsionalitas sebuah Aplikasi SPBE tanpa mengetahui struktur program.\n\nUji kelaikan dilakukan berjenjang dari pengujian komponen terkecil hingga pengujian secara keseluruhan dari Aplikasi SPBE.\n\nEvaluasi uji kelaikan terdiri atas pelaksanaan penilaian terhadap:\na. kesesuaian proses uji kelaikan yang sudah dilakukan dengan keseluruhan tahapan uji kelaikan;\nb. kesesuaian hasil uji kelaikan dengan analisis kebutuhan, rancang bangun, dan kriteria; dan\nc. mendokumentasikan keseluruhan tahapan uji kelaikan.",
        ],

        'pemeliharaan' => [
            'judul' => 'Pemeliharaan',
            'format' => 'Format-Dokumen-Pemeliharaan.docx',
            'petunjuk' => "Pemeliharaan Aplikasi SPBE dilaksanakan untuk memperpanjang umur kegunaan aplikasi sebagai aset dan mempertahankan keandalan layanan.\n\nPemeliharaan atas Aplikasi SPBE meliputi:\n\na. Pemeliharaan perfektif\n   Penambahan atau penyempurnaan aplikasi yang meliputi penambahan fungsi baru, perbaikan antarmuka, perbaikan kinerja, dan/atau perbaikan dokumentasi implementasi.\n\nb. Pemeliharaan adaptif\n   Adaptasi terhadap teknologi atau lingkungan operasional baru, dan penerapan protokol baru.\n\nc. Pemeliharaan korektif\n   Perbaikan terhadap permasalahan yang timbul setelah aplikasi digunakan.\n\nd. Pemeliharaan preventif\n   Pemeriksaan secara berkala aplikasi untuk mengantisipasi permasalahan, yang harus terdokumentasi.\n\nKetentuan Pemeliharaan:\n- Instansi Pusat dan Pemerintah Daerah harus melaksanakan pemeliharaan terhadap Aplikasi SPBE yang diselengarakannya.\n- Pemeliharaan dilaksanakan oleh unit kerja yang menyelenggarakan fungsi pengelolaan teknologi informasi dan komunikasi bersama unit kerja pemilik Proses Bisnis.\n- Pemeliharaan perlu dilakukan pemantauan oleh koordinator SPBE secara berkala dan/atau sewaktu-waktu sesuai kebutuhan.",
        ],

        'evaluasi' => [
            'judul' => 'Evaluasi',
            'format' => 'Format-Dokumen-Evaluasi.docx',
            'petunjuk' => "Evaluasi atas siklus pembangunan dan pengembangan Aplikasi SPBE harus dilaksanakan minimal 2 (dua) kali dalam 1 (satu) tahun dan/atau sewaktu-waktu sesuai kebutuhan.\n\nEvaluasi dilakukan oleh unit kerja yang menyelenggarakan fungsi pengelolaan teknologi informasi dan komunikasi pemerintah bersama unit kerja pemilik Proses Bisnis yang menyelenggarakan Aplikasi SPBE.\n\nPelaksanaan evaluasi minimal meliputi:\n\na. Menyusun kebijakan internal terkait evaluasi pembangunan dan pengembangan Aplikasi SPBE\n   Membuat dan menetapkan kebijakan evaluasi yang mengatur prosedur, kriteria, dan standar penilaian.\n\nb. Melakukan pengukuran penilaian indikator keberhasilan\n   Mengukur dan menilai indikator keberhasilan sesuai dengan siklus pembangunan dan pengembangan Aplikasi SPBE, mencakup fase:\n   - Analisis kebutuhan\n   - Rancang bangun\n   - Implementasi\n   - Uji kelaikan\n   - Pemeliharaan\n\nc. Menyusun laporan hasil evaluasi\n   Mendokumentasikan hasil evaluasi secara komprehensif meliputi temuan, analisis, dan rekomendasi perbaikan.\n\nd. Menyampaikan laporan hasil evaluasi\n   Menyampaikan laporan hasil evaluasi kepada pimpinan Instansi Pusat atau kepala daerah untuk mendapatkan arahan dan keputusan tindak lanjut.\n\ne. Melaksanakan tindak lanjut hasil evaluasi\n   Mengimplementasikan rekomendasi dan perbaikan yang diperlukan berdasarkan hasil evaluasi.\n\nPemantauan:\n- Evaluasi perlu dilakukan pemantauan oleh koordinator SPBE Instansi Pusat atau koordinator SPBE Pemerintah Daerah.\n- Pemantauan dilakukan secara berkala dan/atau sewaktu-waktu sesuai kebutuhan.",
        ],

    ],

];
