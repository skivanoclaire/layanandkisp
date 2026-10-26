<?php

use App\Services\MasterArsitektur\Skema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kolom seperti Status, Status Reviu, Target/Metode Layanan, dan Klasifikasi Sistem
 * kini berupa dropdown. Samakan penulisan data lama dengan opsi baku
 * ("elektronik " => "Elektronik"); nilai di luar opsi dibiarkan apa adanya.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (array_keys(Skema::jenis()) as $jenis) {
            $pilihan = array_filter(Skema::kolom($jenis), fn ($kolom) => Skema::opsi($kolom) !== []);

            if ($pilihan === []) {
                continue;
            }

            DB::table('master_arsitektur_items')->where('jenis', $jenis)->orderBy('id')
                ->chunkById(500, function ($baris) use ($pilihan) {
                    foreach ($baris as $b) {
                        $ubah = [];
                        $atribut = $b->atribut ? json_decode($b->atribut, true) : [];
                        $atributBerubah = false;

                        foreach ($pilihan as $kunci => $kolom) {
                            if (in_array($kunci, Skema::INTI, true)) {
                                $baku = Skema::bakukan($kolom, $b->$kunci);
                                if ($baku !== $b->$kunci) {
                                    $ubah[$kunci] = $baku;
                                }
                            } elseif (isset($atribut[$kunci])) {
                                $baku = Skema::bakukan($kolom, (string) $atribut[$kunci]);
                                if ($baku !== $atribut[$kunci]) {
                                    $atribut[$kunci] = $baku;
                                    $atributBerubah = true;
                                }
                            }
                        }

                        if ($atributBerubah) {
                            $ubah['atribut'] = json_encode($atribut, JSON_UNESCAPED_UNICODE);
                        }

                        if ($ubah) {
                            DB::table('master_arsitektur_items')->where('id', $b->id)->update($ubah);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Penyeragaman penulisan tidak dikembalikan
    }
};
