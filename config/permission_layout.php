<?php

/*
|--------------------------------------------------------------------------
| Tata Letak Halaman Kelola Kewenangan
|--------------------------------------------------------------------------
|
| Urutan bagian & permission di /admin/role-permissions, disusun mengikuti
| urutan menu di sidebar (resources/views/layouts/authenticated.blade.php).
|
| Format: 'Judul Bagian' => ['Sub-menu' => ['nama.permission', ...], ...]
|
| Permission yang belum dicantumkan di sini tetap tampil di bagian
| "Belum Dipetakan" paling bawah — tambahkan ke sini saat membuat menu baru.
|
*/

return [

    'sections' => [
        'Dashboard' => [
            'Dashboard' => ['admin.dashboard', 'user.dashboard'],
        ],

        'Kelola Permohonan' => [
            'Manual - Unggah Surat' => ['admin.permohonan'],
            'Email' => [
                'admin.email',
                'admin.email.index',
                'admin.email.show',
                'admin.email.update-status',
                'admin.email-password-reset.index',
            ],
            'Pemendek Tautan' => ['admin.shortlink.index', 'admin.shortlink.show', 'admin.shortlink.manage'],
            'Subdomain' => [
                'admin.subdomain.index',
                'admin.subdomain.show',
                'admin.subdomain.update-status',
                'admin.subdomain.name-change.index',
                'admin.subdomain.name-change.show',
                'admin.subdomain.name-change.approve',
                'admin.subdomain.name-change.reject',
                'admin.subdomain.name-change.complete',
            ],
            'Rekomendasi Aplikasi' => [
                'admin.rekomendasi.verifikasi.index',
                'admin.rekomendasi.verifikasi.show',
                'admin.rekomendasi.verifikasi.approve',
                'admin.rekomendasi.verifikasi.reject',
                'admin.rekomendasi.verifikasi.revisi',
                'admin.rekomendasi.surat.generate',
                'admin.rekomendasi.surat.upload',
                'admin.rekomendasi.surat.send',
                'admin.rekomendasi.kementerian.update',
                'admin.fase-pengembangan.view',
                'admin.rekomendasi.monitoring.index',
                'admin.rekomendasi.fase.monitor',
                'admin.rekomendasi.evaluasi.review',
                'admin.rekomendasi.index',
                'admin.rekomendasi.show',
                'admin.rekomendasi.update-status',
            ],
            'Zoom/Youtube Live' => ['admin.vidcon.index'],
            'Survei Kepuasan' => ['Kelola Survei Kepuasan'],
            'Kuesioner Portal' => ['Kelola Kuesioner Portal'],
            'Internet' => ['Kelola Laporan Gangguan Internet', 'Kelola Starlink Jelajah'],
            'Jaringan Privat/VPN' => ['Kelola Pendaftaran VPN', 'Kelola Reset Akun VPN', 'Kelola Akses JIP PDNS'],
            'Pusat Data/Komputasi' => [
                'Kelola Kunjungan/Colocation',
                'Kelola VPS/VM',
                'Kelola Backup',
                'Kelola Cloud Storage',
            ],
            'Tanda Tangan Elektronik' => [
                'Kelola Bantuan TTE',
                'Kelola Registrasi TTE',
                'Kelola Reset Passphrase TTE',
                'admin.tte.passphrase-reset',
                'Kelola Pembaruan Sertifikat TTE',
            ],
            'Manajemen PSE' => ['Kelola Permohonan PSE'],
            'Integrasi (SPLP)' => ['Kelola SPLP'],
            'Berbagi Pakai Data (DTSEN)' => [
                'Kelola Akun DTSEN',
                'Kelola Permohonan DTSEN',
                'Verifikasi Substansi DTSEN',
                'Kelola Laporan DTSEN',
            ],
        ],

        'Layanan Digital' => [
            'Email' => [
                'user.email.index',
                'user.email.create',
                'user.email.show',
                'user.email-password-reset.index',
                'user.email-password-reset.create',
            ],
            'Rekomendasi Aplikasi' => [
                'user.rekomendasi.usulan.create',
                'user.rekomendasi.usulan.show',
                'user.rekomendasi.usulan.edit',
                'user.rekomendasi.dokumen.upload',
                'user.rekomendasi.dokumen.download',
                'user.fase-pengembangan',
                'user.rekomendasi.fase.update',
                'user.rekomendasi.evaluasi.create',
                'user.rekomendasi.index',
                'user.rekomendasi.create',
            ],
            'Subdomain' => [
                'user.subdomain.index',
                'user.subdomain.create',
                'user.subdomain.show',
                'user.subdomain.name-change.index',
                'user.subdomain.name-change.create',
                'user.subdomain.name-change.show',
            ],
            'Pemendek Tautan' => ['user.shortlink.index', 'user.shortlink.create', 'user.shortlink.show'],
            'PSE' => ['Akses Update Data PSE'],
            'Konsultasi SPBE AI' => ['Akses Konsultasi SPBE AI'],
            'Survei Kepuasan' => ['Akses Survei Kepuasan'],
            'Kuesioner Portal' => ['Akses Kuesioner Portal'],
            'Tanda Tangan Elektronik' => [
                'Akses Bantuan TTE',
                'Akses Registrasi TTE',
                'Akses Reset Passphrase TTE',
                'Akses Pembaruan Sertifikat TTE',
            ],
            'Zoom/Youtube Live' => ['Akses Video Conference'],
            'Internet' => ['Akses Lapor Gangguan Internet', 'Akses Starlink Jelajah'],
            'Jaringan Privat/VPN' => ['Akses Pendaftaran VPN', 'Akses Reset Akun VPN', 'Akses JIP PDNS'],
            'Pusat Data/Komputasi' => [
                'Akses Kunjungan/Colocation Data Center',
                'Akses VPS/VM',
                'Akses Backup',
                'Akses Cloud Storage',
            ],
            'Integrasi (SPLP)' => ['Akses SPLP'],
            'Berbagi Pakai Data (DTSEN)' => ['Akses DTSEN'],
            'Manual - Unggah Surat' => ['user.permohonan'],
        ],

        'Kelola Subdomain Terpadu' => [
            'Subdomain Terpadu' => ['Manajemen Subdomain Terpadu'],
        ],

        'Master Data' => [
            'Instansi' => ['admin.unit-kerja'],
            'Subdomain' => ['admin.web-monitor'],
            'Email' => ['Kelola Master Data Email'],
            'IP' => ['admin.web-monitor.check-ip-publik'],
            'Vidcon' => ['admin.vidcon.data'],
            'Aset TIK' => ['admin.google-aset-tik'],
            'Integrasi (SPLP)' => ['admin.splp.services', 'admin.splp.consumers', 'admin.splp.audit'],
            'Berbagi Pakai Data (DTSEN)' => [
                'admin.dtsen.dashboard',
                'admin.dtsen.variables',
                'admin.dtsen.releases',
                'admin.dtsen.wilayah',
            ],
        ],

        'Peminjaman & Vidcon' => [
            'Peminjaman' => ['op.tik.borrow.index', 'op.tik.borrow.create'],
            'Inventaris' => ['admin.tik.assets', 'admin.tik.borrow'],
            'Jadwal & Statistik' => ['admin.schedule', 'op.tik.schedule', 'admin.statistic'],
            'Pelaporan' => ['operator.vidcon'],
        ],

        'Pengguna & Akses' => [
            'Pengguna & Akses' => ['admin.simpeg', 'admin.running-text'],
        ],

        'Menu Lainnya' => [
            'Survei Digital' => ['admin.survei-digital'],
            'Knowledge Base AI' => ['Kelola Knowledge Base AI'],
            'Manajemen SLA' => ['Manajemen SLA'],
            'Profil' => ['user.profile'],
        ],
    ],

    /*
    | Halaman inti yang dikunci ke role Admin di routes/web.php. Tidak ditampilkan
    | di tabel centang, dan relasinya ke role tidak diubah saat menyimpan.
    */
    'admin_only' => [
        'admin.users',
        'admin.roles.index',
        'admin.roles.create',
        'admin.roles.edit',
        'admin.roles.destroy',
        'admin.role-permissions',
    ],

    // Label yang ditampilkan untuk halaman inti di atas (hanya informasi).
    'admin_only_pages' => [
        'Kelola Pengguna',
        'Kelola Peran (Role)',
        'Kelola Kewenangan',
        'Log Audit',
        'Manajemen API',
    ],
];
