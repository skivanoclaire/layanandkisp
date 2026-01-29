@extends('layouts.authenticated')

@section('title', '- Fase Pengembangan')
@section('header-title', 'Fase Pengembangan - ' . $proposal->nama_aplikasi)

@section('content')
    <div class="mb-6">
        <!-- Header Section -->
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $proposal->nama_aplikasi }}</h1>
                <p class="text-sm text-gray-600 mt-1">Tiket: {{ $proposal->ticket_number }}</p>
            </div>
            <div class="flex gap-2">
                <!-- Phase Badge -->
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                    Fase Pengembangan
                </span>
                <!-- Ministry Status Badge -->
                @if ($proposal->statusKementerian && $proposal->statusKementerian->status === 'disetujui')
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                        Disetujui Kementerian
                    </span>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('fase-pengembangan.index') }}"
            class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Admin Notes & Activity Log -->
    @php
        $adminNotes = $proposal->historiAktivitas()
            ->where('aktivitas', 'admin_add_note_fase')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    @endphp

    @if ($adminNotes->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm mb-4">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Catatan & Instruksi dari Admin
                </h3>
                <p class="text-sm text-gray-600 mt-1">Admin telah memberikan catatan atau instruksi terkait fase pengembangan ini</p>
            </div>
            <div class="p-6 space-y-4">
                @foreach ($adminNotes as $note)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-yellow-900">{{ $note->deskripsi }}</p>
                                <div class="flex items-center mt-2 text-xs text-yellow-700">
                                    <span class="font-medium">{{ $note->user->name ?? 'Admin' }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $note->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm" x-data="{ activeTab: 'rancang_bangun' }">
        <!-- Tab Headers -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'rancang_bangun'"
                    :class="activeTab === 'rancang_bangun' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                    Rancang Bangun
                </button>
                <button @click="activeTab = 'implementasi'"
                    :class="activeTab === 'implementasi' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                    Implementasi
                </button>
                <button @click="activeTab = 'uji_kelaikan'"
                    :class="activeTab === 'uji_kelaikan' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                    Uji Kelaikan
                </button>
                <button @click="activeTab = 'pemeliharaan'"
                    :class="activeTab === 'pemeliharaan' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                    Pemeliharaan
                </button>
                <button @click="activeTab = 'evaluasi'"
                    :class="activeTab === 'evaluasi' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition">
                    Evaluasi
                </button>
            </nav>
        </div>

        <!-- Tab Content: Rancang Bangun -->
        <div x-show="activeTab === 'rancang_bangun'" class="p-6">
            @include('user.fase-pengembangan.partials.phase-tab', [
                'fase' => 'rancang_bangun',
                'title' => 'Rancang Bangun',
                'instructions' => "Dokumentasi Rancang bangun sebagaimana paling kurang meliputi minimal memuat:\na. pemodelan rancang bangun;\nb. alur proses Aplikasi SPBE;\nc. pemetaan dan keterhubungan fungsi dan basis data dalam Aplikasi SPBE;\nd. pemetaan hak akses dan peran untuk pengguna aplikasi;\ne. rancangan antarmuka pengguna dan navigasi dari layar ke layar sesuai dengan tingkatan pengguna;\nf. rancangan kendali internal yang diperlukan dalam proses validasi, otorisasi, dan pencatatan aktivitas; dan\ng. rancangan integrasi antara aplikasi dengan aplikasi lain",
                'documents' => $dokumenByFase->get('rancang_bangun', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Implementasi -->
        <div x-show="activeTab === 'implementasi'" class="p-6">
            @include('user.fase-pengembangan.partials.phase-tab', [
                'fase' => 'implementasi',
                'title' => 'Implementasi',
                'instructions' => "Dokumentasi Implementasi sebagaimana dimaksud minimal memuat:\na. menerjemahkan bentuk rancang bangun menjadi kode atau bentuk bahasa pemrograman;\nb. melakukan pengkodean Aplikasi SPBE dan basis data sesuai dengan rancang bangun yang telah disetujui;\nc. melakukan kendali mutu melalui serangkaian uji kelaikan atas Aplikasi SPBE yang dibangun dan dikembangkan;\nd. melaksanakan instalasi dan konfigurasi Aplikasi SPBE;\ne. menyediakan antarmuka pemrograman aplikasi sebagai fasilitas interoperabilitas data;\nf. menyusun dokumentasi atas pembangunan dan pengembangan Aplikasi SPBE;\ng. melaksanakan alih pengetahuan dan teknologi melalui sosialisasi, bimbingan teknis, konsultasi, dan/atau pendampingan; dan\nh. menyusun dokumen serah terima pekerjaan dalam hal pembangunan dan pengembangan Aplikasi SPBE tidak dilakukan secara swakelola.",
                'documents' => $dokumenByFase->get('implementasi', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Uji Kelaikan -->
        <div x-show="activeTab === 'uji_kelaikan'" class="p-6">
            @include('user.fase-pengembangan.partials.phase-tab', [
                'fase' => 'uji_kelaikan',
                'title' => 'Uji Kelaikan',
                'instructions' => "Dokumentasi Pelaksanaan uji kelaikan sebagaimana dimaksud meliputi aspek:\na. uji fungsi, meliputi pengujian yang memastikan Aplikasi SPBE yang dibangun dan dikembangkan sudah memenuhi fungsi Aplikasi SPBE sesuai dengan dokumentasi terkait;\nb. uji integrasi, meliputi pengujian yang memastikan Aplikasi SPBE yang dibangun dan dikembangkan sudah memenuhi kebutuhan dan persyaratan integrasi dengan Aplikasi SPBE, data, serta komponen lain yang terkait;\nc. uji beban, meliputi pengujian yang memastikan Aplikasi SPBE dapat berfungsi sebagaimana mestinya menghadapi beban kerja yang dikenakan terhadapnya; dan\nd. uji keamanan, meliputi pengujian yang memastikan Aplikasi SPBE dapat berfungsi sebagaimana mestinya dengan menjaga keamanan data dan informasi yang terkait dengannya sesuai dengan ketentuan peraturan perundang-undangan.\n\nUji kelaikan sebagaimana dimaksud dilakukan dengan tahapan:\na. menyusun perencanaan uji kelaikan yang terdiri atas penentuan jadwal pelaksanaan uji kelaikan, penyiapan lingkungan dan sumber daya;\nb. mengidentifikasi uji kelaikan yang terdiri atas penentuan ruang lingkup dan kriteria uji kelaikan;\nc. menyusun rancangan uji kelaikan yang terdiri atas penyiapan alur proses uji kelaikan;\nd. menetapkan skenario uji kelaikan yang terdiri atas penentuan uji kelaikan dengan menggunakan berbagai skenario yang berbeda;\ne. melaksanakan uji kelaikan; dan\nf. melakukan evaluasi uji kelaikan.\n\nUji kelaikan sebagaimana dimaksud dilakukan dengan menggunakan metode dan pendekatan pengujian yang berlaku, meliputi:\na. pengujian kotak putih, yang merupakan uji kelaikan terhadap fungsi fungsionalitas sebuah Aplikasi SPBE dengan mengetahui struktur program; dan/atau\nb. pengujian kotak hitam, yang merupakan uji kelaikan terhadap fungsi fungsionalitas sebuah Aplikasi SPBE tanpa mengetahui struktur program.\n\nUji kelaikan dilakukan berjenjang dari pengujian komponen terkecil hingga pengujian secara keseluruhan dari Aplikasi SPBE.\n\nEvaluasi uji kelaikan terdiri atas pelaksanaan penilaian terhadap:\na. kesesuaian proses uji kelaikan yang sudah dilakukan dengan keseluruhan tahapan uji kelaikan;\nb. kesesuaian hasil uji kelaikan dengan analisis kebutuhan, rancang bangun, dan kriteria; dan\nc. mendokumentasikan keseluruhan tahapan uji kelaikan.",
                'documents' => $dokumenByFase->get('uji_kelaikan', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Pemeliharaan -->
        <div x-show="activeTab === 'pemeliharaan'" class="p-6">
            @include('user.fase-pengembangan.partials.phase-tab', [
                'fase' => 'pemeliharaan',
                'title' => 'Pemeliharaan',
                'instructions' => "Pemeliharaan Aplikasi SPBE dilaksanakan untuk memperpanjang umur kegunaan aplikasi sebagai aset dan mempertahankan keandalan layanan.\n\nPemeliharaan atas Aplikasi SPBE meliputi:\n\na. Pemeliharaan perfektif\n   Penambahan atau penyempurnaan aplikasi yang meliputi penambahan fungsi baru, perbaikan antarmuka, perbaikan kinerja, dan/atau perbaikan dokumentasi implementasi.\n\nb. Pemeliharaan adaptif\n   Adaptasi terhadap teknologi atau lingkungan operasional baru, dan penerapan protokol baru.\n\nc. Pemeliharaan korektif\n   Perbaikan terhadap permasalahan yang timbul setelah aplikasi digunakan.\n\nd. Pemeliharaan preventif\n   Pemeriksaan secara berkala aplikasi untuk mengantisipasi permasalahan, yang harus terdokumentasi.\n\nKetentuan Pemeliharaan:\n- Instansi Pusat dan Pemerintah Daerah harus melaksanakan pemeliharaan terhadap Aplikasi SPBE yang diselengarakannya.\n- Pemeliharaan dilaksanakan oleh unit kerja yang menyelenggarakan fungsi pengelolaan teknologi informasi dan komunikasi bersama unit kerja pemilik Proses Bisnis.\n- Pemeliharaan perlu dilakukan pemantauan oleh koordinator SPBE secara berkala dan/atau sewaktu-waktu sesuai kebutuhan.",
                'documents' => $dokumenByFase->get('pemeliharaan', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Evaluasi -->
        <div x-show="activeTab === 'evaluasi'" class="p-6">
            @include('user.fase-pengembangan.partials.phase-tab', [
                'fase' => 'evaluasi',
                'title' => 'Evaluasi',
                'instructions' => "Evaluasi atas siklus pembangunan dan pengembangan Aplikasi SPBE harus dilaksanakan minimal 2 (dua) kali dalam 1 (satu) tahun dan/atau sewaktu-waktu sesuai kebutuhan.\n\nEvaluasi dilakukan oleh unit kerja yang menyelenggarakan fungsi pengelolaan teknologi informasi dan komunikasi pemerintah bersama unit kerja pemilik Proses Bisnis yang menyelenggarakan Aplikasi SPBE.\n\nPelaksanaan evaluasi minimal meliputi:\n\na. Menyusun kebijakan internal terkait evaluasi pembangunan dan pengembangan Aplikasi SPBE\n   Membuat dan menetapkan kebijakan evaluasi yang mengatur prosedur, kriteria, dan standar penilaian.\n\nb. Melakukan pengukuran penilaian indikator keberhasilan\n   Mengukur dan menilai indikator keberhasilan sesuai dengan siklus pembangunan dan pengembangan Aplikasi SPBE, mencakup fase:\n   - Analisis kebutuhan\n   - Rancang bangun\n   - Implementasi\n   - Uji kelaikan\n   - Pemeliharaan\n\nc. Menyusun laporan hasil evaluasi\n   Mendokumentasikan hasil evaluasi secara komprehensif meliputi temuan, analisis, dan rekomendasi perbaikan.\n\nd. Menyampaikan laporan hasil evaluasi\n   Menyampaikan laporan hasil evaluasi kepada pimpinan Instansi Pusat atau kepala daerah untuk mendapatkan arahan dan keputusan tindak lanjut.\n\ne. Melaksanakan tindak lanjut hasil evaluasi\n   Mengimplementasikan rekomendasi dan perbaikan yang diperlukan berdasarkan hasil evaluasi.\n\nPemantauan:\n- Evaluasi perlu dilakukan pemantauan oleh koordinator SPBE Instansi Pusat atau koordinator SPBE Pemerintah Daerah.\n- Pemantauan dilakukan secara berkala dan/atau sewaktu-waktu sesuai kebutuhan.",
                'documents' => $dokumenByFase->get('evaluasi', collect()),
                'proposalId' => $proposal->id
            ])
        </div>
    </div>

    <script>
        // File size validation
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                if (this.files[0] && this.files[0].size > maxSize) {
                    alert('Ukuran file maksimal 50MB. File yang Anda pilih: ' +
                        (this.files[0].size / 1024 / 1024).toFixed(2) + ' MB');
                    this.value = '';
                }
            });
        });

        // Delete confirmation
        function confirmDelete(event) {
            if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endsection
