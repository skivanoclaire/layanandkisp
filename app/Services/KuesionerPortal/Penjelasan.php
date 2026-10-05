<?php

namespace App\Services\KuesionerPortal;

/**
 * Teks tooltip halaman analisis Kuesioner Portal. Ditulis untuk belajar: apa arti
 * angkanya, bagaimana membacanya, dan rujukannya di proposal (Bab 2–3).
 */
class Penjelasan
{
    public const TEKS = [
        // Halaman & ringkasan
        'halaman' => 'Halaman ini menghitung ulang seluruh analisis Bab 3 dari jawaban yang sudah masuk setiap kali dibuka. Gunakan untuk memantau selama pengumpulan data. Angka final tesis diambil dari Jupyter, lalu dicocokkan dengan halaman ini.',
        'ekspor' => 'Mengunduh data mentah tanpa identitas untuk dianalisis di Jupyter (pandas.read_excel). Satu baris per responden. Sheet "kodebook" menjelaskan arti setiap kolom dan kode jawaban.',
        'masuk' => 'Semua akun yang sudah membuka lembar persetujuan pada periode ini, apa pun pilihannya.',
        'menolak' => 'Akun yang memilih "Tidak bersedia". Tetap dicatat agar tingkat respons dapat dilaporkan apa adanya.',
        'belum_selesai' => 'Sudah menyatakan bersedia tetapi belum menekan "Kirim Jawaban". Belum ikut dianalisis.',
        'selesai' => 'Kuesioner terkirim lengkap.',
        'dianalisis' => 'Respons selesai yang lolos pra-pemrosesan (Subbab 3.3.2): tidak dikecualikan peneliti, durasi wajar, item kosong tidak lebih dari 20%, dan jawabannya tidak seragam. Angka ini menjadi dasar uji instrumen, gap, Tk, dan IPA.',
        'dianalisis_kano' => 'Respons yang dianalisis dikurangi responden yang jawaban Kano-nya questionable (Q) pada lebih dari 20% atribut. Jawaban Kano mereka dianggap tidak konsisten sehingga tidak dipakai untuk klasifikasi Kano, tetapi tetap dipakai untuk IPA.',

        // Uji instrumen (3.3.1)
        'uji_instrumen' => 'Sebelum angka dipercaya, kuesioner diuji dulu. Validitas: apakah setiap butir benar-benar mengukur hal yang sama dengan butir lain di bloknya. Reliabilitas: apakah blok itu konsisten, sehingga bila pengukuran diulang hasilnya serupa.',
        'n_blok' => 'Jumlah responden yang menjawab semua butir di blok ini. Responden yang melewatkan satu butir tidak dihitung. Kinerja CS1–CS4 hanya diisi responden yang pernah menghubungi petugas, sehingga n blok kinerja bisa jauh lebih kecil daripada blok kepentingan.',
        'r_tabel' => 'Batas minimal korelasi agar butir dinyatakan valid pada taraf signifikansi 5% (dua sisi), dengan derajat bebas n − 2. Makin besar n, makin kecil r tabel. Contoh: n = 30 → 0,361; n = 100 → 0,197.',
        'alpha' => "Cronbach's alpha mengukur konsistensi internal: apakah butir-butir dalam satu blok saling sejalan. Nilainya 0 sampai 1. Nilai ≥ 0,70 dianggap reliabel (Papadomichelaki & Mentzas, 2012). Angka merah berarti di bawah 0,70.",
        'r_item' => 'Korelasi Pearson corrected item-total: korelasi skor butir ini dengan jumlah skor butir lain di blok yang sama. Butir ini sendiri tidak ikut dijumlahkan agar tidak berkorelasi dengan dirinya. Bila r > r tabel, butir valid. Butir tidak valid dikeluarkan dari analisis dan dilaporkan.',
        'alpha_dimensi' => 'Alpha dihitung terpisah untuk setiap dimensi E-GovQual, untuk melihat dimensi mana yang butirnya kurang kompak. Dimensi dengan sedikit butir (Trust dan Citizen Support, masing-masing 4 butir) cenderung memiliki alpha lebih rendah. Angka dalam kurung adalah n.',

        // Deskriptif, gap, Tk (3.3.3)
        'deskriptif' => 'Membandingkan harapan (kepentingan) dengan kenyataan (kinerja) untuk setiap atribut. Ini inti logika expectancy-disconfirmation: kepuasan muncul bila kenyataan memenuhi harapan.',
        'n_pasangan' => 'Jumlah responden yang menjawab kepentingan dan kinerja atribut ini. Semua angka di baris ini dihitung dari responden yang sama agar perbandingannya adil. Untuk CS1–CS4, n hanya mencakup responden yang pernah menghubungi petugas.',
        'kepentingan' => 'Rata-rata skor Bagian B (Ȳ, skala 1–5): seberapa penting atribut menurut responden. Label kecil di sampingnya adalah kelas interval 0,80: sangat rendah (1,00–1,80), rendah (1,81–2,60), sedang (2,61–3,40), tinggi (3,41–4,20), sangat tinggi (4,21–5,00).',
        'kinerja' => 'Rata-rata skor Bagian C (X̄, skala 1–5): seberapa baik portal saat ini menurut responden. Kelasnya sama dengan kolom kepentingan.',
        'gap' => 'Gap = X̄ − Ȳ (kinerja dikurangi kepentingan). Negatif (merah) berarti kinerja belum memenuhi harapan; makin negatif, makin lebar jaraknya. Positif berarti kinerja melampaui harapan.',
        'tk' => 'Tingkat kesesuaian = ΣX ÷ ΣY × 100%, yaitu total skor kinerja dibagi total skor kepentingan. Contoh: 85% berarti kinerja baru memenuhi 85% dari yang diharapkan pengguna.',
        'interpretasi_tk' => 'Tabel 3.4 (Muthmainah dkk, 2023): Tk < 80% berarti belum memenuhi harapan; 80–100% memenuhi tetapi masih perlu perbaikan; > 100% memenuhi atau melampaui harapan.',
        'z' => 'Statistik uji Wilcoxon signed-rank. Uji ini memeriksa apakah selisih kinerja dan kepentingan per responden benar-benar ada, bukan kebetulan. Dipakai karena skor Likert umumnya tidak berdistribusi normal. Tanda negatif berarti kinerja cenderung di bawah kepentingan.',
        'p' => 'Peluang memperoleh selisih sebesar ini bila sebenarnya tidak ada selisih. p < 0,05 (dicetak tebal) berarti gap signifikan secara statistik. Halaman ini memakai pendekatan normal; untuk n kecil, cocokkan dengan hasil SciPy di Jupyter.',
        'per_dimensi' => 'Rata-rata dari rata-rata atribut di setiap dimensi E-GovQual. Berguna untuk ringkasan Bab 4, misalnya dimensi mana yang gap-nya paling lebar.',
        'kepuasan' => 'Tiga butir kepuasan pengguna (KPS1–KPS3) yang hanya dinilai di Bagian C. Tidak masuk IPA maupun Kano; dipakai untuk menggambarkan kepuasan keseluruhan dan diuji reliabilitasnya.',

        // IPA (3.3.4)
        'ipa' => 'Importance-Performance Analysis (Martilla & James, 1977) memetakan setiap atribut ke diagram kartesius: sumbu X kinerja, sumbu Y kepentingan. Posisi titik menentukan tindakan terhadap atribut itu.',
        'garis' => 'Garis putus-putus memotong diagram pada rata-rata seluruh atribut (X̄ = rata-rata kinerja, Ȳ = rata-rata kepentingan). Jadi setiap atribut dibandingkan dengan atribut lain, bukan dengan angka mutlak.',
        'kuadran_I' => 'Kepentingan tinggi, kinerja rendah. Paling mendesak: responden menganggapnya penting tetapi portal belum memuaskan. Atribut di sini diurutkan menurut gap terbesar.',
        'kuadran_II' => 'Kepentingan tinggi, kinerja tinggi. Sudah baik dan perlu dipertahankan.',
        'kuadran_III' => 'Kepentingan rendah, kinerja rendah. Prioritas rendah, tetapi lihat kategori Kano-nya: atribut must-be di kuadran ini tetap perlu diperhatikan.',
        'kuadran_IV' => 'Kepentingan rendah, kinerja tinggi. Mungkin berlebihan sehingga sumber daya dapat dialihkan, kecuali bila kategorinya attractive karena justru menjadi sumber kepuasan.',
        'ketahanan' => 'Uji ketahanan (robustness): kuadran dihitung ulang dengan garis potong di titik tengah skala (3,0), sesuai pendekatan asli Martilla dan James. Atribut yang berpindah kuadran posisinya sensitif terhadap pilihan garis potong, jadi perlu ditafsirkan hati-hati.',

        // Kano (3.3.5)
        'kano' => 'Model Kano (Kano dkk, 1984) membedakan cara atribut memengaruhi kepuasan. Setiap atribut ditanyakan dua kali, saat terpenuhi (F) dan saat tidak terpenuhi (D). Pasangan jawaban diterjemahkan dengan Tabel 2.3 menjadi satu kategori.',
        'kano_A' => 'Attractive (pemikat). Menambah kepuasan bila ada, tetapi tidak mengecewakan bila tidak ada. Contoh: notifikasi otomatis saat status permohonan berubah.',
        'kano_O' => 'One-dimensional (kinerja). Makin baik makin puas; makin buruk makin kecewa. Contoh: kecepatan penyelesaian permohonan.',
        'kano_M' => 'Must-be (wajib). Tidak menambah kepuasan bila ada karena dianggap wajar, tetapi sangat mengecewakan bila tidak ada. Contoh: portal dapat diakses.',
        'kano_I' => 'Indifferent (tidak berpengaruh). Ada atau tidak, responden tidak peduli.',
        'kano_R' => 'Reverse (kebalikan). Responden justru lebih suka bila atribut ini tidak ada.',
        'kano_Q' => 'Questionable (meragukan). Jawabannya bertentangan, misalnya suka saat terpenuhi dan juga suka saat tidak terpenuhi. Biasanya tanda responden salah memahami pertanyaan; dikeluarkan dari n.',
        'n_kano' => 'Jumlah jawaban sah (A + O + M + I + R), tanpa Q.',
        'kategori_kano' => 'Ditetapkan dengan aturan Berger dkk (1993): bila A + O + M lebih banyak dari I + R, pilih yang terbanyak di antara A, O, M; bila tidak, pilih di antara I dan R. Label seperti M/O berarti kategori campuran karena tidak lolos uji Fong.',
        'fong_selisih' => '|a − b| adalah selisih frekuensi kategori terpilih (a) dengan kategori pesaing terdekat (b).',
        'fong_batas' => 'Batas uji Fong (1996) = 1,65 × √[(a + b)(2n − a − b) / 2n]. Bila |a − b| lebih kecil dari batas ini, kedua kategori tidak berbeda nyata, sehingga atribut dilaporkan sebagai kategori campuran (misalnya M/O).',
        'better' => 'Koefisien better (0 sampai 1) = (A + O) ÷ (A + O + M + I). Menunjukkan seberapa besar kepuasan naik bila atribut dipenuhi. Mendekati 1 berarti sangat menambah kepuasan.',
        'worse' => 'Koefisien worse (−1 sampai 0) = −(O + M) ÷ (A + O + M + I). Menunjukkan seberapa besar kepuasan turun bila atribut tidak dipenuhi. Mendekati −1 berarti sangat mengecewakan bila tidak ada.',
        'diagram_bw' => 'Sumbu X = |worse| (dampak bila tidak ada), sumbu Y = better (dampak bila ada), garis potong 0,5. Kanan atas: one-dimensional (berpengaruh dua arah). Kanan bawah: must-be (wajib dijaga). Kiri atas: attractive (pemikat). Kiri bawah: indifferent (tidak berpengaruh).',

        // Pemaduan (3.3.6)
        'matriks' => 'Gabungan IPA dan Kano (Tabel 3.5, berdasarkan Matzler dkk, 2004). IPA menjawab "atribut mana yang tertinggal", Kano menjawab "seberapa besar pengaruhnya terhadap kepuasan". Gabungan keduanya menghasilkan urutan perbaikan.',
        'urutan' => 'Urutan akhir prioritas perbaikan. Baris merah adalah prioritas 1–2. Dalam kelompok yang sama, atribut must-be dan one-dimensional diurutkan menurut |worse| terbesar, attractive menurut better terbesar, lalu gap terbesar.',
        'kuadran_kolom' => 'Posisi atribut pada diagram IPA dengan garis potong rata-rata (I, II, III, atau IV).',
        'kano_kolom' => 'Kategori Kano atribut. Label campuran seperti M/O dipetakan ke Tabel 3.5 memakai kategori pertamanya (yang dominan).',
        'tindak_lanjut' => 'Rekomendasi Tabel 3.5. Kuadran I + must-be: perbaiki segera. Kuadran I + one-dimensional: tingkatkan. Kuadran III + must-be: naik ke prioritas 2. Kuadran II + M/O: standar layanan minimum. Kuadran IV + attractive: pertahankan. "Di luar Tabel 3.5" berarti kombinasinya tidak tercantum di tabel, jadi tafsirkan bersama better-worse.',
        'spearman' => 'Korelasi peringkat Spearman (ρ, −1 sampai 1) antara rata-rata kepentingan dan |worse| setiap atribut. Bila ρ lemah (mendekati 0), kepentingan yang dinyatakan responden tidak mencerminkan seberapa kecewa mereka bila atribut hilang. Inilah alasan IPA perlu dipadukan dengan Kano.',

        // Profil
        'profil' => 'Karakteristik responden yang lolos pra-pemrosesan (Bagian A). Dilaporkan di Bab 4 untuk menggambarkan siapa saja yang menilai portal.',
    ];

    public static function get(string $kunci): string
    {
        return self::TEKS[$kunci] ?? '';
    }
}
