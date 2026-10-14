<?php

namespace Database\Seeders;

use App\Models\TataKelolaKeamanan\Aktivitas;
use App\Models\TataKelolaKeamanan\DesignFactor;
use App\Models\TataKelolaKeamanan\Kontrol;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\Penilaian;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Putaran;
use App\Models\TataKelolaKeamanan\JawabanAktivitas;
use App\Models\TataKelolaKeamanan\Responden;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\User;
use App\Services\TataKelolaKeamanan\RoleModul;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Data SIMULASI untuk mencoba modul Tata Kelola Keamanan Informasi (bukan data evaluasi nyata).
 * Evaluasi ditandai is_simulasi agar tidak tertukar dengan data yang sebenarnya.
 * Akun simulasi (responden dan pakar) memakai domain example.test dan kata sandi acak.
 * Uraian aktivitas sengaja ditulis sebagai contoh ringkas, bukan kutipan buku COBIT 2019.
 *
 * Jalankan: php artisan db:seed --class=TataKelolaKeamananSimulasiSeeder
 */
class TataKelolaKeamananSimulasiSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Evaluasi::where('is_simulasi', true)->where('nama', 'like', '[SIMULASI]%')->get()->each->delete();

            $evaluasi = Evaluasi::create([
                'nama' => '[SIMULASI] Evaluasi Tata Kelola Keamanan Informasi',
                'instansi' => 'Instansi contoh',
                'keterangan' => 'Data rekaan untuk latihan memakai modul. Jangan dipakai sebagai hasil evaluasi.',
                'is_simulasi' => true,
            ]);

            $respondens = $this->responden($evaluasi);
            $this->designFactors($evaluasi, $respondens);
            $this->objektif($evaluasi);
            $pemetaans = $this->pemetaan($evaluasi);
            $this->delphi($evaluasi, $pemetaans);
            $this->asesmen($evaluasi);
            $this->jawabanInforman($evaluasi, $respondens);
            $this->kontrol($evaluasi);
            $this->rekomendasi($evaluasi);
        });
    }

    /**
     * Dua akun responden simulasi (role User, terverifikasi) dengan kata sandi acak.
     * Untuk mencoba tampilan responden di lingkungan lokal, atur ulang kata sandinya.
     */
    private function responden(Evaluasi $evaluasi): array
    {
        $data = [
            ['responden1.simulasi@example.test', '[SIMULASI] Kepala Bidang Persandian', 'Kepala Bidang Persandian dan Keamanan Informasi', 'CISO', true, ['APO12', 'APO13', 'DSS05']],
            ['responden2.simulasi@example.test', '[SIMULASI] Pengelola Pusat Data', 'Pengelola Pusat Data dan Jaringan', 'Head of IT Operations', false, ['DSS04', 'DSS05']],
        ];
        $hasil = [];
        foreach ($data as [$email, $nama, $jabatan, $peran, $isiDf, $objektif]) {
            $user = $this->akunSimulasi($email, $nama, RoleModul::RESPONDEN);
            $hasil[] = Responden::create([
                'evaluasi_id' => $evaluasi->id, 'user_id' => $user->id, 'jabatan' => $jabatan,
                'peran_raci' => $peran, 'isi_df' => $isiDf, 'objektif' => $objektif,
            ]);
        }

        return $hasil;
    }

    /** Akun simulasi terverifikasi dengan role User dan role modul. */
    private function akunSimulasi(string $email, string $nama, string $roleModul): User
    {
        $user = User::firstOrCreate(['email' => $email], [
            'name' => $nama,
            'phone' => '080000000000',
            'password' => Hash::make(Str::random(40)),
            'is_verified' => true,
            'verified_at' => now(),
        ]);
        $roleUser = DB::table('roles')->where('name', 'User')->value('id');
        if ($roleUser && ! DB::table('role_user')->where('user_id', $user->id)->where('role_id', $roleUser)->exists()) {
            DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $roleUser, 'created_at' => now(), 'updated_at' => now()]);
        }
        app(RoleModul::class)->pasang($user, $roleModul);

        return $user;
    }

    private function jawabanInforman(Evaluasi $evaluasi, array $respondens): void
    {
        foreach ($respondens as $r) {
            $aktivitas = Aktivitas::whereIn('praktik_id', Praktik::where('evaluasi_id', $evaluasi->id)->whereIn('objektif_kode', $r->objektif)->pluck('id'))->get();
            foreach ($aktivitas as $a) {
                // Informan cenderung menjawab "ya" lebih banyak daripada hasil validasi bukti
                JawabanAktivitas::create([
                    'aktivitas_id' => $a->id, 'responden_id' => $r->id,
                    'jawaban' => ($a->terpenuhi || $a->id % 3 === 0) ? 'ya' : 'tidak',
                    'keterangan' => '[contoh] keterangan informan',
                ]);
            }
        }
    }

    private function designFactors(Evaluasi $evaluasi, array $respondens): void
    {
        $nilai = [
            'DF1' => [3, 3, 2, 5],
            'DF2' => [2, 4, 5, 3, 5, 5, 4, 4, 3, 4, 4, 4, 2],
            'DF4' => [1, 2, 3, 2, 2, 2, 1, 2, 3, 2, 1, 2, 2, 2, 2, 2, 1, 2, 2, 2],
            'DF5' => [60, 40],
            'DF6' => [0, 20, 80],
            'DF7' => [3, 3, 2, 3],
            'DF8' => [30, 20, 50],
            'DF9' => [20, 0, 70, 10],
            'DF10' => [10, 70, 20],
            'DF11' => ['pilihan' => 1],
        ];
        $dampak = [3, 2, 2, 4, 3, 4, 4, 2, 3, 3, 5, 3, 4, 1, 1, 4, 2, 2, 4];
        $kemungkinan = [2, 2, 2, 4, 3, 3, 3, 2, 3, 3, 4, 3, 3, 1, 1, 2, 2, 1, 3];
        $nilai['DF3'] = ['dampak' => $dampak, 'kemungkinan' => $kemungkinan];

        foreach ($nilai as $kode => $v) {
            DesignFactor::create(['evaluasi_id' => $evaluasi->id, 'df_kode' => $kode, 'responden' => DesignFactor::NILAI_AKHIR, 'nilai' => $v]);
            DesignFactor::create(['evaluasi_id' => $evaluasi->id, 'df_kode' => $kode, 'responden' => 'Kepala Dinas (contoh, entri manual)', 'nilai' => $v]);
            DesignFactor::create(['evaluasi_id' => $evaluasi->id, 'df_kode' => $kode, 'responden_id' => $respondens[0]->id, 'responden' => $respondens[0]->load('user')->label(), 'nilai' => $v]);
        }
    }

    private function objektif(Evaluasi $evaluasi): void
    {
        $skor = [
            'EDM03' => 70, 'APO12' => 95, 'APO13' => 90, 'APO14' => 55, 'BAI10' => 45, 'DSS04' => 75,
            'DSS05' => 100, 'MEA03' => 60, 'DSS02' => 65, 'APO10' => 35, 'BAI06' => 40, 'APO07' => 50,
        ];
        foreach (config('tata_kelola_keamanan.objektif') as $kode => $o) {
            $evaluasi->objektifs()->create([
                'kode' => $kode,
                'skor_kepentingan' => $skor[$kode] ?? 10,
                'terkait_keamanan' => $o['keamanan'],
                'dipilih' => in_array($kode, ['EDM03', 'APO12', 'APO13', 'DSS04', 'DSS05', 'MEA03'], true),
            ]);
        }
        foreach ($evaluasi->objektifs()->where('dipilih', true)->pluck('kode') as $kode) {
            $urutan = 0;
            foreach (config("tata_kelola_keamanan.praktik.$kode", []) as $kp => $nama) {
                Praktik::create(['evaluasi_id' => $evaluasi->id, 'objektif_kode' => $kode, 'kode' => $kp, 'nama' => $nama, 'urutan' => ++$urutan]);
            }
        }
    }

    private function pemetaan(Evaluasi $evaluasi): array
    {
        $p = Praktik::where('evaluasi_id', $evaluasi->id)->pluck('id', 'kode');
        $data = [
            ['EDM03.01', ['K5.1', 'K6.1.1', 'K9.3'], ['BSSN4-5', 'PG20-5'], 'Penetapan selera risiko, laporan risiko ke pimpinan'],
            ['APO12.02', ['K6.1.2', 'K8.2', 'A.5.7'], ['PG20-14.2'], 'Daftar risiko (risk register)'],
            ['APO12.06', ['K6.1.3', 'K8.3'], ['PG20-14.2'], 'Rencana perlakuan risiko'],
            ['APO13.01', ['K4.3', 'K5.2', 'K6.2', 'A.5.1'], ['BSSN4-3.1', 'PG20-3'], 'Ruang lingkup SMKI, kebijakan keamanan'],
            ['APO13.03', ['K9.1', 'K9.2', 'K9.3', 'A.5.35', 'A.5.36'], ['BSSN4-15', 'PG20-12'], 'Laporan evaluasi kinerja tahunan'],
            ['DSS04.03', ['A.5.29', 'A.5.30'], ['PG20-7.1', 'PG20-15.2s'], 'Dokumen BCP'],
            ['DSS04.07', ['A.8.13', 'A.8.14'], ['PG20-15.2t'], 'Jadwal dan uji pemulihan cadangan'],
            ['DSS05.01', ['A.8.7'], ['PG20-15.2h'], 'Kebijakan dan log antimalware'],
            ['DSS05.02', ['A.8.20', 'A.8.21', 'A.8.22'], ['PG20-15.2b'], 'Diagram dan segmentasi jaringan'],
            ['DSS05.04', ['A.5.15', 'A.5.16', 'A.5.17', 'A.5.18', 'A.8.2', 'A.8.5'], ['PG20-15.2g'], 'Prosedur hak akses, tinjauan akun'],
            ['DSS05.07', ['A.8.8', 'A.8.15', 'A.8.16'], ['BSSN4-8.3b'], 'Laporan pemindaian kerentanan, log pemantauan'],
            ['MEA03.01', ['A.5.31', 'A.5.34'], ['PP95-41.1', 'PG20-15.2m'], 'Daftar kewajiban regulasi'],
            ['DSS05.05', ['A.8.12'], ['PG20-15.2c'], 'Contoh butir yang kurang tepat (untuk uji Delphi)'],
        ];
        $hasil = [];
        foreach ($data as [$kode, $iso, $reg, $bukti]) {
            $hasil[$kode] = Pemetaan::create([
                'evaluasi_id' => $evaluasi->id, 'praktik_id' => $p[$kode], 'iso_items' => $iso,
                'regulasi_items' => $reg, 'bukti_diharapkan' => $bukti, 'status' => Pemetaan::STATUS_DRAF,
            ]);
        }

        return $hasil;
    }

    private function delphi(Evaluasi $evaluasi, array $pemetaans): void
    {
        $pakars = collect([
            ['Pakar A (contoh)', 'Perguruan tinggi', 'Tata kelola TI'],
            ['Pakar B (contoh)', 'Konsultan', 'Lead Auditor ISO/IEC 27001'],
            ['Pakar C (contoh)', 'Instansi pemerintah', 'Keamanan SPBE'],
            ['Pakar D (contoh)', 'Perguruan tinggi', 'Keamanan informasi'],
            ['Pakar E (contoh)', 'Instansi pemerintah', 'COBIT 2019 Foundation'],
        ])->map(function ($x, $i) use ($evaluasi) {
            $user = $this->akunSimulasi('pakar'.($i + 1).'.simulasi@example.test', '[SIMULASI] '.$x[0], RoleModul::PAKAR);

            return $evaluasi->pakars()->create(['user_id' => $user->id, 'instansi' => $x[1], 'keahlian' => $x[2]]);
        });

        // Putaran 1: semua disepakati kecuali DSS05.05 (2 dari 5 setuju) dan DSS04.07 (3 dari 5)
        $p1 = Putaran::create(['evaluasi_id' => $evaluasi->id, 'nomor' => 1, 'status' => Putaran::STATUS_DITUTUP, 'dibuka_at' => now()->subDays(14), 'ditutup_at' => now()->subDays(7)]);
        foreach ($pemetaans as $kode => $pm) {
            $setuju = ['DSS05.05' => 2, 'DSS04.07' => 3][$kode] ?? 5;
            foreach ($pakars as $i => $pk) {
                Penilaian::create([
                    'putaran_id' => $p1->id, 'pakar_id' => $pk->id, 'pemetaan_id' => $pm->id, 'setuju' => $i < $setuju,
                    'saran' => $i >= $setuju ? ($kode === 'DSS05.05' ? 'Data leakage prevention kurang tepat untuk akses fisik; gunakan kontrol fisik 7.x.' : 'Tambahkan klausul atau kontrol pendukung.') : null,
                ]);
            }
            if ($setuju === 5) {
                $pm->update(['status' => Pemetaan::STATUS_DISEPAKATI, 'putaran_disepakati' => 1]);
            }
        }

        // Revisi setelah putaran 1
        $pemetaans['DSS05.05']->update(['iso_items' => ['A.7.1', 'A.7.2', 'A.7.4'], 'catatan_revisi' => 'Diganti ke kontrol fisik sesuai saran pakar.', 'bukti_diharapkan' => 'Log akses ruang server, CCTV']);
        $pemetaans['DSS04.07']->update(['iso_items' => ['A.8.13', 'A.8.14', 'A.5.30'], 'catatan_revisi' => 'Ditambah 5.30 ICT readiness.']);

        // Putaran 2 (ditutup): keduanya disepakati
        $p2 = Putaran::create(['evaluasi_id' => $evaluasi->id, 'nomor' => 2, 'status' => Putaran::STATUS_DITUTUP, 'dibuka_at' => now()->subDays(6), 'ditutup_at' => now()->subDay()]);
        foreach (['DSS05.05', 'DSS04.07'] as $kode) {
            foreach ($pakars as $i => $pk) {
                Penilaian::create(['putaran_id' => $p2->id, 'pakar_id' => $pk->id, 'pemetaan_id' => $pemetaans[$kode]->id, 'setuju' => $i < 4]);
            }
            $pemetaans[$kode]->update(['status' => Pemetaan::STATUS_DISEPAKATI, 'putaran_disepakati' => 2]);
        }
    }

    private function asesmen(Evaluasi $evaluasi): void
    {
        // [kode praktik, level, contoh aktivitas, terpenuhi?]
        $contoh = [
            ['EDM03.01', 2, '[contoh] Pimpinan menetapkan arah manajemen risiko I&T', true],
            ['EDM03.02', 2, '[contoh] Pimpinan menetapkan selera dan toleransi risiko', false],
            ['EDM03.03', 3, '[contoh] Laporan risiko dipantau secara berkala', false],
            ['APO12.01', 2, '[contoh] Data kejadian risiko dikumpulkan', true],
            ['APO12.02', 2, '[contoh] Risiko dianalisis dampak dan kemungkinannya', true],
            ['APO12.03', 2, '[contoh] Profil risiko dipelihara', true],
            ['APO12.06', 2, '[contoh] Respons risiko dijalankan', true],
            ['APO12.03', 3, '[contoh] Profil risiko diperbarui sesuai perubahan', false],
            ['APO12.05', 3, '[contoh] Portofolio tindakan risiko ditetapkan', false],
            ['APO13.01', 2, '[contoh] Ruang lingkup SMKI ditetapkan', true],
            ['APO13.01', 2, '[contoh] Kebijakan keamanan informasi disahkan', true],
            ['APO13.02', 2, '[contoh] Rencana perlakuan risiko keamanan disusun', false],
            ['APO13.03', 3, '[contoh] SMKI ditinjau berkala', false],
            ['DSS04.01', 2, '[contoh] Kebijakan kelangsungan layanan ditetapkan', false],
            ['DSS04.07', 2, '[contoh] Cadangan data dibuat terjadwal', true],
            ['DSS04.04', 3, '[contoh] Uji pemulihan dilakukan', false],
            ['DSS05.01', 2, '[contoh] Antimalware terpasang pada perangkat', true],
            ['DSS05.02', 2, '[contoh] Jaringan disegmentasi', true],
            ['DSS05.04', 2, '[contoh] Hak akses ditinjau berkala', true],
            ['DSS05.05', 2, '[contoh] Akses ruang server dicatat', true],
            ['DSS05.07', 2, '[contoh] Pemindaian kerentanan dilakukan', true],
            ['DSS05.07', 3, '[contoh] Peristiwa keamanan dipantau terpusat', false],
            ['DSS05.02', 3, '[contoh] Konfigurasi keamanan jaringan didokumentasikan', true],
            ['MEA03.01', 2, '[contoh] Kewajiban regulasi diidentifikasi', true],
            ['MEA03.03', 2, '[contoh] Kepatuhan dikonfirmasi', false],
        ];
        $praktik = Praktik::where('evaluasi_id', $evaluasi->id)->pluck('id', 'kode');
        foreach ($contoh as $i => [$kode, $level, $uraian, $ok]) {
            Aktivitas::create([
                'praktik_id' => $praktik[$kode], 'level' => $level, 'uraian' => $uraian, 'terpenuhi' => $ok,
                'bukti_ditemukan' => $ok ? '[contoh] dokumen ada' : '[contoh] belum ada bukti', 'sumber_bukti' => ['dokumen', 'wawancara'],
                'informan' => 'Informan (contoh)', 'dinilai_at' => now(), 'urutan' => $i,
            ]);
        }
    }

    private function kontrol(Evaluasi $evaluasi): void
    {
        $status = [
            'A.5.1' => 'diterapkan', 'A.5.7' => 'belum', 'A.5.15' => 'diterapkan', 'A.5.16' => 'sebagian', 'A.5.17' => 'diterapkan',
            'A.5.18' => 'sebagian', 'A.5.29' => 'belum', 'A.5.30' => 'belum', 'A.5.31' => 'diterapkan', 'A.5.34' => 'sebagian',
            'A.5.35' => 'belum', 'A.5.36' => 'sebagian', 'A.7.1' => 'diterapkan', 'A.7.2' => 'diterapkan', 'A.7.4' => 'sebagian',
            'A.8.2' => 'sebagian', 'A.8.5' => 'diterapkan', 'A.8.7' => 'diterapkan', 'A.8.8' => 'sebagian', 'A.8.13' => 'diterapkan',
            'A.8.14' => 'belum', 'A.8.15' => 'sebagian', 'A.8.16' => 'belum', 'A.8.20' => 'diterapkan', 'A.8.21' => 'diterapkan', 'A.8.22' => 'sebagian',
        ];
        foreach ($status as $kode => $s) {
            Kontrol::create(['evaluasi_id' => $evaluasi->id, 'iso_kode' => $kode, 'status' => $s, 'bukti' => '[contoh]']);
        }
    }

    private function rekomendasi(Evaluasi $evaluasi): void
    {
        $praktik = Praktik::where('evaluasi_id', $evaluasi->id)->get()->keyBy('kode');
        $data = [
            ['APO12.03', null, '[contoh] Menyusun dan menetapkan daftar risiko keamanan informasi instansi sesuai Pergub 20/2024 Pasal 14', 'pendek', 1],
            ['DSS04.01', 'A.5.30', '[contoh] Menyusun BCP/DRP layanan TIK prioritas', 'pendek', 2],
            ['EDM03.02', null, '[contoh] Penetapan selera risiko oleh pimpinan', 'menengah', 1],
            ['DSS05.07', 'A.8.16', '[contoh] Membangun pemantauan peristiwa keamanan terpusat', 'menengah', 2],
            ['APO13.03', 'A.5.35', '[contoh] Audit internal keamanan SPBE tahunan oleh pihak independen', 'panjang', 1],
        ];
        foreach ($data as [$kp, $iso, $uraian, $h, $prio]) {
            $evaluasi->rekomendasis()->create([
                'praktik_id' => $praktik[$kp]->id, 'objektif_kode' => $praktik[$kp]->objektif_kode, 'iso_kode' => $iso,
                'uraian' => $uraian, 'horizon' => $h, 'prioritas' => $prio, 'penanggung_jawab' => 'Bidang Persandian (contoh)',
            ]);
        }
    }
}
