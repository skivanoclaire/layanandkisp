@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Usulan Rekomendasi Aplikasi</h1>
            <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi }} - {{ $proposal->ticket_number }}</p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center flex-1" id="step-indicator-1">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">
                        1
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Informasi Dasar</p>
                    </div>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2"></div>
                <div class="flex items-center flex-1" id="step-indicator-2">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">
                        2
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Analisis Kebutuhan</p>
                    </div>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2"></div>
                <div class="flex items-center flex-1" id="step-indicator-3">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">
                        3
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Perencanaan</p>
                    </div>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2"></div>
                <div class="flex items-center flex-1" id="step-indicator-4">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">
                        4
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Manajemen Risiko</p>
                    </div>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2"></div>
                <div class="flex items-center flex-1" id="step-indicator-5">
                    <div class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">
                        5
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Review</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Display revision notice --}}
        @if($proposal->status === 'perlu_revisi' && $proposal->verifikasi)
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-orange-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800">Catatan Revisi dari Verifikator</h3>
                        <p class="text-sm text-orange-700 mt-1">{{ $proposal->verifikasi->catatan_verifikasi }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Display validation errors --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-red-800 mb-2">Terdapat kesalahan pada form:</h3>
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Display success/error messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <form id="usulan-form" method="POST" action="{{ route('user.rekomendasi.usulan.update', $proposal->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Step 1: Informasi Dasar -->
            <div id="step-1" class="step-content bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Dasar Aplikasi</h2>

                <!-- Petunjuk Pengisian Step 1 -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <h3 class="text-base font-semibold text-blue-900 mb-2">📋 Petunjuk Pengisian</h3>
                    <div class="text-sm text-blue-800 space-y-2">
                        <p><strong>Contoh pengisian:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li><strong>Nama Aplikasi:</strong> "Sistem Informasi Kepegawaian Daerah (SIKD)" atau "E-Perizinan Terpadu"</li>
                            <li><strong>Deskripsi:</strong> Jelaskan fungsi utama aplikasi, contoh: "Aplikasi untuk mengelola data kepegawaian ASN meliputi absensi, cuti, kenaikan pangkat, dan pensiun secara digital"</li>
                            <li><strong>Tujuan:</strong> Sebutkan target yang ingin dicapai, contoh: "Meningkatkan efisiensi pengelolaan kepegawaian hingga 60% dan mengurangi penggunaan kertas"</li>
                            <li><strong>Manfaat:</strong> Jelaskan keuntungan bagi stakeholder, contoh: "Pegawai dapat mengajukan cuti online, HRD dapat memantau real-time, Pimpinan mendapat dashboard analitik"</li>
                            <li><strong>Platform:</strong> Pilih sesuai kebutuhan, Web untuk akses browser, Mobile untuk aplikasi HP, Desktop untuk aplikasi komputer</li>
                            <li><strong>Estimasi Biaya:</strong> Perkiraan total biaya termasuk pengembangan, infrastruktur, dan maintenance tahun pertama</li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Nama Aplikasi -->
                    <div>
                        <label for="nama_aplikasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Aplikasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_aplikasi" name="nama_aplikasi"
                            value="{{ old('nama_aplikasi', $proposal->nama_aplikasi) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('nama_aplikasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi Aplikasi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field"
                            >{{ old('deskripsi', $proposal->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tujuan -->
                    <div>
                        <label for="tujuan" class="block text-sm font-medium text-gray-700 mb-1">
                            Tujuan Pengembangan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="tujuan" name="tujuan" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field"
                            >{{ old('tujuan', $proposal->tujuan) }}</textarea>
                        @error('tujuan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Manfaat -->
                    <div>
                        <label for="manfaat" class="block text-sm font-medium text-gray-700 mb-1">
                            Manfaat yang Diharapkan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="manfaat" name="manfaat" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field"
                            >{{ old('manfaat', $proposal->manfaat) }}</textarea>
                        @error('manfaat')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pemilik Proses Bisnis -->
                    <div>
                        <label for="pemilik_proses_bisnis_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Pemilik Proses Bisnis <span class="text-red-500">*</span>
                        </label>
                        <select id="pemilik_proses_bisnis_id" name="pemilik_proses_bisnis_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($unitKerjaList as $uk)
                                <option value="{{ $uk->id }}" {{ old('pemilik_proses_bisnis_id', $proposal->pemilik_proses_bisnis_id) == $uk->id ? 'selected' : '' }}>
                                    {{ $uk->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('pemilik_proses_bisnis_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Layanan -->
                    <div>
                        <label for="jenis_layanan" class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Layanan <span class="text-red-500">*</span>
                        </label>
                        <select id="jenis_layanan" name="jenis_layanan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Pilih Jenis Layanan</option>
                            <option value="internal" {{ old('jenis_layanan', $proposal->jenis_layanan) == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="eksternal" {{ old('jenis_layanan', $proposal->jenis_layanan) == 'eksternal' ? 'selected' : '' }}>Eksternal</option>
                            <option value="hybrid" {{ old('jenis_layanan', $proposal->jenis_layanan) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                        @error('jenis_layanan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Target Pengguna -->
                    <div>
                        <label for="target_pengguna" class="block text-sm font-medium text-gray-700 mb-1">
                            Target Pengguna <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="target_pengguna" name="target_pengguna"
                            value="{{ old('target_pengguna', $proposal->target_pengguna) }}"
                            placeholder="Contoh: Pegawai Internal, Masyarakat Umum, UMKM"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('target_pengguna')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimasi Pengguna -->
                    <div>
                        <label for="estimasi_pengguna" class="block text-sm font-medium text-gray-700 mb-1">
                            Estimasi Jumlah Pengguna <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="estimasi_pengguna" name="estimasi_pengguna"
                            value="{{ old('estimasi_pengguna', $proposal->estimasi_pengguna) }}"
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('estimasi_pengguna')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lingkup Aplikasi -->
                    <div>
                        <label for="lingkup_aplikasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Lingkup Aplikasi <span class="text-red-500">*</span>
                        </label>
                        <select id="lingkup_aplikasi" name="lingkup_aplikasi"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Pilih Lingkup</option>
                            <option value="lokal" {{ old('lingkup_aplikasi', $proposal->lingkup_aplikasi) == 'lokal' ? 'selected' : '' }}>Lokal</option>
                            <option value="regional" {{ old('lingkup_aplikasi', $proposal->lingkup_aplikasi) == 'regional' ? 'selected' : '' }}>Regional</option>
                            <option value="nasional" {{ old('lingkup_aplikasi', $proposal->lingkup_aplikasi) == 'nasional' ? 'selected' : '' }}>Nasional</option>
                        </select>
                        @error('lingkup_aplikasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Platform -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Platform <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            @php
                                $platformValues = old('platform', $proposal->platform ?? []);
                            @endphp
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" name="platform[]" value="web"
                                    {{ in_array('web', $platformValues) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2">Web</span>
                            </label>
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" name="platform[]" value="mobile"
                                    {{ in_array('mobile', $platformValues) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2">Mobile</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="platform[]" value="desktop"
                                    {{ in_array('desktop', $platformValues) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2">Desktop</span>
                            </label>
                        </div>
                        @error('platform')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Teknologi yang Diusulkan -->
                    <div>
                        <label for="teknologi_diusulkan" class="block text-sm font-medium text-gray-700 mb-1">
                            Teknologi yang Diusulkan
                        </label>
                        <input type="text" id="teknologi_diusulkan" name="teknologi_diusulkan"
                            value="{{ old('teknologi_diusulkan', $proposal->teknologi_diusulkan) }}"
                            placeholder="Contoh: Laravel, React, PostgreSQL"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('teknologi_diusulkan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimasi Waktu Pengembangan -->
                    <div>
                        <label for="estimasi_waktu_pengembangan" class="block text-sm font-medium text-gray-700 mb-1">
                            Estimasi Waktu Pengembangan (bulan) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="estimasi_waktu_pengembangan" name="estimasi_waktu_pengembangan"
                            value="{{ old('estimasi_waktu_pengembangan', $proposal->estimasi_waktu_pengembangan) }}"
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('estimasi_waktu_pengembangan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimasi Biaya -->
                    <div>
                        <label for="estimasi_biaya" class="block text-sm font-medium text-gray-700 mb-1">
                            Estimasi Biaya (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="estimasi_biaya" name="estimasi_biaya"
                            value="{{ old('estimasi_biaya', $proposal->estimasi_biaya) }}"
                            min="0" step="1000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('estimasi_biaya')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Pendanaan -->
                    <div>
                        <label for="sumber_pendanaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Sumber Pendanaan <span class="text-red-500">*</span>
                        </label>
                        <select id="sumber_pendanaan" name="sumber_pendanaan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Pilih Sumber Pendanaan</option>
                            <option value="apbd" {{ old('sumber_pendanaan', $proposal->sumber_pendanaan) == 'apbd' ? 'selected' : '' }}>APBD</option>
                            <option value="apbn" {{ old('sumber_pendanaan', $proposal->sumber_pendanaan) == 'apbn' ? 'selected' : '' }}>APBN</option>
                            <option value="hibah" {{ old('sumber_pendanaan', $proposal->sumber_pendanaan) == 'hibah' ? 'selected' : '' }}>Hibah</option>
                            <option value="swasta" {{ old('sumber_pendanaan', $proposal->sumber_pendanaan) == 'swasta' ? 'selected' : '' }}>Swasta</option>
                            <option value="lainnya" {{ old('sumber_pendanaan', $proposal->sumber_pendanaan) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('sumber_pendanaan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Integrasi dengan Sistem Lain -->
                    <div>
                        <label for="integrasi_sistem_lain" class="block text-sm font-medium text-gray-700 mb-1">
                            Integrasi dengan Sistem Lain?
                        </label>
                        <select id="integrasi_sistem_lain" name="integrasi_sistem_lain"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="tidak" {{ old('integrasi_sistem_lain', $proposal->integrasi_sistem_lain) == 'tidak' ? 'selected' : '' }}>Tidak</option>
                            <option value="ya" {{ old('integrasi_sistem_lain', $proposal->integrasi_sistem_lain) == 'ya' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>

                    <!-- Detail Integrasi (conditional) -->
                    <div id="detail-integrasi-wrapper" style="display: {{ old('integrasi_sistem_lain', $proposal->integrasi_sistem_lain) == 'ya' ? 'block' : 'none' }}">
                        <label for="detail_integrasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Detail Sistem yang Akan Diintegrasikan
                        </label>
                        <textarea id="detail_integrasi" name="detail_integrasi" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('detail_integrasi', $proposal->detail_integrasi) }}</textarea>
                    </div>

                    <!-- Kebutuhan Khusus -->
                    <div>
                        <label for="kebutuhan_khusus" class="block text-sm font-medium text-gray-700 mb-1">
                            Kebutuhan Khusus (jika ada)
                        </label>
                        <textarea id="kebutuhan_khusus" name="kebutuhan_khusus" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('kebutuhan_khusus', $proposal->kebutuhan_khusus) }}</textarea>
                    </div>

                    <!-- Dampak Jika Tidak Dibangun -->
                    <div>
                        <label for="dampak_tidak_dibangun" class="block text-sm font-medium text-gray-700 mb-1">
                            Dampak Jika Tidak Dibangun
                        </label>
                        <textarea id="dampak_tidak_dibangun" name="dampak_tidak_dibangun" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('dampak_tidak_dibangun', $proposal->dampak_tidak_dibangun) }}</textarea>
                    </div>

                    <!-- Prioritas -->
                    <div>
                        <label for="prioritas" class="block text-sm font-medium text-gray-700 mb-1">
                            Prioritas <span class="text-red-500">*</span>
                        </label>
                        <select id="prioritas" name="prioritas"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Pilih Prioritas</option>
                            <option value="rendah" {{ old('prioritas', $proposal->prioritas) == 'rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="sedang" {{ old('prioritas', $proposal->prioritas) == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="tinggi" {{ old('prioritas', $proposal->prioritas) == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="sangat_tinggi" {{ old('prioritas', $proposal->prioritas) == 'sangat_tinggi' ? 'selected' : '' }}>Sangat Tinggi</option>
                        </select>
                        @error('prioritas')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Navigation -->
                <div class="flex justify-end mt-6 pt-4 border-t">
                    <button type="button" onclick="nextStep(2)" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Selanjutnya
                    </button>
                </div>
            </div>

            <!-- Step 2: Analisis Kebutuhan -->
            <div id="step-2" class="step-content bg-white rounded-lg shadow-md p-6" style="display: none;">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Analisis Kebutuhan</h2>
                <p class="text-sm text-gray-600 mb-6">Sesuai Permenkomdigi No. 6 Tahun 2025 tentang Pembangunan Aplikasi Khusus</p>

                <!-- Petunjuk Pengisian Step 2 -->
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <h3 class="text-base font-semibold text-green-900 mb-2">📋 Petunjuk Pengisian</h3>
                    <div class="text-sm text-green-800 space-y-2">
                        <p><strong>Contoh pengisian:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li><strong>Dasar Hukum:</strong> "UU No. 5 Tahun 2014 tentang ASN; PP No. 11 Tahun 2017 tentang Manajemen PNS; Permendagri No. 35 Tahun 2021 tentang Sistem Informasi Kepegawaian"</li>
                            <li><strong>Uraian Permasalahan:</strong> "Pengelolaan data kepegawaian masih manual menggunakan excel, sering terjadi duplikasi data, kesulitan dalam pelaporan berkala, dan proses pengajuan cuti memakan waktu hingga 5 hari kerja"</li>
                            <li><strong>Pihak Terkait:</strong> "BKD sebagai pengelola, Seluruh SKPD sebagai pengguna, Inspektorat sebagai pengawas, BPK sebagai auditor eksternal"</li>
                            <li><strong>Ruang Lingkup:</strong> "Mencakup 15 SKPD dengan total 2.500 pegawai, meliputi data pegawai, absensi, cuti, kinerja, dan penggajian"</li>
                            <li><strong>Analisis Biaya Manfaat:</strong> "Investasi Rp 500 juta, efisiensi waktu 40%, penghematan kertas Rp 50 juta/tahun, ROI tercapai dalam 2 tahun"</li>
                            <li><strong>Lokasi Implementasi:</strong> "Kantor BKD (server utama), 15 SKPD (klien), dengan akses cloud untuk pegawai"</li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Dasar Hukum -->
                    <div>
                        <label for="dasar_hukum" class="block text-sm font-medium text-gray-700 mb-1">
                            Dasar Hukum
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Peraturan perundang-undangan yang mendasari kebutuhan aplikasi</p>
                        <textarea id="dasar_hukum" name="dasar_hukum" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('dasar_hukum', $proposal->dasar_hukum) }}</textarea>
                        @error('dasar_hukum')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Uraian Permasalahan -->
                    <div>
                        <label for="uraian_permasalahan" class="block text-sm font-medium text-gray-700 mb-1">
                            Uraian Permasalahan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Deskripsi permasalahan atau kebutuhan yang mendasari pengembangan aplikasi</p>
                        <textarea id="uraian_permasalahan" name="uraian_permasalahan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('uraian_permasalahan', $proposal->uraian_permasalahan) }}</textarea>
                        @error('uraian_permasalahan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pihak Terkait -->
                    <div>
                        <label for="pihak_terkait" class="block text-sm font-medium text-gray-700 mb-1">
                            Pihak Terkait (Stakeholder)
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Unit kerja atau pihak-pihak yang terlibat dalam aplikasi</p>
                        <textarea id="pihak_terkait" name="pihak_terkait" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('pihak_terkait', $proposal->pihak_terkait) }}</textarea>
                        @error('pihak_terkait')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ruang Lingkup -->
                    <div>
                        <label for="ruang_lingkup" class="block text-sm font-medium text-gray-700 mb-1">
                            Ruang Lingkup
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Batasan dan cakupan pengembangan aplikasi</p>
                        <textarea id="ruang_lingkup" name="ruang_lingkup" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('ruang_lingkup', $proposal->ruang_lingkup) }}</textarea>
                        @error('ruang_lingkup')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Analisis Biaya Manfaat -->
                    <div>
                        <label for="analisis_biaya_manfaat" class="block text-sm font-medium text-gray-700 mb-1">
                            Analisis Biaya dan Manfaat
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Perbandingan biaya investasi dengan manfaat yang akan diperoleh</p>
                        <textarea id="analisis_biaya_manfaat" name="analisis_biaya_manfaat" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('analisis_biaya_manfaat', $proposal->analisis_biaya_manfaat) }}</textarea>
                        @error('analisis_biaya_manfaat')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lokasi Implementasi -->
                    <div>
                        <label for="lokasi_implementasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Lokasi Implementasi
                        </label>
                        <input type="text" id="lokasi_implementasi" name="lokasi_implementasi"
                            value="{{ old('lokasi_implementasi', $proposal->lokasi_implementasi) }}"
                            placeholder="Contoh: Seluruh wilayah Kota XYZ"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('lokasi_implementasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-6 pt-4 border-t">
                    <button type="button" onclick="previousStep(1)" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Kembali
                    </button>
                    <button type="button" onclick="nextStep(3)" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Selanjutnya
                    </button>
                </div>
            </div>

            <!-- Step 3: Perencanaan -->
            <div id="step-3" class="step-content bg-white rounded-lg shadow-md p-6" style="display: none;">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Perencanaan</h2>
                <p class="text-sm text-gray-600 mb-6">Sesuai Permenkomdigi No. 6 Tahun 2025 tentang Pembangunan Aplikasi Khusus</p>

                <!-- Petunjuk Pengisian Step 3 -->
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-6">
                    <h3 class="text-base font-semibold text-purple-900 mb-2">📋 Petunjuk Pengisian</h3>
                    <div class="text-sm text-purple-800 space-y-2">
                        <p><strong>Contoh pengisian:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li><strong>Proses Bisnis:</strong> "1) Pegawai login → 2) Mengisi form cuti → 3) Atasan approve/reject → 4) HRD verifikasi → 5) Sistem update database → 6) Notifikasi ke pegawai"</li>
                            <li><strong>Kerangka Kerja:</strong> "Menggunakan Agile Scrum dengan sprint 2 minggu, daily standup, sprint review, dan retrospective. Tech stack: Laravel, Vue.js, PostgreSQL"</li>
                            <li><strong>Pelaksana Pembangunan:</strong> Pilih Menteri jika ditangani Kementerian, Swakelola jika tim internal, Pihak Ketiga jika vendor eksternal</li>
                            <li><strong>Jadwal Pelaksanaan:</strong> "Q1 2026: Requirement gathering & design (2 bulan); Q2-Q3 2026: Development & testing (4 bulan); Q4 2026: UAT & deployment (2 bulan)"</li>
                            <li><strong>SDM:</strong> "Project Manager (1), Backend Developer (2), Frontend Developer (2), UI/UX Designer (1), QA Tester (1), DBA (1)"</li>
                            <li><strong>Anggaran:</strong> "Development: Rp 300 juta, Infrastruktur: Rp 100 juta, Training: Rp 50 juta, Maintenance 1 tahun: Rp 50 juta"</li>
                            <li><strong>Indikator Keberhasilan:</strong> "Waktu proses cuti berkurang dari 5 hari menjadi 1 hari, akurasi data 99%, user satisfaction score minimal 4.0/5.0"</li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Uraian Ruang Lingkup -->
                    <div>
                        <label for="uraian_ruang_lingkup" class="block text-sm font-medium text-gray-700 mb-1">
                            Uraian Ruang Lingkup Perencanaan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Detail ruang lingkup dalam tahap perencanaan</p>
                        <textarea id="uraian_ruang_lingkup" name="uraian_ruang_lingkup" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('uraian_ruang_lingkup', $proposal->uraian_ruang_lingkup) }}</textarea>
                        @error('uraian_ruang_lingkup')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Proses Bisnis -->
                    <div>
                        <label for="proses_bisnis" class="block text-sm font-medium text-gray-700 mb-1">
                            Proses Bisnis
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Alur proses bisnis yang akan didukung aplikasi</p>
                        <textarea id="proses_bisnis" name="proses_bisnis" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('proses_bisnis', $proposal->proses_bisnis) }}</textarea>
                        @error('proses_bisnis')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Proses Bisnis File Upload (Opsional) -->
                    <div>
                        <label for="proses_bisnis_file" class="block text-sm font-medium text-gray-700 mb-1">
                            Upload Diagram Proses Bisnis <span class="text-gray-500 text-xs">(Opsional)</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-2">
                            Upload diagram/dokumentasi proses bisnis jika tersedia
                            <br>
                            Format yang didukung: PDF, Gambar (PNG, JPG, SVG), Dokumen (DOC, DOCX), Diagram (VSDX, VSD, Draw.io, BPMN), Arsip (ZIP, RAR, 7Z)
                            <br>
                            Maksimal ukuran: 10 MB
                        </p>

                        @if($proposal->proses_bisnis_file)
                            <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                                        <span class="text-sm text-green-800">File saat ini: {{ basename($proposal->proses_bisnis_file) }}</span>
                                    </div>
                                    <a href="{{ Storage::url($proposal->proses_bisnis_file) }}" target="_blank"
                                       class="text-sm text-blue-600 hover:text-blue-800">
                                        Lihat File
                                    </a>
                                </div>
                                <p class="text-xs text-green-600 mt-1">Upload file baru untuk mengganti file yang ada</p>
                            </div>
                        @endif

                        <input type="file" id="proses_bisnis_file" name="proses_bisnis_file"
                            accept=".pdf,.png,.jpg,.jpeg,.svg,.doc,.docx,.zip,.rar,.7z,.vsdx,.vsd,.drawio,.bpmn"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">
                            <strong>Saran format:</strong> PDF (universal), PNG/JPG (diagram), VSDX (Microsoft Visio), Draw.io, atau BPMN
                        </p>
                        @error('proses_bisnis_file')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kerangka Kerja -->
                    <div>
                        <label for="kerangka_kerja" class="block text-sm font-medium text-gray-700 mb-1">
                            Kerangka Kerja/Metodologi
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Metodologi pengembangan yang akan digunakan (Agile, Waterfall, dll)</p>
                        <textarea id="kerangka_kerja" name="kerangka_kerja" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('kerangka_kerja', $proposal->kerangka_kerja) }}</textarea>
                        @error('kerangka_kerja')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pelaksana Pembangunan -->
                    <div>
                        <label for="pelaksana_pembangunan" class="block text-sm font-medium text-gray-700 mb-1">
                            Pelaksana Pembangunan
                        </label>
                        <select id="pelaksana_pembangunan" name="pelaksana_pembangunan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Pelaksana</option>
                            <option value="menteri" {{ old('pelaksana_pembangunan', $proposal->pelaksana_pembangunan) == 'menteri' ? 'selected' : '' }}>Menteri</option>
                            <option value="swakelola" {{ old('pelaksana_pembangunan', $proposal->pelaksana_pembangunan) == 'swakelola' ? 'selected' : '' }}>Swakelola</option>
                            <option value="pihak_ketiga" {{ old('pelaksana_pembangunan', $proposal->pelaksana_pembangunan) == 'pihak_ketiga' ? 'selected' : '' }}>Pihak Ketiga</option>
                        </select>
                        @error('pelaksana_pembangunan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Peran dan Tanggung Jawab -->
                    <div>
                        <label for="peran_tanggung_jawab" class="block text-sm font-medium text-gray-700 mb-1">
                            Peran dan Tanggung Jawab Tim
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Pembagian peran dan tanggung jawab tim pengembang</p>
                        <textarea id="peran_tanggung_jawab" name="peran_tanggung_jawab" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('peran_tanggung_jawab', $proposal->peran_tanggung_jawab) }}</textarea>
                        @error('peran_tanggung_jawab')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jadwal Pelaksanaan -->
                    <div>
                        <label for="jadwal_pelaksanaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Jadwal Pelaksanaan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Timeline pengembangan aplikasi</p>
                        <textarea id="jadwal_pelaksanaan" name="jadwal_pelaksanaan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('jadwal_pelaksanaan', $proposal->jadwal_pelaksanaan) }}</textarea>
                        @error('jadwal_pelaksanaan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rencana Aksi -->
                    <div>
                        <label for="rencana_aksi" class="block text-sm font-medium text-gray-700 mb-1">
                            Rencana Aksi
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Langkah-langkah konkret yang akan dilakukan</p>
                        <textarea id="rencana_aksi" name="rencana_aksi" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('rencana_aksi', $proposal->rencana_aksi) }}</textarea>
                        @error('rencana_aksi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keamanan Informasi -->
                    <div>
                        <label for="keamanan_informasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Keamanan Informasi
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Aspek keamanan data dan informasi yang akan diterapkan</p>
                        <textarea id="keamanan_informasi" name="keamanan_informasi" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('keamanan_informasi', $proposal->keamanan_informasi) }}</textarea>
                        @error('keamanan_informasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Daya Manusia -->
                    <div>
                        <label for="sumber_daya_manusia" class="block text-sm font-medium text-gray-700 mb-1">
                            Sumber Daya Manusia
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Kebutuhan SDM untuk pengembangan dan operasional</p>
                        <textarea id="sumber_daya_manusia" name="sumber_daya_manusia" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('sumber_daya_manusia', $proposal->sumber_daya_manusia) }}</textarea>
                        @error('sumber_daya_manusia')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Daya Anggaran -->
                    <div>
                        <label for="sumber_daya_anggaran" class="block text-sm font-medium text-gray-700 mb-1">
                            Sumber Daya Anggaran
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Rincian alokasi anggaran untuk pengembangan</p>
                        <textarea id="sumber_daya_anggaran" name="sumber_daya_anggaran" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('sumber_daya_anggaran', $proposal->sumber_daya_anggaran) }}</textarea>
                        @error('sumber_daya_anggaran')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Daya Sarana -->
                    <div>
                        <label for="sumber_daya_sarana" class="block text-sm font-medium text-gray-700 mb-1">
                            Sumber Daya Sarana Prasarana
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Infrastruktur dan peralatan yang dibutuhkan</p>
                        <textarea id="sumber_daya_sarana" name="sumber_daya_sarana" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('sumber_daya_sarana', $proposal->sumber_daya_sarana) }}</textarea>
                        @error('sumber_daya_sarana')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Indikator Keberhasilan -->
                    <div>
                        <label for="indikator_keberhasilan" class="block text-sm font-medium text-gray-700 mb-1">
                            Indikator Keberhasilan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Kriteria untuk mengukur keberhasilan proyek</p>
                        <textarea id="indikator_keberhasilan" name="indikator_keberhasilan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('indikator_keberhasilan', $proposal->indikator_keberhasilan) }}</textarea>
                        @error('indikator_keberhasilan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alih Pengetahuan -->
                    <div>
                        <label for="alih_pengetahuan" class="block text-sm font-medium text-gray-700 mb-1">
                            Alih Pengetahuan (Knowledge Transfer)
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Rencana transfer pengetahuan kepada tim internal</p>
                        <textarea id="alih_pengetahuan" name="alih_pengetahuan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('alih_pengetahuan', $proposal->alih_pengetahuan) }}</textarea>
                        @error('alih_pengetahuan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pemantauan dan Pelaporan -->
                    <div>
                        <label for="pemantauan_pelaporan" class="block text-sm font-medium text-gray-700 mb-1">
                            Pemantauan dan Pelaporan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Mekanisme monitoring dan pelaporan progres pengembangan</p>
                        <textarea id="pemantauan_pelaporan" name="pemantauan_pelaporan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('pemantauan_pelaporan', $proposal->pemantauan_pelaporan) }}</textarea>
                        @error('pemantauan_pelaporan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-6 pt-4 border-t">
                    <button type="button" onclick="previousStep(2)" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Kembali
                    </button>
                    <button type="button" onclick="nextStep(4)" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Selanjutnya
                    </button>
                </div>
            </div>

            <!-- Step 4: Manajemen Risiko -->
            <div id="step-4" class="step-content bg-white rounded-lg shadow-md p-6" style="display: none;">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Manajemen Risiko SPBE</h2>
                <p class="text-sm text-gray-600 mb-6">Identifikasi dan kelola risiko sesuai Permenkomdigi No. 6 Tahun 2025</p>

                <!-- Petunjuk Pengisian -->
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6">
                    <h3 class="text-base font-semibold text-orange-900 mb-2">📋 Petunjuk Pengisian Manajemen Risiko SPBE</h3>
                    <p class="text-sm text-orange-800 mb-3">
                        Manajemen Risiko SPBE merupakan bagian penting dari perencanaan aplikasi. Silakan baca pedoman dan panduan berikut sebelum mengisi:
                    </p>
                    <div class="space-y-2 mb-4">
                        <a href="https://drive.google.com/file/d/1xQXin3YnIOiQybDU8O90MobXx6pb-uEf/view?usp=sharing" target="_blank"
                           class="inline-flex items-center text-sm text-orange-700 hover:text-orange-900 font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Buka Pedoman Manajemen Risiko SPBE (PDF)
                        </a>
                        <br>
                        <a href="https://s.id/manrisk-spbe-kaltara" target="_blank"
                           class="inline-flex items-center text-sm text-orange-700 hover:text-orange-900 font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            Kunjungi Microsite Manajemen Risiko SPBE
                        </a>
                    </div>
                    <div class="bg-orange-100 rounded p-3 mt-3">
                        <p class="text-sm text-orange-900 font-semibold mb-2">Contoh Risiko SPBE:</p>
                        <ul class="text-xs text-orange-800 space-y-1 ml-2">
                            <li><strong>Jenis:</strong> Negatif | <strong>Kategori:</strong> Keamanan Informasi | <strong>Area Dampak:</strong> Keamanan</li>
                            <li><strong>Kejadian:</strong> "Data pegawai bocor ke pihak tidak berwenang"</li>
                            <li><strong>Penyebab:</strong> "Sistem keamanan tidak memadai, password lemah"</li>
                            <li><strong>Dampak:</strong> "Kehilangan kepercayaan publik, kerugian reputasi"</li>
                            <li><strong>Level Kemungkinan:</strong> 3 (Kadang-kadang) | <strong>Level Dampak:</strong> 4 (Major) = <strong>Besaran:</strong> 12 (Tinggi)</li>
                            <li><strong>Penanganan:</strong> "Implementasi 2FA, enkripsi data, audit keamanan berkala"</li>
                            <li><strong>Penanggung Jawab:</strong> "Tim IT Security & Privacy Officer"</li>
                        </ul>
                    </div>
                </div>

                <!-- Warning Container -->
                <div id="risiko-warning" class="hidden bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-yellow-700 font-medium">
                            <strong>Perhatian:</strong> Minimal 1 risiko harus diidentifikasi sebelum melanjutkan ke tahap berikutnya.
                        </p>
                    </div>
                </div>

                <!-- Risiko Container -->
                <div id="risiko-wrapper" class="space-y-4 mb-6">
                    @php
                        $risikoItems = old('risiko_items', $proposal->risiko_items ?? []);
                    @endphp
                    @if(!empty($risikoItems))
                        @foreach($risikoItems as $index => $risiko)
                            <div class="risiko-item bg-gray-50 border border-gray-300 rounded-lg p-6 mb-4" data-index="{{ $index }}">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Risiko SPBE #{{ $index + 1 }}</h3>
                                    <button type="button" onclick="removeRisiko({{ $index }})" class="px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition">
                                        Hapus Risiko
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <!-- Jenis Risiko SPBE -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Jenis Risiko SPBE <span class="text-red-500">*</span>
                                        </label>
                                        <select name="risiko_items[{{ $index }}][jenis_risiko]"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Pilih Jenis Risiko</option>
                                            <option value="positif" {{ ($risiko['jenis_risiko'] ?? '') == 'positif' ? 'selected' : '' }}>Positif (Peluang)</option>
                                            <option value="negatif" {{ ($risiko['jenis_risiko'] ?? '') == 'negatif' ? 'selected' : '' }}>Negatif (Ancaman)</option>
                                        </select>
                                    </div>

                                    <!-- Kategori Risiko SPBE -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Kategori Risiko SPBE <span class="text-red-500">*</span>
                                        </label>
                                        <select name="risiko_items[{{ $index }}][kategori_risiko]"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Pilih Kategori</option>
                                            <option value="rencana_induk_spbe" {{ ($risiko['kategori_risiko'] ?? '') == 'rencana_induk_spbe' ? 'selected' : '' }}>Rencana Induk SPBE Nasional</option>
                                            <option value="arsitektur_spbe" {{ ($risiko['kategori_risiko'] ?? '') == 'arsitektur_spbe' ? 'selected' : '' }}>Arsitektur SPBE</option>
                                            <option value="peta_rencana" {{ ($risiko['kategori_risiko'] ?? '') == 'peta_rencana' ? 'selected' : '' }}>Peta Rencana SPBE</option>
                                            <option value="aplikasi_umum" {{ ($risiko['kategori_risiko'] ?? '') == 'aplikasi_umum' ? 'selected' : '' }}>Aplikasi Umum</option>
                                            <option value="aplikasi_khusus" {{ ($risiko['kategori_risiko'] ?? '') == 'aplikasi_khusus' ? 'selected' : '' }}>Aplikasi Khusus</option>
                                            <option value="keamanan_informasi" {{ ($risiko['kategori_risiko'] ?? '') == 'keamanan_informasi' ? 'selected' : '' }}>Keamanan Informasi</option>
                                            <option value="audit_tik" {{ ($risiko['kategori_risiko'] ?? '') == 'audit_tik' ? 'selected' : '' }}>Audit Teknologi Informasi dan Komunikasi</option>
                                            <option value="pengadaan_tik" {{ ($risiko['kategori_risiko'] ?? '') == 'pengadaan_tik' ? 'selected' : '' }}>Pengadaan Teknologi Informasi dan Komunikasi</option>
                                            <option value="penyelenggara_spbe" {{ ($risiko['kategori_risiko'] ?? '') == 'penyelenggara_spbe' ? 'selected' : '' }}>Penyelenggara SPBE</option>
                                            <option value="layanan_pusat_data" {{ ($risiko['kategori_risiko'] ?? '') == 'layanan_pusat_data' ? 'selected' : '' }}>Layanan Pusat Data Nasional</option>
                                            <option value="data_informasi" {{ ($risiko['kategori_risiko'] ?? '') == 'data_informasi' ? 'selected' : '' }}>Data dan Informasi</option>
                                            <option value="infrastruktur" {{ ($risiko['kategori_risiko'] ?? '') == 'infrastruktur' ? 'selected' : '' }}>Infrastruktur</option>
                                            <option value="sumber_daya_manusia" {{ ($risiko['kategori_risiko'] ?? '') == 'sumber_daya_manusia' ? 'selected' : '' }}>Sumber Daya Manusia</option>
                                            <option value="anggaran" {{ ($risiko['kategori_risiko'] ?? '') == 'anggaran' ? 'selected' : '' }}>Anggaran</option>
                                            <option value="regulasi" {{ ($risiko['kategori_risiko'] ?? '') == 'regulasi' ? 'selected' : '' }}>Regulasi</option>
                                            <option value="lainnya" {{ ($risiko['kategori_risiko'] ?? '') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                    </div>

                                    <!-- Area Dampak Risiko SPBE -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Area Dampak Risiko SPBE <span class="text-red-500">*</span>
                                        </label>
                                        <select name="risiko_items[{{ $index }}][area_dampak]"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Pilih Area Dampak</option>
                                            <option value="finansial" {{ ($risiko['area_dampak'] ?? '') == 'finansial' ? 'selected' : '' }}>Finansial</option>
                                            <option value="reputasi" {{ ($risiko['area_dampak'] ?? '') == 'reputasi' ? 'selected' : '' }}>Reputasi</option>
                                            <option value="kinerja_operasional" {{ ($risiko['area_dampak'] ?? '') == 'kinerja_operasional' ? 'selected' : '' }}>Kinerja Operasional</option>
                                            <option value="kepatuhan" {{ ($risiko['area_dampak'] ?? '') == 'kepatuhan' ? 'selected' : '' }}>Kepatuhan</option>
                                            <option value="keamanan" {{ ($risiko['area_dampak'] ?? '') == 'keamanan' ? 'selected' : '' }}>Keamanan</option>
                                            <option value="lingkungan" {{ ($risiko['area_dampak'] ?? '') == 'lingkungan' ? 'selected' : '' }}>Lingkungan</option>
                                            <option value="kesehatan_keselamatan" {{ ($risiko['area_dampak'] ?? '') == 'kesehatan_keselamatan' ? 'selected' : '' }}>Kesehatan dan Keselamatan</option>
                                        </select>
                                    </div>

                                    <!-- Uraian Kejadian -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Uraian Kejadian <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="risiko_items[{{ $index }}][uraian_kejadian]" rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                  placeholder="Jelaskan detail kejadian risiko yang mungkin terjadi">{{ $risiko['uraian_kejadian'] ?? '' }}</textarea>
                                    </div>

                                    <!-- Penyebab -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Penyebab <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="risiko_items[{{ $index }}][penyebab]"
                                               value="{{ $risiko['penyebab'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Contoh: Kurangnya SDM kompeten, Keterbatasan anggaran">
                                    </div>

                                    <!-- Dampak -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Dampak <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="risiko_items[{{ $index }}][dampak]"
                                               value="{{ $risiko['dampak'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Contoh: Keterlambatan project, Penurunan kualitas aplikasi">
                                    </div>

                                    <!-- Level Kemungkinan & Level Dampak -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Level Kemungkinan <span class="text-red-500">*</span>
                                            </label>
                                            <select name="risiko_items[{{ $index }}][level_kemungkinan]"
                                                    onchange="hitungBesaranRisiko({{ $index }})"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                                <option value="">Pilih Level</option>
                                                <option value="1" {{ ($risiko['level_kemungkinan'] ?? '') == '1' ? 'selected' : '' }}>1 - Sangat Jarang (0-10%)</option>
                                                <option value="2" {{ ($risiko['level_kemungkinan'] ?? '') == '2' ? 'selected' : '' }}>2 - Jarang (11-30%)</option>
                                                <option value="3" {{ ($risiko['level_kemungkinan'] ?? '') == '3' ? 'selected' : '' }}>3 - Kadang-kadang (31-50%)</option>
                                                <option value="4" {{ ($risiko['level_kemungkinan'] ?? '') == '4' ? 'selected' : '' }}>4 - Sering (51-70%)</option>
                                                <option value="5" {{ ($risiko['level_kemungkinan'] ?? '') == '5' ? 'selected' : '' }}>5 - Sangat Sering (>70%)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Level Dampak <span class="text-red-500">*</span>
                                            </label>
                                            <select name="risiko_items[{{ $index }}][level_dampak]"
                                                    onchange="hitungBesaranRisiko({{ $index }})"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                                <option value="">Pilih Level</option>
                                                <option value="1" {{ ($risiko['level_dampak'] ?? '') == '1' ? 'selected' : '' }}>1 - Tidak Signifikan</option>
                                                <option value="2" {{ ($risiko['level_dampak'] ?? '') == '2' ? 'selected' : '' }}>2 - Minor</option>
                                                <option value="3" {{ ($risiko['level_dampak'] ?? '') == '3' ? 'selected' : '' }}>3 - Moderat</option>
                                                <option value="4" {{ ($risiko['level_dampak'] ?? '') == '4' ? 'selected' : '' }}>4 - Major</option>
                                                <option value="5" {{ ($risiko['level_dampak'] ?? '') == '5' ? 'selected' : '' }}>5 - Ekstrem</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Besaran Risiko (Auto-calculated) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Besaran Risiko (Otomatis Terhitung)
                                        </label>
                                        <input type="text" id="besaran-risiko-{{ $index }}" name="risiko_items[{{ $index }}][besaran_risiko]"
                                               value="{{ $risiko['besaran_risiko'] ?? '' }}"
                                               class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg" readonly
                                               placeholder="Akan dihitung otomatis">
                                        <input type="hidden" id="besaran-risiko-nilai-{{ $index }}" name="risiko_items[{{ $index }}][besaran_risiko_nilai]"
                                               value="{{ $risiko['besaran_risiko_nilai'] ?? '' }}">
                                    </div>

                                    <!-- Perlu Penanganan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Perlu Penanganan? <span class="text-red-500">*</span>
                                        </label>
                                        <select name="risiko_items[{{ $index }}][perlu_penanganan]"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Pilih</option>
                                            <option value="ya" {{ ($risiko['perlu_penanganan'] ?? '') == 'ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="tidak" {{ ($risiko['perlu_penanganan'] ?? '') == 'tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>

                                    <!-- Opsi Penanganan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Opsi Penanganan
                                        </label>
                                        <textarea name="risiko_items[{{ $index }}][opsi_penanganan]" rows="2"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                  placeholder="Jelaskan pilihan strategi penanganan risiko">{{ $risiko['opsi_penanganan'] ?? '' }}</textarea>
                                    </div>

                                    <!-- Rencana Aksi -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Rencana Aksi
                                        </label>
                                        <textarea name="risiko_items[{{ $index }}][rencana_aksi]" rows="2"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                  placeholder="Jelaskan rencana aksi konkret untuk menangani risiko">{{ $risiko['rencana_aksi'] ?? '' }}</textarea>
                                    </div>

                                    <!-- Jadwal Implementasi -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Jadwal Implementasi
                                        </label>
                                        <input type="text" name="risiko_items[{{ $index }}][jadwal_implementasi]"
                                               value="{{ $risiko['jadwal_implementasi'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Contoh: Bulan 1-2, Q1 2026">
                                    </div>

                                    <!-- Penanggung Jawab -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Penanggung Jawab
                                        </label>
                                        <input type="text" name="risiko_items[{{ $index }}][penanggung_jawab]"
                                               value="{{ $risiko['penanggung_jawab'] ?? '' }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Contoh: Tim Teknis, Project Manager">
                                    </div>

                                    <!-- Risiko Residual -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Risiko Residual?
                                        </label>
                                        <select name="risiko_items[{{ $index }}][risiko_residual]"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Pilih</option>
                                            <option value="ya" {{ ($risiko['risiko_residual'] ?? '') == 'ya' ? 'selected' : '' }}>Ya</option>
                                            <option value="tidak" {{ ($risiko['risiko_residual'] ?? '') == 'tidak' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Empty state - JavaScript will add first risiko -->
                    @endif
                </div>

                <button type="button" onclick="addRisiko()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Tambah Risiko
                </button>

                <!-- Navigation -->
                <div class="flex justify-between mt-6 pt-4 border-t">
                    <button type="button" onclick="previousStep(3)" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Kembali
                    </button>
                    <button type="button" onclick="nextStep(5)" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Selanjutnya
                    </button>
                </div>
            </div>

            <!-- Step 5: Review -->
            <div id="step-5" class="step-content bg-white rounded-lg shadow-md p-6" style="display: none;">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Review & Submit</h2>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Pastikan semua data yang Anda masukkan sudah benar. Setelah diupdate, perubahan akan tersimpan.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="review-content" class="space-y-4">
                    <!-- Review content will be populated by JavaScript -->
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-6 pt-4 border-t">
                    <button type="button" onclick="previousStep(4)" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Kembali
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

<script>
// Store editor instances
const editorInstances = {};

// Initialize CKEditor for all editor fields
document.addEventListener('DOMContentLoaded', function() {
    const editorFields = document.querySelectorAll('.editor-field');

    editorFields.forEach(function(textarea) {
        ClassicEditor
            .create(textarea, {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'undo', 'redo'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                    ]
                }
            })
            .then(editor => {
                editorInstances[textarea.id] = editor;
            })
            .catch(error => {
                console.error('CKEditor initialization error:', error);
            });
    });
});

let currentStep = 1;
let risikoCounter = {{ !empty($risikoItems ?? []) ? count($risikoItems ?? []) : 1 }};

// Step Navigation
function nextStep(step) {
    if (validateStep(currentStep)) {
        document.getElementById(`step-${currentStep}`).style.display = 'none';
        document.getElementById(`step-${step}`).style.display = 'block';

        updateStepIndicator(step);
        currentStep = step;

        if (step === 5) {
            populateReview();
        }

        window.scrollTo(0, 0);
    }
}

function previousStep(step) {
    document.getElementById(`step-${currentStep}`).style.display = 'none';
    document.getElementById(`step-${step}`).style.display = 'block';

    updateStepIndicator(step);
    currentStep = step;

    window.scrollTo(0, 0);
}

function updateStepIndicator(activeStep) {
    for (let i = 1; i <= 5; i++) {
        const indicator = document.getElementById(`step-indicator-${i}`);
        const circle = indicator.querySelector('div');
        const text = indicator.querySelector('p');

        if (i === activeStep) {
            circle.className = 'w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold';
            text.className = 'text-sm font-medium text-blue-600';
        } else if (i < activeStep) {
            circle.className = 'w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold';
            text.className = 'text-sm font-medium text-green-600';
        } else {
            circle.className = 'w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold';
            text.className = 'text-sm font-medium text-gray-500';
        }
    }
}

function validateStep(step) {
    if (step === 1) {
        // Sync CKEditor data first
        syncEditorData();

        const requiredFields = [
            'nama_aplikasi', 'pemilik_proses_bisnis_id', 'jenis_layanan', 'target_pengguna',
            'estimasi_pengguna', 'lingkup_aplikasi', 'estimasi_waktu_pengembangan',
            'estimasi_biaya', 'sumber_pendanaan', 'prioritas'
        ];

        // Check regular fields
        for (let field of requiredFields) {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                alert(`Mohon lengkapi field: ${input.previousElementSibling.textContent}`);
                input.focus();
                return false;
            }
        }

        // Check CKEditor fields
        const editorRequiredFields = ['deskripsi', 'tujuan', 'manfaat'];
        for (let field of editorRequiredFields) {
            const editor = editorInstances[field];
            if (editor) {
                const content = editor.getData().replace(/<[^>]*>/g, '').trim();
                if (!content) {
                    alert(`Mohon lengkapi field: ${document.querySelector(`label[for="${field}"]`).textContent}`);
                    editor.focus();
                    return false;
                }
            }
        }

        // Check platform
        const platforms = document.querySelectorAll('input[name="platform[]"]:checked');
        if (platforms.length === 0) {
            alert('Mohon pilih minimal satu platform');
            return false;
        }
    }

    return true;
}

// Sync CKEditor data to textarea
function syncEditorData() {
    for (const [fieldId, editor] of Object.entries(editorInstances)) {
        const textarea = document.getElementById(fieldId);
        if (textarea && editor) {
            textarea.value = editor.getData();
        }
    }
}

// Sync before form submit
document.getElementById('usulan-form').addEventListener('submit', function(e) {
    syncEditorData();
});

// Risiko Management - SPBE Compliant
function addRisiko() {
    const wrapper = document.getElementById('risiko-wrapper');
    const index = risikoCounter;

    const risikoHTML = `
        <div class="risiko-item bg-gray-50 border border-gray-300 rounded-lg p-6 mb-4" data-index="${index}">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Risiko SPBE #${index + 1}</h3>
                <button type="button" onclick="removeRisiko(${index})" class="px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition">
                    Hapus Risiko
                </button>
            </div>

            <div class="space-y-4">
                <!-- Jenis Risiko SPBE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Risiko SPBE <span class="text-red-500">*</span>
                    </label>
                    <select name="risiko_items[${index}][jenis_risiko]"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Risiko</option>
                        <option value="positif">Positif (Peluang)</option>
                        <option value="negatif">Negatif (Ancaman)</option>
                    </select>
                </div>

                <!-- Kategori Risiko SPBE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Kategori Risiko SPBE <span class="text-red-500">*</span>
                    </label>
                    <select name="risiko_items[${index}][kategori_risiko]"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Kategori</option>
                        <option value="rencana_induk_spbe">Rencana Induk SPBE Nasional</option>
                        <option value="arsitektur_spbe">Arsitektur SPBE</option>
                        <option value="peta_rencana">Peta Rencana SPBE</option>
                        <option value="aplikasi_umum">Aplikasi Umum</option>
                        <option value="aplikasi_khusus">Aplikasi Khusus</option>
                        <option value="keamanan_informasi">Keamanan Informasi</option>
                        <option value="audit_tik">Audit Teknologi Informasi dan Komunikasi</option>
                        <option value="pengadaan_tik">Pengadaan Teknologi Informasi dan Komunikasi</option>
                        <option value="penyelenggara_spbe">Penyelenggara SPBE</option>
                        <option value="layanan_pusat_data">Layanan Pusat Data Nasional</option>
                        <option value="data_informasi">Data dan Informasi</option>
                        <option value="infrastruktur">Infrastruktur</option>
                        <option value="sumber_daya_manusia">Sumber Daya Manusia</option>
                        <option value="anggaran">Anggaran</option>
                        <option value="regulasi">Regulasi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <!-- Area Dampak Risiko SPBE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Area Dampak Risiko SPBE <span class="text-red-500">*</span>
                    </label>
                    <select name="risiko_items[${index}][area_dampak]"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Area Dampak</option>
                        <option value="finansial">Finansial</option>
                        <option value="reputasi">Reputasi</option>
                        <option value="kinerja_operasional">Kinerja Operasional</option>
                        <option value="kepatuhan">Kepatuhan</option>
                        <option value="keamanan">Keamanan</option>
                        <option value="lingkungan">Lingkungan</option>
                        <option value="kesehatan_keselamatan">Kesehatan dan Keselamatan</option>
                    </select>
                </div>

                <!-- Uraian Kejadian -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Uraian Kejadian <span class="text-red-500">*</span>
                    </label>
                    <textarea name="risiko_items[${index}][uraian_kejadian]" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Jelaskan detail kejadian risiko yang mungkin terjadi"></textarea>
                </div>

                <!-- Penyebab -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Penyebab <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="risiko_items[${index}][penyebab]"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Kurangnya SDM kompeten, Keterbatasan anggaran">
                </div>

                <!-- Dampak -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dampak <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="risiko_items[${index}][dampak]"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Keterlambatan project, Penurunan kualitas aplikasi">
                </div>

                <!-- Level Kemungkinan & Level Dampak -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Level Kemungkinan <span class="text-red-500">*</span>
                        </label>
                        <select name="risiko_items[${index}][level_kemungkinan]"
                                onchange="hitungBesaranRisiko(${index})"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Level</option>
                            <option value="1">1 - Sangat Jarang (0-10%)</option>
                            <option value="2">2 - Jarang (11-30%)</option>
                            <option value="3">3 - Kadang-kadang (31-50%)</option>
                            <option value="4">4 - Sering (51-70%)</option>
                            <option value="5">5 - Sangat Sering (>70%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Level Dampak <span class="text-red-500">*</span>
                        </label>
                        <select name="risiko_items[${index}][level_dampak]"
                                onchange="hitungBesaranRisiko(${index})"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Level</option>
                            <option value="1">1 - Tidak Signifikan</option>
                            <option value="2">2 - Minor</option>
                            <option value="3">3 - Moderat</option>
                            <option value="4">4 - Major</option>
                            <option value="5">5 - Ekstrem</option>
                        </select>
                    </div>
                </div>

                <!-- Besaran Risiko (Auto-calculated) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Besaran Risiko (Otomatis Terhitung)
                    </label>
                    <input type="text" id="besaran-risiko-${index}" name="risiko_items[${index}][besaran_risiko]"
                           class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg" readonly
                           placeholder="Akan dihitung otomatis">
                    <input type="hidden" id="besaran-risiko-nilai-${index}" name="risiko_items[${index}][besaran_risiko_nilai]">
                </div>

                <!-- Perlu Penanganan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Perlu Penanganan? <span class="text-red-500">*</span>
                    </label>
                    <select name="risiko_items[${index}][perlu_penanganan]"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih</option>
                        <option value="ya">Ya</option>
                        <option value="tidak">Tidak</option>
                    </select>
                </div>

                <!-- Opsi Penanganan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Opsi Penanganan
                    </label>
                    <textarea name="risiko_items[${index}][opsi_penanganan]" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Jelaskan pilihan strategi penanganan risiko"></textarea>
                </div>

                <!-- Rencana Aksi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Rencana Aksi
                    </label>
                    <textarea name="risiko_items[${index}][rencana_aksi]" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Jelaskan rencana aksi konkret untuk menangani risiko"></textarea>
                </div>

                <!-- Jadwal Implementasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jadwal Implementasi
                    </label>
                    <input type="text" name="risiko_items[${index}][jadwal_implementasi]"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Bulan 1-2, Q1 2026">
                </div>

                <!-- Penanggung Jawab -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Penanggung Jawab
                    </label>
                    <input type="text" name="risiko_items[${index}][penanggung_jawab]"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: Tim Teknis, Project Manager">
                </div>

                <!-- Risiko Residual -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Risiko Residual?
                    </label>
                    <select name="risiko_items[${index}][risiko_residual]"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih</option>
                        <option value="ya">Ya</option>
                        <option value="tidak">Tidak</option>
                    </select>
                </div>
            </div>
        </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', risikoHTML);
    risikoCounter++;
    updateRisikoWarning();
}

function removeRisiko(index) {
    const item = document.querySelector(`.risiko-item[data-index="${index}"]`);
    if (item) {
        item.remove();
        updateRisikoWarning();
    }
}

function hitungBesaranRisiko(index) {
    const kemungkinanSelect = document.querySelector(`select[name="risiko_items[${index}][level_kemungkinan]"]`);
    const dampakSelect = document.querySelector(`select[name="risiko_items[${index}][level_dampak]"]`);
    const besaranInput = document.getElementById(`besaran-risiko-${index}`);
    const besaranNilaiInput = document.getElementById(`besaran-risiko-nilai-${index}`);

    const kemungkinan = parseInt(kemungkinanSelect?.value || 0);
    const dampak = parseInt(dampakSelect?.value || 0);

    if (kemungkinan > 0 && dampak > 0) {
        const nilai = kemungkinan * dampak;
        besaranNilaiInput.value = nilai;

        let level = '';
        let color = '';

        if (nilai >= 1 && nilai <= 5) {
            level = 'Rendah';
            color = 'text-green-700 bg-green-100';
        } else if (nilai >= 6 && nilai <= 10) {
            level = 'Sedang';
            color = 'text-yellow-700 bg-yellow-100';
        } else if (nilai >= 11 && nilai <= 15) {
            level = 'Tinggi';
            color = 'text-orange-700 bg-orange-100';
        } else if (nilai >= 16) {
            level = 'Sangat Tinggi';
            color = 'text-red-700 bg-red-100';
        }

        besaranInput.value = `${nilai} - ${level}`;
        besaranInput.className = `w-full px-4 py-2 border border-gray-300 rounded-lg font-semibold ${color}`;
    } else {
        besaranInput.value = '';
        besaranNilaiInput.value = '';
        besaranInput.className = 'w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg';
    }
}

function updateRisikoWarning() {
    const risikoCount = document.querySelectorAll('.risiko-item').length;
    const warning = document.getElementById('risiko-warning');

    if (risikoCount === 0) {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
}

// Initialize risk calculations on page load for existing data
document.addEventListener('DOMContentLoaded', function() {
    // Calculate besaran risiko for existing items
    const existingItems = document.querySelectorAll('.risiko-item[data-index]');
    existingItems.forEach(item => {
        const index = item.getAttribute('data-index');
        hitungBesaranRisiko(index);
    });
});

// Conditional field handling
document.getElementById('integrasi_sistem_lain').addEventListener('change', function() {
    const detailWrapper = document.getElementById('detail-integrasi-wrapper');
    detailWrapper.style.display = this.value === 'ya' ? 'block' : 'none';
});

function populateReview() {
    const form = document.getElementById('usulan-form');
    const formData = new FormData(form);

    let reviewHTML = '<div class="space-y-6">';

    // Step 1: Informasi Dasar
    reviewHTML += '<div class="border-b pb-4"><h3 class="font-semibold text-lg mb-3">1. Informasi Dasar</h3>';
    reviewHTML += `<div class="grid grid-cols-2 gap-4">`;
    reviewHTML += `<div><span class="text-gray-600">Nama Aplikasi:</span> <span class="font-medium">${formData.get('nama_aplikasi') || '-'}</span></div>`;
    reviewHTML += `<div><span class="text-gray-600">Prioritas:</span> <span class="font-medium">${formData.get('prioritas') || '-'}</span></div>`;
    reviewHTML += `<div><span class="text-gray-600">Jenis Layanan:</span> <span class="font-medium">${formData.get('jenis_layanan') || '-'}</span></div>`;
    reviewHTML += `<div><span class="text-gray-600">Lingkup:</span> <span class="font-medium">${formData.get('lingkup_aplikasi') || '-'}</span></div>`;
    reviewHTML += `<div><span class="text-gray-600">Estimasi Waktu:</span> <span class="font-medium">${formData.get('estimasi_waktu_pengembangan') || '-'} bulan</span></div>`;
    reviewHTML += `<div><span class="text-gray-600">Estimasi Biaya:</span> <span class="font-medium">Rp ${parseInt(formData.get('estimasi_biaya') || 0).toLocaleString('id-ID')}</span></div>`;
    reviewHTML += `</div></div>`;

    // Step 2: Analisis Kebutuhan
    reviewHTML += '<div class="border-b pb-4"><h3 class="font-semibold text-lg mb-3">2. Analisis Kebutuhan</h3>';
    reviewHTML += `<div class="text-gray-600">`;
    reviewHTML += `<p class="mb-2"><strong>Dasar Hukum:</strong> ${formData.get('dasar_hukum') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Uraian Permasalahan:</strong> ${formData.get('uraian_permasalahan') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Pihak Terkait:</strong> ${formData.get('pihak_terkait') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Ruang Lingkup:</strong> ${formData.get('ruang_lingkup') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Analisis Biaya Manfaat:</strong> ${formData.get('analisis_biaya_manfaat') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Lokasi Implementasi:</strong> ${formData.get('lokasi_implementasi') || '-'}</p>`;
    reviewHTML += `</div></div>`;

    // Step 3: Perencanaan
    reviewHTML += '<div class="border-b pb-4"><h3 class="font-semibold text-lg mb-3">3. Perencanaan</h3>';
    reviewHTML += `<div class="text-gray-600">`;
    reviewHTML += `<p class="mb-2"><strong>Uraian Ruang Lingkup:</strong> ${formData.get('uraian_ruang_lingkup') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Proses Bisnis:</strong> ${formData.get('proses_bisnis') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Kerangka Kerja:</strong> ${formData.get('kerangka_kerja') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Pelaksana Pembangunan:</strong> ${formData.get('pelaksana_pembangunan') || 'Belum dipilih'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Peran Tanggung Jawab:</strong> ${formData.get('peran_tanggung_jawab') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Jadwal Pelaksanaan:</strong> ${formData.get('jadwal_pelaksanaan') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Rencana Aksi:</strong> ${formData.get('rencana_aksi') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Keamanan Informasi:</strong> ${formData.get('keamanan_informasi') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Sumber Daya Manusia:</strong> ${formData.get('sumber_daya_manusia') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Sumber Daya Anggaran:</strong> ${formData.get('sumber_daya_anggaran') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Sumber Daya Sarana:</strong> ${formData.get('sumber_daya_sarana') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Indikator Keberhasilan:</strong> ${formData.get('indikator_keberhasilan') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Alih Pengetahuan:</strong> ${formData.get('alih_pengetahuan') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `<p class="mb-2"><strong>Pemantauan Pelaporan:</strong> ${formData.get('pemantauan_pelaporan') ? 'Diisi ✓' : 'Belum diisi'}</p>`;
    reviewHTML += `</div></div>`;

    // Step 4: Manajemen Risiko
    reviewHTML += '<div class="border-b pb-4"><h3 class="font-semibold text-lg mb-3">4. Manajemen Risiko</h3>';
    const risikoItems = document.querySelectorAll('.risiko-item');
    reviewHTML += `<div class="text-gray-600">`;
    reviewHTML += `<p class="mb-3"><strong>Total Risiko Teridentifikasi:</strong> ${risikoItems.length} risiko</p>`;

    if (risikoItems.length > 0) {
        reviewHTML += `<div class="space-y-3">`;
        risikoItems.forEach((item, index) => {
            const jenis = item.querySelector(`input[name*="[jenis]"]`)?.value || '-';
            const tingkat = item.querySelector(`select[name*="[tingkat]"]`)?.value || '-';
            const kategori = item.querySelector(`select[name*="[kategori]"]`)?.value || '-';
            const mitigasi = item.querySelector(`textarea[name*="[mitigasi]"]`)?.value || '-';

            reviewHTML += `<div class="bg-gray-50 p-3 rounded-lg">`;
            reviewHTML += `<p class="font-medium mb-1">Risiko #${index + 1}: ${jenis}</p>`;
            reviewHTML += `<p class="text-sm"><strong>Tingkat:</strong> ${tingkat} | <strong>Kategori:</strong> ${kategori}</p>`;
            reviewHTML += `<p class="text-sm"><strong>Mitigasi:</strong> ${mitigasi}</p>`;
            reviewHTML += `</div>`;
        });
        reviewHTML += `</div>`;
    } else {
        reviewHTML += `<p class="text-gray-500 italic">Belum ada risiko yang diidentifikasi</p>`;
    }
    reviewHTML += `</div></div>`;

    reviewHTML += '</div>';

    document.getElementById('review-content').innerHTML = reviewHTML;
}
</script>
@endpush
@endsection
