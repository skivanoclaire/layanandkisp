<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master kategori knowledge base Konsultasi SPBE Berbasis AI.
     * Dipakai sebagai pilihan (dropdown) pada dokumen dasar dan pertanyaan contoh,
     * dengan taksonomi terpisah: dokumen (Regulasi/SOP/...) dan faq (Umum/TTE/SPLP/...).
     */
    public function up(): void
    {
        Schema::create('konsultasi_ai_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['dokumen', 'faq']);
            $table->string('nama', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tipe', 'nama']);
            $table->index(['tipe', 'is_active', 'urutan']);
        });

        $this->backfill('dokumen', 'konsultasi_ai_documents');
        $this->backfill('faq', 'konsultasi_ai_faqs');
        $this->seedDefaultDokumen();
    }

    /**
     * Angkat nilai kategori lama (input bebas) menjadi data master agar
     * dokumen dan pertanyaan yang sudah ada tetap cocok dengan pilihan baru.
     */
    private function backfill(string $tipe, string $tabel): void
    {
        if (! Schema::hasTable($tabel)) {
            return;
        }

        $sekarang = now();

        $nama = DB::table($tabel)
            ->whereNotNull('kategori')
            ->where('kategori', '<>', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $baris = [];
        $urutan = 0;

        foreach ($nama as $item) {
            $baris[] = [
                'tipe'       => $tipe,
                'nama'       => mb_substr(trim($item), 0, 100),
                'urutan'     => ++$urutan,
                'is_active'  => true,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        if ($baris !== []) {
            DB::table('konsultasi_ai_categories')->insertOrIgnore($baris);
        }
    }

    /**
     * Bila belum ada kategori dokumen sama sekali, sediakan pilihan awal
     * agar formulir unggah tidak kosong pada instalasi baru.
     */
    private function seedDefaultDokumen(): void
    {
        $sudahAda = DB::table('konsultasi_ai_categories')->where('tipe', 'dokumen')->exists();

        if ($sudahAda) {
            return;
        }

        $sekarang = now();
        $default = ['Regulasi', 'SOP', 'Panduan Teknis', 'Kebijakan Internal'];

        DB::table('konsultasi_ai_categories')->insert(array_map(fn ($nama, $i) => [
            'tipe'       => 'dokumen',
            'nama'       => $nama,
            'urutan'     => $i + 1,
            'is_active'  => true,
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ], $default, array_keys($default)));
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi_ai_categories');
    }
};
