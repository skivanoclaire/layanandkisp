<?php

namespace Database\Seeders;

use App\Models\DtsenAccountReactivation;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenAccessToken;
use App\Models\DtsenComplaint;
use App\Models\DtsenDataRequest;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenExtensionRequest;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenRelease;
use App\Models\DtsenRequestLog;
use App\Models\DtsenUtilizationReport;
use App\Models\DtsenVariable;
use App\Models\DtsenWilayah;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data uji coba modul Berbagi Pakai Data DTSEN.
 *
 * Menyiapkan tiga aktor (OPD pemohon, DKISP, Bapperida) beserta berkas di SELURUH
 * status siklus hidup, sehingga tiap layar dapat dicoba tanpa harus menjalankan
 * alurnya satu per satu dari awal.
 *
 * Jalankan:  php artisan db:seed --class=DtsenTestSeeder
 *
 * Seeder ini idempoten: data uji coba sebelumnya (milik ketiga akun uji di bawah)
 * dihapus lebih dulu, lalu dibuat ulang — jadi hasilnya selalu sama. Data DTSEN
 * milik pengguna lain tidak disentuh.
 */
class DtsenTestSeeder extends Seeder
{
    /** Penanda akun uji coba; dipakai juga untuk membersihkan data lama. */
    private const EMAIL_PEMOHON = 'dtsen.opd@kaltaraprov.go.id';
    private const EMAIL_DKISP = 'dtsen.dkisp@kaltaraprov.go.id';
    private const EMAIL_BAPPERIDA = 'dtsen.bapperida@kaltaraprov.go.id';

    private User $pemohon;
    private User $dkisp;
    private User $bapperida;
    private UnitKerja $opd;
    private DtsenAccountRequest $akunAktif;

    /** @var array<int, DtsenVariable> */
    private array $variabel = [];
    private ?DtsenWilayah $wilayah = null;
    private ?DtsenRelease $rilis = null;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DtsenTestSeeder berisi data fiktif dan tidak boleh dijalankan di produksi.');

            return;
        }

        if (! $this->prasyaratTerpenuhi()) {
            return;
        }

        $this->siapkanAktor();
        $this->bersihkanDataLama();
        $this->siapkanLampiranContoh();

        $this->seedAkunLayanan();
        $this->seedPermohonan();
        $this->seedPelaporan();
        $this->seedPengaduan();

        $this->tampilkanRingkasan();
    }

    // ------------------------------------------------------------ Prasyarat

    private function prasyaratTerpenuhi(): bool
    {
        $this->rilis = DtsenRelease::current();
        $this->wilayah = DtsenWilayah::where('tingkat', 'kabupaten_kota')->orderBy('nama')->first();

        foreach ([2, 3, 4] as $level) {
            $this->variabel[$level] = DtsenVariable::active()->where('level_minimal', $level)->first();
        }

        $kurang = [];
        if (! $this->wilayah) {
            $kurang[] = 'master wilayah';
        }
        foreach ($this->variabel as $level => $variabel) {
            if (! $variabel) {
                $kurang[] = "variabel level {$level}";
            }
        }

        if ($kurang) {
            $this->command?->error('Master data DTSEN belum lengkap: ' . implode(', ', $kurang) . '.');
            $this->command?->warn('Jalankan `php artisan migrate` lebih dulu — master data dibuat oleh migration 2026_09_10_000020.');

            return false;
        }

        return true;
    }

    // ------------------------------------------------------------ Aktor

    private function siapkanAktor(): void
    {
        // Password diambil dari env DTSEN_TEST_PASSWORD, kalau tidak ada dibuat acak.
        $password = env('DTSEN_TEST_PASSWORD') ?: Str::password(16);
        $this->passwordUji = $password;

        $this->opd = $this->unitKerja('DINAS SOSIAL', 'DINAS SOSIAL');
        $unitDkisp = $this->unitKerja('KOMUNIKASI, INFORMATIKA', 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN');
        $unitBapperida = $this->unitKerja('PERENCANAAN PEMBANGUNAN', 'BADAN PERENCANAAN PEMBANGUNAN, RISET DAN INOVASI DAERAH');

        $this->pemohon = $this->buatUser(self::EMAIL_PEMOHON, [
            'name' => 'Rahmawati Dewi',
            'nip' => '198705122010012003',
            'phone' => '081250010001',
            'unit_kerja_id' => $this->opd->id,
        ], $password, 'Kepala Seksi Perlindungan dan Jaminan Sosial');

        $this->dkisp = $this->buatUser(self::EMAIL_DKISP, [
            'name' => 'Andi Prasetyo',
            'nip' => '198903142012011005',
            'phone' => '081250010002',
            'unit_kerja_id' => $unitDkisp->id,
        ], $password, 'Analis Statistik — Bidang Statistik DKISP');

        $this->bapperida = $this->buatUser(self::EMAIL_BAPPERIDA, [
            'name' => 'Siti Nurhaliza',
            'nip' => '198512202011012004',
            'phone' => '081250010003',
            'unit_kerja_id' => $unitBapperida->id,
        ], $password, 'Koordinator Forum Satu Data Daerah');

        $this->beriPeran($this->pemohon, 'Uji DTSEN - Pemohon OPD', ['Akses DTSEN']);
        $this->beriPeran($this->dkisp, 'Uji DTSEN - Prosesor DKISP', [
            'Kelola Akun DTSEN', 'Kelola Permohonan DTSEN', 'Kelola Laporan DTSEN',
            'admin.dtsen.dashboard', 'admin.dtsen.variables', 'admin.dtsen.releases', 'admin.dtsen.wilayah',
        ]);
        $this->beriPeran($this->bapperida, 'Uji DTSEN - Forum Satu Data', [
            'Verifikasi Substansi DTSEN', 'Kelola Laporan DTSEN',
            'admin.dtsen.dashboard', 'admin.dtsen.variables', 'admin.dtsen.releases',
        ]);
    }

    private string $passwordUji = '';

    /**
     * Cari perangkat daerah berdasarkan potongan nama; buat bila belum ada supaya
     * seeder tetap jalan di database yang master OPD-nya masih kosong.
     */
    private function unitKerja(string $cari, string $namaDefault): UnitKerja
    {
        $unit = UnitKerja::where('nama', 'like', '%' . $cari . '%')->first();

        if ($unit) {
            return $unit;
        }

        $this->command?->warn("Perangkat daerah \"{$cari}\" belum ada — dibuat otomatis sebagai data uji.");

        return UnitKerja::create([
            'nama' => $namaDefault,
            'tipe' => UnitKerja::TIPE_INDUK,
            'is_active' => true,
        ]);
    }

    private function buatUser(string $email, array $atribut, string $password, string $jabatan): User
    {
        $user = User::withoutEvents(fn () => User::updateOrCreate(
            ['email' => $email],
            array_merge($atribut, [
                'password' => Hash::make($password),
                'role' => 'user',
                'is_verified' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
            ])
        ));

        // Jabatan dipakai form DTSEN untuk mengunci kolom "Jabatan" dari profil.
        Jabatan::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_jabatan' => $jabatan,
                'unit_kerja_id' => $atribut['unit_kerja_id'] ?? null,
            ]
        );

        return $user->refresh();
    }

    /** Buat role khusus uji coba lalu tautkan permission dan pengguna. */
    private function beriPeran(User $user, string $namaRole, array $permissions): void
    {
        $roleId = DB::table('roles')->where('name', $namaRole)->value('id')
            ?: DB::table('roles')->insertGetId([
                'name' => $namaRole,
                'display_name' => $namaRole,
                'description' => 'Role bawaan DtsenTestSeeder untuk uji coba modul DTSEN.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('role_user')->updateOrInsert(
            ['role_id' => $roleId, 'user_id' => $user->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $ids = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');

        if ($ids->count() !== count($permissions)) {
            $this->command?->warn("Sebagian permission untuk role {$namaRole} belum ada — jalankan migrate DTSEN.");
        }

        foreach ($ids as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    // ------------------------------------------------------------ Pembersihan

    /**
     * Hapus data uji coba terdahulu agar hasil seeding selalu sama.
     * Cakupannya dibatasi pada berkas milik ketiga akun uji di atas.
     */
    private function bersihkanDataLama(): void
    {
        $userIds = [$this->pemohon->id, $this->dkisp->id, $this->bapperida->id];

        $permohonanIds = DtsenDataRequest::whereIn('user_id', $userIds)->pluck('id');
        if ($permohonanIds->isNotEmpty()) {
            DtsenRequestLog::where('request_type', DtsenRequestLog::TYPE_PERMOHONAN)
                ->whereIn('request_id', $permohonanIds)->delete();
        }

        $akunIds = DtsenAccountRequest::whereIn('user_id', $userIds)->pluck('id');
        if ($akunIds->isNotEmpty()) {
            DtsenRequestLog::where('request_type', DtsenRequestLog::TYPE_AKUN)
                ->whereIn('request_id', $akunIds)->delete();
        }

        // Insiden & pengaduan tidak punya foreign key ke tabel log, jadi lognya
        // harus dihapus manual agar tidak menumpuk tiap kali seeder dijalankan.
        $insidenIds = DtsenIncidentReport::whereIn('user_id', $userIds)->pluck('id');
        if ($insidenIds->isNotEmpty()) {
            DtsenRequestLog::where('request_type', DtsenRequestLog::TYPE_INSIDEN)
                ->whereIn('request_id', $insidenIds)->delete();
        }

        $pengaduanIds = DtsenComplaint::whereIn('user_id', $userIds)->pluck('id');
        if ($pengaduanIds->isNotEmpty()) {
            DtsenRequestLog::where('request_type', DtsenRequestLog::TYPE_PENGADUAN)
                ->whereIn('request_id', $pengaduanIds)->delete();
        }

        // Sisanya ikut terhapus lewat cascade foreign key (variabel, dokumen,
        // token, log unduhan, perpanjangan, dan laporan tahap 5).
        DtsenDataRequest::whereIn('user_id', $userIds)->delete();
        DtsenAccountRequest::whereIn('user_id', $userIds)->delete();
        DtsenIncidentReport::whereIn('user_id', $userIds)->delete();
        DtsenComplaint::whereIn('user_id', $userIds)->delete();
    }

    // ------------------------------------------------------------ Lampiran

    /** @var array<string, string> jenis berkas => path di disk public */
    private array $lampiran = [];

    /**
     * Buat PDF contoh sungguhan agar tautan unduh pada UI benar-benar terbuka.
     */
    private function siapkanLampiranContoh(): void
    {
        $berkas = [
            'surat' => 'Surat Permohonan Data DTSEN',
            'kak' => 'Kerangka Acuan Kerja (KAK)',
            'pendukung' => 'Dokumen Perencanaan Program',
            'bast' => 'Berita Acara Serah Terima (BAST)',
            'klarifikasi' => 'Berita Acara Klarifikasi',
            'pemusnahan' => 'Berita Acara Pemusnahan Data',
            'pemanfaatan' => 'Laporan Pemanfaatan Data',
        ];

        foreach ($berkas as $kunci => $judul) {
            $path = "dtsen-docs/contoh/CONTOH_{$kunci}.pdf";

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->pdfContoh($judul));
            }

            $this->lampiran[$kunci] = $path;
        }

        // Berkas data yang "dikirim" DKISP lewat token unduh.
        $dataPath = 'dtsen-data/contoh/CONTOH_data_dtsen.csv';
        if (! Storage::disk('public')->exists($dataPath)) {
            Storage::disk('public')->put($dataPath, $this->csvContoh());
        }
        $this->lampiran['data'] = $dataPath;
    }

    private function pdfContoh(string $judul): string
    {
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(
            '<div style="font-family: sans-serif; padding: 40px;">'
            . '<h2 style="margin-bottom:4px;">' . e($judul) . '</h2>'
            . '<p style="color:#666;">Dokumen contoh untuk uji coba modul DTSEN.</p>'
            . '<p style="color:#b91c1c;"><strong>Bukan dokumen resmi.</strong> '
            . 'Dibuat otomatis oleh DtsenTestSeeder pada ' . now()->format('d/m/Y H:i') . '.</p>'
            . '</div>'
        );
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    private function csvContoh(): string
    {
        $baris = ['id_keluarga;desil;kecamatan;jumlah_anggota'];
        for ($i = 1; $i <= 25; $i++) {
            $baris[] = sprintf('KLG-%04d;%d;%s;%d', $i, ($i % 10) + 1, $this->wilayah->nama, ($i % 5) + 2);
        }

        return implode("\n", $baris) . "\n";
    }

    // ------------------------------------------------------------ Tahap 1

    private function seedAkunLayanan(): void
    {
        // Akun utama: disetujui & aktif — dipakai seluruh permohonan di bawah.
        $this->akunAktif = $this->buatAkun([
            'nomor_surat' => '005/DTSEN/DINSOS/2026',
            'status' => DtsenAccountRequest::STATUS_DISETUJUI,
            'submitted_at' => now()->subDays(40),
            'verified_at' => now()->subDays(39),
            'is_active' => true,
            'activated_at' => now()->subDays(39),
            'last_used_at' => now()->subDays(2),
        ], ['Rekan Analis Data', 'Operator Pendataan']);

        // Menunggu verifikasi Admin DKISP.
        $this->buatAkun([
            'nomor_surat' => '011/DTSEN/DINKES/2026',
            'status' => DtsenAccountRequest::STATUS_DIAJUKAN,
            'submitted_at' => now()->subHours(6),
        ]);

        // Dikembalikan untuk perbaikan.
        $this->buatAkun([
            'nomor_surat' => '012/DTSEN/DISDIK/2026',
            'status' => DtsenAccountRequest::STATUS_DIKEMBALIKAN,
            'submitted_at' => now()->subDays(3),
            'verified_at' => now()->subDays(2),
            'catatan_perbaikan' => 'Surat belum ditandatangani Kepala Perangkat Daerah dan NIP salah satu personel tidak valid.',
        ]);

        // Draft milik pemohon.
        $this->buatAkun([
            'nomor_surat' => '013/DTSEN/DPMD/2026',
            'status' => DtsenAccountRequest::STATUS_DRAFT,
            'submitted_at' => null,
        ]);

        // Ditolak — pengaju bukan petugas yang ditunjuk Kepala Perangkat Daerah.
        $this->buatAkun([
            'nomor_surat' => '014/DTSEN/UPT/2026',
            'status' => DtsenAccountRequest::STATUS_DITOLAK,
            'submitted_at' => now()->subDays(20),
            'verified_at' => now()->subDays(19),
            'check_ttd_kepala_opd' => false,
            'catatan_perbaikan' => 'Surat permohonan ditandatangani pejabat yang tidak berwenang '
                . 'dan personel yang didaftarkan bukan petugas yang ditunjuk Kepala Perangkat Daerah.',
        ]);

        // Nonaktif karena menganggur, dengan permintaan aktivasi ulang menunggu keputusan.
        $akunNonaktif = $this->buatAkun([
            'nomor_surat' => '008/DTSEN/DPPPA/2026',
            'status' => DtsenAccountRequest::STATUS_DISETUJUI,
            'submitted_at' => now()->subDays(90),
            'verified_at' => now()->subDays(89),
            'is_active' => false,
            'activated_at' => now()->subDays(89),
            'last_used_at' => now()->subDays(45),
            'deactivated_at' => now()->subDays(15),
        ]);

        DtsenAccountReactivation::create([
            'dtsen_account_request_id' => $akunNonaktif->id,
            'user_id' => $this->pemohon->id,
            'alasan' => 'Akan mengajukan permintaan data untuk program bantuan sosial tahun anggaran berjalan.',
            'status' => DtsenAccountReactivation::STATUS_DIAJUKAN,
        ]);

        $this->catat(DtsenRequestLog::TYPE_AKUN, $akunNonaktif->id, 'akun_dinonaktifkan_otomatis',
            'Akun dinonaktifkan otomatis karena tidak digunakan selama 30 hari kalender.', now()->subDays(15));
        $this->catat(DtsenRequestLog::TYPE_AKUN, $akunNonaktif->id, 'reaktivasi_diajukan',
            'Permintaan aktivasi ulang akun diajukan', now()->subHours(3));
    }

    /** @param  array<string>  $personelTambahan */
    private function buatAkun(array $atribut, array $personelTambahan = []): DtsenAccountRequest
    {
        $akun = DtsenAccountRequest::create(array_merge([
            'user_id' => $this->pemohon->id,
            'unit_kerja_id' => $this->opd->id,
            'sifat_surat' => 'biasa',
            'jumlah_lampiran' => '1 berkas',
            'tanggal_surat' => now()->subDays(41)->toDateString(),
            'surat_path' => $this->lampiran['surat'],
            'narahubung_nama' => $this->pemohon->name,
            'narahubung_kontak' => $this->pemohon->phone,
            'narahubung_email' => $this->pemohon->email,
            'check_surat_lengkap' => true,
            'check_ttd_kepala_opd' => true,
            'check_data_personel' => true,
            'consent_true' => true,
        ], $atribut));

        if (in_array($akun->status, [DtsenAccountRequest::STATUS_DISETUJUI, DtsenAccountRequest::STATUS_DIKEMBALIKAN], true)) {
            $akun->forceFill(['verified_by' => $this->dkisp->id])->save();
        }

        // Personel pertama selalu pengaju sendiri (sesuai aturan form).
        $akun->members()->create([
            'user_id' => $this->pemohon->id,
            'nama' => $this->pemohon->name,
            'nip' => $this->pemohon->nip,
            'jabatan' => $this->pemohon->jabatan?->nama_jabatan,
            'unit_kerja' => $this->opd->nama,
            'no_hp' => $this->pemohon->phone,
            'email' => $this->pemohon->email,
        ]);

        foreach ($personelTambahan as $i => $nama) {
            $akun->members()->create([
                'nama' => $nama,
                'nip' => '19900' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) . '2015011000',
                'jabatan' => 'Pelaksana',
                'unit_kerja' => $this->opd->nama,
                'no_hp' => '08125002000' . ($i + 1),
                // Sengaja satu memakai domain non-pemerintah untuk menguji peringatan.
                'email' => $i === 0
                    ? 'analis.data@kaltaraprov.go.id'
                    : 'operator.pendataan@gmail.com',
            ]);
        }

        $this->catat(DtsenRequestLog::TYPE_AKUN, $akun->id, 'created_submitted',
            'Permohonan akun layanan DTSEN diajukan', $akun->submitted_at ?? $akun->created_at);

        if ($akun->verified_at) {
            $this->catat(DtsenRequestLog::TYPE_AKUN, $akun->id, "status:diajukan->{$akun->status}",
                $akun->catatan_perbaikan, $akun->verified_at);
        }

        return $akun;
    }

    // ------------------------------------------------------------ Tahap 2-4

    private function seedPermohonan(): void
    {
        // 1. Draft milik pemohon.
        $this->buatPermohonan('draft', 'Pemutakhiran Basis Data Terpadu Kesejahteraan Sosial', 3);

        // 2. Baru diajukan — menunggu verifikasi administrasi DKISP.
        $this->buatPermohonan(DtsenDataRequest::STATUS_DIAJUKAN, 'Verifikasi Sasaran Bantuan Pangan Non-Tunai', 3);

        // 3. Sedang diverifikasi administrasi.
        $this->buatPermohonan(DtsenDataRequest::STATUS_VERIF_ADMIN, 'Pendataan Penerima Bantuan Iuran JKN', 3);

        // 4. Dikembalikan untuk perbaikan — pemohon bisa memperbaiki.
        $this->buatPermohonan(DtsenDataRequest::STATUS_PERLU_PERBAIKAN, 'Penetapan Sasaran Rehabilitasi Rumah Tidak Layak Huni', 4);

        // 5. Menunggu verifikasi substansi Bapperida.
        $this->buatPermohonan(DtsenDataRequest::STATUS_VERIF_SUBSTANSI, 'Pemadanan Data Penyandang Disabilitas', 3);

        // 6. Diundang klarifikasi, lengkap dengan berita acaranya.
        $this->buatPermohonan(DtsenDataRequest::STATUS_KLARIFIKASI, 'Analisis Kemiskinan Ekstrem Tingkat Kecamatan', 4);

        // 7. Ditolak (final).
        $this->buatPermohonan(DtsenDataRequest::STATUS_DITOLAK, 'Pemetaan Calon Penerima Hibah Kelompok Masyarakat', 4);

        // 8. Diterima — siap diproses DKISP.
        $this->buatPermohonan(DtsenDataRequest::STATUS_DITERIMA, 'Validasi Data Lansia Terlantar', 3);

        // 9. Sedang diproses & QA.
        $this->buatPermohonan(DtsenDataRequest::STATUS_PEMROSESAN_QA, 'Penyusunan Profil Kemiskinan Daerah', 3);

        // 10. Menunggu BAST (level 4/BNBA).
        $this->buatPermohonan(DtsenDataRequest::STATUS_MENUNGGU_BAST, 'Penyaluran Bantuan Sosial Daerah Tahap I', 4);

        // 11. Data tersedia — token aktif + riwayat unduhan + perpanjangan menunggu.
        $tersedia = $this->buatPermohonan(DtsenDataRequest::STATUS_DATA_TERSEDIA, 'Penetapan Keluarga Penerima Manfaat PKH Daerah', 4);

        // 12. Kedaluwarsa — token habis masa aktif.
        $this->buatPermohonan(DtsenDataRequest::STATUS_KEDALUWARSA, 'Evaluasi Ketepatan Sasaran Bantuan Tahun Lalu', 3);

        // 13. Selesai — jadi rujukan laporan pemanfaatan & pemusnahan.
        $this->selesai = $this->buatPermohonan(DtsenDataRequest::STATUS_SELESAI, 'Basis Data Sasaran Program Keluarga Harapan 2025', 4);

        // 14. Permintaan ulang yang merujuk permohonan selesai (Form 4.5).
        $this->buatPermohonan(DtsenDataRequest::STATUS_DIAJUKAN, 'Pembaruan Basis Data Sasaran PKH 2026', 4, [
            'parent_request_id' => $this->selesai->id,
            'ada_perubahan_signifikan' => false,
        ]);

        $this->dataTersedia = $tersedia;
    }

    private DtsenDataRequest $selesai;
    private DtsenDataRequest $dataTersedia;

    /**
     * Bangun satu permohonan lengkap pada status tertentu, beserta stempel waktu,
     * hasil verifikasi, token, dan jejak audit yang konsisten dengan statusnya.
     */
    private function buatPermohonan(string $status, string $program, int $level, array $tambahan = []): DtsenDataRequest
    {
        // Mundurkan waktu mulai supaya tiap tahap punya durasi yang masuk akal.
        $mulai = now()->subDays(match ($status) {
            'draft' => 1,
            DtsenDataRequest::STATUS_DIAJUKAN => 1,
            DtsenDataRequest::STATUS_VERIF_ADMIN => 2,
            DtsenDataRequest::STATUS_PERLU_PERBAIKAN => 4,
            DtsenDataRequest::STATUS_VERIF_SUBSTANSI => 5,
            DtsenDataRequest::STATUS_KLARIFIKASI => 7,
            DtsenDataRequest::STATUS_DITOLAK => 9,
            DtsenDataRequest::STATUS_DITERIMA => 6,
            DtsenDataRequest::STATUS_PEMROSESAN_QA => 8,
            DtsenDataRequest::STATUS_MENUNGGU_BAST => 10,
            DtsenDataRequest::STATUS_DATA_TERSEDIA => 14,
            DtsenDataRequest::STATUS_KEDALUWARSA => 60,
            DtsenDataRequest::STATUS_SELESAI => 30,
            default => 5,
        });

        $isDraft = $status === 'draft';

        $item = DtsenDataRequest::create(array_merge([
            'user_id' => $this->pemohon->id,
            'unit_kerja_id' => $this->opd->id,
            'dtsen_account_request_id' => $this->akunAktif->id,
            'dtsen_release_id' => $this->rilis?->id,
            'pemohon_nama' => $this->pemohon->name,
            'pemohon_nip' => $this->pemohon->nip,
            'pemohon_jabatan' => $this->pemohon->jabatan?->nama_jabatan,
            'pemohon_telepon' => $this->pemohon->phone,
            'nomor_surat' => '470/' . random_int(100, 999) . '/DINSOS/2026',
            'sifat_surat' => 'biasa',
            'jumlah_lampiran' => $level >= 4 ? '3 berkas' : '2 berkas',
            'tanggal_surat' => $mulai->copy()->subDay()->toDateString(),
            'nama_program' => $program,
            'jenis_permintaan' => $level >= 4 ? 'bnba' : 'pemadanan',
            'cakupan_wilayah_ids' => [$this->wilayah->id],
            'cakupan_wilayah_catatan' => 'Difokuskan pada kecamatan prioritas program.',
            'tujuan_penggunaan' => "Menetapkan dan memverifikasi sasaran penerima manfaat pada kegiatan {$program}.",
            'surat_permohonan_path' => $isDraft ? null : $this->lampiran['surat'],
            'level_akses' => $level,
            'metode_akses' => 'excel_terenkripsi',
            'metode_enkripsi' => 'AES-256 pada berkas, kanal HTTPS internal.',
            'kapasitas_sdm' => 'Dua pranata komputer dan satu analis data.',
            'status' => $status,
            'submitted_at' => $isDraft ? null : $mulai,
            'consent_true' => true,
        ], $this->isiKak($level, $mulai), $tambahan));

        $this->lampirkanVariabel($item, $level);

        if (! $isDraft) {
            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'created_submitted',
                "Permintaan data diajukan pada {$item->level_label}", $mulai);
        }

        if ($level >= 4 && ! $isDraft) {
            $item->documents()->create([
                'jenis' => 'pendukung',
                'nama_dokumen' => 'Dokumen Perencanaan Program.pdf',
                'keterangan' => 'Dokumen perencanaan kegiatan tahun anggaran berjalan.',
                'file_path' => $this->lampiran['pendukung'],
                'uploaded_by' => $this->pemohon->id,
            ]);
        }

        $this->terapkanTahapan($item, $status, $mulai, $level);

        return $item->refresh();
    }

    /** Isian KAK hanya relevan untuk level 3 ke atas. */
    private function isiKak(int $level, Carbon $mulai): array
    {
        if ($level < 3) {
            return [];
        }

        return [
            'kak_latar_belakang' => 'Penetapan sasaran program perlindungan sosial selama ini masih memakai basis data '
                . 'yang belum termutakhirkan, sehingga berpotensi salah sasaran (exclusion & inclusion error).',
            'kak_dasar_hukum' => [
                'Peraturan Menteri PPN/Bappenas Nomor 7 Tahun 2025 tentang Data Tunggal Sosial Ekonomi Nasional',
                'Peraturan Daerah Provinsi Kalimantan Utara tentang Penyelenggaraan Kesejahteraan Sosial',
                'Peraturan Gubernur tentang Kedudukan, Susunan Organisasi, Tugas dan Fungsi Perangkat Daerah',
            ],
            'kak_maksud_tujuan' => 'Memperoleh data sasaran yang mutakhir dan terverifikasi untuk menetapkan penerima '
                . 'manfaat secara tepat sasaran, dengan keluaran berupa basis data sasaran terverifikasi.',
            'kak_metodologi' => 'Pemadanan data berdasarkan NIK, pemeringkatan desil kesejahteraan, '
                . 'verifikasi lapangan pada sampel, lalu penetapan daftar sasaran final.',
            'kak_keluaran' => 'Basis data sasaran terverifikasi dan dashboard monitoring penyaluran.',
            'kak_unit_akses' => 'Bidang Perlindungan dan Jaminan Sosial',
            'kak_jangka_mulai' => $mulai->copy()->toDateString(),
            'kak_jangka_akhir' => $mulai->copy()->addMonths(6)->toDateString(),
            'kak_infrastruktur_penyimpanan' => 'Server OPD yang ditempatkan di Pusat Data Provinsi, '
                . 'akses dibatasi melalui VPN dan kontrol akses berbasis peran.',
            'kak_personel_akses' => [
                ['nama' => 'Rahmawati Dewi', 'nip' => '198705122010012003', 'jabatan' => 'Kepala Seksi'],
                ['nama' => 'Rekan Analis Data', 'nip' => '199001232015011001', 'jabatan' => 'Analis Data'],
            ],
            'kak_teknik_pelindungan' => $level >= 4
                ? ['masking', 'access_control', 'enkripsi_penyimpanan', 'minimasi']
                : ['anonimisasi', 'access_control'],
            'kak_retensi_batas_waktu' => $mulai->copy()->addMonths(7)->toDateString(),
            'kak_metode_pemusnahan' => 'Penghapusan permanen berkas elektronik beserta seluruh salinan cadangan, '
                . 'disertai penimpaan media penyimpanan dan berita acara.',
            'kak_pernyataan' => true,
            'kak_file_path' => $this->lampiran['kak'],
        ];
    }

    private function lampirkanVariabel(DtsenDataRequest $item, int $level): void
    {
        $variabel = DtsenVariable::active()
            ->where('level_minimal', '<=', $level)
            ->orderByDesc('level_minimal')
            ->take($level >= 4 ? 6 : 4)
            ->get();

        // Pastikan variabel penentu level ikut terpilih.
        if (! $variabel->contains('id', $this->variabel[$level]->id)) {
            $variabel->push($this->variabel[$level]);
        }

        $kegunaan = [
            'Kunci pemadanan dengan basis data internal OPD.',
            'Dasar pemeringkatan prioritas penerima manfaat.',
            'Bahan verifikasi kelayakan calon penerima.',
            'Pelengkap profil sasaran untuk perencanaan intervensi.',
            'Dasar penentuan jenis bantuan yang sesuai.',
            'Bahan analisis sebaran sasaran antarwilayah.',
        ];

        foreach ($variabel->unique('id')->values() as $i => $v) {
            $item->requestVariables()->create([
                'dtsen_variable_id' => $v->id,
                'kegunaan' => $kegunaan[$i % count($kegunaan)],
            ]);
        }
    }

    /**
     * Isi kolom hasil tiap tahap sesuai status akhir yang diminta, sehingga
     * timeline, SLA, dan panel tindakan menampilkan kondisi yang konsisten.
     */
    private function terapkanTahapan(DtsenDataRequest $item, string $status, Carbon $mulai, int $level): void
    {
        if ($status === 'draft' || $status === DtsenDataRequest::STATUS_DIAJUKAN) {
            return;
        }

        // --- Verifikasi administrasi ---
        $verifAdmin = $mulai->copy()->addDay();
        $item->forceFill([
            'adm_check_surat' => true,
            'adm_check_kak' => $level >= 3,
            'adm_check_dokumen_pendukung' => $level >= 4,
            'adm_check_metode_akses' => true,
            'adm_check_enkripsi' => true,
            'adm_verified_by' => $this->dkisp->id,
            'verif_admin_at' => $verifAdmin,
        ])->save();

        if ($status === DtsenDataRequest::STATUS_VERIF_ADMIN) {
            // Masih dipegang DKISP; kembalikan status ke tahap verifikasi administrasi.
            $item->forceFill(['status' => DtsenDataRequest::STATUS_VERIF_ADMIN, 'verif_admin_at' => null])->save();
            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'dibuka_verifikator',
                'Berkas dibuka untuk verifikasi administrasi', $verifAdmin);

            return;
        }

        if ($status === DtsenDataRequest::STATUS_PERLU_PERBAIKAN) {
            $item->forceFill([
                'adm_catatan' => 'Dokumen pendukung yang dilampirkan belum memuat dokumen perencanaan program. '
                    . 'Mohon lengkapi dan ajukan ulang.',
                'dikembalikan_at' => $verifAdmin,
            ])->save();

            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
                'verif_administrasi:diajukan->perlu_perbaikan', $item->adm_catatan, $verifAdmin);

            return;
        }

        $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
            'verif_administrasi:diajukan->verifikasi_substansi', 'Dokumen dinyatakan lengkap & sesuai', $verifAdmin);

        if ($status === DtsenDataRequest::STATUS_VERIF_SUBSTANSI) {
            return;
        }

        // --- Klarifikasi (cabang) ---
        if ($status === DtsenDataRequest::STATUS_KLARIFIKASI) {
            $tanggal = $verifAdmin->copy()->addDay();

            $item->forceFill([
                'sub_hasil' => 'klarifikasi',
                'sub_catatan' => 'Perlu penjelasan mengenai keterkaitan variabel BNBA dengan tugas dan fungsi OPD.',
                'sub_verified_by' => $this->bapperida->id,
                'verif_substansi_at' => $tanggal,
                'klarifikasi_at' => $tanggal,
            ])->save();

            $item->clarifications()->create([
                'tanggal' => $tanggal->toDateString(),
                'tempat_media' => 'Ruang Rapat Bapperida / Zoom Meeting',
                'peserta' => 'Tim Pelaksana Forum Satu Data Daerah, Bidang Statistik DKISP, perwakilan Dinas Sosial.',
                'pokok_klarifikasi' => 'Kesesuaian variabel by name by address dengan kewenangan OPD pemohon '
                    . 'serta mekanisme pelindungan data yang akan diterapkan.',
                'hasil' => 'OPD menyampaikan dasar hukum tambahan dan menyanggupi penerapan masking pada '
                    . 'kolom identitas saat pengolahan. Klarifikasi dinyatakan cukup.',
                'file_path' => $this->lampiran['klarifikasi'],
                'created_by' => $this->bapperida->id,
            ]);

            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
                'verif_substansi:verifikasi_substansi->klarifikasi', $item->sub_catatan, $tanggal);

            return;
        }

        // --- Penolakan (final) ---
        if ($status === DtsenDataRequest::STATUS_DITOLAK) {
            $tanggal = $verifAdmin->copy()->addDays(2);

            $item->forceFill([
                'sub_hasil' => 'ditolak',
                'sub_alasan_penolakan' => 'Variabel by name by address yang dimohonkan tidak sepadan dengan tujuan '
                    . 'penggunaan yang diuraikan, dan dasar hukum yang dicantumkan belum menunjukkan kewenangan '
                    . 'OPD atas data dimaksud.',
                'sub_verified_by' => $this->bapperida->id,
                'verif_substansi_at' => $tanggal,
                'ditolak_at' => $tanggal,
            ])->save();

            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
                'verif_substansi:verifikasi_substansi->ditolak', $item->sub_alasan_penolakan, $tanggal);

            return;
        }

        // --- Diterima ---
        $diterima = $verifAdmin->copy()->addDays(2);
        $item->forceFill([
            'sub_hasil' => 'diterima',
            'sub_catatan' => 'KAK sesuai dengan dasar hukum dan tugas fungsi OPD pemohon.',
            'sub_verified_by' => $this->bapperida->id,
            'verif_substansi_at' => $diterima,
            'diterima_at' => $diterima,
        ])->save();

        $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
            'verif_substansi:verifikasi_substansi->diterima', $item->sub_catatan, $diterima);

        if ($status === DtsenDataRequest::STATUS_DITERIMA) {
            return;
        }

        // --- Pemrosesan & QA ---
        $qa = $diterima->copy()->addDays(2);
        $item->forceFill([
            'qa_check_pemilahan' => true,
            'qa_check_agregasi' => true,
            'qa_check_mutu' => true,
            'qa_check_kesesuaian' => true,
            'qa_catatan' => 'Data dipilah sesuai cakupan wilayah dan variabel yang disetujui, mutu diperiksa ulang.',
            'qa_by' => $this->dkisp->id,
        ])->save();

        if ($status === DtsenDataRequest::STATUS_PEMROSESAN_QA) {
            // Masih berjalan: pemrosesan belum ditandai selesai.
            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
                'pemrosesan_qa:diterima->pemrosesan_qa', 'Pemrosesan data dimulai', $qa);

            return;
        }

        $item->forceFill(['pemrosesan_at' => $qa])->save();
        $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id,
            'pemrosesan_qa:diterima->' . $item->status, $item->qa_catatan, $qa);

        // --- BAST (khusus level 4) ---
        if ($status === DtsenDataRequest::STATUS_MENUNGGU_BAST) {
            // Sudah diunggah pemohon, menunggu pengesahan DKISP.
            $item->forceFill([
                'bast_nomor' => 'BAST/' . random_int(100, 999) . '/DTSEN/2026',
                'bast_tanggal' => $qa->copy()->addDay()->toDateString(),
                'bast_file_path' => $this->lampiran['bast'],
                'bast_uploaded_at' => $qa->copy()->addDay(),
                'bast_verified' => false,
            ])->save();

            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'bast_diunggah',
                "BAST {$item->bast_nomor} diunggah pemohon", $qa->copy()->addDay());

            return;
        }

        $bast = $qa->copy()->addDay();
        if ($level >= 4) {
            $item->forceFill([
                'bast_nomor' => 'BAST/' . random_int(100, 999) . '/DTSEN/2026',
                'bast_tanggal' => $bast->toDateString(),
                'bast_file_path' => $this->lampiran['bast'],
                'bast_uploaded_at' => $bast,
                'bast_verified' => true,
                'bast_verified_by' => $this->dkisp->id,
                'bast_at' => $bast,
            ])->save();

            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'bast_disahkan',
                'BAST disahkan DKISP', $bast);
        }

        // --- Penerbitan token & akses data ---
        $akses = $bast->copy()->addDay();
        $kedaluwarsa = $status === DtsenDataRequest::STATUS_KEDALUWARSA;

        $item->forceFill([
            'infra_final' => 'excel_terenkripsi',
            'infra_parameter' => 'Berkas terenkripsi AES-256; kata sandi dikirim terpisah melalui narahubung teknis.',
            'akses_at' => $akses,
        ])->save();

        $token = DtsenAccessToken::create([
            'dtsen_data_request_id' => $item->id,
            'token' => DtsenAccessToken::generateToken(),
            'metode' => 'excel_terenkripsi',
            'file_path' => $this->lampiran['data'],
            'nama_berkas' => 'DTSEN_' . $item->ticket_no . '.csv',
            'catatan' => 'Gunakan data sesuai variabel dan cakupan wilayah yang disetujui.',
            'issued_by' => $this->dkisp->id,
            'issued_at' => $akses,
            'expires_at' => $kedaluwarsa
                ? $akses->copy()->addDays(DtsenDataRequest::TOKEN_ACTIVE_DAYS)   // sudah lewat
                : now()->addDays(21),
            'download_count' => $kedaluwarsa ? 3 : 2,
        ]);

        $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'token_diterbitkan',
            'Token akses terbit, berlaku sampai ' . $token->expires_at->format('d/m/Y H:i'), $akses);

        foreach (range(1, $token->download_count) as $n) {
            $token->downloadLogs()->create([
                'dtsen_data_request_id' => $item->id,
                'user_id' => $this->pemohon->id,
                'ip_address' => '10.10.' . random_int(1, 20) . '.' . random_int(2, 250),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0 Safari/537.36',
                'downloaded_at' => $akses->copy()->addDays($n),
            ]);
        }

        if ($kedaluwarsa) {
            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'akses_kedaluwarsa',
                'Masa aktif token akses berakhir. Ajukan perpanjangan atau permintaan ulang data.',
                $token->expires_at);

            return;
        }

        // Permohonan yang sudah dilayani penuh: satu perpanjangan menunggu keputusan.
        if ($status === DtsenDataRequest::STATUS_DATA_TERSEDIA) {
            DtsenExtensionRequest::create([
                'dtsen_data_request_id' => $item->id,
                'dtsen_access_token_id' => $token->id,
                'user_id' => $this->pemohon->id,
                'alasan' => 'Proses verifikasi lapangan pada tiga kecamatan belum selesai sehingga data masih diperlukan.',
                'durasi_hari' => 30,
                'status' => DtsenExtensionRequest::STATUS_DIAJUKAN,
            ]);
        }

        if ($status === DtsenDataRequest::STATUS_SELESAI) {
            $selesaiAt = $akses->copy()->addDays(10);
            $item->forceFill(['selesai_at' => $selesaiAt])->save();
            $this->catat(DtsenRequestLog::TYPE_PERMOHONAN, $item->id, 'selesai',
                'Pemanfaatan data dinyatakan selesai.', $selesaiAt);
        }
    }

    // ------------------------------------------------------------ Tahap 5

    private function seedPelaporan(): void
    {
        // Laporan pemanfaatan: satu menunggu telaah, satu dikembalikan, satu terverifikasi.
        $variabelIds = $this->selesai->requestVariables()->pluck('dtsen_variable_id')->all();

        DtsenUtilizationReport::create([
            'dtsen_data_request_id' => $this->selesai->id,
            'user_id' => $this->pemohon->id,
            'periode_mulai' => now()->subMonths(6)->toDateString(),
            'periode_akhir' => now()->subDays(5)->toDateString(),
            'nama_program' => $this->selesai->nama_program,
            'variabel_ids' => $variabelIds,
            'hasil_pemanfaatan' => 'Data dimanfaatkan untuk menetapkan 1.248 keluarga penerima manfaat. '
                . 'Ditemukan 87 keluarga tidak lagi memenuhi kriteria dan dikeluarkan dari daftar sasaran.',
            'kendala' => 'Sebagian data alamat tidak sesuai kondisi lapangan sehingga perlu verifikasi ulang.',
            'file_path' => $this->lampiran['pemanfaatan'],
            'status' => DtsenUtilizationReport::STATUS_TERKIRIM,
        ]);

        DtsenUtilizationReport::create([
            'dtsen_data_request_id' => $this->dataTersedia->id,
            'user_id' => $this->pemohon->id,
            'periode_mulai' => now()->subMonths(3)->toDateString(),
            'periode_akhir' => now()->subDays(10)->toDateString(),
            'nama_program' => $this->dataTersedia->nama_program,
            'variabel_ids' => $this->dataTersedia->requestVariables()->pluck('dtsen_variable_id')->all(),
            'hasil_pemanfaatan' => 'Data dipakai untuk penyaluran tahap pertama.',
            'status' => DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN,
            'catatan_review' => 'Uraian hasil pemanfaatan terlalu ringkas. Mohon lengkapi capaian kuantitatif '
                . 'dan lampirkan dokumentasi pendukung.',
            'reviewed_by' => $this->bapperida->id,
            'reviewed_at' => now()->subDays(4),
        ]);

        // Berita acara pemusnahan: satu sudah dilaporkan, satu masih draft & telat.
        $waktuMusnah = now()->subDays(3);
        DtsenDestructionReport::create([
            'dtsen_data_request_id' => $this->selesai->id,
            'user_id' => $this->pemohon->id,
            'dasar_pemusnahan' => 'pemanfaatan_selesai',
            'metode_pemusnahan' => 'Penghapusan permanen berkas beserta salinan cadangan, disertai penimpaan media.',
            'waktu_pelaksanaan' => $waktuMusnah,
            'batas_penyampaian' => $waktuMusnah->copy()->addDays(DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI)->toDateString(),
            'petugas_nama' => 'Rekan Analis Data',
            'petugas_nip' => '199001232015011001',
            'saksi_nama' => 'Petugas Keamanan Informasi',
            'saksi_unit' => 'Unit Keamanan Informasi dan Persandian',
            'file_path' => $this->lampiran['pemusnahan'],
            'status' => DtsenDestructionReport::STATUS_DILAPORKAN,
            'reported_at' => now()->subDays(2),
        ]);

        $waktuTelat = now()->subDays(25);
        DtsenDestructionReport::create([
            'dtsen_data_request_id' => $this->dataTersedia->id,
            'user_id' => $this->pemohon->id,
            'dasar_pemusnahan' => 'digantikan_rilis_terbaru',
            'metode_pemusnahan' => 'Penghapusan berkas pada server OPD.',
            'waktu_pelaksanaan' => $waktuTelat,
            'batas_penyampaian' => $waktuTelat->copy()->addDays(DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI)->toDateString(),
            'petugas_nama' => 'Operator Pendataan',
            'status' => DtsenDestructionReport::STATUS_DRAFT,
        ]);

        // Insiden: satu dilaporkan tepat waktu, satu terlambat & sudah dieskalasi.
        $this->buatInsiden([
            'jenis_insiden' => 'lainnya',
            'waktu_diketahui' => now()->subHours(20),
            'kronologi' => 'Perangkat kerja yang memuat salinan data sempat tertinggal di ruang rapat selama dua jam.',
            'dampak' => 'Tidak ada indikasi akses oleh pihak tidak berwenang; berkas terenkripsi.',
            'tindakan_awal' => 'Perangkat diamankan, kata sandi diganti, dan akses berkas ditinjau ulang.',
            'status' => DtsenIncidentReport::STATUS_DITANGANI,
            'tindak_lanjut' => 'Diminta melakukan audit akses internal dan melaporkan hasilnya dalam 7 hari.',
        ]);

        $this->buatInsiden([
            'jenis_insiden' => 'kebocoran',
            'waktu_diketahui' => now()->subDays(12),
            'kronologi' => 'Tautan berkas rekap sasaran sempat dibagikan pada grup pesan instan lintas unit '
                . 'sebelum disadari memuat kolom identitas.',
            'dampak' => 'Diperkirakan 540 baris data BNBA sempat dapat diakses oleh 18 orang di luar personel yang ditunjuk.',
            'tindakan_awal' => 'Tautan dicabut, pesan ditarik, dan seluruh penerima diminta menghapus salinan.',
            'status' => DtsenIncidentReport::STATUS_DILAPORKAN,
        ]);
    }

    private function buatInsiden(array $atribut): DtsenIncidentReport
    {
        $insiden = new DtsenIncidentReport(array_merge([
            'dtsen_data_request_id' => $this->dataTersedia->id,
            'user_id' => $this->pemohon->id,
            'unit_kerja_id' => $this->opd->id,
            'file_path' => $this->lampiran['pendukung'],
        ], $atribut));

        $insiden->applyDeadline(now());

        if ($insiden->terlambat) {
            $insiden->escalated_at = now()->subDays(1);
        }
        if (! empty($atribut['tindak_lanjut'])) {
            $insiden->handled_by = $this->dkisp->id;
            $insiden->handled_at = now()->subHours(4);
        }

        $insiden->save();

        $this->catat(DtsenRequestLog::TYPE_INSIDEN, $insiden->id,
            $insiden->terlambat ? 'dilaporkan_terlambat' : 'dilaporkan',
            $insiden->jenis_label . ' dilaporkan', $insiden->created_at);

        return $insiden;
    }

    // ------------------------------------------------------------ Pendukung

    private function seedPengaduan(): void
    {
        $daftar = [
            [
                'kategori' => 'pengaduan',
                'uraian' => 'Verifikasi administrasi permohonan kami melewati satu hari kerja tanpa pemberitahuan.',
                'is_anonim' => false,
                'status' => DtsenComplaint::STATUS_BARU,
            ],
            [
                'kategori' => 'saran',
                'uraian' => 'Mohon katalog variabel dilengkapi keterangan satuan dan periode rujukan datanya, '
                    . 'agar OPD lebih mudah memilih variabel yang tepat.',
                'is_anonim' => true,
                'status' => DtsenComplaint::STATUS_DIPROSES,
                'tindak_lanjut' => 'Usulan diteruskan ke Bapperida untuk pemutakhiran master variabel.',
            ],
            [
                'kategori' => 'masukan',
                'uraian' => 'Notifikasi token menjelang kedaluwarsa sangat membantu. Mohon ditambah pengingat '
                    . 'untuk jadwal pelaporan pemanfaatan.',
                'is_anonim' => false,
                'status' => DtsenComplaint::STATUS_SELESAI,
                'tindak_lanjut' => 'Pengingat jadwal pelaporan sudah ditambahkan pada pemeriksaan harian sistem.',
            ],
        ];

        foreach ($daftar as $data) {
            $pengaduan = new DtsenComplaint(array_merge([
                'nama_pelapor' => $this->pemohon->name,
                'kontak_pelapor' => $this->pemohon->email,
            ], $data));

            $pengaduan->user_id = $this->pemohon->id;

            if (! empty($data['tindak_lanjut'])) {
                $pengaduan->handled_by = $this->dkisp->id;
                $pengaduan->handled_at = now()->subDays(1);
            }

            $pengaduan->save();

            $this->catat(DtsenRequestLog::TYPE_PENGADUAN, $pengaduan->id, 'dikirim',
                $pengaduan->kategori_label . ' disampaikan', $pengaduan->created_at);
        }
    }

    /** Catat jejak audit dengan stempel waktu tertentu (bukan waktu seeding). */
    private function catat(string $tipe, int $id, string $aksi, ?string $catatan, ?Carbon $waktu = null): void
    {
        $waktu = $waktu ?? now();

        DtsenRequestLog::create([
            'request_type' => $tipe,
            'request_id' => $id,
            'actor_id' => $this->pemohon->id,
            'action' => $aksi,
            'note' => $catatan,
            'ip_address' => '10.10.1.1',
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);
    }

    // ------------------------------------------------------------ Ringkasan

    private function tampilkanRingkasan(): void
    {
        $this->command?->newLine();
        $this->command?->info('Data uji coba DTSEN siap.');

        $this->command?->table(
            ['Peran', 'Surel', 'Menu yang bisa dicoba'],
            [
                ['OPD Pemohon', self::EMAIL_PEMOHON, 'Layanan Digital > Berbagi Pakai Data (DTSEN)'],
                ['Prosesor DKISP', self::EMAIL_DKISP, 'Kelola Permohonan > Berbagi Pakai Data; Master Data'],
                ['Forum SDD (Bapperida)', self::EMAIL_BAPPERIDA, 'Kelola Permohonan > Verifikasi Substansi'],
            ]
        );

        $this->command?->table(
            ['Objek', 'Jumlah'],
            [
                ['Akun layanan', DtsenAccountRequest::where('user_id', $this->pemohon->id)->count()],
                ['Permintaan data', DtsenDataRequest::where('user_id', $this->pemohon->id)->count()],
                ['Token akses', DtsenAccessToken::whereIn('dtsen_data_request_id',
                    DtsenDataRequest::where('user_id', $this->pemohon->id)->pluck('id'))->count()],
                ['Laporan pemanfaatan', DtsenUtilizationReport::where('user_id', $this->pemohon->id)->count()],
                ['Berita acara pemusnahan', DtsenDestructionReport::where('user_id', $this->pemohon->id)->count()],
                ['Laporan insiden', DtsenIncidentReport::where('user_id', $this->pemohon->id)->count()],
                ['Pengaduan & masukan', DtsenComplaint::where('user_id', $this->pemohon->id)->count()],
            ]
        );

        $this->command?->warn('Password ketiga akun uji: ' . $this->passwordUji);
        $this->command?->warn('Simpan sekarang — password ini tidak ditampilkan lagi.');
        $this->command?->comment('Setel DTSEN_TEST_PASSWORD di .env bila ingin password tetap.');
    }
}
