@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Buat Usulan Rekomendasi Aplikasi Baru</h1>
            <p class="text-gray-600 mt-1">Isi formulir di bawah untuk mengajukan usulan rekomendasi aplikasi</p>
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
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <form id="usulan-form" method="POST" action="{{ route('user.rekomendasi.usulan.store') }}" enctype="multipart/form-data">
            @csrf

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
                            value="{{ old('nama_aplikasi') }}"
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
                            >{{ old('deskripsi') }}</textarea>
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
                            >{{ old('tujuan') }}</textarea>
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
                            >{{ old('manfaat') }}</textarea>
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
                                <option value="{{ $uk->id }}" {{ old('pemilik_proses_bisnis_id') == $uk->id ? 'selected' : '' }}>
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
                            <option value="internal" {{ old('jenis_layanan') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="eksternal" {{ old('jenis_layanan') == 'eksternal' ? 'selected' : '' }}>Eksternal</option>
                            <option value="hybrid" {{ old('jenis_layanan') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
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
                            value="{{ old('target_pengguna') }}"
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
                            value="{{ old('estimasi_pengguna') }}"
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
                            <option value="lokal" {{ old('lingkup_aplikasi') == 'lokal' ? 'selected' : '' }}>Lokal</option>
                            <option value="regional" {{ old('lingkup_aplikasi') == 'regional' ? 'selected' : '' }}>Regional</option>
                            <option value="nasional" {{ old('lingkup_aplikasi') == 'nasional' ? 'selected' : '' }}>Nasional</option>
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
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" name="platform[]" value="web"
                                    {{ in_array('web', old('platform', [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2">Web</span>
                            </label>
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" name="platform[]" value="mobile"
                                    {{ in_array('mobile', old('platform', [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2">Mobile</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="platform[]" value="desktop"
                                    {{ in_array('desktop', old('platform', [])) ? 'checked' : '' }}
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
                            value="{{ old('teknologi_diusulkan') }}"
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
                            value="{{ old('estimasi_waktu_pengembangan') }}"
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
                            value="{{ old('estimasi_biaya') }}"
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
                            <option value="apbd" {{ old('sumber_pendanaan') == 'apbd' ? 'selected' : '' }}>APBD</option>
                            <option value="apbn" {{ old('sumber_pendanaan') == 'apbn' ? 'selected' : '' }}>APBN</option>
                            <option value="hibah" {{ old('sumber_pendanaan') == 'hibah' ? 'selected' : '' }}>Hibah</option>
                            <option value="swasta" {{ old('sumber_pendanaan') == 'swasta' ? 'selected' : '' }}>Swasta</option>
                            <option value="lainnya" {{ old('sumber_pendanaan') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                            <option value="tidak" {{ old('integrasi_sistem_lain') == 'tidak' ? 'selected' : '' }}>Tidak</option>
                            <option value="ya" {{ old('integrasi_sistem_lain') == 'ya' ? 'selected' : '' }}>Ya</option>
                        </select>
                    </div>

                    <!-- Detail Integrasi (conditional) -->
                    <div id="detail-integrasi-wrapper" style="display: {{ old('integrasi_sistem_lain') == 'ya' ? 'block' : 'none' }}">
                        <label for="detail_integrasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Detail Sistem yang Akan Diintegrasikan
                        </label>
                        <textarea id="detail_integrasi" name="detail_integrasi" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('detail_integrasi') }}</textarea>
                    </div>

                    <!-- Kebutuhan Khusus -->
                    <div>
                        <label for="kebutuhan_khusus" class="block text-sm font-medium text-gray-700 mb-1">
                            Kebutuhan Khusus (jika ada)
                        </label>
                        <textarea id="kebutuhan_khusus" name="kebutuhan_khusus" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('kebutuhan_khusus') }}</textarea>
                    </div>

                    <!-- Dampak Jika Tidak Dibangun -->
                    <div>
                        <label for="dampak_tidak_dibangun" class="block text-sm font-medium text-gray-700 mb-1">
                            Dampak Jika Tidak Dibangun
                        </label>
                        <textarea id="dampak_tidak_dibangun" name="dampak_tidak_dibangun" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('dampak_tidak_dibangun') }}</textarea>
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
                            <option value="rendah" {{ old('prioritas') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="sedang" {{ old('prioritas') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="tinggi" {{ old('prioritas') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="sangat_tinggi" {{ old('prioritas') == 'sangat_tinggi' ? 'selected' : '' }}>Sangat Tinggi</option>
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

                <div class="space-y-4">
                    <!-- Dasar Hukum -->
                    <div>
                        <label for="dasar_hukum" class="block text-sm font-medium text-gray-700 mb-1">
                            Dasar Hukum
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Peraturan perundang-undangan yang mendasari kebutuhan aplikasi</p>

                        {{-- Petunjuk Pengisian Dasar Hukum --}}
                        <div class="bg-green-50 border border-green-200 rounded p-3 mb-2">
                            <p class="text-xs text-green-800"><strong>💡 Contoh:</strong> "UU No. 5 Tahun 2014 tentang ASN; PP No. 11 Tahun 2017 tentang Manajemen PNS; Permendagri No. 35 Tahun 2021 tentang Sistem Informasi Kepegawaian"</p>
                        </div>
                        <textarea id="dasar_hukum" name="dasar_hukum" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('dasar_hukum') }}</textarea>
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

                        {{-- Petunjuk Pengisian Uraian Permasalahan --}}
                        <div class="bg-green-50 border border-green-200 rounded p-3 mb-2">
                            <p class="text-xs text-green-800"><strong>💡 Contoh:</strong> "Pengelolaan data kepegawaian masih manual menggunakan excel, sering terjadi duplikasi data, kesulitan dalam pelaporan berkala, dan proses pengajuan cuti memakan waktu hingga 5 hari kerja"</p>
                        </div>
                        <textarea id="uraian_permasalahan" name="uraian_permasalahan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('uraian_permasalahan') }}</textarea>
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

                        {{-- Petunjuk Pengisian Pihak Terkait --}}
                        <div class="bg-green-50 border border-green-200 rounded p-3 mb-2">
                            <p class="text-xs text-green-800"><strong>💡 Contoh:</strong> "BKD sebagai pengelola, Seluruh SKPD sebagai pengguna, Inspektorat sebagai pengawas, BPK sebagai auditor eksternal"</p>
                        </div>
                        <textarea id="pihak_terkait" name="pihak_terkait" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('pihak_terkait') }}</textarea>
                        @error('pihak_terkait')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ruang Lingkup -->
                    {{-- Petunjuk Pengisian Ruang Lingkup --}}
                    <div class="mb-3">
                        <button type="button" onclick="toggleGuidance('ruang-lingkup-guidance')"
                                class="flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium mb-2 focus:outline-none">
                            <svg id="ruang-lingkup-guidance-icon-plus" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg id="ruang-lingkup-guidance-icon-minus" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span id="ruang-lingkup-guidance-text">Lihat Petunjuk untuk Ruang Lingkup</span>
                        </button>

                        <div id="ruang-lingkup-guidance" class="hidden bg-blue-50 border-l-4 border-blue-500 p-4 mb-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-semibold text-blue-800 mb-2">📋 Petunjuk Pengisian: Ruang Lingkup</h3>
                                    <div class="text-sm text-blue-700 space-y-2">
                                        <p><strong>Fokus:</strong> Batasan dan cakupan dimensi aplikasi (kuantitatif)</p>

                                        <p><strong>Apa yang perlu dijelaskan:</strong></p>
                                        <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                            <li>Cakupan geografis/organisasi (berapa SKPD/unit kerja terlibat?)</li>
                                            <li>Jumlah pengguna yang akan menggunakan sistem</li>
                                            <li>Area fungsional yang dicakup (contoh: kepegawaian, keuangan, layanan publik)</li>
                                            <li>Batasan: apa yang TIDAK termasuk dalam scope</li>
                                        </ul>

                                        <div class="bg-white p-3 rounded border border-blue-200 mt-2">
                                            <p class="font-semibold text-xs mb-1">💡 Contoh Pengisian (SIMPEG Kaltara):</p>
                                            <p class="text-xs">"Aplikasi mencakup <strong>15 SKPD Pemprov Kaltara dengan 2.500 pegawai ASN</strong>. Meliputi pengelolaan <strong>data pegawai, absensi, cuti, penilaian kinerja, dan penggajian</strong>.
                                            <br><br>
                                            <strong>Tidak termasuk:</strong> sistem rekrutmen CPNS (sudah ditangani oleh sistem terpisah), pengelolaan dana pensiun (domain BKN)."</p>
                                        </div>

                                        <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                            <p class="text-xs"><strong>⚠️ Catatan:</strong> Anda akan diminta mengisi "Uraian Ruang Lingkup" yang lebih detail di bagian Perencanaan nanti. Di sini cukup jelaskan batasan umum dan dimensi scope.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="ruang_lingkup" class="block text-sm font-medium text-gray-700 mb-1">
                            Ruang Lingkup
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Batasan dan cakupan pengembangan aplikasi</p>
                        <textarea id="ruang_lingkup" name="ruang_lingkup" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('ruang_lingkup') }}</textarea>
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

                        {{-- Petunjuk Pengisian Analisis Biaya Manfaat --}}
                        <div class="bg-green-50 border border-green-200 rounded p-3 mb-2">
                            <p class="text-xs text-green-800"><strong>💡 Contoh:</strong> "Investasi Rp 500 juta, efisiensi waktu 40%, penghematan kertas Rp 50 juta/tahun, ROI tercapai dalam 2 tahun"</p>
                        </div>
                        <textarea id="analisis_biaya_manfaat" name="analisis_biaya_manfaat" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('analisis_biaya_manfaat') }}</textarea>
                        @error('analisis_biaya_manfaat')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lokasi Implementasi -->
                    <div>
                        <label for="lokasi_implementasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Lokasi Implementasi
                        </label>

                        {{-- Petunjuk Pengisian Lokasi Implementasi --}}
                        <div class="bg-green-50 border border-green-200 rounded p-3 mb-2">
                            <p class="text-xs text-green-800"><strong>💡 Contoh:</strong> "Kantor BKD (server utama), 15 SKPD (klien), dengan akses cloud untuk pegawai"</p>
                        </div>
                        <input type="text" id="lokasi_implementasi" name="lokasi_implementasi"
                            value="{{ old('lokasi_implementasi') }}"
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
                    {{-- Petunjuk Pengisian Uraian Ruang Lingkup --}}
                    <div class="mb-3">
                        <button type="button" onclick="toggleGuidance('uraian-ruang-lingkup-guidance')"
                                class="flex items-center text-green-600 hover:text-green-800 text-sm font-medium mb-2 focus:outline-none">
                            <svg id="uraian-ruang-lingkup-guidance-icon-plus" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg id="uraian-ruang-lingkup-guidance-icon-minus" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span id="uraian-ruang-lingkup-guidance-text">Lihat Petunjuk untuk Perencanaan Ruang Lingkup</span>
                        </button>

                        <div id="uraian-ruang-lingkup-guidance" class="hidden bg-green-50 border-l-4 border-green-500 p-4 mb-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-semibold text-green-800 mb-2">📋 Petunjuk Pengisian: Perencanaan Ruang Lingkup</h3>
                                <div class="text-sm text-green-700 space-y-2">
                                    <p><strong>Fokus:</strong> Uraian detail implementasi - apa yang akan dibangun (kualitatif)</p>

                                    <p><strong>Perbedaan dengan "Ruang Lingkup" sebelumnya:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                        <li><strong>"Ruang Lingkup"</strong> = Batasan dimensi (15 SKPD, 2.500 user)</li>
                                        <li><strong>"Perencanaan Ruang Lingkup"</strong> = Detail modul/fitur yang akan dibangun</li>
                                    </ul>

                                    <p class="mt-2"><strong>Apa yang perlu dijelaskan:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                        <li>Daftar modul utama dan sub-modul</li>
                                        <li>Fitur-fitur spesifik dalam setiap modul</li>
                                        <li>Integrasi dengan sistem lain (jika ada)</li>
                                        <li>Deliverables per fase development</li>
                                    </ul>

                                    <div class="bg-white p-3 rounded border border-green-200 mt-2">
                                        <p class="font-semibold text-xs mb-1">💡 Contoh Pengisian (SIMPEG Kaltara):</p>
                                        <div class="text-xs space-y-2">
                                            <p><strong>Sistem akan terdiri dari 5 modul utama:</strong></p>
                                            <p><strong>1. Modul Master Data Pegawai</strong>
                                            <br>- CRUD data pegawai (NIP, nama, jabatan, golongan, unit kerja)
                                            <br>- Import bulk data dari Excel/CSV
                                            <br>- Validasi otomatis NIP dengan database BKN
                                            <br>- Generate QR code kartu pegawai</p>

                                            <p><strong>2. Modul Absensi</strong>
                                            <br>- Integrasi dengan fingerprint reader (3 unit per SKPD)
                                            <br>- Mobile check-in dengan GPS tracking
                                            <br>- Laporan kehadiran real-time (harian, bulanan)
                                            <br>- Alert notifikasi untuk keterlambatan</p>

                                            <p><strong>3. Modul Cuti</strong>
                                            <br>- Pengajuan cuti online dengan workflow approval
                                            <br>- Tracking saldo cuti otomatis
                                            <br>- Notifikasi email/WhatsApp ke atasan</p>

                                            <p><strong>4. Modul Penilaian Kinerja</strong>
                                            <br>- SKP (Sasaran Kinerja Pegawai) digital
                                            <br>- Penilaian bulanan dengan rating 1-5
                                            <br>- Dashboard analitik kinerja per SKPD</p>

                                            <p><strong>5. Modul Penggajian</strong>
                                            <br>- Generate slip gaji otomatis
                                            <br>- Integrasi dengan SIPD (sistem keuangan daerah)
                                            <br>- Perhitungan TPP berdasarkan kehadiran dan kinerja</p>
                                        </div>
                                    </div>

                                    <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                        <p class="text-xs"><strong>💡 Tips:</strong> Jangan hanya copy-paste "Ruang Lingkup" sebelumnya. Di sini jelaskan DETAIL TEKNIS dan FITUR SPESIFIK yang akan dibangun!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div>
                        <label for="uraian_ruang_lingkup" class="block text-sm font-medium text-gray-700 mb-1">
                            Uraian Ruang Lingkup Perencanaan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Detail ruang lingkup dalam tahap perencanaan</p>
                        <textarea id="uraian_ruang_lingkup" name="uraian_ruang_lingkup" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('uraian_ruang_lingkup') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('proses_bisnis') }}</textarea>
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
                    {{-- Petunjuk Pengisian Kerangka Kerja/Metodologi --}}
                    <div class="mb-3">
                        <button type="button" onclick="toggleGuidance('kerangka-kerja-guidance')"
                                class="flex items-center text-indigo-600 hover:text-indigo-800 text-sm font-medium mb-2 focus:outline-none">
                            <svg id="kerangka-kerja-guidance-icon-plus" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg id="kerangka-kerja-guidance-icon-minus" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span id="kerangka-kerja-guidance-text">Lihat Petunjuk untuk Kerangka Kerja/Metodologi</span>
                        </button>

                        <div id="kerangka-kerja-guidance" class="hidden bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-indigo-800 mb-2">🔄 Petunjuk Pengisian: Kerangka Kerja/Metodologi</h3>
                                <div class="text-sm text-indigo-700 space-y-2">
                                    <p><strong>Fokus: BAGAIMANA cara/pendekatan pengembangan aplikasi (metodologi & framework kerja)</strong></p>

                                    <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                        <p class="text-xs"><strong>⚠️ Bedakan dengan kolom lain:</strong></p>
                                        <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                            <li><strong>"Teknologi yang Diusulkan"</strong> (Step 1) = Tech stack/tools (Laravel, React, PostgreSQL)</li>
                                            <li><strong>"Kerangka Kerja/Metodologi"</strong> (Step 3 - kolom ini) = Cara/pendekatan pengembangan (Agile, Waterfall)</li>
                                            <li><strong>"Rencana Aksi"</strong> (Step 3) = Langkah-langkah konkret (siapa, apa, deliverable)</li>
                                        </ul>
                                    </div>

                                    <p><strong>Apa yang perlu dijelaskan:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                        <li>Metodologi pengembangan yang dipilih (Agile/Scrum, Waterfall, RAD, dll)</li>
                                        <li>Alasan pemilihan metodologi tersebut</li>
                                        <li>Proses/ritual yang akan dijalankan (sprint planning, daily standup, dll)</li>
                                        <li>Standar/framework yang digunakan (PMBOK, ISO, COBIT, dll)</li>
                                    </ul>

                                    <div class="bg-white p-3 rounded border border-indigo-200 mt-2">
                                        <p class="font-semibold text-xs mb-1">💡 Contoh Pengisian (SIMPEG Kaltara):</p>
                                        <div class="text-xs space-y-2">
                                            <p><strong>Metodologi Pengembangan: Waterfall</strong></p>

                                            <p><strong>Alasan Pemilihan:</strong>
                                            <br>- Kebutuhan sudah jelas dan tidak akan banyak berubah
                                            <br>- Proyek pemerintah membutuhkan dokumentasi lengkap di setiap tahap
                                            <br>- Cocok untuk tim kecil (3-5 orang) dengan pengalaman terbatas
                                            <br>- Mudah dipahami oleh stakeholder non-IT (BKD, Diskominfo)</p>

                                            <p><strong>Tahapan Waterfall yang Akan Dijalankan:</strong>
                                            <br>1. <strong>Analisis Kebutuhan (Requirement Analysis)</strong> - Durasi: 1 bulan
                                            <br>&nbsp;&nbsp;&nbsp;• Wawancara dengan BKD dan SKPD untuk memahami kebutuhan
                                            <br>&nbsp;&nbsp;&nbsp;• Buat dokumen Spesifikasi Kebutuhan Sistem (SRS)
                                            <br>&nbsp;&nbsp;&nbsp;• Dokumentasi harus disetujui stakeholder sebelum lanjut
                                            <br>
                                            <br>2. <strong>Desain Sistem (System Design)</strong> - Durasi: 1 bulan
                                            <br>&nbsp;&nbsp;&nbsp;• Desain arsitektur aplikasi (database, server, user interface)
                                            <br>&nbsp;&nbsp;&nbsp;• Buat mockup/wireframe tampilan untuk persetujuan user
                                            <br>&nbsp;&nbsp;&nbsp;• Desain database dan ERD (Entity Relationship Diagram)
                                            <br>
                                            <br>3. <strong>Implementasi/Coding</strong> - Durasi: 3 bulan
                                            <br>&nbsp;&nbsp;&nbsp;• Coding berdasarkan desain yang sudah disetujui
                                            <br>&nbsp;&nbsp;&nbsp;• Development modul per modul (Master Data → Absensi → Cuti → dll)
                                            <br>&nbsp;&nbsp;&nbsp;• Dokumentasi kode dan manual teknis
                                            <br>
                                            <br>4. <strong>Testing & Verifikasi</strong> - Durasi: 1 bulan
                                            <br>&nbsp;&nbsp;&nbsp;• Unit testing untuk setiap modul
                                            <br>&nbsp;&nbsp;&nbsp;• Integration testing antar modul
                                            <br>&nbsp;&nbsp;&nbsp;• User Acceptance Testing (UAT) dengan BKD dan 2-3 SKPD pilot
                                            <br>&nbsp;&nbsp;&nbsp;• Perbaikan bug dan error yang ditemukan
                                            <br>
                                            <br>5. <strong>Deployment & Maintenance</strong> - Durasi: 1 bulan
                                            <br>&nbsp;&nbsp;&nbsp;• Instalasi di server production
                                            <br>&nbsp;&nbsp;&nbsp;• Training user untuk admin dan pegawai
                                            <br>&nbsp;&nbsp;&nbsp;• Serah terima dokumentasi lengkap ke Diskominfo
                                            <br>&nbsp;&nbsp;&nbsp;• Support dan maintenance 6 bulan pertama</p>

                                            <p><strong>Tools Sederhana yang Digunakan:</strong>
                                            <br>- <strong>Dokumentasi:</strong> Microsoft Word/Excel untuk dokumen SRS dan laporan
                                            <br>- <strong>Komunikasi:</strong> WhatsApp Group untuk koordinasi tim
                                            <br>- <strong>Meeting:</strong> Rapat rutin setiap akhir bulan untuk update progress
                                            <br>- <strong>File Sharing:</strong> Google Drive untuk berbagi file dan dokumentasi</p>

                                            <p><strong>Standar Kualitas:</strong>
                                            <br>- Setiap fase harus menghasilkan dokumen yang ditandatangani stakeholder
                                            <br>- Testing dilakukan manual oleh tim developer dan user
                                            <br>- Dokumentasi lengkap (SRS, Manual User, Manual Teknis)
                                            <br>- Code harus rapi dan mudah dipahami untuk maintenance kedepan</p>
                                        </div>
                                    </div>

                                    <div class="bg-white p-3 rounded border border-indigo-200 mt-2">
                                        <p class="font-semibold text-xs mb-1">📚 Pilihan Metodologi Umum:</p>
                                        <div class="text-xs space-y-2">
                                            <p><strong>1. Agile/Scrum</strong> - Cocok untuk: proyek dengan kebutuhan dinamis, tim kecil, butuh feedback cepat</p>
                                            <p><strong>2. Waterfall</strong> - Cocok untuk: kebutuhan fix, timeline ketat, dokumentasi formal, proyek government</p>
                                            <p><strong>3. RAD (Rapid Application Development)</strong> - Cocok untuk: prototype cepat, proof of concept, MVP</p>
                                            <p><strong>4. DevOps</strong> - Cocok untuk: continuous deployment, automation, monitoring intensive</p>
                                            <p><strong>5. Hybrid (Agile-Waterfall)</strong> - Cocok untuk: kebutuhan formal dokumentasi + iterative development</p>
                                        </div>
                                    </div>

                                    <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                        <p class="text-xs"><strong>💡 Tips:</strong> Jangan hanya tulis "Menggunakan Agile". Jelaskan DETAIL: sprint berapa lama, ritual apa saja, tools apa yang dipakai, standar apa yang diikuti. Tunjukkan bahwa Anda benar-benar memahami metodologi yang dipilih!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div>
                        <label for="kerangka_kerja" class="block text-sm font-medium text-gray-700 mb-1">
                            Kerangka Kerja/Metodologi
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Metodologi pengembangan yang akan digunakan (Agile, Waterfall, dll)</p>
                        <textarea id="kerangka_kerja" name="kerangka_kerja" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('kerangka_kerja') }}</textarea>
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
                            <option value="menteri" {{ old('pelaksana_pembangunan') == 'menteri' ? 'selected' : '' }}>Menteri</option>
                            <option value="swakelola" {{ old('pelaksana_pembangunan') == 'swakelola' ? 'selected' : '' }}>Swakelola</option>
                            <option value="pihak_ketiga" {{ old('pelaksana_pembangunan') == 'pihak_ketiga' ? 'selected' : '' }}>Pihak Ketiga</option>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('peran_tanggung_jawab') }}</textarea>
                        @error('peran_tanggung_jawab')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jadwal Pelaksanaan -->
                    {{-- Petunjuk Pengisian Jadwal Pelaksanaan --}}
                    <div class="mb-3">
                        <button type="button" onclick="toggleGuidance('jadwal-pelaksanaan-guidance')"
                                class="flex items-center text-purple-600 hover:text-purple-800 text-sm font-medium mb-2 focus:outline-none">
                            <svg id="jadwal-pelaksanaan-guidance-icon-plus" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg id="jadwal-pelaksanaan-guidance-icon-minus" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span id="jadwal-pelaksanaan-guidance-text">Lihat Petunjuk untuk Jadwal Pelaksanaan</span>
                        </button>

                        <div id="jadwal-pelaksanaan-guidance" class="hidden bg-purple-50 border-l-4 border-purple-500 p-4 mb-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-purple-800 mb-2">⏰ Petunjuk Pengisian: Jadwal Pelaksanaan</h3>
                                <div class="text-sm text-purple-700 space-y-2">
                                    <p><strong>Fokus: KAPAN setiap fase dilaksanakan (timeline)</strong></p>

                                    <p><strong>Apa yang perlu dijelaskan:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                        <li>Timeline pengembangan (dalam kuartal/bulan/minggu)</li>
                                        <li>Durasi setiap fase</li>
                                        <li>Milestone penting dan deadline</li>
                                        <li>Target tanggal go-live/deployment</li>
                                    </ul>

                                    <div class="bg-white p-3 rounded border border-purple-200 mt-2">
                                        <p class="font-semibold text-xs mb-1">💡 Contoh Pengisian (SIMPEG Kaltara):</p>
                                        <div class="text-xs space-y-2">
                                            <p><strong>📅 Q1 2026 (Januari - Maret): Analisis & Desain (2 bulan)</strong>
                                            <br>- Minggu 1-2: Workshop requirement gathering dengan BKD dan 3 SKPD pilot
                                            <br>- Minggu 3-6: System design dan pembuatan dokumentasi SRS
                                            <br>- Minggu 7-8: Desain UI/UX dan approval stakeholder
                                            <br>- <em>Milestone: Dokumen SRS final approved (28 Februari 2026)</em></p>

                                            <p><strong>📅 Q2-Q3 2026 (April - September): Development & Testing (6 bulan)</strong>
                                            <br>- Sprint 1-2 (Apr-Mei): Modul Master Data & Autentikasi
                                            <br>- Sprint 3-4 (Jun-Jul): Modul Absensi & Cuti
                                            <br>- Sprint 5-6 (Agt-Sep): Modul Kinerja & Penggajian
                                            <br>- Parallel: Integration testing & security testing
                                            <br>- <em>Milestone: MVP ready untuk UAT (30 September 2026)</em></p>

                                            <p><strong>📅 Q4 2026 (Oktober - Desember): UAT & Deployment (3 bulan)</strong>
                                            <br>- Minggu 1-4 (Okt): User Acceptance Testing di 3 SKPD pilot
                                            <br>- Minggu 5-8 (Nov): Bug fixing & refinement
                                            <br>- Minggu 9-10 (Des): Training user & admin 15 SKPD
                                            <br>- Minggu 11-12 (Des): Deployment production & cutover data
                                            <br>- <em>Milestone: Go-live production (16 Desember 2026)</em></p>

                                            <p><strong>📅 Q1 2027 (Januari - Maret): Stabilisasi & Hypercare (3 bulan)</strong>
                                            <br>- Support intensif post-launch
                                            <br>- Monitoring dan quick-fix bug critical
                                            <br>- Evaluasi performa sistem</p>
                                        </div>
                                    </div>

                                    <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                        <p class="text-xs"><strong>⚠️ Bedakan dengan "Rencana Aksi":</strong> Jadwal Pelaksanaan fokus pada TIMELINE (kapan), sedangkan Rencana Aksi fokus pada LANGKAH KONKRET (apa yang dilakukan).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div>
                        <label for="jadwal_pelaksanaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Jadwal Pelaksanaan
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Timeline pengembangan aplikasi</p>
                        <textarea id="jadwal_pelaksanaan" name="jadwal_pelaksanaan" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('jadwal_pelaksanaan') }}</textarea>
                        @error('jadwal_pelaksanaan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rencana Aksi -->
                    {{-- Petunjuk Pengisian Rencana Aksi --}}
                    <div class="mb-3">
                        <button type="button" onclick="toggleGuidance('rencana-aksi-guidance')"
                                class="flex items-center text-orange-600 hover:text-orange-800 text-sm font-medium mb-2 focus:outline-none">
                            <svg id="rencana-aksi-guidance-icon-plus" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg id="rencana-aksi-guidance-icon-minus" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span id="rencana-aksi-guidance-text">Lihat Petunjuk untuk Rencana Aksi</span>
                        </button>

                        <div id="rencana-aksi-guidance" class="hidden bg-orange-50 border-l-4 border-orange-500 p-4 mb-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-orange-800 mb-2">✅ Petunjuk Pengisian: Rencana Aksi</h3>
                                <div class="text-sm text-orange-700 space-y-2">
                                    <p><strong>Fokus: APA langkah konkret yang akan dilakukan (action items)</strong></p>

                                    <p><strong>Apa yang perlu dijelaskan:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                        <li>Daftar langkah-langkah spesifik yang terukur</li>
                                        <li>PIC (Person in Charge) untuk setiap aksi</li>
                                        <li>Deliverables yang dihasilkan dari setiap aksi</li>
                                        <li>Dependencies antar aksi (jika ada)</li>
                                    </ul>

                                    <div class="bg-white p-3 rounded border border-orange-200 mt-2">
                                        <p class="font-semibold text-xs mb-1">💡 Contoh Pengisian (SIMPEG Kaltara):</p>
                                        <div class="text-xs space-y-2">
                                            <p><strong>1. Pembentukan Tim Proyek & Kick-off</strong>
                                            <br>- Bentuk tim: PM (1), Backend Dev (2), Frontend Dev (2), UI/UX (1), QA (1), DBA (1)
                                            <br>- Kick-off meeting dengan stakeholder Diskominfo dan BKD
                                            <br>- PIC: Kepala Bidang TI Diskominfo
                                            <br>- Deliverable: SK Tim Proyek</p>

                                            <p><strong>2. Requirement Gathering</strong>
                                            <br>- Workshop dengan BKD untuk capture business process
                                            <br>- Interview dengan 3 SKPD pilot (Diskominfo, BKPSDM, Setda)
                                            <br>- PIC: Business Analyst & PM
                                            <br>- Deliverable: Dokumen BRD</p>

                                            <p><strong>3. Setup Development Environment</strong>
                                            <br>- Provisioning server development & staging di Data Center Kaltara
                                            <br>- Setup repository Git dan CI/CD pipeline
                                            <br>- PIC: DevOps Engineer & DBA
                                            <br>- Deliverable: Dev environment ready</p>

                                            <p><strong>4. Development Sprint (Agile)</strong>
                                            <br>- Sprint planning setiap 2 minggu
                                            <br>- Code review mandatory sebelum merge
                                            <br>- PIC: Scrum Master & Development Team
                                            <br>- Deliverable: Working software increment</p>

                                            <p><strong>5. Testing & QA</strong>
                                            <br>- Unit testing (coverage 80%)
                                            <br>- Security testing (penetration test)
                                            <br>- PIC: QA Engineer
                                            <br>- Deliverable: Test report</p>

                                            <p><strong>6. Training & Deployment</strong>
                                            <br>- Training untuk IT SKPD (3 hari)
                                            <br>- Deployment production
                                            <br>- PIC: Tim Training & DevOps
                                            <br>- Deliverable: Sistem live</p>
                                        </div>
                                    </div>

                                    <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                        <p class="text-xs"><strong>⚠️ Bedakan dengan "Jadwal Pelaksanaan":</strong> Rencana Aksi fokus pada LANGKAH KONKRET (apa & siapa), sedangkan Jadwal fokus pada WAKTU (kapan).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div>
                        <label for="rencana_aksi" class="block text-sm font-medium text-gray-700 mb-1">
                            Rencana Aksi
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Langkah-langkah konkret yang akan dilakukan</p>
                        <textarea id="rencana_aksi" name="rencana_aksi" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('rencana_aksi') }}</textarea>
                        @error('rencana_aksi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keamanan Informasi -->
                    {{-- Petunjuk Pengisian Keamanan Informasi --}}
                    <div class="mb-3">
                        <button type="button" onclick="toggleGuidance('keamanan-informasi-guidance')"
                                class="flex items-center text-red-600 hover:text-red-800 text-sm font-medium mb-2 focus:outline-none">
                            <svg id="keamanan-informasi-guidance-icon-plus" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <svg id="keamanan-informasi-guidance-icon-minus" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                            <span id="keamanan-informasi-guidance-text">Lihat Petunjuk untuk Keamanan Informasi</span>
                        </button>

                        <div id="keamanan-informasi-guidance" class="hidden bg-red-50 border-l-4 border-red-500 p-4 mb-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-red-800 mb-2">🔒 Petunjuk Pengisian: Keamanan Informasi</h3>
                                <div class="text-sm text-red-700 space-y-2">
                                    <p><strong>Fokus: Strategi dan mekanisme pengamanan sistem</strong></p>

                                    <p><strong>5 Aspek Keamanan yang Harus Didokumentasikan:</strong></p>

                                    <div class="bg-white p-3 rounded border border-red-200 mt-2 space-y-3">
                                        <div>
                                            <p class="font-semibold text-xs mb-1">🔐 1. AUTENTIKASI & OTORISASI</p>
                                            <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                                <li><strong>Metode Login:</strong> Username/password, SSO, 2FA/MFA</li>
                                                <li><strong>Password Policy:</strong> Min 12 karakter, kompleksitas, expired 90 hari</li>
                                                <li><strong>Session Management:</strong> Auto logout 30 menit idle</li>
                                                <li><strong>Role-Based Access:</strong> Admin, Operator, User biasa</li>
                                            </ul>
                                            <p class="text-xs mt-1 italic">Contoh: "SSO dengan Keycloak Pemprov Kaltara. 2FA wajib untuk Admin menggunakan Google Authenticator."</p>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-xs mb-1">🔒 2. ENKRIPSI DATA</p>
                                            <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                                <li><strong>Data in Transit:</strong> HTTPS/TLS 1.3</li>
                                                <li><strong>Data at Rest:</strong> Enkripsi database AES-256</li>
                                                <li><strong>Password Hashing:</strong> bcrypt/argon2 (BUKAN MD5)</li>
                                                <li><strong>API Security:</strong> JWT token expiry 1 jam</li>
                                            </ul>
                                            <p class="text-xs mt-1 italic">Contoh: "Data sensitif (NIP, gaji) dienkripsi AES-256. TLS 1.3 dengan certificate Sectigo."</p>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-xs mb-1">🛡️ 3. KONTROL AKSES & AUDIT LOGGING</p>
                                            <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                                <li><strong>RBAC:</strong> Pembatasan akses per role</li>
                                                <li><strong>Least Privilege:</strong> User hanya akses yang dibutuhkan</li>
                                                <li><strong>Audit Trail:</strong> Log semua aktivitas (login, CRUD)</li>
                                                <li><strong>Monitoring:</strong> Dashboard aktivitas mencurigakan</li>
                                            </ul>
                                            <p class="text-xs mt-1 italic">Contoh: "Setiap perubahan data dicatat dalam audit log. Retention 5 tahun sesuai UU ITE."</p>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-xs mb-1">💾 4. BACKUP & DISASTER RECOVERY</p>
                                            <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                                <li><strong>Backup Schedule:</strong> Full backup mingguan + incremental harian</li>
                                                <li><strong>Backup Storage:</strong> Encrypted backup di offsite/cloud</li>
                                                <li><strong>RTO:</strong> Target waktu restore (contoh: 4 jam)</li>
                                                <li><strong>RPO:</strong> Target data loss maksimal (contoh: 1 jam)</li>
                                            </ul>
                                            <p class="text-xs mt-1 italic">Contoh: "Backup harian pukul 02:00 WIB ke AWS S3. RTO: 4 jam, RPO: 1 jam. DR testing 6 bulan sekali."</p>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-xs mb-1">🔍 5. COMPLIANCE & SECURITY TESTING</p>
                                            <ul class="list-disc list-inside space-y-1 text-xs ml-2">
                                                <li><strong>Security Audit:</strong> Audit berkala pihak independen</li>
                                                <li><strong>Penetration Testing:</strong> Pentest tahunan</li>
                                                <li><strong>Vulnerability Scanning:</strong> Automated scan (OWASP ZAP)</li>
                                                <li><strong>Compliance:</strong> ISO 27001, UU ITE, Permenkominfo</li>
                                            </ul>
                                            <p class="text-xs mt-1 italic">Contoh: "Pentest tahunan oleh konsultan tersertifikasi. Vulnerability scan otomatis mingguan."</p>
                                        </div>
                                    </div>

                                    <div class="bg-white p-3 rounded border border-red-200 mt-2">
                                        <p class="font-semibold text-xs mb-1">💡 Contoh Lengkap (SIMPEG Kaltara):</p>
                                        <div class="text-xs space-y-2">
                                            <p><strong>1. Autentikasi & Otorisasi:</strong> SSO Keycloak Pemprov Kaltara + 2FA untuk Admin. Password min 12 karakter, expired 90 hari. 4 level role: Superadmin, Admin SKPD, Operator, User. Auto logout 30 menit.</p>

                                            <p><strong>2. Enkripsi Data:</strong> HTTPS TLS 1.3 (Sectigo), Database PostgreSQL encrypted AES-256, Password hashing bcrypt (cost 12), JWT token (expiry 1 jam).</p>

                                            <p><strong>3. Kontrol Akses & Audit:</strong> RBAC (Admin SKPD hanya akses data SKPD sendiri), Audit log semua aktivitas, Log retention 5 tahun, Dashboard monitoring real-time.</p>

                                            <p><strong>4. Backup & Recovery:</strong> Full backup Minggu 02:00, Incremental backup harian 02:00, Backup encrypted di AWS S3 Jakarta + local NAS, RTO 4 jam, RPO 1 jam, DR testing 6 bulan.</p>

                                            <p><strong>5. Compliance & Testing:</strong> Pentest tahunan (konsultan CEH), Vulnerability scan mingguan (OWASP ZAP), Security audit 6 bulan (Inspektorat), ISO 27001 framework + UU ITE.</p>
                                        </div>
                                    </div>

                                    <div class="bg-yellow-50 border border-yellow-200 p-2 rounded mt-2">
                                        <p class="text-xs"><strong>💡 Tips:</strong> Jangan hanya tulis "enkripsi dan backup". Jelaskan SPESIFIK: algoritma (AES-256), frekuensi (harian), RTO/RPO (berapa jam).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div>
                        <label for="keamanan_informasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Keamanan Informasi
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Aspek keamanan data dan informasi yang akan diterapkan</p>
                        <textarea id="keamanan_informasi" name="keamanan_informasi" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('keamanan_informasi') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('sumber_daya_manusia') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('sumber_daya_anggaran') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('sumber_daya_sarana') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('indikator_keberhasilan') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('alih_pengetahuan') }}</textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg editor-field">{{ old('pemantauan_pelaporan') }}</textarea>
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
                    <!-- Risiko items will be added here dynamically -->
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
                                Pastikan semua data yang Anda masukkan sudah benar. Setelah disimpan sebagai draft, Anda masih dapat mengedit.
                                Namun setelah diajukan untuk verifikasi, data tidak dapat diubah.
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
                    <div class="space-x-3">
                        <button type="submit" name="action" value="draft" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                            Simpan sebagai Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                            Ajukan untuk Verifikasi
                        </button>
                    </div>
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
let risikoCounter = 0;

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

    if (step === 4) {
        // Validate Manajemen Risiko - at least 1 risk required
        const risikoCount = document.querySelectorAll('.risiko-item').length;
        if (risikoCount === 0) {
            alert('Mohon tambahkan minimal 1 risiko SPBE sebelum melanjutkan');
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

// Initialize with one empty risiko
document.addEventListener('DOMContentLoaded', function() {
    addRisiko();
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

    // Step 4: Manajemen Risiko SPBE
    reviewHTML += '<div class="border-b pb-4"><h3 class="font-semibold text-lg mb-3">4. Manajemen Risiko SPBE</h3>';
    const risikoItems = document.querySelectorAll('.risiko-item');
    reviewHTML += `<div class="text-gray-600">`;
    reviewHTML += `<p class="mb-3"><strong>Total Risiko Teridentifikasi:</strong> ${risikoItems.length} risiko</p>`;

    if (risikoItems.length > 0) {
        reviewHTML += `<div class="space-y-4">`;
        risikoItems.forEach((item, index) => {
            const jenisRisiko = item.querySelector(`select[name*="[jenis_risiko]"]`)?.value || '-';
            const kategoriRisiko = item.querySelector(`select[name*="[kategori_risiko]"]`)?.value || '-';
            const areaDampak = item.querySelector(`select[name*="[area_dampak]"]`)?.value || '-';
            const uraianKejadian = item.querySelector(`textarea[name*="[uraian_kejadian]"]`)?.value || '-';
            const penyebab = item.querySelector(`input[name*="[penyebab]"]`)?.value || '-';
            const dampak = item.querySelector(`input[name*="[dampak]"]`)?.value || '-';
            const levelKemungkinan = item.querySelector(`select[name*="[level_kemungkinan]"]`)?.selectedOptions[0]?.text || '-';
            const levelDampak = item.querySelector(`select[name*="[level_dampak]"]`)?.selectedOptions[0]?.text || '-';
            const besaranRisiko = item.querySelector(`input[name*="[besaran_risiko]"]`)?.value || '-';
            const perluPenanganan = item.querySelector(`select[name*="[perlu_penanganan]"]`)?.value || '-';
            const rencanaAksi = item.querySelector(`textarea[name*="[rencana_aksi]"]`)?.value || '-';
            const penanggungJawab = item.querySelector(`input[name*="[penanggung_jawab]"]`)?.value || '-';

            reviewHTML += `<div class="bg-gray-50 p-4 rounded-lg border border-gray-200">`;
            reviewHTML += `<p class="font-semibold text-base mb-3">Risiko SPBE #${index + 1}</p>`;
            reviewHTML += `<div class="grid grid-cols-2 gap-3 text-sm">`;
            reviewHTML += `<div><strong>Jenis:</strong> ${jenisRisiko}</div>`;
            reviewHTML += `<div><strong>Kategori:</strong> ${kategoriRisiko}</div>`;
            reviewHTML += `<div><strong>Area Dampak:</strong> ${areaDampak}</div>`;
            reviewHTML += `<div><strong>Besaran Risiko:</strong> <span class="font-semibold">${besaranRisiko}</span></div>`;
            reviewHTML += `<div class="col-span-2"><strong>Uraian Kejadian:</strong> ${uraianKejadian.substring(0, 100)}${uraianKejadian.length > 100 ? '...' : ''}</div>`;
            reviewHTML += `<div><strong>Penyebab:</strong> ${penyebab}</div>`;
            reviewHTML += `<div><strong>Dampak:</strong> ${dampak}</div>`;
            reviewHTML += `<div><strong>Level Kemungkinan:</strong> ${levelKemungkinan}</div>`;
            reviewHTML += `<div><strong>Level Dampak:</strong> ${levelDampak}</div>`;
            reviewHTML += `<div><strong>Perlu Penanganan:</strong> ${perluPenanganan}</div>`;
            reviewHTML += `<div><strong>Penanggung Jawab:</strong> ${penanggungJawab}</div>`;
            if (rencanaAksi && rencanaAksi !== '-') {
                reviewHTML += `<div class="col-span-2"><strong>Rencana Aksi:</strong> ${rencanaAksi.substring(0, 150)}${rencanaAksi.length > 150 ? '...' : ''}</div>`;
            }
            reviewHTML += `</div></div>`;
        });
        reviewHTML += `</div>`;
    } else {
        reviewHTML += `<p class="text-gray-500 italic">Belum ada risiko yang diidentifikasi</p>`;
    }
    reviewHTML += `</div></div>`;

    reviewHTML += '</div>';

    document.getElementById('review-content').innerHTML = reviewHTML;
}

// Toggle guidance function
function toggleGuidance(guidanceId) {
    const guidance = document.getElementById(guidanceId);
    const iconPlus = document.getElementById(guidanceId + '-icon-plus');
    const iconMinus = document.getElementById(guidanceId + '-icon-minus');
    const text = document.getElementById(guidanceId + '-text');

    if (guidance.classList.contains('hidden')) {
        guidance.classList.remove('hidden');
        iconPlus.classList.add('hidden');
        iconMinus.classList.remove('hidden');
        text.textContent = text.textContent.replace('Lihat', 'Sembunyikan');
    } else {
        guidance.classList.add('hidden');
        iconPlus.classList.remove('hidden');
        iconMinus.classList.add('hidden');
        text.textContent = text.textContent.replace('Sembunyikan', 'Lihat');
    }
}
</script>
@endpush
@endsection
