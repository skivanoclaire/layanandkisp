@extends('layouts.authenticated')
@section('title', '- Formulir Subdomain Baru')
@section('header-title', 'Formulir Subdomain')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Formulir Permohonan Subdomain Baru</h1>

    <div class="mb-4 text-sm bg-blue-50 border border-blue-200 p-4 rounded">
        <p class="font-semibold mb-2">Informasi Penting:</p>
        <ul class="list-disc list-inside space-y-1 text-gray-700">
            <li>Formulir ini HANYA untuk permohonan subdomain BARU (.kaltaraprov.go.id)</li>
            <li>Pastikan semua data yang diisi akurat dan sesuai kondisi sebenarnya</li>
            <li>Subdomain yang disetujui akan otomatis terdaftar di DNS Cloudflare</li>
            <li>Data teknologi yang Anda isi akan digunakan untuk monitoring dan inventarisasi</li>
        </ul>
    </div>

    <form action="{{ route('user.subdomain.store') }}" method="POST" class="space-y-6" id="subdomainForm">
        @csrf

        {{-- SECTION 1: INFORMASI PEMOHON --}}
        <div class="bg-white rounded shadow-md">
            <div class="bg-green-600 text-white px-6 py-3 rounded-t font-semibold">
                1. Informasi Pemohon
            </div>
            <div class="p-6 space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', auth()->user()->name) }}" required
                            class="w-full border rounded p-2 @error('nama') border-red-500 @enderror">
                        @error('nama')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">NIP <span class="text-red-500">*</span></label>
                        <input type="text" name="nip_display" value="{{ auth()->user()->nip }}" readonly
                            class="w-full border rounded p-2 bg-gray-100 cursor-not-allowed font-mono">
                        <p class="text-xs text-gray-500 mt-1">NIP diambil dari data akun Anda</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Instansi <span class="text-red-500">*</span></label>
                    <select name="unit_kerja_id" required class="w-full border rounded p-2 @error('unit_kerja_id') border-red-500 @enderror">
                        <option value="">- Pilih Instansi -</option>
                        @foreach($unitKerjaList as $uk)
                            <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                                {{ $uk->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_kerja_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Email Pemohon <span class="text-red-500">*</span></label>
                        <input type="email" name="email_pemohon" value="{{ old('email_pemohon', auth()->user()->email) }}" required
                            class="w-full border rounded p-2 @error('email_pemohon') border-red-500 @enderror">
                        @error('email_pemohon')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">No. HP/WA <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" value="{{ old('no_hp', auth()->user()->phone) }}" required
                            class="w-full border rounded p-2 @error('no_hp') border-red-500 @enderror"
                            placeholder="08xxxxxxxxxx">
                        @error('no_hp')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: DETAIL SUBDOMAIN --}}
        <div class="bg-white rounded shadow-md">
            <div class="bg-green-600 text-white px-6 py-3 font-semibold">
                2. Detail Subdomain yang Diminta
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Subdomain Diminta <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="subdomain_requested" id="subdomain_requested"
                            value="{{ old('subdomain_requested') }}" required
                            class="flex-1 border rounded p-2 @error('subdomain_requested') border-red-500 @enderror font-mono"
                            placeholder="contoh"
                            pattern="[a-zA-Z0-9\-]+"
                            title="Hanya huruf, angka, dan tanda hubung (-)">
                        <span class="text-gray-600">.kaltaraprov.go.id</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Hanya huruf, angka, dan tanda hubung (-)</p>
                    <div id="subdomain-availability-message" class="text-sm mt-1"></div>
                    @error('subdomain_requested')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Lokasi Server <span class="text-red-500">*</span></label>
                        <select name="server_location" id="server_location" required
                            class="w-full border rounded p-2 @error('server_location') border-red-500 @enderror">
                            <option value="">- Pilih Lokasi Server -</option>
                            <option value="dkisp" {{ old('server_location') == 'dkisp' ? 'selected' : '' }}>Server DKISP</option>
                            <option value="external" {{ old('server_location') == 'external' ? 'selected' : '' }}>Server Luar DKISP</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih lokasi hosting server Anda</p>
                        @error('server_location')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="ip-address-container" style="display: none;">
                        <label class="block text-sm font-medium mb-1">IP Address Server <span class="text-red-500" id="ip-required">*</span></label>
                        <input type="text" name="ip_address" id="ip_address_input" value="{{ old('ip_address') }}"
                            class="w-full border rounded p-2 @error('ip_address') border-red-500 @enderror font-mono"
                            placeholder="192.168.1.1"
                            pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
                        <p class="text-xs text-gray-500 mt-1">Format: xxx.xxx.xxx.xxx</p>
                        @error('ip_address')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="ip-auto-message" class="hidden">
                        <label class="block text-sm font-medium mb-1">IP Address Server</label>
                        <div class="w-full border rounded p-2 bg-green-50 border-green-300">
                            <p class="text-sm text-green-700 font-semibold">✓ Otomatis Dipilih oleh Sistem</p>
                            <p class="text-xs text-green-600 mt-1">IP akan dialokasikan dari pool DKISP</p>
                        </div>
                        <input type="hidden" name="ip_address_auto" value="auto">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jenis Website/Aplikasi <span class="text-red-500">*</span>
                        <span class="text-gray-500 font-normal text-xs ml-1">(Pilih sesuai kategori penggunaan)</span>
                    </label>
                    <select name="jenis_website" id="jenis_website" required
                        class="w-full border rounded p-2 @error('jenis_website') border-red-500 @enderror">
                        <option value="">- Pilih Jenis -</option>
                        <option value="Website Resmi" {{ old('jenis_website') == 'Website Resmi' ? 'selected' : '' }}>
                            Website Resmi
                        </option>
                        <option value="Aplikasi Layanan Publik" {{ old('jenis_website') == 'Aplikasi Layanan Publik' ? 'selected' : '' }}>
                            Aplikasi Layanan Publik
                        </option>
                        <option value="Aplikasi Administrasi Pemerintah" {{ old('jenis_website') == 'Aplikasi Administrasi Pemerintah' ? 'selected' : '' }}>
                            Aplikasi Administrasi Pemerintah
                        </option>
                        <option value="Aplikasi Fungsi Tertentu" {{ old('jenis_website') == 'Aplikasi Fungsi Tertentu' ? 'selected' : '' }}>
                            Aplikasi Fungsi Tertentu
                        </option>
                    </select>
                    @error('jenis_website')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Panduan Jenis --}}
                    <div id="jenis-help" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm hidden">
                        <p class="font-semibold text-blue-800 mb-1">Panduan Memilih Jenis:</p>
                        <div id="jenis-help-content" class="text-blue-700 text-xs space-y-1"></div>
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION 3: INFORMASI APLIKASI --}}
        <div class="bg-white rounded shadow-md">
            <div class="bg-green-600 text-white px-6 py-3 font-semibold">
                3. Informasi Aplikasi/Website
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Aplikasi/Website <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_aplikasi" value="{{ old('nama_aplikasi') }}" required
                        class="w-full border rounded p-2 @error('nama_aplikasi') border-red-500 @enderror">
                    @error('nama_aplikasi')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi Website/Aplikasi</label>
                    <textarea name="description" rows="3" class="w-full border rounded p-2"
                        placeholder="Deskripsi singkat tentang website/aplikasi yang akan menggunakan subdomain ini">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Latar Belakang Pembuatan <span class="text-red-500">*</span></label>
                    <textarea name="latar_belakang" rows="3" required
                        class="w-full border rounded p-2 @error('latar_belakang') border-red-500 @enderror"
                        placeholder="Jelaskan alasan dan latar belakang pembuatan aplikasi ini">{{ old('latar_belakang') }}</textarea>
                    @error('latar_belakang')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Manfaat Aplikasi <span class="text-red-500">*</span></label>
                    <textarea name="manfaat_aplikasi" rows="3" required
                        class="w-full border rounded p-2 @error('manfaat_aplikasi') border-red-500 @enderror"
                        placeholder="Jelaskan manfaat dan kegunaan aplikasi ini">{{ old('manfaat_aplikasi') }}</textarea>
                    @error('manfaat_aplikasi')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tahun Pembuatan <span class="text-red-500">*</span></label>
                        <input type="number" name="tahun_pembuatan" value="{{ old('tahun_pembuatan', date('Y')) }}"
                            required min="2000" max="{{ date('Y') + 1 }}"
                            class="w-full border rounded p-2 @error('tahun_pembuatan') border-red-500 @enderror">
                        @error('tahun_pembuatan')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Pembuat/Developer <span class="text-red-500">*</span></label>
                        <input type="text" name="developer" value="{{ old('developer') }}" required
                            class="w-full border rounded p-2 @error('developer') border-red-500 @enderror"
                            placeholder="Nama pembuat/tim developer">
                        @error('developer')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                            class="w-full border rounded p-2 @error('contact_person') border-red-500 @enderror"
                            placeholder="Nama penanggung jawab aplikasi">
                        @error('contact_person')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">No. HP Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" required
                            class="w-full border rounded p-2 @error('contact_phone') border-red-500 @enderror"
                            placeholder="08xxxxxxxxxx">
                        @error('contact_phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 4: TEKNOLOGI YANG DIGUNAKAN --}}
        <div class="bg-white rounded shadow-md">
            <div class="bg-green-600 text-white px-6 py-3 font-semibold">
                4. Teknologi yang Digunakan
            </div>
            <div class="p-6 space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Bahasa Pemrograman <span class="text-red-500">*</span></label>
                        <select name="programming_language_id" id="programming_language_id" required
                            class="w-full border rounded p-2 @error('programming_language_id') border-red-500 @enderror">
                            <option value="">- Pilih Bahasa -</option>
                            @foreach($programmingLanguages as $lang)
                                <option value="{{ $lang->id }}" {{ old('programming_language_id') == $lang->id ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('programming_language_id') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('programming_language_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Versi Bahasa</label>
                        <input type="text" name="programming_language_version" value="{{ old('programming_language_version') }}"
                            class="w-full border rounded p-2" placeholder="Contoh: 8.2">
                    </div>
                </div>

                {{-- Input untuk bahasa pemrograman lainnya --}}
                <div id="other-language-container" style="display: none;">
                    <label class="block text-sm font-medium mb-1">Nama Bahasa Pemrograman <span class="text-red-500">*</span></label>
                    <input type="text" name="other_programming_language" id="other_programming_language"
                        value="{{ old('other_programming_language') }}"
                        class="w-full border rounded p-2"
                        placeholder="Contoh: Golang, Rust, Ruby, dll">
                    <p class="text-xs text-gray-500 mt-1">Sebutkan nama bahasa pemrograman yang Anda gunakan</p>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Framework</label>
                        <select name="framework_id" id="framework_id"
                            class="w-full border rounded p-2">
                            <option value="">- Pilih Framework (opsional) -</option>
                            {{-- Will be populated via AJAX based on programming language --}}
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Versi Framework</label>
                        <input type="text" name="framework_version" value="{{ old('framework_version') }}"
                            class="w-full border rounded p-2" placeholder="Contoh: 11.x">
                    </div>
                </div>

                {{-- Input untuk framework lainnya --}}
                <div id="other-framework-container" style="display: none;">
                    <label class="block text-sm font-medium mb-1">Nama Framework</label>
                    <input type="text" name="other_framework" id="other_framework"
                        value="{{ old('other_framework') }}"
                        class="w-full border rounded p-2"
                        placeholder="Contoh: FastAPI, Gin, Actix, dll">
                    <p class="text-xs text-gray-500 mt-1">Sebutkan nama framework yang Anda gunakan</p>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Database <span class="text-red-500">*</span></label>
                        <select name="database_id" required
                            class="w-full border rounded p-2 @error('database_id') border-red-500 @enderror">
                            <option value="">- Pilih Database -</option>
                            @foreach($databases as $db)
                                <option value="{{ $db->id }}" {{ old('database_id') == $db->id ? 'selected' : '' }}>
                                    {{ $db->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('database_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Versi Database</label>
                        <input type="text" name="database_version" value="{{ old('database_version') }}"
                            class="w-full border rounded p-2" placeholder="Contoh: 10.11">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Teknologi Frontend</label>
                    <input type="text" name="frontend_tech" value="{{ old('frontend_tech') }}"
                        class="w-full border rounded p-2"
                        placeholder="Contoh: Vue.js 3, React, Tailwind CSS, Bootstrap, dll">
                    <p class="text-xs text-gray-500 mt-1">Opsional: Sebutkan library/framework frontend yang digunakan</p>
                </div>
            </div>
        </div>

        {{-- SECTION 5: BACKUP & PEMELIHARAAN --}}
        <div class="bg-white rounded shadow-md">
            <div class="bg-green-600 text-white px-6 py-3 font-semibold">
                5. Informasi Backup & Pemeliharaan
            </div>
            <div class="p-6 space-y-4">
                {{-- Info Panel --}}
                <div class="bg-blue-50 border border-blue-200 p-4 rounded text-sm">
                    <p class="font-semibold text-blue-800 mb-2">📌 Penjelasan Backup:</p>
                    <ul class="text-blue-700 text-xs space-y-1 list-disc list-inside">
                        <li><strong>Backup</strong> adalah salinan cadangan data website/aplikasi Anda</li>
                        <li><strong>Frekuensi Backup:</strong> Seberapa sering data dicadangkan</li>
                        <li><strong>Retensi:</strong> Berapa lama backup disimpan sebelum dihapus</li>
                    </ul>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Frekuensi Backup <span class="text-red-500">*</span>
                        </label>
                        <select name="backup_frequency" id="backup_frequency" required
                            class="w-full border rounded p-2 @error('backup_frequency') border-red-500 @enderror">
                            <option value="">- Pilih Frekuensi -</option>
                            <option value="Realtime" {{ old('backup_frequency') == 'Realtime' ? 'selected' : '' }}>
                                Realtime (Setiap ada perubahan)
                            </option>
                            <option value="Harian" {{ old('backup_frequency') == 'Harian' ? 'selected' : '' }}>
                                Harian (Setiap hari)
                            </option>
                            <option value="Mingguan" {{ old('backup_frequency') == 'Mingguan' ? 'selected' : '' }}>
                                Mingguan (Setiap minggu)
                            </option>
                            <option value="Bulanan" {{ old('backup_frequency') == 'Bulanan' ? 'selected' : '' }}>
                                Bulanan (Setiap bulan)
                            </option>
                        </select>
                        @error('backup_frequency')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Retensi Backup <span class="text-red-500">*</span>
                        </label>
                        <select name="backup_retention" id="backup_retention" required
                            class="w-full border rounded p-2 @error('backup_retention') border-red-500 @enderror">
                            <option value="">- Pilih Retensi -</option>
                            <option value="7 hari" {{ old('backup_retention') == '7 hari' ? 'selected' : '' }}>
                                7 hari (1 minggu)
                            </option>
                            <option value="14 hari" {{ old('backup_retention') == '14 hari' ? 'selected' : '' }}>
                                14 hari (2 minggu)
                            </option>
                            <option value="30 hari" {{ old('backup_retention') == '30 hari' ? 'selected' : '' }}>
                                30 hari (1 bulan)
                            </option>
                            <option value="60 hari" {{ old('backup_retention') == '60 hari' ? 'selected' : '' }}>
                                60 hari (2 bulan)
                            </option>
                            <option value="90 hari" {{ old('backup_retention') == '90 hari' ? 'selected' : '' }}>
                                90 hari (3 bulan)
                            </option>
                            <option value="180 hari" {{ old('backup_retention') == '180 hari' ? 'selected' : '' }}>
                                180 hari (6 bulan)
                            </option>
                            <option value="365 hari" {{ old('backup_retention') == '365 hari' ? 'selected' : '' }}>
                                365 hari (1 tahun)
                            </option>
                        </select>
                        @error('backup_retention')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Panduan Backup --}}
                <div id="backup-guide" class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded text-sm">
                    <p class="font-semibold text-green-800 mb-1">💡 Rekomendasi:</p>
                    <div id="backup-guide-content" class="text-green-700 text-xs"></div>
                </div>

                {{-- BCP & DRP Section --}}
                <div class="border-t pt-4 mt-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Business Continuity Plan (BCP) & Disaster Recovery Plan (DRP)</h4>

                    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-sm mb-4">
                        <p class="font-semibold text-yellow-800 mb-2">ℹ️ Apa itu BCP & DRP?</p>
                        <ul class="text-yellow-700 text-xs space-y-1 list-disc list-inside">
                            <li><strong>BCP (Business Continuity Plan):</strong> Rencana untuk menjaga layanan tetap berjalan saat terjadi gangguan</li>
                            <li><strong>DRP (Disaster Recovery Plan):</strong> Rencana untuk memulihkan sistem setelah terjadi bencana atau kegagalan besar</li>
                        </ul>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Apakah memiliki BCP?
                            </label>
                            <select name="has_bcp" id="has_bcp"
                                class="w-full border rounded p-2 @error('has_bcp') border-red-500 @enderror">
                                <option value="">- Pilih -</option>
                                <option value="Ya" {{ old('has_bcp') == 'Ya' ? 'selected' : '' }}>Ya, sudah ada</option>
                                <option value="Dalam Proses" {{ old('has_bcp') == 'Dalam Proses' ? 'selected' : '' }}>Dalam proses penyusunan</option>
                                <option value="Belum" {{ old('has_bcp') == 'Belum' ? 'selected' : '' }}>Belum ada</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Dokumen yang mengatur kelangsungan layanan</p>
                            @error('has_bcp')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Apakah memiliki DRP?
                            </label>
                            <select name="has_drp" id="has_drp"
                                class="w-full border rounded p-2 @error('has_drp') border-red-500 @enderror">
                                <option value="">- Pilih -</option>
                                <option value="Ya" {{ old('has_drp') == 'Ya' ? 'selected' : '' }}>Ya, sudah ada</option>
                                <option value="Dalam Proses" {{ old('has_drp') == 'Dalam Proses' ? 'selected' : '' }}>Dalam proses penyusunan</option>
                                <option value="Belum" {{ old('has_drp') == 'Belum' ? 'selected' : '' }}>Belum ada</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Dokumen prosedur pemulihan dari bencana</p>
                            @error('has_drp')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-medium mb-1">
                            Target Waktu Pemulihan (RTO - Recovery Time Objective)
                        </label>
                        <select name="rto" id="rto"
                            class="w-full border rounded p-2 @error('rto') border-red-500 @enderror">
                            <option value="">- Pilih Target Waktu -</option>
                            <option value="< 1 jam" {{ old('rto') == '< 1 jam' ? 'selected' : '' }}>Kurang dari 1 jam (Sangat Kritikal)</option>
                            <option value="1-4 jam" {{ old('rto') == '1-4 jam' ? 'selected' : '' }}>1-4 jam (Kritikal)</option>
                            <option value="4-24 jam" {{ old('rto') == '4-24 jam' ? 'selected' : '' }}>4-24 jam (Penting)</option>
                            <option value="1-3 hari" {{ old('rto') == '1-3 hari' ? 'selected' : '' }}>1-3 hari (Sedang)</option>
                            <option value="> 3 hari" {{ old('rto') == '> 3 hari' ? 'selected' : '' }}>Lebih dari 3 hari (Rendah)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Berapa lama maksimal website/aplikasi boleh mati sebelum harus dipulihkan?</p>
                        @error('rto')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Jadwal Pemeliharaan</label>
                    <textarea name="maintenance_schedule" rows="2" class="w-full border rounded p-2"
                        placeholder="Contoh: Setiap hari Minggu pukul 00:00 - 03:00 WIB">{{ old('maintenance_schedule') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Opsional: Jadwal rutin pemeliharaan/maintenance website (jika ada downtime terjadwal)</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="has_https" id="has_https" value="1"
                        {{ old('has_https', true) ? 'checked' : '' }}
                        class="rounded border-gray-300">
                    <label for="has_https" class="text-sm">Website menggunakan HTTPS/SSL</label>
                </div>
            </div>
        </div>

        {{-- Hidden fields for Cloudflare configuration (default: false) --}}
        <input type="hidden" name="needs_ssl" value="0">
        <input type="hidden" name="needs_proxy" value="0">

        {{-- SECTION 6: PERSETUJUAN --}}
        <div class="bg-white rounded shadow-md">
            <div class="bg-green-600 text-white px-6 py-3 font-semibold">
                6. Pernyataan & Persetujuan
            </div>
            <div class="p-6">
                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded mb-4">
                    <p class="text-sm text-gray-700 mb-2">
                        Dengan mengajukan permohonan subdomain ini, saya menyatakan bahwa:
                    </p>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                        <li>Semua data yang diisi dalam formulir ini adalah benar dan akurat</li>
                        <li>Subdomain akan digunakan untuk kepentingan resmi Pemerintah Provinsi Kalimantan Utara</li>
                        <li>Saya bertanggung jawab atas konten dan keamanan website/aplikasi yang menggunakan subdomain ini</li>
                        <li>Saya akan menjaga agar website/aplikasi tetap aman dan terupdate</li>
                        <li>Administrator berhak menonaktifkan subdomain jika ditemukan penyalahgunaan</li>
                    </ul>
                </div>

                <div class="flex items-start gap-2">
                    <input type="checkbox" name="consent_true" id="consent_true" value="1" required
                        class="mt-1 rounded border-gray-300 @error('consent_true') border-red-500 @enderror">
                    <label for="consent_true" class="text-sm">
                        Saya menyetujui semua pernyataan di atas dan bersedia mengikuti ketentuan yang berlaku <span class="text-red-500">*</span>
                    </label>
                </div>
                @error('consent_true')
                    <p class="text-red-600 text-sm mt-1 ml-6">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- SUBMIT BUTTONS --}}
        <div class="flex gap-4 justify-end">
            <a href="{{ route('user.subdomain.index') }}"
                class="px-6 py-3 rounded bg-gray-500 hover:bg-gray-600 text-white font-semibold">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-3 rounded bg-green-600 hover:bg-green-700 text-white font-semibold">
                Kirim Permohonan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Subdomain availability check
let subdomainTimeout;
document.getElementById('subdomain_requested').addEventListener('input', function() {
    clearTimeout(subdomainTimeout);
    const subdomain = this.value.trim();
    const messageDiv = document.getElementById('subdomain-availability-message');

    if (subdomain.length < 3) {
        messageDiv.innerHTML = '';
        return;
    }

    messageDiv.innerHTML = '<span class="text-gray-500">Mengecek ketersediaan...</span>';

    subdomainTimeout = setTimeout(() => {
        fetch('{{ route("user.subdomain.check-subdomain") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ subdomain: subdomain })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                messageDiv.innerHTML = '<span class="text-green-600">✓ ' + data.message + '</span>';
            } else {
                messageDiv.innerHTML = '<span class="text-red-600">✗ ' + data.message + '</span>';
            }
        });
    }, 500);
});

// Handle programming language selection - show/hide "Lainnya" input
document.getElementById('programming_language_id').addEventListener('change', function() {
    const langId = this.value;
    const otherLangContainer = document.getElementById('other-language-container');
    const otherLangInput = document.getElementById('other_programming_language');
    const frameworkSelect = document.getElementById('framework_id');

    // Show/hide other language input
    if (langId === 'other') {
        otherLangContainer.style.display = 'block';
        otherLangInput.required = true;
        // Clear framework selection when "Lainnya" is selected
        frameworkSelect.innerHTML = '<option value="">- Pilih Framework (opsional) -</option><option value="other">Lainnya</option>';
        return;
    } else {
        otherLangContainer.style.display = 'none';
        otherLangInput.required = false;
        otherLangInput.value = '';
    }

    // Load frameworks based on selected language
    frameworkSelect.innerHTML = '<option value="">- Loading... -</option>';

    if (!langId) {
        frameworkSelect.innerHTML = '<option value="">- Pilih Framework (opsional) -</option>';
        return;
    }

    fetch('{{ route("user.subdomain.get-frameworks") }}?programming_language_id=' + langId)
        .then(response => response.json())
        .then(data => {
            frameworkSelect.innerHTML = '<option value="">- Pilih Framework (opsional) -</option>';
            data.forEach(framework => {
                const option = document.createElement('option');
                option.value = framework.id;
                option.textContent = framework.name;
                if ('{{ old("framework_id") }}' == framework.id) {
                    option.selected = true;
                }
                frameworkSelect.appendChild(option);
            });
            // Add "Lainnya" option at the end
            const otherOption = document.createElement('option');
            otherOption.value = 'other';
            otherOption.textContent = 'Lainnya';
            if ('{{ old("framework_id") }}' == 'other') {
                otherOption.selected = true;
            }
            frameworkSelect.appendChild(otherOption);
        });
});

// Handle framework selection - show/hide "Lainnya" input
document.getElementById('framework_id').addEventListener('change', function() {
    const frameworkId = this.value;
    const otherFrameworkContainer = document.getElementById('other-framework-container');
    const otherFrameworkInput = document.getElementById('other_framework');

    if (frameworkId === 'other') {
        otherFrameworkContainer.style.display = 'block';
    } else {
        otherFrameworkContainer.style.display = 'none';
        otherFrameworkInput.value = '';
    }
});

// Trigger framework load on page load if language is selected
document.addEventListener('DOMContentLoaded', function() {
    const langId = document.getElementById('programming_language_id').value;
    if (langId) {
        document.getElementById('programming_language_id').dispatchEvent(new Event('change'));
    }

    // Check if "other" options are selected on page load (for old values)
    if (langId === 'other') {
        document.getElementById('other-language-container').style.display = 'block';
        document.getElementById('other_programming_language').required = true;
    }

    const frameworkId = document.getElementById('framework_id').value;
    if (frameworkId === 'other') {
        document.getElementById('other-framework-container').style.display = 'block';
    }
});

// Handle server location selection
document.addEventListener('DOMContentLoaded', function() {
    const serverLocation = document.getElementById('server_location');
    const ipContainer = document.getElementById('ip-address-container');
    const ipInput = document.getElementById('ip_address_input');
    const ipAutoMessage = document.getElementById('ip-auto-message');

    function toggleIPField() {
        const selectedValue = serverLocation.value;

        if (selectedValue === 'dkisp') {
            // Server DKISP - hide manual IP, show auto message
            ipContainer.style.display = 'none';
            ipAutoMessage.classList.remove('hidden');
            ipInput.removeAttribute('required');
            ipInput.value = ''; // Clear manual IP value
        } else if (selectedValue === 'external') {
            // Server Luar - show manual IP, hide auto message
            ipContainer.style.display = 'block';
            ipAutoMessage.classList.add('hidden');
            ipInput.setAttribute('required', 'required');
        } else {
            // No selection - hide both
            ipContainer.style.display = 'none';
            ipAutoMessage.classList.add('hidden');
            ipInput.removeAttribute('required');
        }
    }

    serverLocation.addEventListener('change', toggleIPField);

    // Trigger on page load if old value exists
    if (serverLocation.value) {
        toggleIPField();
    }
});

// Handle jenis website selection - show help text
document.addEventListener('DOMContentLoaded', function() {
    const jenisSelect = document.getElementById('jenis_website');
    const jenisHelp = document.getElementById('jenis-help');
    const jenisHelpContent = document.getElementById('jenis-help-content');

    const helpTexts = {
        'Website Resmi': [
            '• Website profil atau portal resmi instansi/OPD',
            '• Contoh: Website profil Dinas, Badan, atau Instansi'
        ],
        'Aplikasi Layanan Publik': [
            '• Aplikasi yang digunakan oleh masyarakat umum atau seluruh ASN Provinsi Kaltara',
            '• Contoh: Sistem pengaduan online, pendaftaran layanan publik, portal informasi untuk masyarakat'
        ],
        'Aplikasi Administrasi Pemerintah': [
            '• Aplikasi yang dipakai internal ASN/instansi untuk tata kelola kegiatan pemerintahan',
            '• Contoh: SIMPEG, Sistem pengelolaan aset, aplikasi keuangan, sistem persuratan internal'
        ],
        'Aplikasi Fungsi Tertentu': [
            '• API endpoint, aplikasi utilitas, atau tools khusus',
            '• Contoh: REST API, webhook endpoint, aplikasi monitoring, tools open-source'
        ]
    };

    function showJenisHelp() {
        const selectedValue = jenisSelect.value;

        if (selectedValue && helpTexts[selectedValue]) {
            jenisHelpContent.innerHTML = helpTexts[selectedValue].join('<br>');
            jenisHelp.classList.remove('hidden');
        } else {
            jenisHelp.classList.add('hidden');
        }
    }

    jenisSelect.addEventListener('change', showJenisHelp);

    // Trigger on page load if old value exists
    if (jenisSelect.value) {
        showJenisHelp();
    }
});

// Handle backup frequency and retention - show recommendations
document.addEventListener('DOMContentLoaded', function() {
    const backupFrequency = document.getElementById('backup_frequency');
    const backupRetention = document.getElementById('backup_retention');
    const backupGuide = document.getElementById('backup-guide');
    const backupGuideContent = document.getElementById('backup-guide-content');

    const recommendations = {
        'Realtime': {
            'retention': ['7 hari', '14 hari'],
            'text': 'Backup realtime cocok untuk aplikasi dengan perubahan data sangat sering (seperti sistem transaksi). Retensi 7-14 hari sudah cukup karena backup sangat sering dilakukan.'
        },
        'Harian': {
            'retention': ['30 hari', '60 hari'],
            'text': 'Backup harian cocok untuk aplikasi dengan data yang berubah setiap hari. Retensi 30-60 hari memberikan jangkauan recovery yang baik tanpa memakan terlalu banyak storage.'
        },
        'Mingguan': {
            'retention': ['90 hari', '180 hari'],
            'text': 'Backup mingguan cocok untuk website yang jarang berubah. Retensi 3-6 bulan memberikan history yang cukup panjang untuk recovery.'
        },
        'Bulanan': {
            'retention': ['180 hari', '365 hari'],
            'text': 'Backup bulanan cocok untuk website statis atau jarang update. Retensi 6-12 bulan cocok untuk arsip jangka panjang.'
        }
    };

    function showBackupGuide() {
        const frequency = backupFrequency.value;
        const retention = backupRetention.value;

        if (frequency && recommendations[frequency]) {
            const rec = recommendations[frequency];
            let guideText = rec.text;

            // Check if retention matches recommendation
            if (retention) {
                if (rec.retention.includes(retention)) {
                    guideText += '<br><br>✅ <strong>Pilihan Anda sudah sesuai rekomendasi!</strong>';
                } else {
                    guideText += '<br><br>⚠️ <em>Untuk ' + frequency + ', disarankan retensi: ' + rec.retention.join(' atau ') + '</em>';
                }
            }

            backupGuideContent.innerHTML = guideText;
            backupGuide.classList.remove('hidden');
        } else {
            backupGuide.classList.add('hidden');
        }
    }

    backupFrequency.addEventListener('change', showBackupGuide);
    backupRetention.addEventListener('change', showBackupGuide);

    // Trigger on page load if old values exist
    if (backupFrequency.value || backupRetention.value) {
        showBackupGuide();
    }
});
</script>
@endpush
@endsection
