<?php

namespace Database\Factories;

use App\Models\RekomendasiAplikasiForm;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RekomendasiAplikasiForm>
 */
class RekomendasiAplikasiFormFactory extends Factory
{
    protected $model = RekomendasiAplikasiForm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pemilik_proses_bisnis_id' => UnitKerja::factory(),
            'status' => 'draft',
            'fase_saat_ini' => 'usulan',

            // Required V2 fields
            'nama_aplikasi' => fake()->sentence(3),
            'deskripsi' => '<p>' . fake()->paragraph() . '</p>',
            'tujuan' => '<p>' . fake()->paragraph() . '</p>',
            'manfaat' => '<p>' . fake()->paragraph() . '</p>',
            'jenis_layanan' => fake()->randomElement(['publik', 'internal']),
            'target_pengguna' => fake()->words(3, true),
            'estimasi_pengguna' => fake()->numberBetween(100, 10000),
            'lingkup_aplikasi' => fake()->randomElement(['lokal', 'regional', 'nasional']),
            'platform' => fake()->randomElements(['web', 'mobile', 'desktop'], fake()->numberBetween(1, 3)),
            'teknologi_diusulkan' => fake()->randomElement(['Laravel', 'React', 'Vue.js', 'Flutter']),
            'estimasi_waktu_pengembangan' => fake()->numberBetween(1, 24),
            'estimasi_biaya' => fake()->randomFloat(2, 10000000, 500000000),
            'sumber_pendanaan' => fake()->randomElement(['apbd', 'apbn', 'hibah', 'swasta', 'lainnya']),
            'prioritas' => fake()->randomElement(['rendah', 'sedang', 'tinggi', 'sangat_tinggi']),

            // Optional fields
            'integrasi_sistem_lain' => fake()->randomElement(['ya', 'tidak']),
            'detail_integrasi' => fake()->optional()->paragraph(),
            'kebutuhan_khusus' => fake()->optional()->paragraph(),
            'dampak_tidak_dibangun' => fake()->optional()->paragraph(),

            // Permenkomdigi fields (optional)
            'dasar_hukum' => fake()->optional()->paragraph(),
            'uraian_permasalahan' => fake()->optional()->paragraph(),
            'pihak_terkait' => fake()->optional()->paragraph(),
            'ruang_lingkup' => fake()->optional()->paragraph(),
            'analisis_biaya_manfaat' => fake()->optional()->paragraph(),
            'lokasi_implementasi' => fake()->optional()->city(),

            // Perencanaan fields (optional)
            'uraian_ruang_lingkup' => fake()->optional()->paragraph(),
            'proses_bisnis' => fake()->optional()->paragraph(),
            'kerangka_kerja' => fake()->optional()->paragraph(),
            'pelaksana_pembangunan' => fake()->optional()->randomElement(['menteri', 'swakelola', 'pihak_ketiga']),
            'peran_tanggung_jawab' => fake()->optional()->paragraph(),
            'jadwal_pelaksanaan' => fake()->optional()->paragraph(),
            'rencana_aksi' => fake()->optional()->paragraph(),
            'keamanan_informasi' => fake()->optional()->paragraph(),
            'sumber_daya_manusia' => fake()->optional()->paragraph(),
            'sumber_daya_anggaran' => fake()->optional()->paragraph(),
            'sumber_daya_sarana' => fake()->optional()->paragraph(),
            'indikator_keberhasilan' => fake()->optional()->paragraph(),
            'alih_pengetahuan' => fake()->optional()->paragraph(),
            'pemantauan_pelaporan' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the proposal is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diajukan',
            'fase_saat_ini' => 'verifikasi',
        ]);
    }

    /**
     * Indicate that the proposal is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disetujui',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the proposal is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditolak',
            'rejected_by' => User::factory(),
            'rejected_at' => now(),
            'admin_feedback' => fake()->paragraph(),
        ]);
    }

    /**
     * Set specific pelaksana_pembangunan value.
     */
    public function withPelaksanaMenteri(): static
    {
        return $this->state(fn (array $attributes) => [
            'pelaksana_pembangunan' => 'menteri',
        ]);
    }

    /**
     * Set pelaksana_pembangunan to swakelola.
     */
    public function withPelaksanaSwakelola(): static
    {
        return $this->state(fn (array $attributes) => [
            'pelaksana_pembangunan' => 'swakelola',
        ]);
    }

    /**
     * Set pelaksana_pembangunan to pihak_ketiga.
     */
    public function withPelaksanaPihakKetiga(): static
    {
        return $this->state(fn (array $attributes) => [
            'pelaksana_pembangunan' => 'pihak_ketiga',
        ]);
    }
}
