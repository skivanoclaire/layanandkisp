<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiRisikoItem;

class RekomendasiAplikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find user userdummy10@kaltaraprov.go.id
        $user = User::where('email', 'userdummy10@kaltaraprov.go.id')->first();

        if (!$user) {
            $this->command->error('User userdummy10@kaltaraprov.go.id tidak ditemukan!');
            return;
        }

        // Get sample Unit Kerja for relationships
        $unitKerja = UnitKerja::where('is_active', true)->first();
        $stakeholderInternal = UnitKerja::where('is_active', true)->take(2)->pluck('id')->toArray();

        // Create Rekomendasi Aplikasi Form
        $form = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'status' => 'diajukan',

            // Dokumen Analisis Kebutuhan
            'judul_aplikasi' => 'Sistem Informasi Manajemen Aset Daerah (SIMADA)',
            'dasar_hukum' => 'Peraturan Pemerintah Nomor 27 Tahun 2014 tentang Pengelolaan Barang Milik Negara/Daerah; Peraturan Menteri Dalam Negeri Nomor 19 Tahun 2016 tentang Pedoman Pengelolaan Barang Milik Daerah; Peraturan Daerah Provinsi Kalimantan Utara Nomor 8 Tahun 2020 tentang Pengelolaan Barang Milik Daerah.',
            'permasalahan_kebutuhan' => 'Saat ini pengelolaan aset daerah masih menggunakan sistem manual dan spreadsheet yang terpisah-pisah di setiap OPD. Hal ini menyebabkan kesulitan dalam monitoring kondisi aset secara real-time, pelaporan yang lambat dan tidak terintegrasi, serta rawan terjadinya kesalahan data dan kehilangan aset. Diperlukan sistem informasi terintegrasi untuk mengelola seluruh aset daerah mulai dari pengadaan, pemeliharaan, hingga penghapusan, sehingga meningkatkan akuntabilitas dan transparansi pengelolaan aset.',
            'pihak_terkait' => 'Dinas Komunikasi Informatika Statistik dan Persandian (Koordinator), Badan Pengelola Keuangan dan Aset Daerah, Inspektorat Daerah, Seluruh OPD di Lingkungan Pemprov Kaltara, Vendor Sistem Informasi.',

            // Pihak Terkait (New Structure)
            'pemilik_proses_bisnis_id' => $unitKerja ? $unitKerja->id : null,
            'stakeholder_internal' => json_encode($stakeholderInternal),
            'stakeholder_eksternal' => 'Badan Pemeriksa Keuangan (BPK), Kementerian Dalam Negeri (Kemendagri), Konsultan IT (PT Digital Nusantara), Masyarakat (untuk transparansi aset)',

            'maksud_tujuan' => 'Mengembangkan aplikasi Sistem Informasi Manajemen Aset Daerah (SIMADA) yang terintegrasi untuk: 1) Meningkatkan efisiensi dan efektivitas pengelolaan aset daerah; 2) Menyediakan data aset yang akurat, terkini, dan mudah diakses; 3) Meningkatkan akuntabilitas dan transparansi dalam pengelolaan barang milik daerah; 4) Memudahkan monitoring kondisi dan lokasi aset secara real-time; 5) Mempercepat proses pelaporan aset ke pemerintah pusat; 6) Mengurangi risiko kehilangan dan penyalahgunaan aset.',
            'ruang_lingkup' => 'Aplikasi SIMADA mencakup: 1) Modul Inventarisasi Aset (pendataan, kodefikasi, labeling); 2) Modul Pemeliharaan dan Perbaikan (jadwal maintenance, tracking perbaikan); 3) Modul Mutasi Aset (pemindahan antar lokasi/OPD); 4) Modul Penghapusan Aset (usulan, persetujuan, dokumentasi); 5) Modul Pelaporan (dashboard, laporan berkala, eksport data); 6) Modul User Management (role-based access untuk admin pusat, admin OPD, user viewer); 7) Integrasi dengan sistem SIPD dan e-Planning. Aplikasi tidak mencakup: Pengadaan aset (sudah ada di sistem e-procurement), Sistem akuntansi keuangan detail.',
            'analisis_biaya_manfaat' => 'Biaya: Development aplikasi (Rp 450 juta), Server dan infrastruktur (Rp 150 juta), Training dan sosialisasi (Rp 75 juta), Maintenance tahunan (Rp 100 juta/tahun). Total investasi tahun pertama: Rp 675 juta. Manfaat: Penghematan biaya operasional pencatatan manual (estimasi Rp 200 juta/tahun), Pengurangan kehilangan aset (estimasi Rp 500 juta/tahun), Peningkatan efisiensi waktu pelaporan hingga 70%, Peningkatan akurasi data aset hingga 95%, Kemudahan audit dan compliance. ROI diperkirakan tercapai dalam 2 tahun.',
            'analisis_risiko' => 'Risiko utama yang teridentifikasi: 1) Resistensi pengguna terhadap sistem baru (Medium Risk); 2) Kesalahan data migrasi dari sistem lama (High Risk); 3) Downtime server mempengaruhi operasional (Medium Risk); 4) Keamanan data dan akses tidak terotorisasi (High Risk); 5) Ketergantungan pada vendor untuk maintenance (Medium Risk). Mitigasi telah dirancang untuk setiap risiko.',
            'target_waktu' => '12 bulan (1 Januari 2025 - 31 Desember 2025)',
            'sasaran_pengguna' => 'Admin BPKAD (5 orang), Admin OPD/SKPD (60 orang dari 30 OPD), Viewer/Auditor (20 orang), Management/Pimpinan (10 orang). Total estimasi: 95 concurrent users, dengan puncak penggunaan saat periode pelaporan.',
            'lokasi_implementasi' => 'Seluruh OPD/SKPD di lingkungan Pemerintah Provinsi Kalimantan Utara (30 OPD), dengan server hosting di Data Center Pemprov Kaltara atau Cloud Server (AWS/Azure Jakarta Region).',

            // Dokumen Perencanaan
            'perencanaan_ruang_lingkup' => 'Sprint 1 (Bulan 1-3): Modul Inventarisasi dan Dashboard; Sprint 2 (Bulan 4-6): Modul Pemeliharaan dan Mutasi; Sprint 3 (Bulan 7-9): Modul Penghapusan dan Pelaporan; Sprint 4 (Bulan 10-11): Integrasi sistem dan User Management; Bulan 12: Testing, training, dan deployment.',
            'perencanaan_proses_bisnis' => 'Workflow utama: 1) Admin OPD input/update data aset di unit masing-masing; 2) Sistem otomatis memberi notifikasi jadwal maintenance; 3) Approval workflow untuk mutasi dan penghapusan aset (Admin OPD → Admin BPKAD → Kepala Dinas); 4) Sistem generate laporan berkala otomatis; 5) Dashboard real-time untuk monitoring; 6) Export data untuk pelaporan ke Kemendagri melalui SIPD.',
            'kerangka_kerja' => 'Agile Scrum dengan sprint 2 minggu, metodologi DevOps untuk CI/CD, standar keamanan ISO 27001',
            'pelaksana_pembangunan' => 'Hybrid: Desain dan Project Management oleh tim internal DISKOMINFOSTANDI (3 orang), Development oleh vendor terpilih (5-7 developers), Testing dan QA oleh tim gabungan internal-vendor (4 orang)',
            'peran_tanggung_jawab' => 'Project Manager: Kepala Bidang Aplikasi DISKOMINFOSTANDI; Product Owner: Kepala BPKAD; Scrum Master: Staff IT Senior; Development Team: Vendor PT Digital Nusantara (5 devs); QA Team: 2 internal + 2 vendor; Stakeholder: Kepala Dinas BPKAD, Sekda, Inspektur',
            'jadwal_pelaksanaan' => 'Januari 2025: Kick-off dan requirement gathering; Februari-April 2025: Development Sprint 1; Mei-Juli 2025: Development Sprint 2; Agustus-Oktober 2025: Development Sprint 3-4; November 2025: UAT dan training; Desember 2025: Go-live dan stabilisasi',
            'rencana_aksi' => 'Week 1-2: Finalisasi requirement dan desain UI/UX; Week 3-24: Development iteratif dengan sprint review setiap 2 minggu; Week 25-40: Testing paralel dengan development; Week 41-44: User Acceptance Testing dengan perwakilan OPD; Week 45-48: Training massal ke seluruh pengguna (3 angkatan); Week 49-50: Pilot implementation di 3 OPD; Week 51-52: Full deployment dan monitoring intensif',
            'keamanan_informasi' => 'Implementasi enkripsi data at-rest (AES-256) dan in-transit (TLS 1.3); Two-factor authentication untuk admin; Role-based access control (RBAC); Audit trail semua aktivitas user; Backup harian dengan retention 30 hari; Disaster recovery plan (RTO: 4 jam, RPO: 1 hari); Penetration testing sebelum go-live; Compliance dengan Peraturan Pemerintah tentang PSE',
            'sumber_daya' => 'Anggaran: Rp 675 juta (APBD 2025); SDM Internal: 3 PM/BA, 2 QA, 2 Network Admin; SDM Vendor: 5 Developers (2 Backend, 2 Frontend, 1 Mobile), 1 UI/UX Designer, 2 QA; Infrastruktur: Server (16 Core CPU, 64GB RAM, 2TB SSD), Bandwidth dedicated 100 Mbps, SSL Certificate, Domain dan hosting',
            'indikator_keberhasilan' => '1) 100% aset ter-inventarisasi dalam sistem dalam 6 bulan setelah go-live; 2) Waktu generate laporan berkurang 70% (dari 5 hari menjadi 1.5 hari); 3) User satisfaction score minimal 80%; 4) System uptime minimal 99.5%; 5) Akurasi data aset minimal 95%; 6) Audit finding terkait aset berkurang minimal 50%; 7) 90% pengguna aktif menggunakan sistem setiap bulan',
            'alih_pengetahuan' => 'Training administrator (5 hari): instalasi, konfigurasi, troubleshooting; Training user regular (2 hari): operasional harian, input data, generate report; Workshop advanced feature (1 hari): custom report, analytics; Dokumentasi lengkap (user manual, admin manual, technical documentation); Video tutorial untuk setiap modul; Helpdesk support 3 bulan pasca go-live; Knowledge base internal untuk FAQ',
            'pemantauan_pelaporan' => 'Daily monitoring: system uptime, error logs, user activities; Weekly report: usage statistics, pending issues, maintenance activities; Monthly report: KPI achievement, user feedback, recommendations; Quarterly review: strategic evaluation, feature enhancement proposal; Dashboard real-time untuk management; Incident management dengan SLA response 2 jam, resolution 24 jam untuk critical issue',
        ]);

        $this->command->info("✓ Created Rekomendasi Aplikasi Form: {$form->ticket_number}");

        // Create 1 Risk Item
        RekomendasiRisikoItem::create([
            'rekomendasi_aplikasi_form_id' => $form->id,
            'jenis_risiko' => 'Risiko SPBE Negatif',
            'kategori_risiko_spbe' => 'Keamanan SPBE',
            'area_dampak_risiko_spbe' => 'Operasional dan Aset TIK',
            'uraian_risiko' => 'Sistem mengalami unauthorized access atau serangan cyber yang menyebabkan kebocoran data aset daerah atau manipulasi data inventaris, yang dapat mempengaruhi integritas data dan kepercayaan pemangku kepentingan',
            'penyebab' => 'Lemahnya implementasi security protocol, penggunaan password default, tidak ada two-factor authentication, kurangnya monitoring aktivitas mencurigakan, vulnerability pada aplikasi yang belum di-patch',
            'dampak' => 'Kehilangan atau manipulasi data aset (nilai kerugian bisa mencapai ratusan juta), penurunan kepercayaan publik, sanksi dari BPK/BPKP, terhambatnya operasional pengelolaan aset, potensi tuntutan hukum',
            'level_kemungkinan' => '3',
            'level_dampak' => '5',
            'besaran_risiko' => '15 (Risiko Tinggi)',
            'perlu_penanganan' => true,
            'opsi_penanganan' => 'Mitigasi - Implementasi berlapis: preventive controls (strong authentication, encryption), detective controls (monitoring, logging), dan corrective controls (incident response plan)',
            'rencana_aksi' => '1) Implementasi two-factor authentication untuk semua admin (Bulan ke-1); 2) Enkripsi database dengan AES-256 (Bulan ke-2); 3) Deploy Web Application Firewall (WAF) dan IDS/IPS (Bulan ke-3); 4) Regular security audit dan penetration testing (setiap 6 bulan); 5) Training security awareness untuk semua user (Bulan ke-4); 6) Implementasi audit trail dan SIEM untuk monitoring real-time (Bulan ke-5); 7) Regular patching dan vulnerability scanning (monthly)',
            'jadwal_implementasi' => 'Tahap 1 (Bulan 1-3): Implementasi authentication & encryption; Tahap 2 (Bulan 4-6): Monitoring dan training; Continuous: Patching dan audit berkala',
            'penanggung_jawab' => 'Security Team Lead (Internal): Budi Santoso, S.Kom; Infrastructure Admin (Internal): Andi Wijaya; Security Consultant (Vendor): PT CyberSec Indonesia; Koordinator: Kepala Seksi Keamanan Informasi',
            'risiko_residual' => true,
        ]);

        $this->command->info("✓ Created 1 Risk Item for the form");
        $this->command->info("✓ Seeder completed successfully!");
    }
}
