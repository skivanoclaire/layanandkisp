<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengganti katalog variabel awal (35 variabel, termasuk kategori Agregat) dengan
     * "Katalog Variabel BNBA/DTSEN Kaltara" (APTIKA DKISP, 5 Okt 2026): 100 variabel
     * dalam dua set yang terhubung lewat nomor kartu keluarga — Keluarga (52) dan
     * Anggota (48). Dataset agregat tidak dilayani lewat permohonan, jadi tidak dimuat.
     *
     * Sensitivitas menentukan level hak akses minimal:
     * terbuka → L2, quasi-identifier → L3, data pribadi → L4.
     */
    private const SKALA_DISABILITAS = 'Sama sekali tidak bisa; Banyak kesulitan dan membutuhkan bantuan; '
        . 'Sedikit kesulitan tanpa bantuan; Tidak mengalami kesulitan';

    private const LEVEL = ['terbuka' => 2, 'quasi_identifier' => 3, 'data_pribadi' => 4];

    /** Kode katalog lama yang digantikan. */
    private const KODE_LAMA = [
        'DTSEN-001', 'DTSEN-002', 'DTSEN-003', 'DTSEN-004', 'DTSEN-005', 'DTSEN-006',
        'DTSEN-010', 'DTSEN-011', 'DTSEN-012', 'DTSEN-013', 'DTSEN-014',
        'DTSEN-020', 'DTSEN-021', 'DTSEN-030', 'DTSEN-031', 'DTSEN-040', 'DTSEN-041',
        'DTSEN-050', 'DTSEN-051', 'DTSEN-052', 'DTSEN-053', 'DTSEN-054', 'DTSEN-055', 'DTSEN-056', 'DTSEN-057',
        'DTSEN-060', 'DTSEN-061', 'DTSEN-070', 'DTSEN-071', 'DTSEN-072',
        'DTSEN-080', 'DTSEN-081', 'DTSEN-082', 'DTSEN-090', 'DTSEN-091',
    ];

    /** [kategori, variabel, definisi, nilai/kode, sensitivitas, bisa filter] */
    private function keluarga(): array
    {
        return [
            ['Wilayah', 'kode_provinsi', 'Kode provinsi pada DTSEN', '65', 'terbuka', true],
            ['Wilayah', 'provinsi', 'Nama provinsi', 'teks', 'terbuka', false],
            ['Wilayah', 'kode_kabupaten_kota', 'Kode kabupaten/kota pada DTSEN', '4 digit', 'terbuka', true],
            ['Wilayah', 'kabupaten_kota', 'Nama kabupaten/kota', 'teks', 'terbuka', false],
            ['Wilayah', 'kode_kecamatan', 'Kode kecamatan pada DTSEN', '7 digit', 'terbuka', true],
            ['Wilayah', 'kecamatan', 'Nama kecamatan', 'teks', 'terbuka', false],
            ['Wilayah', 'kode_kelurahan_desa', 'Kode kelurahan/desa pada DTSEN', '10 digit', 'quasi_identifier', true],
            ['Wilayah', 'kelurahan_desa', 'Nama kelurahan/desa', 'teks', 'quasi_identifier', false],

            ['Identitas', 'alamat', 'Alamat domisili', 'teks', 'data_pribadi', false],
            ['Identitas', 'nomor_kartu_keluarga', 'Nomor KK yang tercatat di Dukcapil; kunci gabung ke set Anggota', '16 digit', 'data_pribadi', false],
            ['Identitas', 'nama_anggota_keluarga', 'Nama kepala keluarga', 'teks', 'data_pribadi', false],
            ['Identitas', 'id_pelanggan_pln', 'Nomor ID pelanggan PLN rumah yang dihuni', 'teks', 'data_pribadi', false],

            ['Kesejahteraan', 'jumlah_anggota_keluarga', 'Jumlah individu dalam keluarga', 'angka', 'terbuka', false],
            ['Kesejahteraan', 'desil_nasional', 'Desil kesejahteraan keluarga tingkat nasional', '1-10', 'terbuka', true],
            ['Kesejahteraan', 'desil_provinsi', 'Desil kesejahteraan keluarga tingkat provinsi', '1-10', 'terbuka', true],
            ['Kesejahteraan', 'desil_kabupaten_kota', 'Desil kesejahteraan keluarga tingkat kabupaten/kota', '1-10', 'terbuka', true],
            ['Kesejahteraan', 'pbi_nas', 'Penerima bantuan iuran (PBI) nasional', 'Ya/Tidak', 'terbuka', true],
            ['Kesejahteraan', 'pbi_pemda', 'Penerima bantuan iuran (PBI) pemda', 'Ya/Tidak', 'terbuka', true],

            ['Perumahan', 'status_kepemilikan_rumah', 'Status kepemilikan rumah yang dihuni', 'Milik sendiri; Kontrak/sewa; Bebas sewa; Dinas; Lainnya', 'terbuka', true],
            ['Perumahan', 'jenis_lantai_terluas', 'Jenis lantai terluas', 'Marmer/granit; Keramik; Parket/vinil/karpet; Ubin/tegel/teraso; Kayu/papan; Semen/bata merah; Bambu; Tanah; Lainnya; Keramik/granit/marmer/ubin/tegel/teraso', 'terbuka', true],
            ['Perumahan', 'luas_lantai', 'Luas lantai bangunan tempat tinggal (m2)', 'angka', 'terbuka', false],
            ['Perumahan', 'jenis_dinding_terluas', 'Jenis dinding terluas', 'Tembok; Plesteran anyaman bambu/kawat; Kayu/papan/gypsum/GRC; Anyaman bambu; Batang kayu; Bambu; Lainnya', 'terbuka', true],
            ['Perumahan', 'jenis_atap_terluas', 'Jenis atap terluas', 'Beton; Genteng; Seng; Asbes; Bambu; Kayu/sirap; Jerami/ijuk/daun-daunan/rumbia; Lainnya; Asbes/seng', 'terbuka', true],
            ['Perumahan', 'sumber_air_minum_utama', 'Sumber air minum utama', 'Air kemasan bermerk; Air isi ulang; Leding; Sumur bor/pompa; Sumur terlindung; Sumur tak terlindung; Mata air terlindung; Mata air tak terlindung; Air permukaan (sungai/danau/waduk/kolam/irigasi); Air hujan; Lainnya; Air kemasan/isi ulang', 'terbuka', true],
            ['Perumahan', 'sumber_penerangan_utama', 'Sumber penerangan utama', 'Listrik PLN dengan meteran; Listrik PLN tanpa meteran; Listrik non-PLN; Bukan listrik', 'terbuka', true],
            ['Perumahan', 'daya_terpasang', 'Daya listrik terpasang', '450 watt; 900 watt; 1.300 watt; 2.200 watt; > 2.200 watt; <= 900 watt; > 900 watt', 'terbuka', true],
            ['Perumahan', 'bahan_bakar_utama_memasak', 'Bahan bakar utama memasak', 'Tidak memasak di rumah; Listrik; Gas elpiji 5,5 kg/blue gaz; Gas elpiji 12 kg; Gas elpiji 3 kg; Gas kota/meteran PGN; Biogas; Minyak tanah; Briket; Arang; Kayu bakar; Lainnya; Listrik/gas', 'terbuka', true],
            ['Perumahan', 'fasilitas_bab', 'Fasilitas buang air besar', 'Ada, hanya keluarga sendiri; Ada, bersama keluarga tertentu; Ada, di MCK komunal; Ada, di MCK umum; Ada, tidak digunakan; Tidak ada fasilitas', 'terbuka', true],
            ['Perumahan', 'jenis_kloset', 'Jenis kloset', 'Leher angsa; Plengsengan dengan tutup; Plengsengan tanpa tutup; Cemplung/cubluk', 'terbuka', true],
            ['Perumahan', 'pembuangan_akhir_tinja', 'Tempat pembuangan akhir tinja', 'Tangki septik; IPAL; Kolam/sawah/sungai/danau/laut; Lubang tanah; Pantai/tanah lapang/kebun; Lainnya; Tanpa tangki septik', 'terbuka', true],

            ['Aset', 'kepemilikan_aset', 'Memiliki aset atau tidak', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_tabung_gas', 'Tabung gas minimal 5,5 kg', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_lemari_es', 'Lemari es/kulkas', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_ac', 'AC', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_pemanas_air', 'Pemanas air untuk mandi', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_telepon_rumah', 'Telepon rumah/PSTN', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_tv_datar', 'Televisi layar datar', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_emas_perhiasan', 'Perhiasan emas', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_komputer_laptop_tablet', 'Komputer/laptop/tablet', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_sepeda_motor', 'Sepeda motor', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_sepeda', 'Sepeda', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_mobil', 'Mobil', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_perahu', 'Perahu', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_kapal_perahu_motor', 'Kapal/perahu motor', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_bergerak_smartphone', 'Smartphone', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_tidak_bergerak_lahan_lainnya', 'Lahan selain yang dihuni', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'aset_tidak_bergerak_rumah_lainnya', 'Rumah selain yang dihuni', 'Ya/Tidak', 'terbuka', true],
            ['Aset', 'jumlah_ternak_sapi', 'Jumlah ternak sapi', 'angka', 'terbuka', false],
            ['Aset', 'jumlah_ternak_kerbau', 'Jumlah ternak kerbau', 'angka', 'terbuka', false],
            ['Aset', 'jumlah_ternak_kuda', 'Jumlah ternak kuda', 'angka', 'terbuka', false],
            ['Aset', 'jumlah_ternak_babi', 'Jumlah ternak babi', 'angka', 'terbuka', false],
            ['Aset', 'jumlah_ternak_kambing_domba', 'Jumlah ternak kambing/domba', 'angka', 'terbuka', false],
        ];
    }

    /** [kategori, variabel, definisi, nilai/kode, sensitivitas, bisa filter] */
    private function anggota(): array
    {
        $kbli = 'Pertanian tanaman pangan; Hortikultura; Perkebunan; Perikanan; Peternakan; Kehutanan; '
            . 'Pertambangan; Industri pengolahan; Listrik/gas; Pengelolaan air/limbah; Konstruksi; Perdagangan; '
            . 'Pengangkutan; Akomodasi & makan minum; Informasi & komunikasi; Keuangan; Real estat; Jasa profesional; '
            . 'Jasa penunjang usaha; Administrasi pemerintahan; Pendidikan; Kesehatan; Kesenian/hiburan; Jasa lainnya; '
            . 'Keluarga sebagai pemberi kerja; Badan internasional';
        $jenjang = 'Paket A; SDLB; SD; MI; SPM/PDF Ula; Paket B; SMP LB; SMP; MTs; SPM/PDF Wustha; Paket C; SMLB; '
            . 'SMA; MA; SMK; MAK; SPM/PDF Ulya; D1/D2/D3; D4/S1; Profesi; S2; S3';
        $disabilitas = self::SKALA_DISABILITAS;

        return [
            ['Identitas', 'nomor_induk_kependudukan', 'NIK yang tercatat di Dukcapil', '16 digit', 'data_pribadi', false],
            ['Identitas', 'nomor_kartu_keluarga', 'Nomor KK yang tercatat di Dukcapil; kunci gabung ke set Keluarga', '16 digit', 'data_pribadi', false],
            ['Identitas', 'nama', 'Nama lengkap yang tercatat di Dukcapil', 'teks', 'data_pribadi', false],
            ['Identitas', 'tanggal_lahir', 'Tanggal lahir yang tercatat di Dukcapil', 'tanggal', 'data_pribadi', false],

            ['Demografi', 'jenis_kelamin', 'Jenis kelamin', 'Laki-laki; Perempuan', 'terbuka', true],
            ['Demografi', 'status_hubungan_keluarga', 'Hubungan dengan kepala keluarga', 'Kepala keluarga; Istri/suami; Anak; Menantu; Cucu; Orangtua/mertua; Pembantu/sopir; Lainnya', 'terbuka', true],
            ['Demografi', 'status_kawin', 'Status perkawinan', 'Belum kawin; Kawin/nikah; Cerai hidup; Cerai mati', 'terbuka', true],

            ['Kesejahteraan', 'pbi_nas', 'Penerima bantuan iuran (PBI) nasional', 'Ya/Tidak', 'terbuka', true],
            ['Kesejahteraan', 'pbi_pemda', 'Penerima bantuan iuran (PBI) pemda', 'Ya/Tidak', 'terbuka', true],

            ['Pendidikan', 'partisipasi_sekolah', 'Status partisipasi sekolah', 'Tidak/belum pernah sekolah; Masih sekolah; Tidak bersekolah lagi', 'terbuka', true],
            ['Pendidikan', 'jenjang_tertinggi_yang_diduduki', 'Jenjang pendidikan tertinggi yang pernah/sedang diduduki', "22 kode: {$jenjang}", 'terbuka', true],
            ['Pendidikan', 'kelas_tertinggi_yang_diduduki', 'Kelas tertinggi yang pernah/sedang diduduki', 'angka', 'terbuka', false],
            ['Pendidikan', 'ijazah_tertinggi_yang_dimiliki', 'Ijazah tertinggi yang dimiliki', '23 kode: sama dengan jenjang, ditambah Tidak punya ijazah SD', 'terbuka', true],
            ['Pendidikan', 'pendidikan_akhir_ktp', 'Pendidikan akhir yang tercatat di Dukcapil', 'teks', 'terbuka', false],

            ['Ketenagakerjaan & Usaha', 'status_bekerja', 'Bekerja/membantu bekerja', 'Ya/Tidak', 'terbuka', true],
            ['Ketenagakerjaan & Usaha', 'lapangan_usaha_dari_pekerjaan_utama', 'Lapangan usaha pekerjaan utama', "26 kategori KBLI: {$kbli}", 'terbuka', true],
            ['Ketenagakerjaan & Usaha', 'status_dalam_pekerjaan_utama', 'Kedudukan dalam pekerjaan utama', 'Berusaha sendiri; Berusaha dibantu buruh tidak tetap; Berusaha dibantu buruh tetap; Buruh/karyawan/pegawai swasta; PNS/TNI/Polri/BUMN/BUMD/pejabat negara; Pekerja bebas pertanian; Pekerja bebas non pertanian; Pekerja keluarga/tidak dibayar', 'terbuka', true],
            ['Ketenagakerjaan & Usaha', 'kepemilikan_usaha', 'Memiliki usaha sendiri/bersama', 'Ya/Tidak', 'terbuka', true],
            ['Ketenagakerjaan & Usaha', 'jumlah_usaha', 'Jumlah usaha yang dimiliki', 'angka', 'terbuka', false],
            ['Ketenagakerjaan & Usaha', 'lapangan_usaha_dari_usaha_utama', 'Lapangan usaha dari usaha utama', '26 kategori, sama dengan lapangan usaha pekerjaan utama', 'terbuka', true],
            ['Ketenagakerjaan & Usaha', 'jumlah_pekerja_yang_dibayar_dari_usaha_utama', 'Jumlah pekerja dibayar di usaha utama', 'angka', 'terbuka', false],
            ['Ketenagakerjaan & Usaha', 'jumlah_pekerja_yang_tidak_dibayar_dari_usaha_utama', 'Jumlah pekerja tidak dibayar di usaha utama', 'angka', 'terbuka', false],
            ['Ketenagakerjaan & Usaha', 'omzet_usaha_utama', 'Omzet usaha utama per bulan', '< 5 juta; 5-<15 juta; 15-<25 juta (ultra mikro); 25-<167 juta (mikro); 167-<1.250 juta (kecil); 1.250-<4.167 juta (menengah); >= 4.167 juta (besar)', 'terbuka', true],
            ['Ketenagakerjaan & Usaha', 'pekerjaan_ktp', 'Pekerjaan yang tercatat di Dukcapil', 'teks', 'terbuka', false],

            ['Kesehatan & Disabilitas', 'kondisi_gizi', 'Kondisi gizi anak menurut buku kontrol', 'Kurang gizi (wasting); Kerdil (stunting); Tidak ada catatan; Tidak tahu', 'terbuka', true],
            ['Kesehatan & Disabilitas', 'penglihatan', 'Kesulitan penglihatan meski memakai alat bantu', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'pendengaran', 'Kesulitan pendengaran meski memakai alat bantu', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'berjalan_atau_naik_tangga', 'Kesulitan berjalan atau naik tangga', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'menggunakan_tangan_jari', 'Kesulitan menggerakkan tangan/jari', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'belajar_kemampuan_intelektual', 'Kesulitan belajar/kemampuan intelektual dibanding sebaya', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'pengendalian_perilaku', 'Kesulitan mengendalikan perilaku dibanding sebaya', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'berbicara_komunikasi', 'Kesulitan berbicara/berkomunikasi', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'mengurus_diri', 'Kesulitan mengurus diri (mandi, makan, berpakaian, BAK, BAB)', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'mengingat_berkonsentrasi', 'Kesulitan mengingat/berkonsentrasi', $disabilitas, 'terbuka', true],
            ['Kesehatan & Disabilitas', 'kesedihan_depresi', 'Gangguan kesedihan/depresi', 'Sangat sering; Sering; Jarang; Tidak pernah', 'terbuka', true],
            ['Kesehatan & Disabilitas', 'penyakit_kronis', 'Keluhan kesehatan kronis/menahun', 'Tidak ada; Hipertensi; Rematik; Asma; Jantung; Diabetes; TBC; Stroke; Kanker; Gagal ginjal; Haemophilia; HIV/AIDS; Kolesterol; Sirosis hati; Thalasemia; Leukimia; Alzheimer; Lainnya', 'terbuka', true],

            ['Alamat KTP', 'kode_provinsi_ktp', 'Kode provinsi di Dukcapil', '2 digit', 'terbuka', true],
            ['Alamat KTP', 'provinsi_ktp', 'Nama provinsi di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'kode_kabupaten_kota_ktp', 'Kode kabupaten/kota di Dukcapil', '4 digit', 'terbuka', true],
            ['Alamat KTP', 'kabupaten_kota_ktp', 'Nama kabupaten/kota di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'kode_kecamatan_ktp', 'Kode kecamatan di Dukcapil', '7 digit', 'terbuka', true],
            ['Alamat KTP', 'kecamatan_ktp', 'Nama kecamatan di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'kode_kelurahan_desa_ktp', 'Kode kelurahan/desa di Dukcapil', '10 digit', 'terbuka', true],
            ['Alamat KTP', 'kelurahan_desa_ktp', 'Nama kelurahan/desa di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'rt_ktp', 'RT di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'rw_ktp', 'RW di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'dusun_ktp', 'Dusun di Dukcapil', 'teks', 'terbuka', false],
            ['Alamat KTP', 'alamat_ktp', 'Alamat yang tercatat di Dukcapil', 'teks', 'data_pribadi', false],
        ];
    }

    public function up(): void
    {
        Schema::table('dtsen_variables', function (Blueprint $table) {
            $table->string('set_data', 20)->nullable()->after('kategori')->index();
            $table->string('sensitivitas', 30)->nullable()->after('set_data');
            $table->boolean('bisa_filter')->default(false)->after('sensitivitas');
            $table->text('nilai_kode')->nullable()->after('deskripsi');
        });

        // Katalog lama: yang pernah dimohonkan cukup dinonaktifkan agar jejak
        // permohonan tetap utuh (hapus akan ikut menghapus baris permohonan lewat cascade).
        $lamaIds = DB::table('dtsen_variables')->whereIn('kode', self::KODE_LAMA)->pluck('id');
        $dipakai = DB::table('dtsen_request_variables')->whereIn('dtsen_variable_id', $lamaIds)
            ->distinct()->pluck('dtsen_variable_id');
        DB::table('dtsen_variables')->whereIn('id', $dipakai)->update(['is_active' => false, 'updated_at' => now()]);
        DB::table('dtsen_variables')->whereIn('id', $lamaIds->diff($dipakai))->delete();

        $releaseId = DB::table('dtsen_releases')->where('is_active', true)->orderByDesc('tanggal_rilis')->value('id');
        $now = now();

        foreach (['keluarga' => ['KLG', $this->keluarga()], 'anggota' => ['AGT', $this->anggota()]] as $set => [$prefix, $rows]) {
            foreach ($rows as $i => [$kategori, $nama, $definisi, $nilai, $sensitivitas, $filter]) {
                $kode = $prefix . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
                if (DB::table('dtsen_variables')->where('kode', $kode)->exists()) {
                    continue;
                }
                DB::table('dtsen_variables')->insert([
                    'kode' => $kode,
                    'nama' => $nama,
                    'deskripsi' => $definisi,
                    'nilai_kode' => $nilai,
                    'kategori' => $kategori,
                    'set_data' => $set,
                    'sensitivitas' => $sensitivitas,
                    'bisa_filter' => $filter,
                    'level_minimal' => self::LEVEL[$sensitivitas],
                    'dtsen_release_id' => $releaseId,
                    'is_active' => true,
                    'urutan' => ($i + 1) * 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Katalog lama yang sudah dihapus tidak dipulihkan di sini — pulihkan dari
     * cadangan basis data bila memang diperlukan.
     */
    public function down(): void
    {
        DB::table('dtsen_variables')->where('kode', 'like', 'KLG-%')->orWhere('kode', 'like', 'AGT-%')->delete();
        DB::table('dtsen_variables')->whereIn('kode', self::KODE_LAMA)->update(['is_active' => true]);

        Schema::table('dtsen_variables', function (Blueprint $table) {
            $table->dropIndex(['set_data']);
            $table->dropColumn(['set_data', 'sensitivitas', 'bisa_filter', 'nilai_kode']);
        });
    }
};
