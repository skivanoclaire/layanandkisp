<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiDokumenUsulan;
use App\Models\RekomendasiVerifikasi;
use App\Models\RekomendasiSurat;
use App\Models\RekomendasiFasePengembangan;
use App\Models\RekomendasiTimPengembangan;
use App\Models\RekomendasiMilestone;
use App\Models\RekomendasiEvaluasi;
use App\Models\RekomendasiHistoriAktivitas;
use Carbon\Carbon;

class RekomendasiV2TestSeeder extends Seeder
{
    /**
     * Seed test data for Rekomendasi V2 system.
     */
    public function run(): void
    {
        // Get a user to create proposals
        $user = User::where('role', 'User')->first();

        if (!$user) {
            $this->command->error('No User role found. Please create a user first.');
            return;
        }

        $this->command->info('Creating test data for Rekomendasi V2...');

        // Scenario 1: Draft proposal (baru diajukan, belum submit)
        $draft = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Ymd') . '-001',
            'judul_aplikasi' => 'Sistem Informasi Kepegawaian',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'draft',
            'fase_saat_ini' => 'usulan',
        ]);

        $this->logActivity($draft, $user, 'Draft Dibuat', 'Usulan aplikasi disimpan sebagai draft');

        // Scenario 2: Submitted proposal waiting for verification
        $submitted = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Ymd') . '-002',
            'judul_aplikasi' => 'Aplikasi E-Planning Daerah',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'diajukan',
            'fase_saat_ini' => 'verifikasi',
        ]);

        // Add documents for submitted proposal
        $this->createDocument($submitted, 'analisis_kebutuhan', 'Analisis Kebutuhan E-Planning.pdf', $user);
        $this->createDocument($submitted, 'perencanaan', 'Dokumen Perencanaan E-Planning.pdf', $user);
        $this->createDocument($submitted, 'manajemen_risiko', 'Manajemen Risiko E-Planning.pdf', $user);

        $this->logActivity($submitted, $user, 'Usulan Diajukan', 'Usulan diajukan untuk verifikasi');

        // Create verification record (pending)
        $verifikator = User::where('role', 'Admin')->first();
        RekomendasiVerifikasi::create([
            'rekomendasi_aplikasi_form_id' => $submitted->id,
            'verifikator_id' => $verifikator ? $verifikator->id : $user->id,
            'status' => 'menunggu',
        ]);

        // Scenario 3: Approved proposal with letter generated
        $approved = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Ymd') . '-003',
            'judul_aplikasi' => 'Sistem Monitoring Keuangan Daerah',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'disetujui',
            'fase_saat_ini' => 'penandatanganan',
        ]);

        $this->createDocument($approved, 'analisis_kebutuhan', 'Analisis Kebutuhan SIMKEU.pdf', $user);
        $this->createDocument($approved, 'perencanaan', 'Perencanaan SIMKEU.pdf', $user);
        $this->createDocument($approved, 'manajemen_risiko', 'Risk Management SIMKEU.pdf', $user);

        // Create verification record (approved)
        $verifikator = User::where('role', 'Admin')->first();
        RekomendasiVerifikasi::create([
            'rekomendasi_aplikasi_form_id' => $approved->id,
            'verifikator_id' => $verifikator ? $verifikator->id : $user->id,
            'status' => 'disetujui',
            'checklist_analisis_kebutuhan' => true,
            'checklist_perencanaan' => true,
            'checklist_manajemen_risiko' => true,
            'checklist_anggaran' => true,
            'checklist_timeline' => true,
            'catatan_verifikasi' => 'Semua dokumen lengkap dan sesuai standar.',
            'tanggal_verifikasi' => Carbon::now()->subDays(2),
        ]);

        // Create letter draft
        RekomendasiSurat::create([
            'rekomendasi_aplikasi_form_id' => $approved->id,
            'nomor_surat_draft' => '005/' . date('Y') . '/DISKOMINFO',
            'tanggal_surat' => Carbon::now()->subDay(),
            'kota' => 'Tanjung Selor',
            'referensi_hukum' => json_encode([
                'UU No. 14 Tahun 2008',
                'Perpres No. 95 Tahun 2018',
            ]),
            'tembusan' => json_encode([
                'Sekretaris Daerah',
                'Kepala BPKAD',
            ]),
            'penandatangan' => 'Gubernur Kalimantan Utara',
        ]);

        $this->logActivity($approved, $user, 'Verifikasi Disetujui', 'Usulan telah diverifikasi dan disetujui');
        if ($verifikator) {
            $this->logActivity($approved, $verifikator, 'Surat Draft Dibuat', 'Surat rekomendasi draft telah dibuat');
        }

        // Scenario 4: In development phase
        $development = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Ymd') . '-004',
            'judul_aplikasi' => 'Portal Layanan Publik Online',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'disetujui',
            'fase_saat_ini' => 'pengembangan',
            'repository_url' => 'https://github.com/kaltara/portal-layanan',
            'url_aplikasi_staging' => 'https://staging-portal.kaltara.go.id',
        ]);

        $this->createDocument($development, 'analisis_kebutuhan', 'Analisis Portal.pdf', $user);
        $this->createDocument($development, 'perencanaan', 'Perencanaan Portal.pdf', $user);
        $this->createDocument($development, 'manajemen_risiko', 'Risk Portal.pdf', $user);

        // Create development phases
        $faseRancang = RekomendasiFasePengembangan::create([
            'rekomendasi_aplikasi_form_id' => $development->id,
            'fase' => 'rancang_bangun',
            'status' => 'selesai',
            'tanggal_mulai' => Carbon::now()->subMonths(2),
            'tanggal_selesai' => Carbon::now()->subMonth(),
            'progress_persen' => 100,
        ]);

        $faseImplementasi = RekomendasiFasePengembangan::create([
            'rekomendasi_aplikasi_form_id' => $development->id,
            'fase' => 'implementasi',
            'status' => 'sedang_berjalan',
            'tanggal_mulai' => Carbon::now()->subMonth(),
            'progress_persen' => 65,
        ]);

        // Add team members
        RekomendasiTimPengembangan::create([
            'rekomendasi_aplikasi_form_id' => $development->id,
            'nama' => 'Budi Santoso',
            'peran' => 'Project Manager',
            'kontak' => 'budi@kaltara.go.id',
        ]);

        RekomendasiTimPengembangan::create([
            'rekomendasi_aplikasi_form_id' => $development->id,
            'nama' => 'Siti Rahma',
            'peran' => 'Lead Developer',
            'kontak' => 'siti@kaltara.go.id',
        ]);

        RekomendasiTimPengembangan::create([
            'rekomendasi_aplikasi_form_id' => $development->id,
            'nama' => 'Ahmad Wijaya',
            'peran' => 'UI/UX Designer',
            'kontak' => 'ahmad@kaltara.go.id',
        ]);

        // Add milestones
        RekomendasiMilestone::create([
            'rekomendasi_fase_pengembangan_id' => $faseImplementasi->id,
            'nama_milestone' => 'Authentication Module',
            'target_tanggal' => Carbon::now()->addDays(5),
            'status' => 'completed',
        ]);

        RekomendasiMilestone::create([
            'rekomendasi_fase_pengembangan_id' => $faseImplementasi->id,
            'nama_milestone' => 'Service Catalog Module',
            'target_tanggal' => Carbon::now()->addDays(15),
            'status' => 'in_progress',
        ]);

        RekomendasiMilestone::create([
            'rekomendasi_fase_pengembangan_id' => $faseImplementasi->id,
            'nama_milestone' => 'Payment Integration',
            'target_tanggal' => Carbon::now()->addDays(30),
            'status' => 'not_started',
        ]);

        $this->logActivity($development, $user, 'Fase Rancang Bangun Selesai', 'Fase rancang bangun telah diselesaikan');
        $this->logActivity($development, $user, 'Fase Implementasi Dimulai', 'Progress implementasi saat ini 65%');

        // Scenario 5: Completed with evaluation
        $completed = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Y', strtotime('-1 year')) . '0101-001',
            'judul_aplikasi' => 'Sistem Pelaporan Kinerja ASN',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'disetujui',
            'fase_saat_ini' => 'selesai',
            'repository_url' => 'https://github.com/kaltara/sipkasn',
            'url_aplikasi_production' => 'https://sipkasn.kaltara.go.id',
        ]);

        // Create evaluation
        RekomendasiEvaluasi::create([
            'rekomendasi_aplikasi_form_id' => $completed->id,
            'periode' => 'Semester 1 ' . date('Y'),
            'tanggal_evaluasi' => Carbon::now()->subDays(10),
            'rating_fungsionalitas' => 4,
            'rating_keamanan' => 5,
            'rating_performance' => 4,
            'rating_ux' => 4,
            'jumlah_pengguna' => 320,
            'frekuensi_akses' => 'Harian (rata-rata 150 akses/hari)',
            'fitur_populer' => 'Input laporan kinerja, Dashboard monitoring, Export PDF',
            'feedback_pengguna' => 'Aplikasi sangat membantu dalam pelaporan kinerja ASN. Interface user friendly.',
            'rekomendasi_tindak_lanjut' => 'perlu_pengembangan',
        ]);

        $this->logActivity($completed, $user, 'Evaluasi Dibuat', 'Evaluasi semester 1 telah dilakukan');

        // Scenario 6: Rejected proposal
        $rejected = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Ymd') . '-005',
            'judul_aplikasi' => 'Aplikasi Mobile Pengaduan',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'ditolak',
            'fase_saat_ini' => 'ditolak',
        ]);

        $this->createDocument($rejected, 'analisis_kebutuhan', 'Analisis Pengaduan.pdf', $user);

        RekomendasiVerifikasi::create([
            'rekomendasi_aplikasi_form_id' => $rejected->id,
            'verifikator_id' => $verifikator ? $verifikator->id : $user->id,
            'status' => 'ditolak',
            'checklist_analisis_kebutuhan' => false,
            'checklist_perencanaan' => false,
            'catatan_verifikasi' => 'Dokumen perencanaan dan manajemen risiko belum dilengkapi. Analisis kebutuhan kurang detail.',
            'tanggal_verifikasi' => Carbon::now()->subDays(3),
        ]);

        $this->logActivity($rejected, $user, 'Usulan Ditolak', 'Usulan ditolak karena dokumen tidak lengkap');

        // Scenario 7: Needs revision
        $revision = RekomendasiAplikasiForm::create([
            'user_id' => $user->id,
            'ticket_number' => 'REK-' . date('Ymd') . '-006',
            'judul_aplikasi' => 'Dashboard Monitoring Pembangunan',
            'pemilik_proses_bisnis_id' => $user->unit_kerja_id,
            'status' => 'diajukan',
            'fase_saat_ini' => 'usulan',
        ]);

        $this->createDocument($revision, 'analisis_kebutuhan', 'Analisis Dashboard.pdf', $user);
        $this->createDocument($revision, 'perencanaan', 'Perencanaan Dashboard.pdf', $user);

        RekomendasiVerifikasi::create([
            'rekomendasi_aplikasi_form_id' => $revision->id,
            'verifikator_id' => $verifikator ? $verifikator->id : $user->id,
            'status' => 'perlu_revisi',
            'checklist_analisis_kebutuhan' => true,
            'checklist_perencanaan' => true,
            'checklist_manajemen_risiko' => false,
            'checklist_anggaran' => false,
            'catatan_verifikasi' => 'Mohon dilengkapi dokumen manajemen risiko dan rincian anggaran yang lebih detail.',
            'tanggal_verifikasi' => Carbon::now()->subDay(),
        ]);

        $this->logActivity($revision, $user, 'Revisi Diminta', 'Diminta untuk melengkapi dokumen manajemen risiko dan anggaran');

        $this->command->info('✓ Created 7 test scenarios:');
        $this->command->info('  1. Draft - ' . $draft->ticket_number);
        $this->command->info('  2. Menunggu Verifikasi - ' . $submitted->ticket_number);
        $this->command->info('  3. Disetujui (Penandatanganan) - ' . $approved->ticket_number);
        $this->command->info('  4. Pengembangan - ' . $development->ticket_number);
        $this->command->info('  5. Selesai + Evaluasi - ' . $completed->ticket_number);
        $this->command->info('  6. Ditolak - ' . $rejected->ticket_number);
        $this->command->info('  7. Perlu Revisi - ' . $revision->ticket_number);
        $this->command->info('');
        $this->command->info('Test data seeded successfully!');
    }

    private function createDocument($proposal, $jenis, $filename, $user)
    {
        RekomendasiDokumenUsulan::create([
            'rekomendasi_aplikasi_form_id' => $proposal->id,
            'jenis_dokumen' => $jenis,
            'nama_file' => $filename,
            'file_path' => 'rekomendasi/dokumen/' . $filename,
            'file_size' => rand(100000, 500000),
            'mime_type' => 'application/pdf',
            'uploaded_by' => $user->id,
        ]);
    }

    private function logActivity($proposal, $user, $aktivitas, $deskripsi = null)
    {
        RekomendasiHistoriAktivitas::create([
            'rekomendasi_aplikasi_form_id' => $proposal->id,
            'user_id' => $user->id,
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Seeder',
        ]);
    }
}
