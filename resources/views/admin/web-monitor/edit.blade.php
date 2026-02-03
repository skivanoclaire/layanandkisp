@extends('layouts.authenticated')

@section('title', '- Edit Web Monitor')
@section('header-title', 'Edit Web Monitor')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Website Monitoring</h1>
        <p class="text-gray-600 mt-2">Edit data website {{ $webMonitor->nama_sistem }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.web-monitor.update', $webMonitor) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="instansi_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Instansi <span class="text-red-500">*</span>
                </label>
                <select id="instansi_id"
                        name="instansi_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('instansi_id') border-red-500 @enderror"
                        required>
                    <option value="">-- Pilih Instansi --</option>
                    @foreach($unitKerjas as $unit)
                        <option value="{{ $unit->id }}" {{ old('instansi_id', $webMonitor->instansi_id) == $unit->id ? 'selected' : '' }}>
                            {{ $unit->nama }} ({{ $unit->tipe }})
                        </option>
                    @endforeach
                </select>
                @error('instansi_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="nama_sistem" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Sistem / Aplikasi
                </label>
                <input type="text"
                       id="nama_sistem"
                       name="nama_sistem"
                       value="{{ old('nama_sistem', $webMonitor->nama_sistem) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama_sistem') border-red-500 @enderror"
                       placeholder="Contoh: Portal Aapanelortala, E-Office, dll">
                @error('nama_sistem')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Opsional: Nama sistem atau aplikasi yang berjalan</p>
            </div>

            <div class="mb-4">
                <label for="subdomain" class="block text-sm font-semibold text-gray-700 mb-2">
                    Subdomain
                </label>
                <input type="text"
                       id="subdomain"
                       name="subdomain"
                       value="{{ old('subdomain', $webMonitor->subdomain) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subdomain') border-red-500 @enderror"
                       placeholder="Contoh: diskominfo.kaltaraprov.go.id">
                @error('subdomain')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Opsional: Kosongkan jika IP hanya untuk VM/server tanpa domain</p>
            </div>

            <div class="mb-4">
                <label for="ip_address" class="block text-sm font-semibold text-gray-700 mb-2">
                    IP Address <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="ip_address"
                       name="ip_address"
                       value="{{ old('ip_address', $webMonitor->ip_address) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ip_address') border-red-500 @enderror"
                       required
                       placeholder="Contoh: 103.144.82.251">
                @error('ip_address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="jenis" class="block text-sm font-semibold text-gray-700 mb-2">
                    Jenis <span class="text-red-500">*</span>
                </label>
                <select id="jenis"
                        name="jenis"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('jenis') border-red-500 @enderror"
                        required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($jenisOptions as $option)
                        <option value="{{ $option }}" {{ old('jenis', $webMonitor->jenis) === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                @error('jenis')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-2">
                    Keterangan / Deskripsi
                </label>
                <textarea id="keterangan"
                          name="keterangan"
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('keterangan') border-red-500 @enderror"
                          placeholder="Jelaskan penggunaan IP ini (contoh: VM Database Server, Server Backup, Mailserver Internal, dll)">{{ old('keterangan', $webMonitor->keterangan) }}</textarea>
                @error('keterangan')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Disarankan diisi untuk memudahkan identifikasi penggunaan IP</p>
            </div>

            <!-- Informasi Aplikasi -->
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h3 class="font-semibold text-green-800 mb-4">Informasi Aplikasi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_aplikasi" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Aplikasi
                        </label>
                        <input type="text"
                               id="nama_aplikasi"
                               name="nama_aplikasi"
                               value="{{ old('nama_aplikasi', $webMonitor->nama_aplikasi) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: SIMPEG, SIPD, E-Office">
                    </div>

                    <div>
                        <label for="tahun_pembuatan" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tahun Pembuatan
                        </label>
                        <input type="number"
                               id="tahun_pembuatan"
                               name="tahun_pembuatan"
                               value="{{ old('tahun_pembuatan', $webMonitor->tahun_pembuatan) }}"
                               min="2000"
                               max="{{ date('Y') + 1 }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 2024">
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Deskripsi Website/Aplikasi
                        </label>
                        <textarea id="description"
                                  name="description"
                                  rows="5"
                                  class="ckeditor-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Deskripsi singkat tentang website/aplikasi">{{ old('description', $webMonitor->description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label for="latar_belakang" class="block text-sm font-semibold text-gray-700 mb-2">
                            Latar Belakang Pembuatan
                        </label>
                        <textarea id="latar_belakang"
                                  name="latar_belakang"
                                  rows="5"
                                  class="ckeditor-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Jelaskan alasan dan latar belakang pembuatan aplikasi ini">{{ old('latar_belakang', $webMonitor->latar_belakang) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label for="manfaat_aplikasi" class="block text-sm font-semibold text-gray-700 mb-2">
                            Manfaat Aplikasi
                        </label>
                        <textarea id="manfaat_aplikasi"
                                  name="manfaat_aplikasi"
                                  rows="5"
                                  class="ckeditor-field w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Jelaskan manfaat dan kegunaan aplikasi ini">{{ old('manfaat_aplikasi', $webMonitor->manfaat_aplikasi) }}</textarea>
                    </div>

                    <div>
                        <label for="developer" class="block text-sm font-semibold text-gray-700 mb-2">
                            Developer / Pengembang
                        </label>
                        <input type="text"
                               id="developer"
                               name="developer"
                               value="{{ old('developer', $webMonitor->developer) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Nama developer atau perusahaan">
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-semibold text-gray-700 mb-2">
                            Contact Person
                        </label>
                        <input type="text"
                               id="contact_person"
                               name="contact_person"
                               value="{{ old('contact_person', $webMonitor->contact_person) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Nama contact person">
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            No. Telepon
                        </label>
                        <input type="text"
                               id="contact_phone"
                               name="contact_phone"
                               value="{{ old('contact_phone', $webMonitor->contact_phone) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 081234567890">
                    </div>
                </div>
            </div>

            <!-- Teknologi yang Digunakan -->
            <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h3 class="font-semibold text-purple-800 mb-4">Teknologi yang Digunakan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="programming_language_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Bahasa Pemrograman
                        </label>
                        <select id="programming_language_id"
                                name="programming_language_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Bahasa --</option>
                            @foreach($programmingLanguages as $lang)
                                <option value="{{ $lang->id }}" {{ old('programming_language_id', $webMonitor->programming_language_id) == $lang->id ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="programming_language_version" class="block text-sm font-semibold text-gray-700 mb-2">
                            Versi Bahasa
                        </label>
                        <input type="text"
                               id="programming_language_version"
                               name="programming_language_version"
                               value="{{ old('programming_language_version', $webMonitor->programming_language_version) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 8.2, 3.11">
                    </div>

                    <div>
                        <label for="framework_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Framework
                        </label>
                        <select id="framework_id"
                                name="framework_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Framework --</option>
                            @foreach($frameworks as $framework)
                                <option value="{{ $framework->id }}" {{ old('framework_id', $webMonitor->framework_id) == $framework->id ? 'selected' : '' }}>
                                    {{ $framework->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="framework_version" class="block text-sm font-semibold text-gray-700 mb-2">
                            Versi Framework
                        </label>
                        <input type="text"
                               id="framework_version"
                               name="framework_version"
                               value="{{ old('framework_version', $webMonitor->framework_version) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 10.x, 5.4">
                    </div>

                    <div>
                        <label for="database_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Database
                        </label>
                        <select id="database_id"
                                name="database_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Database --</option>
                            @foreach($databases as $database)
                                <option value="{{ $database->id }}" {{ old('database_id', $webMonitor->database_id) == $database->id ? 'selected' : '' }}>
                                    {{ $database->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="database_version" class="block text-sm font-semibold text-gray-700 mb-2">
                            Versi Database
                        </label>
                        <input type="text"
                               id="database_version"
                               name="database_version"
                               value="{{ old('database_version', $webMonitor->database_version) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 8.0, 15.2">
                    </div>

                    <div class="md:col-span-2">
                        <label for="frontend_tech" class="block text-sm font-semibold text-gray-700 mb-2">
                            Teknologi Frontend
                        </label>
                        <input type="text"
                               id="frontend_tech"
                               name="frontend_tech"
                               value="{{ old('frontend_tech', $webMonitor->frontend_tech) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: React, Vue.js, Bootstrap, jQuery">
                    </div>
                </div>
            </div>

            <!-- Informasi Server -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-semibold text-yellow-800 mb-4">Informasi Server</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="server_ownership" class="block text-sm font-semibold text-gray-700 mb-2">
                            Kepemilikan Server
                        </label>
                        <select id="server_ownership"
                                name="server_ownership"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih --</option>
                            <option value="Provinsi Kaltara" {{ old('server_ownership', $webMonitor->server_ownership) == 'Provinsi Kaltara' ? 'selected' : '' }}>Provinsi Kaltara</option>
                            <option value="Pihak Ketiga" {{ old('server_ownership', $webMonitor->server_ownership) == 'Pihak Ketiga' ? 'selected' : '' }}>Pihak Ketiga</option>
                        </select>
                    </div>

                    <div>
                        <label for="server_owner_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Pemilik/Provider
                        </label>
                        <input type="text"
                               id="server_owner_name"
                               name="server_owner_name"
                               value="{{ old('server_owner_name', $webMonitor->server_owner_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: Diskominfo Kaltara, AWS, DigitalOcean">
                    </div>

                    <div class="md:col-span-2">
                        <label for="server_location_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Lokasi Server
                        </label>
                        <select id="server_location_id"
                                name="server_location_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($serverLocations as $location)
                                <option value="{{ $location->id }}" {{ old('server_location_id', $webMonitor->server_location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Kategori Sistem Elektronik (ESC) -->
            <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                <h3 class="font-semibold text-indigo-800 mb-4">Kategori Sistem Elektronik (ESC)</h3>

                @if($webMonitor->esc_category)
                    <div class="mb-4 p-3 bg-white border-l-4 border-indigo-500 rounded">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Kategori Saat Ini:</p>
                                <p class="text-xl font-bold">
                                    <span class="
                                        @if($webMonitor->esc_category === 'Strategis') text-red-600
                                        @elseif($webMonitor->esc_category === 'Tinggi') text-orange-600
                                        @else text-green-600
                                        @endif
                                    ">{{ $webMonitor->esc_category }}</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Total Skor:</p>
                                <p class="text-xl font-bold text-indigo-700">{{ $webMonitor->esc_total_score }}/50</p>
                            </div>
                        </div>
                        @if($webMonitor->esc_filled_at)
                            <p class="text-xs text-gray-500 mt-2">
                                Terakhir diisi: {{ $webMonitor->esc_filled_at->format('d/m/Y H:i') }}
                                @if($webMonitor->esc_updated_by)
                                    oleh {{ $webMonitor->escUpdatedBy->name ?? 'Admin' }}
                                @endif
                            </p>
                        @endif
                    </div>
                @endif

                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                    <p class="font-semibold text-blue-800 mb-2">Tentang Kuesioner ESC:</p>
                    <ul class="text-blue-700 text-xs space-y-1 list-disc list-inside">
                        <li>Kuesioner ini digunakan untuk mengkategorikan tingkat kekritisan sistem elektronik</li>
                        <li>Terdapat 10 pertanyaan dengan pilihan jawaban A, B, atau C</li>
                        <li>Kategori akan dihitung otomatis: <strong>Strategis (36-50 poin)</strong>, <strong>Tinggi (16-35 poin)</strong>, atau <strong>Rendah (0-15 poin)</strong></li>
                        <li>Isi lengkap 10 pertanyaan atau kosongkan semua (tidak boleh sebagian)</li>
                    </ul>
                </div>

                <!-- Question 1.1 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.1. Nilai investasi sistem elektronik yang terpasang</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_1]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_1', $webMonitor->esc_answers['1_1'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Lebih dari Rp.30 Miliar</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_1]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_1', $webMonitor->esc_answers['1_1'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Lebih dari Rp.3 Miliar s/d Rp.30 Miliar</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_1]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_1', $webMonitor->esc_answers['1_1'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Kurang dari Rp.3 Miliar</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.2 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.2. Total anggaran operasional tahunan yang dialokasikan untuk pengelolaan Sistem Elektronik</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_2]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_2', $webMonitor->esc_answers['1_2'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Lebih dari Rp.10 Miliar</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_2]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_2', $webMonitor->esc_answers['1_2'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Lebih dari Rp.1 Miliar s/d Rp.10 Miliar</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_2]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_2', $webMonitor->esc_answers['1_2'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Kurang dari Rp.1 Miliar</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.3 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.3. Memiliki kewajiban kepatuhan terhadap Peraturan atau Standar tertentu</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_3]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_3', $webMonitor->esc_answers['1_3'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Peraturan atau Standar nasional dan internasional</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_3]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_3', $webMonitor->esc_answers['1_3'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Peraturan atau Standar nasional</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_3]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_3', $webMonitor->esc_answers['1_3'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Tidak ada Peraturan khusus</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.4 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.4. Menggunakan teknik kriptografi khusus untuk keamanan informasi dalam Sistem Elektronik</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_4]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_4', $webMonitor->esc_answers['1_4'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Teknik kriptografi khusus yang disertifikasi oleh Negara</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_4]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_4', $webMonitor->esc_answers['1_4'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Teknik kriptografi sesuai standar industri, tersedia secara publik atau dikembangkan sendiri</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_4]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_4', $webMonitor->esc_answers['1_4'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Tidak ada penggunaan teknik kriptografi</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.5 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.5. Jumlah pengguna Sistem Elektronik</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_5]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_5', $webMonitor->esc_answers['1_5'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Lebih dari 5.000 pengguna</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_5]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_5', $webMonitor->esc_answers['1_5'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] 1.000 sampai dengan 5.000 pengguna</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_5]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_5', $webMonitor->esc_answers['1_5'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Kurang dari 1.000 pengguna</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.6 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.6. Data pribadi yang dikelola Sistem Elektronik</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_6]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_6', $webMonitor->esc_answers['1_6'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Data pribadi yang memiliki hubungan dengan Data Pribadi lainnya</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_6]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_6', $webMonitor->esc_answers['1_6'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Data pribadi yang bersifat individu dan/atau data pribadi yang terkait dengan kepemilikan badan usaha</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_6]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_6', $webMonitor->esc_answers['1_6'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Tidak ada data pribadi</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.7 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.7. Tingkat klasifikasi/kekritisan Data yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau peneroboson keamanan informasi</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_7]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_7', $webMonitor->esc_answers['1_7'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Sangat Rahasia</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_7]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_7', $webMonitor->esc_answers['1_7'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Rahasia dan/ atau Terbatas</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_7]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_7', $webMonitor->esc_answers['1_7'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Biasa</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.8 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.8. Tingkat kekritisan proses yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau peneroboson keamanan informasi</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_8]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_8', $webMonitor->esc_answers['1_8'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Proses yang berisiko mengganggu hajat hidup orang banyak dan memberi dampak langsung pada layanan publik</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_8]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_8', $webMonitor->esc_answers['1_8'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Proses yang berisiko mengganggu hajat hidup orang banyak dan memberi dampak tidak langsung</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_8]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_8', $webMonitor->esc_answers['1_8'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Proses yang hanya berdampak pada bisnis perusahaan</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.9 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.9. Dampak dari kegagalan Sistem Elektronik</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_9]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_9', $webMonitor->esc_answers['1_9'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Tidak tersedianya layanan publik berskala nasional atau membahayakan pertahanan keamanan negara</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_9]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_9', $webMonitor->esc_answers['1_9'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Tidak tersedianya layanan publik dalam 1 provinsi atau lebih</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_9]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_9', $webMonitor->esc_answers['1_9'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Tidak tersedianya layanan publik dalam 1 kabupaten/kota atau lebih</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.10 -->
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="font-semibold text-gray-800 mb-2">1.10. Potensi kerugian atau dampak negatif dari insiden ditembusnya keamanan informasi Sistem Elektronik (sabotase, terorisme)</p>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_10]" value="A" class="mt-1 esc-radio" {{ old('esc_answers.1_10', $webMonitor->esc_answers['1_10'] ?? '') === 'A' ? 'checked' : '' }}>
                            <span class="text-sm">[A] Menimbulkan korban jiwa</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_10]" value="B" class="mt-1 esc-radio" {{ old('esc_answers.1_10', $webMonitor->esc_answers['1_10'] ?? '') === 'B' ? 'checked' : '' }}>
                            <span class="text-sm">[B] Terbatas pada kerugian finansial</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" name="esc_answers[1_10]" value="C" class="mt-1 esc-radio" {{ old('esc_answers.1_10', $webMonitor->esc_answers['1_10'] ?? '') === 'C' ? 'checked' : '' }}>
                            <span class="text-sm">[C] Mengakibatkan gangguan operasional sementara (tidak membahayakan finansial)</span>
                        </label>
                    </div>
                </div>

                <!-- Live Score Display -->
                <div class="mt-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-2 border-indigo-300 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Skor Saat Ini:</p>
                            <p class="text-3xl font-bold text-indigo-700" id="esc-total-score">0</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Kategori:</p>
                            <p class="text-2xl font-bold" id="esc-category">-</p>
                        </div>
                    </div>
                </div>

                <!-- Supporting Document Upload -->
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Dokumen Pendukung (Opsional)</label>
                    @if($webMonitor->esc_document_path)
                        <div class="mb-2 p-2 bg-green-50 border border-green-200 rounded text-sm">
                            <span class="text-green-700">📄 Dokumen saat ini: </span>
                            <a href="{{ asset('storage/' . $webMonitor->esc_document_path) }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ basename($webMonitor->esc_document_path) }}
                            </a>
                        </div>
                    @endif
                    <input type="file" name="esc_document" accept=".pdf,.doc,.docx,.xls,.xlsx"
                        class="w-full border rounded p-2 @error('esc_document') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        Upload dokumen pendukung (PDF, DOC, DOCX, XLS, XLSX, maksimal 10MB)
                        @if($webMonitor->esc_document_path)
                            <br><span class="text-orange-600">Upload file baru akan mengganti dokumen yang sudah ada</span>
                        @endif
                    </p>
                    @error('esc_document')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Klasifikasi Data -->
            <div class="mb-6 p-4 bg-cyan-50 border border-cyan-200 rounded-lg">
                <div class="mb-4">
                    <h3 class="font-semibold text-cyan-800">Klasifikasi Data</h3>
                </div>

                @if($webMonitor->dc_data_name)
                    <div class="mb-4 p-3 bg-white border-l-4 border-cyan-500 rounded">
                        <div class="grid grid-cols-4 gap-2 text-sm">
                            <div>
                                <p class="text-gray-600">Kerahasiaan:</p>
                                <p class="font-semibold text-blue-700">{{ $webMonitor->dc_confidentiality }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Integritas:</p>
                                <p class="font-semibold text-orange-700">{{ $webMonitor->dc_integrity }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Ketersediaan:</p>
                                <p class="font-semibold text-red-700">{{ $webMonitor->dc_availability }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Total Skor:</p>
                                <p class="text-xl font-bold text-cyan-700">{{ $webMonitor->dc_total_score }}/9</p>
                            </div>
                        </div>
                        @if($webMonitor->dc_filled_at)
                            <p class="text-xs text-gray-500 mt-2">
                                Terakhir diisi: {{ $webMonitor->dc_filled_at->format('d/m/Y H:i') }}
                                @if($webMonitor->dc_updated_by)
                                    oleh {{ $webMonitor->dcUpdatedBy->name ?? 'Admin' }}
                                @endif
                            </p>
                        @endif
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Data</label>
                        <input type="text" name="dc_data_name" value="{{ old('dc_data_name', $webMonitor->dc_data_name) }}"
                            class="w-full border rounded p-2 @error('dc_data_name') border-red-500 @enderror"
                            placeholder="Contoh: Data Pribadi ASN, Data Keluarga ASN">
                        @error('dc_data_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Atribut Data</label>
                        <textarea name="dc_data_attributes" rows="2"
                            class="w-full border rounded p-2 @error('dc_data_attributes') border-red-500 @enderror"
                            placeholder="Contoh: Nama, NIK, Alamat, Tempat Tanggal Lahir, dll.">{{ old('dc_data_attributes', $webMonitor->dc_data_attributes) }}</textarea>
                        @error('dc_data_attributes')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Petunjuk Dampak Potensial Button --}}
                    <div class="flex justify-center mb-2">
                        <button type="button" onclick="showDcInfoModal()" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg text-base font-medium shadow-md transition-all hover:shadow-lg">
                            <i class="fas fa-info-circle mr-2"></i> Petunjuk Pengisian Dampak Potensial
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Kerahasiaan --}}
                        <div class="border-l-4 border-blue-500 pl-3">
                            <label class="block text-sm font-semibold mb-2">Kerahasiaan (Confidentiality)</label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_confidentiality" value="Rendah" class="mt-1 dc-edit-radio" {{ old('dc_confidentiality', $webMonitor->dc_confidentiality) == 'Rendah' ? 'checked' : '' }}>
                                    <span>Pengungkapan informasi yang tidak sah berdampak <strong class="text-green-600">rendah</strong> pada aktivitas dan aset individu, organisasi, atau nasional.</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_confidentiality" value="Sedang" class="mt-1 dc-edit-radio" {{ old('dc_confidentiality', $webMonitor->dc_confidentiality) == 'Sedang' ? 'checked' : '' }}>
                                    <span>Pengungkapan informasi yang tidak sah berdampak <strong class="text-orange-600">sedang</strong> pada aktivitas dan aset individu, organisasi, atau nasional.</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_confidentiality" value="Tinggi" class="mt-1 dc-edit-radio" {{ old('dc_confidentiality', $webMonitor->dc_confidentiality) == 'Tinggi' ? 'checked' : '' }}>
                                    <span>Pengungkapan informasi yang tidak sah berdampak <strong class="text-red-600">tinggi</strong> pada aktivitas dan aset individu, organisasi, atau nasional.</span>
                                </label>
                            </div>
                        </div>

                        {{-- Integritas --}}
                        <div class="border-l-4 border-orange-500 pl-3">
                            <label class="block text-sm font-semibold mb-2">Integritas (Integrity)</label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_integrity" value="Rendah" class="mt-1 dc-edit-radio" {{ old('dc_integrity', $webMonitor->dc_integrity) == 'Rendah' ? 'checked' : '' }}>
                                    <span>Perubahan atau perusakan informasi berdampak <strong class="text-green-600">rendah</strong> pada aktivitas dan aset individu, organisasi, atau nasional.</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_integrity" value="Sedang" class="mt-1 dc-edit-radio" {{ old('dc_integrity', $webMonitor->dc_integrity) == 'Sedang' ? 'checked' : '' }}>
                                    <span>Perubahan atau perusakan informasi berdampak <strong class="text-orange-600">sedang</strong> pada aktivitas dan aset individu, organisasi, atau nasional.</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_integrity" value="Tinggi" class="mt-1 dc-edit-radio" {{ old('dc_integrity', $webMonitor->dc_integrity) == 'Tinggi' ? 'checked' : '' }}>
                                    <span>Perubahan atau perusakan informasi berdampak <strong class="text-red-600">tinggi</strong> pada aktivitas dan aset individu, organisasi, atau nasional.</span>
                                </label>
                            </div>
                        </div>

                        {{-- Ketersediaan --}}
                        <div class="border-l-4 border-red-500 pl-3">
                            <label class="block text-sm font-semibold mb-2">Ketersediaan (Availability)</label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_availability" value="Rendah" class="mt-1 dc-edit-radio" {{ old('dc_availability', $webMonitor->dc_availability) == 'Rendah' ? 'checked' : '' }}>
                                    <span>Gangguan terhadap Akses untuk membuka atau menggunakan informasi berdampak <strong class="text-green-600">rendah</strong> pada aktivitas organisasi, aset organisasi, atau individu.</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_availability" value="Sedang" class="mt-1 dc-edit-radio" {{ old('dc_availability', $webMonitor->dc_availability) == 'Sedang' ? 'checked' : '' }}>
                                    <span>Gangguan terhadap Akses untuk membuka atau menggunakan informasi berdampak <strong class="text-orange-600">sedang</strong> pada aktivitas organisasi, aset organisasi, atau individu.</span>
                                </label>
                                <label class="flex items-start gap-2 text-xs cursor-pointer p-2 rounded border hover:bg-gray-50">
                                    <input type="radio" name="dc_availability" value="Tinggi" class="mt-1 dc-edit-radio" {{ old('dc_availability', $webMonitor->dc_availability) == 'Tinggi' ? 'checked' : '' }}>
                                    <span>Gangguan terhadap Akses untuk membuka atau menggunakan informasi berdampak <strong class="text-red-600">tinggi</strong> pada aktivitas organisasi, aset organisasi, atau individu.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-cyan-50 to-blue-50 border-2 border-cyan-300 rounded-lg p-3">
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div>
                                <p class="text-xs text-gray-600">Kerahasiaan</p>
                                <p class="text-xl font-bold text-blue-700" id="dc-edit-score-conf">0</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Integritas</p>
                                <p class="text-xl font-bold text-orange-700" id="dc-edit-score-int">0</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Ketersediaan</p>
                                <p class="text-xl font-bold text-red-700" id="dc-edit-score-avail">0</p>
                            </div>
                            <div class="border-l-2 border-cyan-400">
                                <p class="text-xs text-gray-600">Total Skor</p>
                                <p class="text-2xl font-bold text-cyan-700" id="dc-edit-total-score">0</p>
                                <p class="text-xs text-gray-500">(dari 15)</p>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-cyan-200 text-center">
                            <p class="text-xs text-gray-600 mb-1">Kategori:</p>
                            <p class="text-lg font-bold" id="dc-edit-category">-</p>
                            <p class="text-xs text-gray-500 mt-1">
                                ≥13: Tinggi | 9-12: Sedang | ≤8: Rendah
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                <label class="flex items-start gap-3">
                    <input type="checkbox"
                           name="is_proxied"
                           class="mt-1"
                           {{ old('is_proxied', $webMonitor->is_proxied) ? 'checked' : '' }}>
                    <div>
                        <span class="font-semibold text-gray-800">Aktifkan Cloudflare Proxy</span>
                        <p class="text-sm text-gray-600 mt-1">
                            Jika dicentang, traffic akan melalui Cloudflare untuk keamanan dan performa lebih baik
                        </p>
                    </div>
                </label>
            </div>

            @if($webMonitor->cloudflare_record_id)
                <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <label class="flex items-start gap-3">
                        <input type="checkbox"
                               name="update_cloudflare"
                               class="mt-1"
                               {{ old('update_cloudflare') ? 'checked' : '' }}>
                        <div>
                            <span class="font-semibold text-orange-800">Update DNS Record di Cloudflare</span>
                            <p class="text-sm text-orange-700 mt-1">
                                Jika dicentang, perubahan akan diterapkan ke Cloudflare DNS record
                            </p>
                            <p class="text-xs text-orange-600 mt-1">
                                Cloudflare Record ID: <code class="bg-white px-2 py-1 rounded">{{ $webMonitor->cloudflare_record_id }}</code>
                            </p>
                        </div>
                    </label>
                </div>
            @endif

            @if($webMonitor->last_checked_at)
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Informasi Status Terakhir</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Status:</span>
                            <span class="ml-2 font-semibold {{ $webMonitor->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $webMonitor->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Terakhir Dicek:</span>
                            <span class="ml-2">{{ $webMonitor->last_checked_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        @if($webMonitor->check_error)
                            <div class="col-span-2">
                                <span class="text-gray-600">Error:</span>
                                <span class="ml-2 text-red-600">{{ $webMonitor->check_error }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Hidden field to track return URL -->
            <input type="hidden" name="return_to" value="{{ request()->query('from') }}">

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Update
                </button>
                @php
                    $backUrl = request()->query('from') === 'check-ip'
                        ? route('admin.web-monitor.check-ip-publik')
                        : route('admin.web-monitor.index');
                @endphp
                <a href="{{ $backUrl }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ESC Questionnaire - Real-time Score Calculation
document.addEventListener('DOMContentLoaded', function() {
    const scoreMap = { 'A': 5, 'B': 2, 'C': 1 };
    const questionIds = ['1_1', '1_2', '1_3', '1_4', '1_5', '1_6', '1_7', '1_8', '1_9', '1_10'];

    function calculateEscScore() {
        let totalScore = 0;
        let answeredCount = 0;

        questionIds.forEach(qId => {
            const selected = document.querySelector(`input[name="esc_answers[${qId}]"]:checked`);
            if (selected) {
                totalScore += scoreMap[selected.value];
                answeredCount++;
            }
        });

        document.getElementById('esc-total-score').textContent = totalScore;

        let category = '-';
        let categoryColor = 'text-gray-600';

        if (answeredCount === 10) {
            if (totalScore >= 36) {
                category = 'Strategis';
                categoryColor = 'text-red-600';
            } else if (totalScore >= 16) {
                category = 'Tinggi';
                categoryColor = 'text-orange-600';
            } else {
                category = 'Rendah';
                categoryColor = 'text-green-600';
            }
        } else if (answeredCount > 0) {
            category = 'Tidak Lengkap';
            categoryColor = 'text-gray-500';
        }

        const categoryEl = document.getElementById('esc-category');
        categoryEl.textContent = category;
        categoryEl.className = `text-2xl font-bold ${categoryColor}`;
    }

    // Attach event listeners to all radio buttons
    document.querySelectorAll('.esc-radio').forEach(radio => {
        radio.addEventListener('change', calculateEscScore);
    });

    // Calculate initial score on page load
    calculateEscScore();

    // Data Classification Score Calculation
    function calculateDcEditScore() {
        const scoreMap = { 'Rendah': 1, 'Sedang': 3, 'Tinggi': 5 };

        const confValue = document.querySelector('input[name="dc_confidentiality"]:checked')?.value;
        const intValue = document.querySelector('input[name="dc_integrity"]:checked')?.value;
        const availValue = document.querySelector('input[name="dc_availability"]:checked')?.value;

        const confScore = scoreMap[confValue] || 0;
        const intScore = scoreMap[intValue] || 0;
        const availScore = scoreMap[availValue] || 0;
        const totalScore = confScore + intScore + availScore;

        document.getElementById('dc-edit-score-conf').textContent = confScore;
        document.getElementById('dc-edit-score-int').textContent = intScore;
        document.getElementById('dc-edit-score-avail').textContent = availScore;
        document.getElementById('dc-edit-total-score').textContent = totalScore;

        // Update category based on total score
        let category = '-';
        let categoryColor = 'text-gray-600';

        if (confValue && intValue && availValue) {
            if (totalScore >= 13) {
                category = 'Tinggi';
                categoryColor = 'text-red-600';
            } else if (totalScore >= 9) {
                category = 'Sedang';
                categoryColor = 'text-orange-600';
            } else {
                category = 'Rendah';
                categoryColor = 'text-green-600';
            }
        }

        const categoryEl = document.getElementById('dc-edit-category');
        categoryEl.textContent = category;
        categoryEl.className = `text-lg font-bold ${categoryColor}`;
    }

    // Attach event listeners to DC radio buttons
    document.querySelectorAll('.dc-edit-radio').forEach(radio => {
        radio.addEventListener('change', calculateDcEditScore);
    });

    // Calculate DC score on page load
    calculateDcEditScore();
});

// Show Data Classification Info Modal
function showDcInfoModal() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.style.zIndex = '9999';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-cyan-600 text-white px-6 py-4 flex justify-between items-center sticky top-0">
                <h3 class="text-xl font-bold">Klasifikasi Dampak Potensial</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                    <div>
                        <h4 class="text-lg font-bold text-green-700 mb-3 text-center">Rendah</h4>
                        <div class="space-y-3">
                            <div><p class="font-semibold text-blue-700">Nasional</p><ul class="list-disc list-inside space-y-1">
                                <li>Urusan pemerintahan sehari-hari, pemberian layanan, dan keuangan publik</li>
                                <li>Hubungan internasional rutin dan kegiatan diplomatik</li>
                                <li>Keamanan publik, peradilan pidana dan kegiatan penegakan hukum</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Organisasi</p><ul class="list-disc list-inside">
                                <li>Kerusakan terbatas pada operasi dan layanan bisnis rutin organisasi</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Individu</p><ul class="list-disc list-inside">
                                <li>Informasi pribadi yang harus dilindungi berdasarkan undang-undang</li>
                            </ul></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-orange-700 mb-3 text-center">Sedang</h4>
                        <div class="space-y-3">
                            <div><p class="font-semibold text-blue-700">Nasional</p><ul class="list-disc list-inside space-y-1">
                                <li>Keselamatan, keamanan atau kemakmuran Indonesia dengan mempengaruhi kepentingan finansial</li>
                                <li>Keamanan aset Infrastruktur Nasional yang penting</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Organisasi</p><ul class="list-disc list-inside">
                                <li>Kerusakan sedang pada operasi dan layanan rutin organisasi</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Individu</p><ul class="list-disc list-inside">
                                <li>Mengancam kehidupan, kebebasan, atau keselamatan seseorang secara langsung</li>
                            </ul></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-red-700 mb-3 text-center">Tinggi</h4>
                        <div class="space-y-3">
                            <div><p class="font-semibold text-blue-700">Nasional</p><ul class="list-disc list-inside space-y-1">
                                <li>Mengancam secara langsung stabilitas internal Indonesia atau negara sahabat</li>
                                <li>Kerusakan jangka panjang bagi perekonomian Indonesia</li>
                                <li>Gangguan besar terhadap aset Infrastruktur Nasional Penting</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Organisasi</p><ul class="list-disc list-inside">
                                <li>Kerusakan fatal terhadap operasi dan layanan rutin organisasi</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Individu</p><ul class="list-disc list-inside">
                                <li>Menyebabkan langsung hilangnya nyawa secara luas</li>
                            </ul></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-100 px-6 py-4 flex justify-end">
                <button onclick="this.closest('.fixed').remove()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded">Tutup</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

<script>
// CKEditor Initialization
document.addEventListener('DOMContentLoaded', function() {
    const ckeditorFields = document.querySelectorAll('.ckeditor-field');
    const editorInstances = {};

    ckeditorFields.forEach(function(textarea) {
        ClassicEditor
            .create(textarea, {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'link', '|',
                        'undo', 'redo'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                    ]
                }
            })
            .then(editor => {
                editorInstances[textarea.id] = editor;
                console.log('CKEditor initialized for:', textarea.id);
            })
            .catch(error => {
                console.error('Error initializing CKEditor for', textarea.id, error);
            });
    });
});
</script>
@endpush
