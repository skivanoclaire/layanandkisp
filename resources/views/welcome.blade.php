@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col-reverse md:flex-row items-center gap-8">
  <!-- Left Grid -->
  <div class="grid grid-cols-3 md:grid-cols-4 gap-4 flex-grow">
    @php
      $items = [
        ['icon' => '📄', 'label' => 'Rekomendasi', 'slug' => 'rekomendasi', 'image' => 'rekomendasi.png', 'link' => '/login'],
        ['icon' => '📹', 'label' => 'Peliputan', 'slug' => 'peliputan', 'image' => 'peliputan.png', 'link' => '/login'],
        ['icon' => '📰', 'label' => 'Publikasi', 'slug' => 'publikasi', 'image' => 'publikasi.png', 'link' => '/login'],
        ['icon' => '🎞️', 'label' => 'Konten Multimedia', 'slug' => 'konten-multimedia', 'image' => 'konten-multimedia.png', 'link' => '/login'],
        ['icon' => '🏛️', 'label' => 'Pengaduan', 'slug' => 'span-lapor', 'image' => 'lapor.png', 'link' => 'https://www.lapor.go.id/'],
        ['icon' => '🌐', 'label' => 'Subdomain dan PSE', 'slug' => 'subdomain', 'image' => 'subdomain.png', 'link' => '/login'],
        ['icon' => '💾', 'label' => 'Pusat Data', 'slug' => 'pusatdata', 'image' => 'hosting.png', 'link' => '/login'],
        ['icon' => '✉️', 'label' => 'Email', 'slug' => 'email', 'image' => 'email.png', 'link' => '/login'],
        ['icon' => '🛡️', 'label' => 'TTE', 'slug' => 'tte', 'image' => 'tte.png', 'link' => '/logie'],
        ['icon' => '📈', 'label' => 'Portal Data', 'slug' => 'portal-data', 'image' => 'portal-data.png', 'link' => 'https://data.kaltaraprov.go.id'],
        ['icon' => '🖧', 'label' => 'SPLP', 'slug' => 'splp', 'image' => 'splp.png', 'link' => '/login'],
        ['icon' => '📖', 'label' => 'SPBE', 'slug' => 'SPBE', 'image' => 'spbe.png', 'link' => 'https://lookerstudio.google.com/u/1/reporting/f768fb19-c4d8-4cb3-9373-81752c9deadc/page/p_8p0vzpu3ad'],
        ['icon' => '📘', 'label' => 'PPID', 'slug' => 'ppid', 'image' => 'ppid.png', 'link' => 'https://ppid.kaltaraprov.go.id'],
        ['icon' => '🛡️', 'label' => 'Keamanan Informasi', 'slug' => 'keamanan-informasi', 'image' => 'keamanan-informasi.png', 'link' => '/login'],
        ['icon' => '📡', 'label' => 'Jaringan Internet', 'slug' => 'jaringan-internet', 'image' => 'jaringan-internet.png', 'link' => '/login'],
        ['icon' => '🔗', 'label' => 'VPN', 'slug' => 'vpn', 'image' => 'vpn.png', 'link' => '/login'],
        ['icon' => '📶', 'label' => 'Wifi Publik', 'slug' => 'wifi-publik', 'image' => 'wifi-publik.png', 'link' => '/login'],
        ['icon' => '☁️', 'label' => 'Cloud Storage', 'slug' => 'cloud-storage', 'image' => 'cloud-storage.png', 'link' => '/login'],
        ['icon' => '❓', 'label' => 'Helpdesk TIK', 'slug' => 'helpdesk-tik', 'image' => 'helpdesk-tik.png', 'link' => '/login'],
        ['icon' => '🎥', 'label' => 'Zoom/Youtube Live Streaming', 'slug' => 'zoom', 'image' => 'zoom.png', 'link' => '/login'],
      ];
    @endphp

    @foreach ($items as $item)
      <a href="#{{ $item['slug'] }}" class="bg-white shadow rounded-xl p-4 text-center hover:shadow-lg transition block">
        <div class="text-6xl mb-3">{{ $item['icon'] }}</div>
        <div class="text-lg md:text-xl font-bold text-gray-800">{{ $item['label'] }}</div>
      </a>
    @endforeach
  </div>

  <!-- Right illustration -->
  <div class="flex-shrink-0">
    <img src="layanankaltara.png" alt="Ilustrasi" class="w-[650px] max-w-full">
  </div>
</div>

<!-- Section Target Content -->
<div class="max-w-7xl mx-auto mt-20 space-y-1">
  @foreach ($items as $item)
    <section id="{{ $item['slug'] }}" class="pt-24 scroll-mt-24">
      <div class="bg-gradient-to-r from-green-100 to-blue-100 p-8 rounded-lg shadow flex flex-col md:flex-row items-start gap-8">
        <div class="w-32 flex-shrink-0 hidden md:block">
          <img src="{{ asset($item['image']) }}" alt="{{ $item['label'] }}" class="w-full">
        </div>
        <div class="flex-grow">
          <h2 class="text-4xl font-bold text-green-800 mb-6">{{ $item['label'] }}</h2>
          <div class="text-justify text-lg text-gray-800 leading-relaxed space-y-4">
            @switch($item['slug'])
              @case('rekomendasi')
                <p>
                  Pelaksanaan rencana dan anggaran Sistem Pemerintahan Berbasis Elektronik oleh Perangkat Daerah di lingkungan Pemerintah Provinsi Kalimantan Utara <strong>wajib disertai Rekomendasi TIK</strong> sebagaimana diamanatkan oleh <em>Peraturan Gubernur Provinsi Kalimantan Utara Nomor 3 Tahun 2023</em> tentang Perubahan <em>Peraturan Gubernur Nomor 51 Tahun 2019 Tentang Tata Kelola Sistem Manajemen Pemerintahan Berbasis Elektronik</em>. Rekomendasi diajukan oleh Perangkat Daerah kepada <strong>Dinas Komunikasi, Informatika, Statistik, dan Persandian (DKISP) Provinsi Kalimantan Utara</strong>.
                </p>
                <p>Adapun tahapan pengajuan rekomendasi TIK adalah sebagai berikut:</p>
                <ol class="list-decimal ml-5 space-y-1">
                  <li>
                    <strong>Pengajuan Permohonan</strong>
                    <ul class="list-[lower-alpha] ml-8 space-y-1">
                      <li>Perangkat Daerah melakukan pengajuan permohonan rekomendasi pelaksanaan investasi TIK kepada Kepala DKISP Provinsi Kalimantan Utara pada tahun anggaran n-1.</li>
                      <li>
                        Dalam permohonan tersebut, Perangkat Daerah wajib menyusun:
                        <ul class="list-[lower-roman] ml-6 space-y-1">
                          <li>analisis kebutuhan;</li>
                          <li>analisis biaya; dan</li>
                          <li>analisis manfaat dari belanja TIK yang direncanakan.</li>
                        </ul>
                      </li>
                    </ul>
                  </li>
                  <li>
                    <strong>Analisis oleh DKISP</strong><br>
                    DKISP melakukan analisis terhadap permohonan rekomendasi pelaksanaan investasi TIK </em>.
                  </li>
                  <li>
                    <strong>Permohonan Pertimbangan Sebelum Pembangunan Aplikasi</strong><br>
                    Berdasarkan Peraturan Menteri Komunikasi dan Digital Nomor 6 Tahun 2025 tentang Standar Teknis dan Prosedur Pembangunan dan Pengembangan Aplikasi Sistem Pemerintahan Berbasis Elektronik (SPBE), sebelum membangun atau mengembangkan aplikasi SPBE, Pemerintah Provinsi Kalimantan Utara wajib mengajukan permohonan pertimbangan kepada Menteri Komunikasi dan Digital melalui Direktur Jenderal Teknologi Pemerintah Digital. Dokumen permohonan dan hasil analisis menjadi bahan lampiran untuk disampaikan kepada Kementerian Komunikasi dan Digital.
                  </li>
                  <li>
                    <strong>Hasil Analisis</strong>
                    <ul class="list-[lower-alpha] ml-8 space-y-1">
                      <li>Menerbitkan rekomendasi; atau</li>
                      <li>Menolak permohonan.</li>
                    </ul>
                  </li>
                  <li>
                    <strong>Penyesuaian Anggaran</strong><br>
                    Apabila permohonan rekomendasi ditolak, Perangkat Daerah wajib melakukan penyesuaian terhadap pelaksanaan investasi TIK dalam Rencana Kerja Anggaran, sesuai saran dari DKISP.
                  </li>
                  <a href="https://docs.google.com/document/d/1W4dnFBotESNRp1fCUj-Ob6wUiUiqK0Dg/edit?usp=sharing&ouid=107712972640306722151&rtpof=true&sd=true" class="text-blue-600 underline hover:text-blue-800">📌 Download Contoh Dokumen Analisis</a>
                </ol>
                @break

              @case('subdomain')
                <p>
                  <b>Subdomain</b><br>
                  DKISP Provinsi Kalimantan Utara menyediakan layanan subdomain *.kaltaraprov.go.id bagi Perangkat Daerah dan stakeholder Pemprov Kalimantan Utara. Subdomain merupakan nama unik pengganti alamat IP untuk mempermudah mengakses alamat suatu Sistem Elektronik. 
                  Subdomain *.kaltaraprov.go.id menunjukkan bahwa Sistem Elektronik tersebut dikelola oleh Pemprov Kalimantan Utara.
                  <br>
                  <a href="https://docs.google.com/document/d/1fqC1F4Fe6u4-iVJ1sa2ZSHj1ltl9GIeV/edit?usp=sharing&ouid=107712972640306722151&rtpof=true&sd=true" class="text-blue-600 underline hover:text-blue-800">Download Format Permohonan</a>
                </p>
                  <p><b>PSE</b><br>
                    DKISP Provinsi Kalimantan Utara menyediakan layanan Pendaftaran Sistem Elektronik (PSE/<a href="https://pse.layanan.go.id" class="text-blue-600 underline hover:text-blue-800">pse.layanan.go.id</a>) bagi seluruh Perangkat Daerah dan stakeholder Pemprov Kalimantan Utara sesuai dengan ketentuan Permenkomdigi Nomor 5 Tahun 2025. Layanan ini memastikan bahwa sistem/aplikasi yang digunakan telah terdaftar sebagai PSE Lingkup Publik pada sistem milik Kementerian Komunikasi dan Digital, sehingga memenuhi standar tata kelola sistem elektronik yang andal, aman, dan terintegrasi.

Pendaftaran PSE menunjukkan bahwa sistem tersebut resmi dikelola oleh Pemprov Kalimantan Utara dan menjadi bagian dari transformasi digital pemerintahan menuju pelayanan publik yang transparan dan efisien. Sistem yang telah diregistrasi akan mendapatkan tanda daftar/sertifikat terdaftar elektronik yang dapat disematkan pada antarmuka sistem serta digunakan sebagai salah satu syarat izin (clearance) untuk pengembangan sistem tersebut.
<br>
📌 Sistem yang wajib didaftarkan meliputi: layanan publik berbasis digital, sistem internal pemerintahan, portal data, dan aplikasi e-government lainnya.
<br>
📌 Perangkat Daerah wajib melakukan self assessment kategori sistem elektronik berdasarkan asas resiko pada link berikut ini :
<a href="https://s.id/PSE-Kaltara" class="text-blue-600 underline hover:text-blue-800">s.id/PSE-Kaltara</a>
                  </p>
                  
                @break

              @case('pusatdata')
                <p>DKISP Provinsi Kalimantan Utara menyediakan layanan pusat data (Hosting/VPS) untuk aplikasi-aplikasi yang dimiliki oleh Perangkat Daerah dan stakeholder Pemprov Kalimantan Utara. 
                  Hosting adalah penyimpanan segala file dan data website agar aplikasi dapat berjalan dan diakses melalui internet. Hosting tersedia menggunakan WHM CPanel dengan fitur perlidungan tambahan seperti Imunify360, CageFS, JetBackup dan Litespeed.
                  </p>
                  <p>VPS (Virtual Private Server) adalah server virtual yang memiliki spesifikasi hardware dan software yang lebih tinggi dibandingkan dengan shared hosting. VPS dapat digunakan untuk aplikasi 
                  yang memerlukan akses yan lebih kompleks.</p>
                 <p>Layanan Hosting dan VPS dikelola pada Pusat Komputasi Dinas KISP Pemprov Kalimantan Utara dan resource Pemprov Kalimantan Utara pada Pusat Data Nasional (PDN).</p>
                @break

              @case('email')
              
                <p class="text-justify text-gray-800 leading-relaxed mb-4">
                  Perangkat Daerah yang ingin menggunakan layanan email resmi Pemerintah Provinsi Kalimantan Utara 
                  (<span class="font-medium text-blue-600">email@kaltaraprov.go.id</span>) menyiapkan surat permohonan yang 
                  memuat informasi mengenai hal-hal sebagai berikut:
                </p>
              
                <ol class="list-decimal list-inside text-gray-800 space-y-2 mb-6">
                  <li>Nama Pemohon</li>
                  <li>NIP Pemohon</li>
                  <li>Instansi Pemohon</li>
                  <li>Nama username email <span class="font-medium text-blue-600">@kaltaraprov.go.id</span> yang ingin dibuat</li>
                  <li>Email alternatif yang dimiliki oleh pemohon (Gmail/Yahoo/dll)</li>
                  <li>Nomor kontak yang dapat dihubungi</li>
                  <li>
                    Pernyataan menyetujui 
                    <a href="{{ route('syarat.email') }}" class="text-blue-600 underline hover:text-blue-800">
                      Persyaratan/Perjanjian Layanan Email Resmi Pemerintah Provinsi Kalimantan Utara
                    </a> 
                    dan bertanggung jawab mutlak terhadap penggunaan dan keamanan akun yang diberikan
                  </li>
                  <li>Tanda tangan Pemohon</li>
                </ol>
              
                <p class="text-justify text-gray-800 leading-relaxed mb-4">
                  Berdasarkan data di atas, selanjutnya tim teknis Dinas KISP Prov. Kaltara memeriksa dan memverifikasi 
                  kelengkapan berkas. Jika lengkap, tim teknis akan membuat akun email sesuai dengan permohonan dan 
                  menyampaikan data akun kepada Instansi Pemohon.
                </p>
              
                <div class="space-y-2">
                  <a href="https://drive.google.com/file/d/1SyHyHvFtko0HqYb3UAdgj0yTtL2gt9Fh/view?usp=sharing"
                     class="inline-block text-blue-600 hover:text-blue-800 underline">
                    📄 Format Formulir Permohonan
                  </a>
                  <br>
                  <a href="https://drive.google.com/drive/folders/1-sOUvbUDW42jAxKHTgOcaJ25ZayP16FP?usp=sharing"
                     class="inline-block text-blue-600 hover:text-blue-800 underline">
                    📘 Buku Panduan Email Resmi ASN via WEB Browser & Mobile
                  </a>
                </div>
              
              

                @break

                @case('tte')
                <div class="space-y-4 text-gray-800">
                    <p><strong>Syarat Pendaftaran Akun Sertifikat Elektronik/Tanda Tangan Elektronik:</strong></p>
                    <ol class="list-decimal ml-5 space-y-1">
                        <li>Memiliki akun email resmi Pemprov Kaltara dengan domain <code>@kaltaraprov.go.id</code></li>
                        <li>Memiliki KTP dengan NIK yang valid dari database Kependudukan Nasional</li>
                        <li>Memahami dan mengetahui konsekuensi serta resiko terhadap penyalahgunaan akun TTE yang akan didaftarkan</li>
                    </ol>
            
                    <p><strong>Cara Mendaftarkan Akun TTE:</strong></p>
                    <ol class="list-decimal ml-5 space-y-1">
                        <li>Buka <a href="https://s.id/daftarbsre" class="text-blue-600 underline" target="_blank">s.i/daftarbsre</a> dan isi seluruh form</li>
                        <li>Konfirmasi ke verifikator via WA (<a href="https://wa.me/6281350042338" class="text-blue-600 underline" target="_blank">081350042338</a>)</li>
                        <li>Setelah mendapat info dari verifikator akun telah didaftarkan, cek email (<a href="https://webmail.kaltaraprov.go.id" class="text-blue-600 underline" target="_blank">webmail.kaltaraprov.go.id</a>) untuk aktivasi akun</li>
                        <li>Setelah aktivasi akun, link set passphrase akan dikirimkan ke email resmi. Cek kembali email untuk melakukan Set Passphrase</li>
                        <li>Setelah melakukan Set Passphrase, Sertifikat Elektronik anda telah terbit dan siap digunakan untuk TTE di aplikasi seperti Srikandi, INAPROC, Besign, dan lainnya</li>
                    </ol>
                </div>
                @break
            

              @case('SPBE')
                <p>Akses dashboard Arsitektur & Peta Rencana SPBE Provinsi Kalimantan Utara.</p>
                @break

              @case('jaringan-internet')
                <p>Layanan jaringan internet fiber/satelit untuk Perangkat Daerah dan satuan pendidikan di Kaltara.</p>
                @break

              @case('vpn')
                <p>Jaringan privat (VPN) antar perangkat daerah & pemerintah kabupaten/kota se-Kaltara.</p>
                @break

                @case('konten-multimedia')
                <p>Pembuatan materi promosi dan informasi yang menarik untuk meningkatkan keberlanjutan dan daya tarik program Pemerintah Daerah</p>
                @break

                @case('peliputan')
                <p>Menyediakan liputan dan dokumentasi secara terbuka tentang kegiatan dan inisiatif pemerintah daerah kepada masyarakat. Memberikan informasi dan dokumentasi mengenai kegiatan Pemda kepada masyarakat, agar masyarakat dapat mengetahui dan memahami program dan kebijakan yang diambil oleh Pemda.</p>
                @break
        
                @case('span-lapor')
                <p>Penyampaian aduan masyarakat melalui SP4N-LAPOR!. Sistem Pengelolaan Pengaduan Pelayanan Publik Nasional - Layanan Aspirasi dan Pengaduan Online Rakyat (SP4N-LAPOR!) adalah platform nasional yang dibangun oleh pemerintah Indonesia untuk memfasilitasi masyarakat dalam menyampaikan aspirasi dan pengaduan terkait pelayanan publik.</p>
                @break

                @case('publikasi')
                <p>1. Publikasi Berita : Penyebaran informasi publik melalui media cetak, online atau radio. Meningkatkan transparansi dan informasi bagi masyarakat tentang kegiatan dan kebijakan pemerintah daerah.</p>
                <p>2. Publikasi Media Luar Ruang : Penyebaran informasi publik melalui media informasi luar ruang berupa stand baliho dan videotron. Menjangkau masyarakat secara luas melalui media luar ruang untuk meningkatkan kesadaran akan program dan layanan pemerintah daerah.</p>
                @break
                
                @case('portal-data')
                <p>Layanan penyediaan data statistik sektoral adalah layanan diseminasi data sektoral Organisasi Perangkat Daerah melalui Portal Data yang dikelola oleh DKISP.
                  Menyediakan data statistik sektoral agar dapat digunakan untuk kepentingan Perangkat Daerah atau masyarakat dalam menunjang pembangunan daerah. 
                </p>
                @break

              @default
                <p>Layanan <strong>{{ $item['label'] }}</strong> mendukung transformasi digital Pemprov Kalimantan Utara.</p>
            @endswitch
          </div>

          <div class="mt-6">
            <a href="{{ $item['link'] }}" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-md text-sm font-semibold">
              Ajukan Sekarang
            </a>
          </div>
        </div>
      </div>
    </section>
  @endforeach
</div>
@endsection
