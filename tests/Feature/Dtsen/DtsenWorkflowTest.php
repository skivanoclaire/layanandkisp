<?php

namespace Tests\Feature\Dtsen;

use App\Models\DtsenAccessToken;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenDataRequest;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenUtilizationReport;
use App\Models\DtsenVariable;
use App\Models\DtsenWilayah;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Alur lengkap berbagi pakai data DTSEN, Tahap 1 s.d. Tahap 5.
 *
 * Menguji jalur "bahagia" end-to-end untuk permohonan level 4 (BNBA) — level dengan
 * persyaratan terberat — plus aturan kelengkapan dokumen per level dan tenggat pelaporan.
 */
class DtsenWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;
    private User $dkisp;
    private User $bapperida;
    private UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->unitKerja = UnitKerja::factory()->create([
            'is_active' => true,
            'tipe' => UnitKerja::TIPE_INDUK,
        ]);

        // is_verified wajib true: middleware EnsureUserIsVerified menghadang
        // sebelum controller dijalankan.
        $this->pemohon = User::factory()->create([
            'is_verified' => true,
            'unit_kerja_id' => $this->unitKerja->id,
            'phone' => '08111111111',
            'nip' => '199001012015011001',
        ]);
        $this->dkisp = User::factory()->create(['is_verified' => true]);
        $this->bapperida = User::factory()->create(['is_verified' => true]);

        $this->grant($this->pemohon, 'Pemohon DTSEN', ['Akses DTSEN']);
        $this->grant($this->dkisp, 'Prosesor DTSEN', [
            'Kelola Akun DTSEN', 'Kelola Permohonan DTSEN', 'Kelola Laporan DTSEN',
        ]);
        $this->grant($this->bapperida, 'Forum SDD', ['Verifikasi Substansi DTSEN']);
    }

    public function test_alur_lengkap_permohonan_level_4_sampai_data_tersedia(): void
    {
        // --- Tahap 1: pembuatan & verifikasi akun ---
        $akun = $this->ajukanAkun();
        $this->assertSame(DtsenAccountRequest::STATUS_DIAJUKAN, $akun->status);
        $this->assertFalse($akun->is_active);

        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.akun.verifikasi', $akun->id), [
                'status' => DtsenAccountRequest::STATUS_DISETUJUI,
                'check_surat_lengkap' => 1,
                'check_ttd_kepala_opd' => 1,
                'check_data_personel' => 1,
            ])->assertRedirect();

        $akun->refresh();
        $this->assertTrue($akun->isUsable(), 'Akun seharusnya aktif setelah disetujui.');

        // --- Tahap 2: pengajuan permintaan data level 4 ---
        $permohonan = $this->ajukanPermintaanData();
        $this->assertSame(4, $permohonan->level_akses, 'Variabel BNBA memaksa level tertinggi.');
        $this->assertSame(DtsenDataRequest::STATUS_DIAJUKAN, $permohonan->status);
        $this->assertCount(2, $permohonan->requestVariables);
        $this->assertTrue($permohonan->documents()->where('jenis', 'pendukung')->exists());

        // --- Tahap 3a: verifikasi administrasi ---
        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.verifikasi-administrasi', $permohonan->id), [
                'hasil' => 'lengkap',
                'adm_check_surat' => 1,
                'adm_check_kak' => 1,
                'adm_check_dokumen_pendukung' => 1,
                'adm_check_metode_akses' => 1,
                'adm_check_enkripsi' => 1,
            ])->assertRedirect();

        $this->assertSame(
            DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
            $permohonan->refresh()->status
        );

        // --- Tahap 3b: verifikasi substansi oleh Bapperida ---
        $this->actingAs($this->bapperida)
            ->post(route('admin.dtsen.substansi.keputusan', $permohonan->id), [
                'sub_hasil' => 'diterima',
                'sub_catatan' => 'KAK sesuai tusi OPD.',
            ])->assertRedirect();

        $this->assertSame(DtsenDataRequest::STATUS_DITERIMA, $permohonan->refresh()->status);

        // --- Tahap 3c: pemrosesan & QA ---
        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.pemrosesan-qa', $permohonan->id), [
                'qa_check_pemilahan' => 1,
                'qa_check_agregasi' => 1,
                'qa_check_mutu' => 1,
                'qa_check_kesesuaian' => 1,
                'selesaikan' => 1,
            ])->assertRedirect();

        $permohonan->refresh();
        $this->assertSame(
            DtsenDataRequest::STATUS_MENUNGGU_BAST,
            $permohonan->status,
            'Permohonan level 4 wajib menunggu BAST sebelum hak akses diberikan.'
        );

        // --- Tahap 4a: pemohon mengunggah BAST ---
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.bast', $permohonan->id), [
                'bast_nomor' => 'BAST/001/2026',
                'bast_tanggal' => now()->toDateString(),
                'bast_file' => UploadedFile::fake()->create('bast.pdf', 120, 'application/pdf'),
            ])->assertRedirect();

        $this->assertNotNull($permohonan->refresh()->bast_file_path);
        $this->assertFalse($permohonan->bast_verified);

        // Token belum boleh terbit sebelum BAST disahkan.
        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.token', $permohonan->id), [
                'metode' => 'excel_terenkripsi',
                'berkas' => UploadedFile::fake()->create('data.xlsx', 40),
            ])->assertForbidden();

        // --- Tahap 4b: pengesahan BAST & penerbitan token ---
        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.verifikasi-bast', $permohonan->id), ['hasil' => 'sah'])
            ->assertRedirect();

        $this->assertTrue($permohonan->refresh()->bast_verified);

        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.token', $permohonan->id), [
                'metode' => 'excel_terenkripsi',
                'berkas' => UploadedFile::fake()->create('data.xlsx', 40),
                'masa_aktif_hari' => DtsenDataRequest::TOKEN_ACTIVE_DAYS,
            ])->assertRedirect();

        $permohonan->refresh();
        $this->assertSame(DtsenDataRequest::STATUS_DATA_TERSEDIA, $permohonan->status);

        $token = $permohonan->activeToken();
        $this->assertNotNull($token);
        $this->assertTrue($token->isUsable());
        $this->assertEqualsWithDelta(
            DtsenDataRequest::TOKEN_ACTIVE_DAYS,
            now()->diffInDays($token->expires_at),
            1,
            'Masa aktif token bawaan 30 hari kalender.'
        );

        // --- Tahap 4c: unduhan tercatat pada audit trail ---
        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akses.download', $token->token))
            ->assertOk();

        $token->refresh();
        $this->assertSame(1, $token->download_count);
        $this->assertDatabaseHas('dtsen_download_logs', [
            'dtsen_access_token_id' => $token->id,
            'user_id' => $this->pemohon->id,
        ]);

        // --- Tahap 5: pelaporan pemanfaatan ---
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.pemanfaatan.store'), [
                'dtsen_data_request_id' => $permohonan->id,
                'periode_mulai' => now()->subMonths(6)->toDateString(),
                'periode_akhir' => now()->toDateString(),
                'nama_program' => 'Bantuan Sosial Daerah',
                'variabel_ids' => $permohonan->requestVariables->pluck('dtsen_variable_id')->all(),
                'hasil_pemanfaatan' => 'Penetapan 1.200 keluarga sasaran.',
            ])->assertRedirect(route('user.dtsen.pemanfaatan.index'));

        $this->assertDatabaseHas('dtsen_utilization_reports', [
            'dtsen_data_request_id' => $permohonan->id,
            'status' => DtsenUtilizationReport::STATUS_TERKIRIM,
        ]);
    }

    public function test_permohonan_level_4_ditolak_bila_dokumen_pendukung_belum_ada(): void
    {
        $this->approveAkun();

        $payload = $this->payloadPermintaanData();
        unset($payload['dokumen_pendukung']);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $payload)
            ->assertSessionHasErrors('dokumen_pendukung');

        $this->assertDatabaseCount('dtsen_data_requests', 0);
    }

    public function test_permohonan_level_3_wajib_mengisi_kak(): void
    {
        $this->approveAkun();

        $variabelLevel3 = DtsenVariable::where('level_minimal', 3)->first();

        $payload = $this->payloadPermintaanData();
        $payload['variabel'] = [$variabelLevel3->id];
        $payload['kegunaan'] = [$variabelLevel3->id => 'Analisis sasaran.'];
        unset($payload['dokumen_pendukung'], $payload['kak_latar_belakang'], $payload['kak_maksud_tujuan']);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $payload)
            ->assertSessionHasErrors(['kak_latar_belakang', 'kak_maksud_tujuan']);
    }

    public function test_permohonan_level_2_tidak_memerlukan_kak(): void
    {
        $this->approveAkun();

        $variabelLevel2 = DtsenVariable::where('level_minimal', 2)->first();

        $payload = $this->payloadPermintaanData();
        $payload['variabel'] = [$variabelLevel2->id];
        $payload['kegunaan'] = [$variabelLevel2->id => 'Rekap agregat wilayah.'];
        unset($payload['dokumen_pendukung'], $payload['kak_latar_belakang'], $payload['kak_maksud_tujuan']);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $payload)
            ->assertSessionHasNoErrors();

        $permohonan = DtsenDataRequest::firstOrFail();
        $this->assertSame(2, $permohonan->level_akses);
        $this->assertFalse($permohonan->requiresKak());
        $this->assertFalse($permohonan->requiresBast());
    }

    public function test_penolakan_substansi_bersifat_final(): void
    {
        $this->approveAkun();
        $permohonan = $this->ajukanPermintaanData();

        $this->actingAs($this->dkisp)->post(
            route('admin.dtsen.permohonan.verifikasi-administrasi', $permohonan->id),
            ['hasil' => 'lengkap']
        );

        $this->actingAs($this->bapperida)
            ->post(route('admin.dtsen.substansi.keputusan', $permohonan->id), [
                'sub_hasil' => 'ditolak',
                'sub_alasan_penolakan' => 'Variabel BNBA tidak sepadan dengan tusi OPD.',
            ])->assertRedirect();

        $permohonan->refresh();
        $this->assertSame(DtsenDataRequest::STATUS_DITOLAK, $permohonan->status);
        $this->assertTrue($permohonan->isFinal());
        $this->assertFalse($permohonan->isEditableByOwner(), 'Permohonan yang ditolak tidak dapat diperbaiki.');

        // Pemrosesan lanjutan harus ditolak sistem.
        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.pemrosesan-qa', $permohonan->id), ['selesaikan' => 1])
            ->assertForbidden();
    }

    public function test_alasan_penolakan_substansi_wajib_diisi(): void
    {
        $this->approveAkun();
        $permohonan = $this->ajukanPermintaanData();

        $this->actingAs($this->dkisp)->post(
            route('admin.dtsen.permohonan.verifikasi-administrasi', $permohonan->id),
            ['hasil' => 'lengkap']
        );

        $this->actingAs($this->bapperida)
            ->post(route('admin.dtsen.substansi.keputusan', $permohonan->id), ['sub_hasil' => 'ditolak'])
            ->assertSessionHasErrors('sub_alasan_penolakan');
    }

    public function test_permohonan_dikembalikan_dapat_diperbaiki_pemohon(): void
    {
        $this->approveAkun();
        $permohonan = $this->ajukanPermintaanData();

        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.permohonan.verifikasi-administrasi', $permohonan->id), [
                'hasil' => 'dikembalikan',
                'adm_catatan' => 'Surat belum ditandatangani Kepala OPD.',
            ])->assertRedirect();

        $permohonan->refresh();
        $this->assertSame(DtsenDataRequest::STATUS_PERLU_PERBAIKAN, $permohonan->status);
        $this->assertTrue($permohonan->isEditableByOwner());

        $this->actingAs($this->pemohon)
            ->put(route('user.dtsen.permohonan.update', $permohonan->id), $this->payloadPermintaanData())
            ->assertSessionHasNoErrors();

        $this->assertSame(DtsenDataRequest::STATUS_DIAJUKAN, $permohonan->refresh()->status);
    }

    public function test_permintaan_data_ditolak_bila_akun_belum_aktif(): void
    {
        // Akun diajukan tetapi belum disetujui DKISP.
        $this->ajukanAkun();

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.create'))
            ->assertForbidden();
    }

    public function test_akun_menganggur_dinonaktifkan_dan_dapat_diajukan_aktivasi_ulang(): void
    {
        $akun = $this->approveAkun();

        // Geser pemakaian terakhir melewati ambang 30 hari kalender.
        $akun->forceFill([
            'last_used_at' => now()->subDays(DtsenAccountRequest::IDLE_DAYS + 1),
        ])->saveQuietly();

        $this->artisan('dtsen:lifecycle-check')->assertSuccessful();

        $akun->refresh();
        $this->assertFalse($akun->is_active, 'Akun menganggur 30 hari harus nonaktif otomatis.');
        $this->assertDatabaseHas('dtsen_request_logs', [
            'request_type' => 'akun',
            'request_id' => $akun->id,
            'action' => 'akun_dinonaktifkan_otomatis',
        ]);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.reaktivasi', $akun->id), ['alasan' => 'Akan mengajukan permintaan data baru.'])
            ->assertRedirect();

        $reaktivasi = $akun->reactivations()->firstOrFail();

        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.akun.reaktivasi', $reaktivasi->id), ['status' => 'disetujui'])
            ->assertRedirect();

        $this->assertTrue($akun->refresh()->is_active);
    }

    public function test_token_kedaluwarsa_menutup_akses_dan_perpanjangan_membukanya_kembali(): void
    {
        $permohonan = $this->siapkanPermohonanDenganToken();
        $token = $permohonan->activeToken();

        // Lewatkan masa aktif token.
        $token->forceFill(['expires_at' => now()->subDay()])->save();

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akses.download', $token->token))
            ->assertStatus(410);

        $this->artisan('dtsen:lifecycle-check')->assertSuccessful();
        $this->assertSame(DtsenDataRequest::STATUS_KEDALUWARSA, $permohonan->refresh()->status);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.perpanjangan.store', $permohonan->id), [
                'alasan' => 'Analisis lanjutan belum selesai.',
                'durasi_hari' => 30,
            ])->assertRedirect();

        $perpanjangan = $permohonan->extensionRequests()->firstOrFail();

        $this->actingAs($this->dkisp)
            ->post(route('admin.dtsen.perpanjangan.decide', $perpanjangan->id), [
                'status' => 'disetujui',
                'durasi_hari' => 30,
            ])->assertRedirect();

        $token->refresh();
        $this->assertTrue($token->expires_at->isFuture(), 'Masa akses harus diperpanjang dari hari ini.');
        $this->assertSame(DtsenDataRequest::STATUS_DATA_TERSEDIA, $permohonan->refresh()->status);

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akses.download', $token->token))
            ->assertOk();
    }

    public function test_token_hanya_dapat_diakses_opd_pemilik_permohonan(): void
    {
        $permohonan = $this->siapkanPermohonanDenganToken();
        $token = $permohonan->activeToken();

        $orangLain = User::factory()->create(['is_verified' => true]);
        $this->grant($orangLain, 'OPD Lain', ['Akses DTSEN']);

        $this->actingAs($orangLain)
            ->get(route('user.dtsen.akses.download', $token->token))
            ->assertForbidden();
    }

    public function test_laporan_insiden_melewati_batas_ditandai_terlambat_dan_dieskalasi(): void
    {
        $this->approveAkun();

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.insiden.store'), [
                'jenis_insiden' => 'kebocoran',
                // Diketahui jauh melewati batas 3x24 jam hari kerja.
                'waktu_diketahui' => now()->subDays(20)->toDateTimeString(),
                'kronologi' => 'Berkas terunduh oleh pihak tak berwenang.',
                'dampak' => 'Sekitar 500 baris data BNBA.',
                'tindakan_awal' => 'Akses dicabut dan berkas dikarantina.',
            ])->assertRedirect(route('user.dtsen.insiden.index'));

        $insiden = DtsenIncidentReport::firstOrFail();
        $this->assertTrue($insiden->terlambat);
        $this->assertNotNull($insiden->escalated_at);
        $this->assertNotNull($insiden->batas_pelaporan);
        $this->assertStringStartsWith('DTSEN-INS-', $insiden->ticket_no);
    }

    public function test_berita_acara_pemusnahan_menghitung_batas_penyampaian_14_hari(): void
    {
        $permohonan = $this->siapkanPermohonanDenganToken();
        $waktu = now()->subDays(2);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.pemusnahan.store'), [
                'dtsen_data_request_id' => $permohonan->id,
                'dasar_pemusnahan' => 'habis_retensi',
                'metode_pemusnahan' => 'Penghapusan permanen beserta salinan cadangan.',
                'waktu_pelaksanaan' => $waktu->toDateTimeString(),
                'petugas_nama' => 'Petugas Pemusnahan',
                'action' => 'lapor',
            ])->assertRedirect(route('user.dtsen.pemusnahan.index'));

        $ba = DtsenDestructionReport::firstOrFail();
        $this->assertSame(DtsenDestructionReport::STATUS_DILAPORKAN, $ba->status);
        $this->assertSame(
            $waktu->copy()->addDays(DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI)->toDateString(),
            $ba->batas_penyampaian->toDateString()
        );
    }

    public function test_pengaduan_anonim_menyembunyikan_identitas_pada_rekap(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.pengaduan.store'), [
                'kategori' => 'pengaduan',
                'uraian' => 'Proses verifikasi melebihi SLA.',
                'is_anonim' => 1,
            ])->assertRedirect(route('user.dtsen.pengaduan.index'));

        $pengaduan = \App\Models\DtsenComplaint::firstOrFail();

        $this->assertTrue($pengaduan->is_anonim);
        $this->assertSame('Anonim', $pengaduan->displayName());
        // Identitas tetap tersimpan untuk keperluan tindak lanjut Prosesor.
        $this->assertSame($this->pemohon->name, $pengaduan->displayName(true));
    }

    // ------------------------------------------------------------ Helper

    /**
     * Buat role baru berisi permission yang diminta, lalu tautkan ke user.
     * hasPermission() membaca relasi roles, bukan kolom legacy `users.role`.
     */
    private function grant(User $user, string $roleName, array $permissions): void
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => $roleName,
            'display_name' => $roleName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $user->id]);

        $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');
        $this->assertCount(
            count($permissions),
            $permissionIds,
            'Permission DTSEN harus sudah dibuat oleh migration.'
        );

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function ajukanAkun(): DtsenAccountRequest
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.store'), [
                'unit_kerja_id' => $this->unitKerja->id,
                'nomor_surat' => '005/DTSEN/2026',
                'sifat_surat' => 'biasa',
                'tanggal_surat' => now()->toDateString(),
                'surat' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
                'narahubung_nama' => 'Narahubung Teknis',
                'narahubung_kontak' => '08123456789',
                'narahubung_email' => 'narahubung@kaltaraprov.go.id',
                'members' => [
                    [
                        'nama' => $this->pemohon->name,
                        'nip' => $this->pemohon->nip,
                        'jabatan' => 'Pranata Komputer',
                        'unit_kerja' => $this->unitKerja->nama,
                        'no_hp' => '08111111111',
                        'email' => $this->pemohon->email,
                    ],
                ],
                'consent_true' => 1,
                'action' => 'submit',
            ])->assertSessionHasNoErrors();

        return DtsenAccountRequest::firstOrFail();
    }

    private function approveAkun(): DtsenAccountRequest
    {
        $akun = $this->ajukanAkun();

        $this->actingAs($this->dkisp)->post(route('admin.dtsen.akun.verifikasi', $akun->id), [
            'status' => DtsenAccountRequest::STATUS_DISETUJUI,
            'check_surat_lengkap' => 1,
            'check_ttd_kepala_opd' => 1,
            'check_data_personel' => 1,
        ]);

        return $akun->refresh();
    }

    /** Payload permintaan data level 4 yang sudah lengkap. */
    private function payloadPermintaanData(): array
    {
        $bnba = DtsenVariable::where('level_minimal', 4)->take(1)->pluck('id')->all();
        $mikro = DtsenVariable::where('level_minimal', 3)->take(1)->pluck('id')->all();
        $variabel = array_merge($bnba, $mikro);

        $wilayah = DtsenWilayah::where('tingkat', 'kabupaten_kota')->first();

        return [
            'pemohon_nama' => $this->pemohon->name,
            'pemohon_nip' => $this->pemohon->nip,
            'pemohon_jabatan' => 'Kepala Bidang',
            'pemohon_telepon' => '08111111111',
            'nomor_surat' => '010/DTSEN/2026',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => now()->toDateString(),
            'nama_program' => 'Bantuan Sosial Daerah',
            'jenis_permintaan' => 'bnba',
            'cakupan_wilayah_ids' => [$wilayah->id],
            'tujuan_penggunaan' => 'Penetapan sasaran penerima bantuan sosial daerah.',
            'surat_permohonan' => UploadedFile::fake()->create('surat-permohonan.pdf', 100, 'application/pdf'),
            'variabel' => $variabel,
            'kegunaan' => array_fill_keys($variabel, 'Dasar penetapan sasaran penerima bantuan.'),
            'metode_akses' => 'excel_terenkripsi',
            'metode_enkripsi' => 'AES-256 pada berkas, kanal HTTPS.',
            'kapasitas_sdm' => 'Dua pranata komputer.',
            'kak_latar_belakang' => 'Ketimpangan penetapan sasaran bantuan sosial.',
            'kak_dasar_hukum' => ['Perda No. 1 Tahun 2020 tentang Kesejahteraan Sosial'],
            'kak_maksud_tujuan' => 'Menetapkan 1.200 keluarga sasaran secara tepat.',
            'kak_metodologi' => 'Pemadanan dan analisis desil kesejahteraan.',
            'kak_keluaran' => 'Basis data sasaran dan dashboard monitoring.',
            'kak_unit_akses' => 'Bidang Perlindungan dan Jaminan Sosial',
            'kak_jangka_mulai' => now()->toDateString(),
            'kak_jangka_akhir' => now()->addMonths(6)->toDateString(),
            'kak_infrastruktur_penyimpanan' => 'Server OPD di Pusat Data Provinsi, akses terbatas VPN.',
            'kak_personel_akses' => [
                ['nama' => 'Analis Data', 'nip' => '199203032016011002', 'jabatan' => 'Analis'],
            ],
            'kak_teknik_pelindungan' => ['masking', 'access_control'],
            'kak_retensi_batas_waktu' => now()->addMonths(7)->toDateString(),
            'kak_metode_pemusnahan' => 'Penghapusan permanen beserta salinan cadangan.',
            'kak_pernyataan' => 1,
            'dokumen_pendukung' => [UploadedFile::fake()->create('proposal.pdf', 80, 'application/pdf')],
            'dokumen_keterangan' => ['Dokumen perencanaan program'],
            'consent_true' => 1,
            'action' => 'submit',
        ];
    }

    private function ajukanPermintaanData(): DtsenDataRequest
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $this->payloadPermintaanData())
            ->assertSessionHasNoErrors();

        return DtsenDataRequest::with('requestVariables')->firstOrFail();
    }

    /** Bawa satu permohonan level 4 sampai berstatus data tersedia. */
    private function siapkanPermohonanDenganToken(): DtsenDataRequest
    {
        $this->approveAkun();
        $permohonan = $this->ajukanPermintaanData();

        $this->actingAs($this->dkisp)->post(
            route('admin.dtsen.permohonan.verifikasi-administrasi', $permohonan->id),
            ['hasil' => 'lengkap']
        );
        $this->actingAs($this->bapperida)->post(
            route('admin.dtsen.substansi.keputusan', $permohonan->id),
            ['sub_hasil' => 'diterima']
        );
        $this->actingAs($this->dkisp)->post(
            route('admin.dtsen.permohonan.pemrosesan-qa', $permohonan->id),
            ['selesaikan' => 1]
        );
        $this->actingAs($this->pemohon)->post(route('user.dtsen.permohonan.bast', $permohonan->id), [
            'bast_nomor' => 'BAST/001/2026',
            'bast_tanggal' => now()->toDateString(),
            'bast_file' => UploadedFile::fake()->create('bast.pdf', 100, 'application/pdf'),
        ]);
        $this->actingAs($this->dkisp)->post(
            route('admin.dtsen.permohonan.verifikasi-bast', $permohonan->id),
            ['hasil' => 'sah']
        );
        $this->actingAs($this->dkisp)->post(route('admin.dtsen.permohonan.token', $permohonan->id), [
            'metode' => 'excel_terenkripsi',
            'berkas' => UploadedFile::fake()->create('data.xlsx', 40),
        ]);

        return $permohonan->refresh();
    }
}
