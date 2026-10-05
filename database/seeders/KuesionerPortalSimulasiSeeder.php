<?php

namespace Database\Seeders;

use App\Models\KuesionerPortalPeriode;
use App\Models\KuesionerPortalResponse;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\KuesionerPortal\Instrumen;
use App\Services\KuesionerPortal\Kelayakan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data simulasi modul Kuesioner Kualitas Layanan Portal (E-GovQual + IPA + Kano).
 *
 * Responden diambil dari akun pemohon yang SUDAH ADA di database (kriteria inklusi
 * yang sama dengan modul), lengkap dengan perangkat daerah dan layanan yang benar-benar
 * pernah mereka ajukan. Tidak ada akun baru yang dibuat.
 *
 * Jawaban dibangkitkan dengan pola yang sengaja beragam agar seluruh bagian halaman
 * analisis terisi: atribut di keempat kuadran IPA, kategori Kano A/O/M/I, kategori
 * campuran pada uji Fong, serta beberapa respons yang layak tersaring pra-pemrosesan.
 *
 * Jalankan:  php artisan db:seed --class=KuesionerPortalSimulasiSeeder
 * Hapus:     php artisan tinker --execute="App\Models\KuesionerPortalPeriode::where('nama', 'Simulasi (data uji)')->delete();"
 *
 * Idempoten dan deterministik (seed acak tetap): setiap kali dijalankan, periode
 * simulasi dikosongkan lalu diisi ulang dengan hasil yang sama. Periode lain tidak disentuh.
 */
class KuesionerPortalSimulasiSeeder extends Seeder
{
    public const NAMA_PERIODE = 'Simulasi (data uji)';
    private const SEED = 20261004;

    /**
     * Profil setiap atribut: [rata-rata kepentingan, rata-rata kinerja, bobot Kano A/O/M/I/R/Q].
     * Pola sengaja dibuat agar setiap kombinasi Tabel 3.5 muncul.
     */
    private const PROFIL_ATRIBUT = [
        // Efficiency
        'EF1' => [4.3, 3.9, ['A' => 15, 'O' => 35, 'M' => 30, 'I' => 15, 'R' => 2, 'Q' => 3]],
        'EF2' => [3.8, 3.1, ['A' => 50, 'O' => 15, 'M' => 5, 'I' => 25, 'R' => 2, 'Q' => 3]],   // Kuadran III, attractive
        'EF3' => [3.9, 4.0, ['A' => 45, 'O' => 15, 'M' => 10, 'I' => 25, 'R' => 2, 'Q' => 3]],  // Kuadran IV, attractive
        'EF4' => [4.1, 3.6, ['A' => 40, 'O' => 25, 'M' => 10, 'I' => 20, 'R' => 2, 'Q' => 3]],
        'EF5' => [4.5, 3.4, ['A' => 10, 'O' => 50, 'M' => 25, 'I' => 12, 'R' => 1, 'Q' => 2]],  // Kuadran I, one-dimensional
        'EF6' => [3.7, 3.9, ['A' => 15, 'O' => 10, 'M' => 10, 'I' => 60, 'R' => 2, 'Q' => 3]],  // Kuadran IV, indifferent
        'EF7' => [4.4, 3.5, ['A' => 8, 'O' => 38, 'M' => 40, 'I' => 11, 'R' => 1, 'Q' => 2]],   // M/O tipis → uji Fong
        // Trust
        'TR1' => [4.6, 4.3, ['A' => 5, 'O' => 30, 'M' => 55, 'I' => 7, 'R' => 1, 'Q' => 2]],    // Kuadran II, must-be
        'TR2' => [3.9, 3.6, ['A' => 5, 'O' => 15, 'M' => 60, 'I' => 17, 'R' => 1, 'Q' => 2]],   // Kuadran III, must-be → naik prioritas
        'TR3' => [4.7, 3.55, ['A' => 3, 'O' => 22, 'M' => 65, 'I' => 7, 'R' => 1, 'Q' => 2]],    // Kuadran I, must-be
        'TR4' => [4.4, 4.1, ['A' => 5, 'O' => 25, 'M' => 55, 'I' => 12, 'R' => 1, 'Q' => 2]],
        // Reliability
        'RL1' => [3.9, 4.1, ['A' => 20, 'O' => 25, 'M' => 15, 'I' => 35, 'R' => 2, 'Q' => 3]],
        'RL2' => [4.7, 3.6, ['A' => 3, 'O' => 25, 'M' => 62, 'I' => 7, 'R' => 1, 'Q' => 2]],    // Kuadran I, must-be
        'RL3' => [4.5, 4.2, ['A' => 5, 'O' => 35, 'M' => 50, 'I' => 7, 'R' => 1, 'Q' => 2]],    // Kuadran II
        'RL4' => [4.6, 3.3, ['A' => 5, 'O' => 55, 'M' => 30, 'I' => 7, 'R' => 1, 'Q' => 2]],    // Kuadran I, one-dimensional, gap terbesar
        'RL5' => [4.0, 3.8, ['A' => 15, 'O' => 40, 'M' => 20, 'I' => 22, 'R' => 1, 'Q' => 2]],
        'RL6' => [4.0, 4.2, ['A' => 10, 'O' => 25, 'M' => 30, 'I' => 32, 'R' => 1, 'Q' => 2]],
        // Citizen Support
        'CS1' => [4.3, 3.8, ['A' => 20, 'O' => 45, 'M' => 20, 'I' => 12, 'R' => 1, 'Q' => 2]],
        'CS2' => [4.5, 3.2, ['A' => 10, 'O' => 55, 'M' => 25, 'I' => 7, 'R' => 1, 'Q' => 2]],   // Kuadran I, one-dimensional
        'CS3' => [4.2, 4.1, ['A' => 25, 'O' => 40, 'M' => 15, 'I' => 17, 'R' => 1, 'Q' => 2]],
        'CS4' => [4.0, 3.9, ['A' => 45, 'O' => 25, 'M' => 10, 'I' => 17, 'R' => 1, 'Q' => 2]],
    ];

    /** Pasangan jawaban (fungsional, disfungsional) yang menghasilkan setiap kategori pada Tabel 2.3. */
    private const PASANGAN = [
        'A' => [[1, 2], [1, 3], [1, 4]],
        'O' => [[1, 5]],
        'M' => [[2, 5], [3, 5], [4, 5]],
        'I' => [[2, 3], [3, 3], [3, 2], [2, 2], [4, 3], [3, 4], [2, 4], [4, 4], [4, 2]],
        'R' => [[5, 1], [5, 3], [3, 1]],
        'Q' => [[1, 1], [5, 5]],
    ];

    private const KELEBIHAN = [
        'Semua layanan TIK cukup diajukan dari satu tempat.',
        'Status permohonan bisa dipantau tanpa harus menelepon.',
        'Login SSO memudahkan, tidak perlu banyak akun.',
        'Formulir cukup jelas dan tampilannya rapi.',
        'Petugas cepat membalas lewat WhatsApp.',
    ];

    private const KEKURANGAN = [
        'Kadang tidak ada kabar setelah permohonan dikirim.',
        'Penyelesaian permohonan melewati waktu yang dijanjikan.',
        'Persyaratan dokumen tidak dijelaskan sejak awal.',
        'Halaman lambat dibuka saat jam kerja.',
        'Fitur pencarian layanan kurang membantu.',
        'Sulit menghubungi petugas saat ada kendala.',
    ];

    private const SARAN = [
        'Tambahkan notifikasi otomatis setiap status berubah.',
        'Cantumkan estimasi waktu penyelesaian di setiap layanan.',
        'Sediakan panduan singkat atau video tutorial.',
        'Perbaiki kecepatan akses portal.',
        'Sediakan kanal bantuan yang jelas jam layanannya.',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('KuesionerPortalSimulasiSeeder berisi data fiktif dan tidak boleh dijalankan di produksi.');

            return;
        }

        $userIds = app(Kelayakan::class)->rekapPopulasi()['user_ids'];
        if (! $userIds) {
            $this->command?->warn('Tidak ada akun pemohon di database. Tidak ada yang disimulasikan.');

            return;
        }

        mt_srand(self::SEED);

        $users = User::whereIn('id', $userIds)->orderBy('id')->get(['id', 'unit_kerja_id']);
        $layananPerAkun = $this->layananPerAkun();
        $unitCadangan = UnitKerja::where('is_active', true)->orderBy('id')->pluck('id')->all();
        $kodeAtribut = Instrumen::kodeAtribut();
        $bersyarat = Instrumen::kodeKinerjaBersyarat();

        DB::transaction(function () use ($users, $layananPerAkun, $unitCadangan, $kodeAtribut, $bersyarat) {
            $periode = KuesionerPortalPeriode::firstOrCreate(
                ['nama' => self::NAMA_PERIODE],
                ['keterangan' => 'Data fiktif dari KuesionerPortalSimulasiSeeder. Jangan dipakai untuk tesis.']
            );
            // Periode simulasi tidak dibuka agar tidak mengganggu periode sungguhan.
            $periode->update([
                'is_active' => false,
                'dibuka_at' => now()->subDays(21)->startOfDay(),
                'ditutup_at' => now(),
            ]);
            $periode->responses()->delete();

            $statistik = ['selesai' => 0, 'menolak' => 0, 'belum' => 0];
            $khusus = [];
            $nomor = 0;

            foreach ($users as $user) {
                $nomor++;
                $kode = sprintf('SIM-%03d', $nomor);
                $mulai = Carbon::instance(now()->subDays(21)->startOfDay())
                    ->addMinutes(mt_rand(0, 20 * 24 * 60));

                // Sekitar 8% menolak dan 7% berhenti di tengah, sisanya selesai.
                $undian = mt_rand(1, 100);
                if ($undian <= 8) {
                    $this->buatRespons($periode, $user, $kode, KuesionerPortalResponse::STATUS_MENOLAK, ['persetujuan_at' => null]);
                    $statistik['menolak']++;

                    continue;
                }
                if ($undian <= 15) {
                    $this->buatRespons($periode, $user, $kode, KuesionerPortalResponse::STATUS_PERSETUJU, ['persetujuan_at' => $mulai]);
                    $statistik['belum']++;

                    continue;
                }

                // Beberapa responden selesai sengaja bermasalah agar aturan pra-pemrosesan teruji.
                $jenis = match ($statistik['selesai'] + 1) {
                    5, 40 => 'seragam',
                    12, 52 => 'kilat',
                    23, 61 => 'questionable',
                    default => 'normal',
                };
                if ($jenis !== 'normal') {
                    $khusus[$jenis][] = $kode;
                }

                $hubungi = mt_rand(1, 100) <= 55;
                $durasi = $jenis === 'kilat' ? mt_rand(90, 200) : max(420, (int) round($this->normal(1050, 330)));
                $layanan = $layananPerAkun[$user->id] ?? ['lainnya'];

                $respons = $this->buatRespons($periode, $user, $kode, KuesionerPortalResponse::STATUS_SELESAI, [
                    'persetujuan_at' => $mulai,
                    'submitted_at' => $mulai->copy()->addSeconds($durasi),
                    'durasi_detik' => $durasi,
                    'unit_kerja_id' => $user->unit_kerja_id ?? $unitCadangan[array_rand($unitCadangan)],
                    'status_kepegawaian' => $this->pilih(['pns' => 60, 'pppk' => 15, 'non_asn' => 25]),
                    'peran' => $this->pilih(['operator' => 55, 'pejabat' => 10, 'staf' => 35]),
                    // Portal beroperasi sejak November 2025, jadi belum ada pengguna di atas satu tahun.
                    'lama_penggunaan' => $this->pilih(['lt6b' => 40, '6_12b' => 60]),
                    'frekuensi' => $this->pilih(['mingguan' => 20, 'bulanan' => 45, 'tahunan' => 35]),
                    'layanan_diajukan' => $layanan,
                    'layanan_lainnya' => in_array('lainnya', $layanan, true) ? 'DTSEN' : null,
                    'pernah_hubungi_petugas' => $hubungi,
                    'kelebihan' => mt_rand(1, 100) <= 70 ? self::KELEBIHAN[array_rand(self::KELEBIHAN)] : null,
                    'kekurangan' => mt_rand(1, 100) <= 75 ? self::KEKURANGAN[array_rand(self::KEKURANGAN)] : null,
                    'saran' => mt_rand(1, 100) <= 80 ? self::SARAN[array_rand(self::SARAN)] : null,
                ]);

                // Kelonggaran penilaian per responden membuat butir saling berkorelasi,
                // sebagaimana data kuesioner sungguhan (dibutuhkan agar Cronbach's alpha bermakna).
                $longgarKepentingan = $this->normal(0, 0.35);
                $longgarKinerja = $this->normal(0, 0.45);
                $jawaban = [];
                $kinerjaResponden = [];

                foreach ($kodeAtribut as $kodeAtr) {
                    [$rataImp, $rataPerf, $bobotKano] = self::PROFIL_ATRIBUT[$kodeAtr];
                    $isiKinerja = ! in_array($kodeAtr, $bersyarat, true) || $hubungi;

                    $imp = $this->skala($rataImp + $longgarKepentingan + $this->normal(0, 0.55));
                    $perf = $isiKinerja ? $this->skala($rataPerf + $longgarKinerja + $this->normal(0, 0.6)) : null;

                    if ($jenis === 'seragam') {
                        $imp = 4;
                        $perf = $isiKinerja ? 4 : null;
                    }

                    $kategori = $jenis === 'questionable' && mt_rand(1, 100) <= 40 ? 'Q' : $this->pilih($bobotKano);
                    $pasangan = self::PASANGAN[$kategori];
                    [$f, $d] = $pasangan[array_rand($pasangan)];

                    if ($perf !== null) {
                        $kinerjaResponden[] = $perf;
                    }
                    $jawaban[] = [
                        'response_id' => $respons->id,
                        'kode' => $kodeAtr,
                        'kepentingan' => $imp,
                        'kinerja' => $perf,
                        'kano_fungsional' => $f,
                        'kano_disfungsional' => $d,
                        'created_at' => $respons->submitted_at,
                        'updated_at' => $respons->submitted_at,
                    ];
                }

                // Kepuasan mengikuti rata-rata kinerja responden tersebut.
                $dasarKepuasan = array_sum($kinerjaResponden) / max(1, count($kinerjaResponden));
                foreach (Instrumen::kodeKepuasan() as $kodeKps) {
                    $jawaban[] = [
                        'response_id' => $respons->id,
                        'kode' => $kodeKps,
                        'kepentingan' => null,
                        'kinerja' => $jenis === 'seragam' ? 4 : $this->skala($dasarKepuasan + $this->normal(0, 0.45)),
                        'kano_fungsional' => null,
                        'kano_disfungsional' => null,
                        'created_at' => $respons->submitted_at,
                        'updated_at' => $respons->submitted_at,
                    ];
                }

                DB::table('kuesioner_portal_answers')->insert($jawaban);
                $statistik['selesai']++;
            }

            $this->command?->info(sprintf(
                'Periode "%s": %d akun pemohon → %d selesai, %d belum selesai, %d menolak.',
                self::NAMA_PERIODE, $users->count(), $statistik['selesai'], $statistik['belum'], $statistik['menolak']
            ));
            foreach (['seragam' => 'jawaban seragam', 'kilat' => 'terlalu cepat', 'questionable' => 'banyak jawaban Q'] as $jenis => $label) {
                $this->command?->line("Uji pra-pemrosesan ({$label}): " . implode(', ', $khusus[$jenis] ?? ['-']));
            }
            $this->command?->line('Buka: /admin/kuesioner-portal → periode "' . self::NAMA_PERIODE . '" → Analisis.');
        });
    }

    private function buatRespons(KuesionerPortalPeriode $periode, User $user, string $kode, string $status, array $atribut): KuesionerPortalResponse
    {
        return KuesionerPortalResponse::create(array_merge([
            'periode_id' => $periode->id,
            'user_id' => $user->id,
            'kode_responden' => $kode,
            'status' => $status,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'KuesionerPortalSimulasiSeeder',
        ], $atribut));
    }

    /**
     * Layanan yang benar-benar pernah diajukan setiap akun, dari tabel permohonan pada config.
     *
     * @return array<int, array<string>>
     */
    private function layananPerAkun(): array
    {
        $hasil = [];
        foreach (config('kuesioner_portal.tabel_permohonan') as $entri) {
            if (! Schema::hasTable($entri['tabel']) || ! Schema::hasColumn($entri['tabel'], 'user_id')) {
                continue;
            }
            $query = DB::table($entri['tabel'])->whereNotNull('user_id');
            foreach ($entri['kecuali'] ?? [] as $kolom => $nilai) {
                $query->where($kolom, '!=', $nilai);
            }
            foreach ($query->distinct()->pluck('user_id') as $id) {
                $hasil[$id][$entri['layanan']] = true;
            }
        }

        return array_map('array_keys', $hasil);
    }

    /** Bilangan acak berdistribusi normal (Box-Muller) dari mt_rand agar dapat diulang. */
    private function normal(float $rata, float $sd): float
    {
        $u1 = max(mt_rand() / mt_getrandmax(), 1e-12);
        $u2 = mt_rand() / mt_getrandmax();

        return $rata + $sd * sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
    }

    private function skala(float $nilai): int
    {
        return (int) max(1, min(5, round($nilai)));
    }

    /** Memilih kunci secara acak sesuai bobotnya. */
    private function pilih(array $bobot): string
    {
        $undian = mt_rand(1, array_sum($bobot));
        foreach ($bobot as $kunci => $b) {
            $undian -= $b;
            if ($undian <= 0) {
                return (string) $kunci;
            }
        }

        return (string) array_key_last($bobot);
    }
}
