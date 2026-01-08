@extends('layouts.app')

@section('content')
    <div class="w-full px-4 md:px-8 lg:px-16 py-12 flex flex-col md:flex-row items-center gap-12">

        <!-- Left Grid: Menu -->
        <div class="w-full md:w-1/2">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                @php
                    $items = [
                        [
                            'icon' => '📄',
                            'label' => 'Rekomendasi',
                            'slug' => 'rekomendasi',
                            'image' => 'rekomendasi.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🤖',
                            'label' => 'AI SPBE Kaltara',
                            'slug' => 'ai-spbe-kaltara',
                            'image' => 'ai-spbe.png',
                            'link' =>
                                'https://chatgpt.com/g/g-68d4e245a8348191b95faca91144169f-asisten-spbe-kalimantan-utara',
                        ],

                         ['icon' => '🛡️', 'label' => 'TTE', 'slug' => 'tte', 'image' => 'tte.png', 'link' => '/login'],

                        [
                            'icon' => '🌐',
                            'label' => 'Subdomain dan PSE',
                            'slug' => 'subdomain',
                            'image' => 'subdomain.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🗄️',
                            'label' => 'Pusat Data',
                            'slug' => 'pusatdata',
                            'image' => 'hosting.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '✉️',
                            'label' => 'Email',
                            'slug' => 'email',
                            'image' => 'email.png',
                            'link' => '/login',
                        ],
                       
                        [
                            'icon' => '📈',
                            'label' => 'Portal Data',
                            'slug' => 'portal-data',
                            'image' => 'portal-data.png',
                            'link' => 'https://data.kaltaraprov.go.id',
                        ],
                        [
                            'icon' => '⚖️',
                            'label' => 'Manajemen Risiko SPBE',
                            'slug' => 'manajemen-risiko-spbe-kaltara',
                            'image' => 'manrisk-spbe.png',
                            'link' => 'http://s.id/manrisk-spbe-kaltara',
                        ],
                        [
                            'icon' => '📖',
                            'label' => 'SPBE',
                            'slug' => 'SPBE',
                            'image' => 'spbe.png',
                            'link' =>
                                'https://lookerstudio.google.com/u/1/reporting/f768fb19-c4d8-4cb3-9373-81752c9deadc/page/p_8p0vzpu3ad',
                        ],
                        [
                            'icon' => '📹',
                            'label' => 'Peliputan',
                            'slug' => 'peliputan',
                            'image' => 'peliputan.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '📰',
                            'label' => 'Publikasi',
                            'slug' => 'publikasi',
                            'image' => 'publikasi.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🎞️',
                            'label' => 'Konten Multimedia',
                            'slug' => 'konten-multimedia',
                            'image' => 'konten-multimedia.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🏛️',
                            'label' => 'Pengaduan',
                            'slug' => 'span-lapor',
                            'image' => 'lapor.png',
                            'link' => 'https://www.lapor.go.id/',
                        ],

                        ['icon' => '🖧', 'label' => 'SPLP', 'slug' => 'splp', 'image' => 'splp.png', 'link' => '/login'],

                        [
                            'icon' => '📘',
                            'label' => 'PPID',
                            'slug' => 'ppid',
                            'image' => 'ppid.png',
                            'link' => 'https://ppid.kaltaraprov.go.id',
                        ],
                        [
                            'icon' => '📡',
                            'label' => 'Jaringan Internet',
                            'slug' => 'jaringan-internet',
                            'image' => 'jaringan-internet.png',
                            'link' => '/login',
                        ],
                        ['icon' => '🔗', 'label' => 'VPN', 'slug' => 'vpn', 'image' => 'vpn.png', 'link' => '/login'],
                        [
                            'icon' => '📶',
                            'label' => 'Wifi Publik',
                            'slug' => 'wifi-publik',
                            'image' => 'wifi-publik.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '☁️',
                            'label' => 'Cloud Storage',
                            'slug' => 'cloud-storage',
                            'image' => 'cloud-storage.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '❓',
                            'label' => 'Helpdesk TIK',
                            'slug' => 'helpdesk-tik',
                            'image' => 'helpdesk-tik.png',
                            'link' => '/login',
                        ],
                        [
                            'icon' => '🎥',
                            'label' => 'Zoom/Youtube Live Streaming',
                            'slug' => 'zoom',
                            'image' => 'zoom.png',
                            'link' => '/login',
                        ],
                                                [
                            'icon' => '🛡️',
                            'label' => 'Keamanan Informasi',
                            'slug' => 'keamanan-informasi',
                            'image' => 'keamanan-informasi.png',
                            'link' => '/login',
                        ],
                    ];
                @endphp

                @foreach ($items as $item)
                    <a href="#{{ $item['slug'] }}"
                        class="bg-white border border-gray-200 shadow rounded-xl p-6 text-center hover:shadow-lg transition">
                        <div class="text-5xl mb-2">{{ $item['icon'] }}</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Right Illustration -->
        <div class="w-full md:w-1/2 flex justify-center items-center">
            <img src="{{ asset('layanankaltara2.png') }}" alt="Ilustrasi"
                class="w-full h-auto max-w-none md:w-[90%] lg:w-full">
        </div>
    </div>

    <!-- Section Target Content -->
    <div class="max-w-7xl mx-auto mt-20 space-y-1">
        @foreach ($items as $item)
            <section id="{{ $item['slug'] }}" class="pt-24 scroll-mt-24">
                <div
                    class="bg-gradient-to-r from-green-100 to-blue-100 p-8 rounded-lg shadow flex flex-col md:flex-row items-start gap-8">
                    <div class="w-32 flex-shrink-0 hidden md:block">
                        <img src="{{ asset($item['image']) }}" alt="{{ $item['label'] }}" class="w-full">
                    </div>
                    <div class="flex-grow">
                        <h2 class="text-4xl font-bold text-green-800 mb-6">{{ $item['label'] }}</h2>
                        <div class="text-justify text-lg text-gray-800 leading-relaxed space-y-4">
                            @switch($item['slug'])
                                @case('rekomendasi')
                                    <p>
                                        Berdasarkan <strong>Peraturan Menteri Komunikasi dan Digital Nomor 6 Tahun 2025</strong>
                                        tentang
                                        <em>Standar Teknis dan Prosedur Pembangunan dan Pengembangan Aplikasi Sistem Pemerintahan
                                            Berbasis Elektronik (SPBE)</em>,
                                        setiap pembangunan atau pengembangan Aplikasi SPBE oleh Instansi Pusat maupun Pemerintah
                                        Daerah
                                        wajib didahului dengan <strong>permohonan pertimbangan kepada Menteri Komunikasi dan
                                            Digital</strong> melalui
                                        <strong>Direktur Jenderal Teknologi Pemerintah Digital</strong>.
                                    </p>
                                    <p>
                                        Permohonan tersebut harus dilampiri dokumen persiapan yang menjadi dasar analisis kelayakan
                                        dan pengambilan keputusan
                                        oleh Kementerian. Dokumen ini wajib disusun sebelum pembangunan dimulai dan mengacu pada
                                        ketentuan Pasal 21
                                        Permen Komdigi 6/2025.
                                    </p>

                                    <p><strong>Adapun tahapan pengajuan permohonan pertimbangan adalah sebagai berikut:</strong></p>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        <li>
                                            <strong>Pengajuan Permohonan</strong>
                                            <ul class="list-[lower-alpha] ml-8 space-y-1">
                                                <li>
                                                    Perangkat Daerah mengajukan permohonan pertimbangan kepada Kepala Dinas
                                                    Komunikasi, Informatika, Statistik,
                                                    dan Persandian Provinsi Kalimantan Utara paling lambat pada tahun anggaran
                                                    <em>n-1</em>.
                                                </li>
                                                <li>
                                                    Permohonan wajib disertai dengan dokumen persiapan yang terdiri atas:
                                                    <ul class="list-[lower-roman] ml-6 space-y-1">
                                                        <li>
                                                            <strong>Dokumen Analisis Kebutuhan</strong>, sebagaimana diatur dalam
                                                            <strong>Pasal 7</strong>, wajib disusun oleh unit TIK dan unit proses
                                                            bisnis. Dokumen ini berisi:
                                                            <ul class="list-disc ml-5">
                                                                <li>Dasar hukum kewenangan pembangunan aplikasi;</li>
                                                                <li>Permasalahan dan kebutuhan yang melatarbelakangi proyek
                                                                    (termasuk hasil audit/evaluasi)
                                                                    ;</li>
                                                                <li>Daftar pihak yang terlibat dalam penyelenggaraan SPBE;</li>
                                                                <li>Tujuan dan manfaat dari aplikasi yang akan dibangun;</li>
                                                                <li>Ruang lingkup dan cakupan layanan aplikasi;</li>
                                                                <li>Analisis biaya dan manfaat;</li>
                                                                <li>Analisis risiko awal (terhubung dengan dokumen manajemen
                                                                    risiko);</li>
                                                                <li>Target waktu implementasi;</li>
                                                                <li>Sasaran pengguna (internal/eksternal);</li>
                                                                <li>Lokasi implementasi yang direncanakan.</li>
                                                            </ul>
                                                        </li>

                                                        <li>
                                                            <strong>Dokumen Perencanaan</strong>, sebagaimana diatur dalam
                                                            <strong>Pasal 8</strong>, memuat rencana terstruktur mengenai
                                                            pelaksanaan proyek SPBE dan harus mencakup:
                                                            <ul class="list-disc ml-5">
                                                                <li>Uraian ruang lingkup pembangunan;</li>
                                                                <li>Proses bisnis dan layanan yang akan didukung oleh aplikasi;</li>
                                                                <li>Kerangka kerja pengembangan (agile, waterfall, rapid dev, dsb.);
                                                                </li>
                                                                <li>Pemilihan pelaksana (Menteri, swakelola, atau pihak ketiga);
                                                                </li>
                                                                <li>Peran dan tanggung jawab pelaksana proyek;</li>
                                                                <li>Jadwal implementasi dan periode kerja;</li>
                                                                <li>Rencana aksi teknis dan langkah pelaksanaan;</li>
                                                                <li>Rencana pemenuhan keamanan informasi sesuai regulasi;</li>
                                                                <li>Kebutuhan sumber daya (SDM, anggaran, sarana);</li>
                                                                <li>Indikator keberhasilan pembangunan aplikasi (output & outcome);
                                                                </li>
                                                                <li>Rencana alih pengetahuan dan teknologi (knowledge transfer);
                                                                </li>
                                                                <li>Mekanisme pemantauan dan pelaporan berkala.</li>
                                                            </ul>
                                                        </li>

                                                        <li>
                                                            <strong>Dokumen Manajemen Risiko</strong>, sebagaimana diatur dalam
                                                            <strong>Pasal 18 ayat (2) huruf a</strong>, wajib menyajikan:
                                                            <ul class="list-disc ml-5">
                                                                <li>Identifikasi risiko yang relevan (teknis, operasional, keamanan,
                                                                    dsb.);</li>
                                                                <li>Deskripsi rinci risiko (termasuk penyebab & dampak);</li>
                                                                <li>Level kemungkinan dan level dampak (dikombinasikan untuk
                                                                    menentukan besaran risiko);</li>
                                                                <li>Status risiko residual (ada atau tidak);</li>
                                                                <li>Apakah perlu penanganan? Jika ya, jelaskan opsi penanganan:
                                                                    Hindari, Mitigasi, Transfer, atau Diterima;</li>
                                                                <li>Rencana aksi mitigasi: jadwal implementasi dan penanggung jawab
                                                                    (PIC);</li>
                                                                <li>Pemantauan risiko secara berkala dan dokumentasi penanganannya.
                                                                </li>
                                                            </ul>

                                                        </li>
                                                    </ul>

                                                </li>
                                            </ul>
                                        </li>

                                        <li>
                                            <strong>Evaluasi dan Hasil Pertimbangan</strong>
                                            <ul class="list-[lower-alpha] ml-8 space-y-1">
                                                <li>Menteri c.q. Direktur Jenderal akan mengevaluasi dokumen dan menerbitkan
                                                    <strong>pertimbangan tertulis</strong>.
                                                </li>
                                                <li>Hasil pertimbangan dapat berupa: <em>disetujui (diberikan rekomendasi)</em> atau
                                                    <em>tidak disetujui (permohonan ditolak)</em>.
                                                </li>
                                            </ul>
                                        </li>

                                        <li>
                                            <strong>Penyesuaian Anggaran</strong><br>
                                            Jika permohonan ditolak, Perangkat Daerah wajib melakukan penyesuaian terhadap rencana
                                            pelaksanaan
                                            investasi TIK dalam dokumen Rencana Kerja dan Anggaran.
                                        </li>

                                        <li>
                                            <strong>Formulir Permohonan</strong><br>
                                            Permohonan disusun menggunakan format surat sesuai Lampiran II Permen Komdigi 6/2025 dan
                                            ditujukan kepada:
                                            <em>Menteri Komunikasi dan Digital c.q. Direktur Jenderal Teknologi Pemerintah
                                                Digital</em>.
                                        </li>

                                    </ol>
                                @break

                                @case('subdomain')
                                    <p>
                                        <b>Subdomain</b><br>
                                        DKISP Provinsi Kalimantan Utara menyediakan layanan subdomain *.kaltaraprov.go.id bagi
                                        Perangkat Daerah dan stakeholder Pemprov Kalimantan Utara. Subdomain merupakan nama unik
                                        pengganti alamat IP untuk mempermudah mengakses alamat suatu Sistem Elektronik.
                                        Subdomain *.kaltaraprov.go.id menunjukkan bahwa Sistem Elektronik tersebut dikelola oleh
                                        Pemprov Kalimantan Utara.
                                    </p>
                                    <p><b>PSE</b><br>
                                        DKISP Provinsi Kalimantan Utara menyediakan layanan Pendaftaran Sistem Elektronik (PSE/<a
                                            href="https://pse.layanan.go.id"
                                            class="text-blue-600 underline hover:text-blue-800">pse.layanan.go.id</a>) bagi seluruh
                                        Perangkat Daerah dan stakeholder Pemprov Kalimantan Utara sesuai dengan ketentuan
                                        Permenkomdigi Nomor 5 Tahun 2025. Layanan ini memastikan bahwa sistem/aplikasi yang
                                        digunakan telah terdaftar sebagai PSE Lingkup Publik pada sistem milik Kementerian
                                        Komunikasi dan Digital, sehingga memenuhi standar tata kelola sistem elektronik yang andal,
                                        aman, dan terintegrasi.

                                        Pendaftaran PSE menunjukkan bahwa sistem tersebut resmi dikelola oleh Pemprov Kalimantan
                                        Utara dan menjadi bagian dari transformasi digital pemerintahan menuju pelayanan publik yang
                                        transparan dan efisien. Sistem yang telah diregistrasi akan mendapatkan tanda
                                        daftar/sertifikat terdaftar elektronik yang dapat disematkan pada antarmuka sistem serta
                                        digunakan sebagai salah satu syarat izin (clearance) untuk pengembangan sistem tersebut.
                                        <br>
                                        📌 Sistem yang wajib didaftarkan meliputi: layanan publik berbasis digital, sistem internal
                                        pemerintahan, portal data, dan aplikasi e-government lainnya.
                                        <br>
                                        📌 Perangkat Daerah wajib melakukan self assessment kategori sistem elektronik berdasarkan
                                        asas resiko pada link berikut ini :
                                        <a href="https://s.id/Form-Risiko-PSE-Kaltara"
                                            class="text-blue-600 underline hover:text-blue-800">s.id/PSE-Kaltara</a>
                                    </p>
                                @break

                                @case('tte')
                                    <div class="space-y-4 text-gray-800">
                                        <p><strong>Syarat Pendaftaran Akun Sertifikat Elektronik/Tanda Tangan Elektronik:</strong>
                                        </p>
                                        <ol class="list-decimal ml-5 space-y-1">
                                            <li>Memiliki akun email resmi Pemprov Kaltara dengan domain
                                                <code>@kaltaraprov.go.id</code>
                                            </li>
                                            <li>Memiliki KTP dengan NIK yang valid dari database Kependudukan Nasional</li>
                                            <li>Memahami dan mengetahui konsekuensi serta resiko terhadap penyalahgunaan akun TTE
                                                yang akan didaftarkan</li>
                                        </ol>

                                        <p><strong>Cara Mendaftarkan Akun TTE:</strong></p>
                                        <ol class="list-decimal ml-5 space-y-1">
                                            <li>Login pada aplikasi E-Layanan dan isi seluruh form
                                            </li>
                                            <li>Admin akan melalukan verifikasi dan melakukan pendaftaran akun</li>
                                            <li>Setelah aktivasi akun, link set passphrase akan dikirimkan ke email resmi. Cek
                                                kembali email untuk melakukan Set Passphrase</li>
                                            <li>Setelah melakukan Set Passphrase, Sertifikat Elektronik anda telah terbit dan siap
                                                digunakan untuk TTE di aplikasi seperti Srikandi, INAPROC, Besign, dan lainnya</li>
                                        </ol>
                                    </div>
                                @break

                                @case('pusatdata')
                                    <p>DKISP Provinsi Kalimantan Utara menyediakan layanan pusat data (Hosting/VPS) untuk
                                        aplikasi-aplikasi yang dimiliki oleh Perangkat Daerah dan stakeholder Pemprov Kalimantan
                                        Utara.
                                        Hosting adalah penyimpanan segala file dan data website agar aplikasi dapat berjalan dan
                                        diakses melalui internet. Hosting tersedia menggunakan WHM CPanel dengan fitur perlidungan
                                        tambahan seperti Imunify360, CageFS, JetBackup dan Litespeed.
                                    </p>
                                    <p>VPS (Virtual Private Server) adalah server virtual yang memiliki spesifikasi hardware dan
                                        software yang lebih tinggi dibandingkan dengan shared hosting. VPS dapat digunakan untuk
                                        aplikasi
                                        yang memerlukan akses yan lebih kompleks.</p>
                                    <p>Layanan Hosting dan VPS dikelola pada Pusat Komputasi Dinas KISP Pemprov Kalimantan Utara dan
                                        resource Pemprov Kalimantan Utara pada Pusat Data Nasional (PDN).</p>
                                @break

                                @case('email')
                                    <p class="text-justify text-gray-800 leading-relaxed mb-4">
                                        Perangkat Daerah yang ingin menggunakan layanan email resmi Pemerintah Provinsi Kalimantan
                                        Utara
                                        (<span class="font-medium text-blue-600">email@kaltaraprov.go.id</span>)
                                        dapat mengisi secara online (login pada aplikasi e-Layanan) informasi mengenai hal-hal sebagai berikut:
                                    </p>

                                    <ol class="list-decimal list-inside text-gray-800 space-y-2 mb-6">
                                        <li>Nama Pemohon</li>
                                        <li>NIP Pemohon</li>
                                        <li>Instansi Pemohon</li>
                                        <li>Nama username email <span class="font-medium text-blue-600">@kaltaraprov.go.id</span>
                                            yang ingin dibuat</li>
                                        <li>Email alternatif yang dimiliki oleh pemohon (Gmail/Yahoo/dll)</li>
                                        <li>Nomor kontak yang dapat dihubungi</li>
                                        <li>
                                            Pernyataan menyetujui
                                            <a href="{{ route('syarat.email') }}"
                                                class="text-blue-600 underline hover:text-blue-800">
                                                Persyaratan/Perjanjian Layanan Email Resmi Pemerintah Provinsi Kalimantan Utara
                                            </a>
                                            dan bertanggung jawab mutlak terhadap penggunaan dan keamanan akun yang diberikan
                                        </li>
                                        
                                    </ol>

                                    <p class="text-justify text-gray-800 leading-relaxed mb-4">
                                        Berdasarkan data di atas, selanjutnya tim teknis Dinas KISP Prov. Kaltara 
                                        memverifikasi permohonan. Jika disetujui, email resmi akan dibuat secara otomatis dan pengguna dapat melihat status permohoannya pada aplikasi E-Layanan.
                                    </p>

                                @break


                                @case('SPBE')
                                    <p>Akses dashboard Arsitektur & Peta Rencana SPBE Provinsi Kalimantan Utara.</p>
                                @break

                                @case('jaringan-internet')
                                    <p>Layanan jaringan internet fiber/satelit untuk Perangkat Daerah dan satuan pendidikan di
                                        Kaltara.</p>
                                @break

                                @case('vpn')
                                    <p>Jaringan privat (VPN) antar perangkat daerah & pemerintah kabupaten/kota se-Kaltara.</p>
                                @break

                                @case('konten-multimedia')
                                    <p>Pembuatan materi promosi dan informasi yang menarik untuk meningkatkan keberlanjutan dan daya
                                        tarik program Pemerintah Daerah</p>
                                @break

                                @case('peliputan')
                                    <p>Menyediakan liputan dan dokumentasi secara terbuka tentang kegiatan dan inisiatif pemerintah
                                        daerah kepada masyarakat. Memberikan informasi dan dokumentasi mengenai kegiatan Pemda
                                        kepada masyarakat, agar masyarakat dapat mengetahui dan memahami program dan kebijakan yang
                                        diambil oleh Pemda.</p>
                                @break

                                @case('span-lapor')
                                    <p>Penyampaian aduan masyarakat melalui SP4N-LAPOR!. Sistem Pengelolaan Pengaduan Pelayanan
                                        Publik Nasional - Layanan Aspirasi dan Pengaduan Online Rakyat (SP4N-LAPOR!) adalah platform
                                        nasional yang dibangun oleh pemerintah Indonesia untuk memfasilitasi masyarakat dalam
                                        menyampaikan aspirasi dan pengaduan terkait pelayanan publik.</p>
                                @break

                                @case('publikasi')
                                    <p>1. Publikasi Berita : Penyebaran informasi publik melalui media cetak, online atau radio.
                                        Meningkatkan transparansi dan informasi bagi masyarakat tentang kegiatan dan kebijakan
                                        pemerintah daerah.</p>
                                    <p>2. Publikasi Media Luar Ruang : Penyebaran informasi publik melalui media informasi luar
                                        ruang berupa stand baliho dan videotron. Menjangkau masyarakat secara luas melalui media
                                        luar ruang untuk meningkatkan kesadaran akan program dan layanan pemerintah daerah.</p>
                                @break

                                @case('portal-data')
                                    <p>Layanan penyediaan data statistik sektoral adalah layanan diseminasi data sektoral Organisasi
                                        Perangkat Daerah melalui Portal Data yang dikelola oleh DKISP.
                                        Menyediakan data statistik sektoral agar dapat digunakan untuk kepentingan Perangkat Daerah
                                        atau masyarakat dalam menunjang pembangunan daerah.
                                    </p>
                                @break

                                @default
                                    <p>Layanan <strong>{{ $item['label'] }}</strong> mendukung transformasi digital Pemprov
                                        Kalimantan Utara.</p>
                            @endswitch
                        </div>

                        <div class="mt-6">
                            <a href="{{ $item['link'] }}"
                                class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-md text-sm font-semibold">
                                Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
@endsection
