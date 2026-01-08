@extends('layouts.authenticated')

@section('title', '- Formulir Rekomendasi Aplikasi')
@section('header-title', 'Formulir Rekomendasi Aplikasi')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6">Formulir Rekomendasi Aplikasi</h1>

        <form action="{{ route('user.rekomendasi.aplikasi.store') }}" method="POST" id="form-rekomendasi">
            @csrf

            {{-- Validation Error Display --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Terjadi Kesalahan!</strong>
                    <span class="block sm:inline">Mohon periksa kembali data yang diinput.</span>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Dokumen Analisis Kebutuhan --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Judul Aplikasi <span class="text-red-500">*</span></label>
                    <input type="text" name="judul_aplikasi" class="w-full border rounded p-2" required>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Nama aplikasi yang akan dikembangkan. Contoh: "Sistem Informasi Pelayanan Publik"</p>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Target Waktu <span class="text-red-500">*</span></label>
                    <input type="text" name="target_waktu" class="w-full border rounded p-2" required>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Estimasi waktu penyelesaian. Contoh: "6 bulan", "Q1 2025"</p>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Sasaran Pengguna <span class="text-red-500">*</span></label>
                    <input type="text" name="sasaran_pengguna" class="w-full border rounded p-2" required>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Target pengguna. Contoh: "Pegawai internal", "Masyarakat umum", "100-500 user"</p>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Lokasi Implementasi <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi_implementasi" class="w-full border rounded p-2" required>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Area implementasi. Contoh: "Seluruh Indonesia", "Kota Bandung", "Kantor Pusat"</p>
                </div>
            </div>

            <div class="mt-4">
                <label class="block font-semibold mb-1">Dasar Hukum <span class="text-red-500">*</span></label>
                <textarea name="dasar_hukum" id="editor_dasar_hukum" class="w-full border rounded p-2 editor-field" rows="3"></textarea>
                <div id="wordcount_dasar_hukum" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Regulasi/peraturan yang menjadi landasan. Contoh: "UU No. 14 Tahun 2008, Perda No. 5/2020"</p>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Permasalahan Kebutuhan <span class="text-red-500">*</span></label>
                <textarea name="permasalahan_kebutuhan" id="editor_permasalahan_kebutuhan" class="w-full border rounded p-2 editor-field" rows="4"></textarea>
                <div id="wordcount_permasalahan_kebutuhan" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Uraian masalah yang ingin diselesaikan dan kebutuhan yang diperlukan. Jelaskan latar belakang mengapa aplikasi ini dibutuhkan</p>
            </div>
            {{-- Pihak Terkait - Structured Fields --}}
            <div class="mt-4">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Pihak Terkait</h3>

                {{-- Pemilik Utama Proses Bisnis --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Pemilik Utama Proses Bisnis / Instansi Utama Pengusul <span class="text-red-500">*</span></label>
                    <select name="pemilik_proses_bisnis_id" class="w-full border rounded p-2" required>
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($unitKerjas as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Unit kerja/instansi yang menjadi pemilik utama dari aplikasi ini</p>
                </div>

                {{-- Stakeholder Internal Lainnya --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Stakeholder Internal Lainnya <span class="text-red-500">*</span></label>
                    <select name="stakeholder_internal[]" class="w-full border rounded p-2" multiple size="5" required>
                        @foreach($unitKerjas as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Unit kerja internal lainnya yang terlibat. Tekan <kbd class="px-1 py-0.5 bg-gray-200 rounded">Ctrl</kbd> (Windows) atau <kbd class="px-1 py-0.5 bg-gray-200 rounded">Cmd</kbd> (Mac) untuk memilih lebih dari satu</p>
                </div>

                {{-- Stakeholder Eksternal --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Stakeholder Eksternal <span class="text-red-500">*</span></label>
                    <textarea name="stakeholder_eksternal" id="editor_stakeholder_eksternal" class="w-full border rounded p-2 editor-field" rows="3" placeholder="Contoh: Masyarakat umum, Vendor IT (PT ABC), Kementerian XYZ"></textarea>
                    <div id="wordcount_stakeholder_eksternal" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Pihak eksternal yang terlibat (masyarakat, vendor, instansi lain di luar organisasi). Tulis manual dengan pemisah koma</p>
                </div>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Maksud & Tujuan <span class="text-red-500">*</span></label>
                <textarea name="maksud_tujuan" id="editor_maksud_tujuan" class="w-full border rounded p-2 editor-field" rows="4"></textarea>
                <div id="wordcount_maksud_tujuan" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Tujuan pengembangan aplikasi dan manfaat yang diharapkan. Jelaskan outcome yang ingin dicapai</p>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Ruang Lingkup <span class="text-red-500">*</span></label>
                <textarea name="ruang_lingkup" id="editor_ruang_lingkup" class="w-full border rounded p-2 editor-field" rows="4"></textarea>
                <div id="wordcount_ruang_lingkup" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Batasan dan cakupan aplikasi. Jelaskan fitur apa saja yang akan dikembangkan dan tidak dikembangkan</p>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Analisis Biaya Manfaat <span class="text-red-500">*</span></label>
                <textarea name="analisis_biaya_manfaat" id="editor_analisis_biaya_manfaat" class="w-full border rounded p-2 editor-field" rows="3"></textarea>
                <div id="wordcount_analisis_biaya_manfaat" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Perbandingan biaya investasi dengan manfaat yang akan diperoleh (cost-benefit analysis)</p>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Analisis Risiko <span class="text-red-500">*</span></label>
                <textarea name="analisis_risiko" id="editor_analisis_risiko" class="w-full border rounded p-2 editor-field" rows="3"></textarea>
                <div id="wordcount_analisis_risiko" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Ringkasan risiko utama yang teridentifikasi dan dampaknya terhadap proyek</p>
            </div>

            {{-- Dokumen Perencanaan --}}
            <hr class="my-6">
            <h2 class="text-lg font-bold mb-2">Dokumen Perencanaan</h2>

            @php
                $fields = [
                    'perencanaan_ruang_lingkup' => [
                        'label' => 'Perencanaan Ruang Lingkup',
                        'help' => 'Detail fitur dan modul yang akan dikembangkan. Bisa berupa daftar menu/fungsi utama'
                    ],
                    'perencanaan_proses_bisnis' => [
                        'label' => 'Perencanaan Proses Bisnis',
                        'help' => 'Alur kerja (workflow) aplikasi. Jelaskan bagaimana proses bisnis akan berjalan di sistem'
                    ],
                    'kerangka_kerja' => [
                        'label' => 'Kerangka Kerja',
                        'help' => 'Metodologi pengembangan. Contoh: "Agile Scrum", "Waterfall", "DevOps"'
                    ],
                    'pelaksana_pembangunan' => [
                        'label' => 'Pelaksana Pembangunan',
                        'help' => 'Cara pelaksanaan proyek. Contoh: "Swakelola (internal)", "Pihak Ketiga (vendor)", "Hybrid"'
                    ],
                    'peran_tanggung_jawab' => [
                        'label' => 'Peran & Tanggung Jawab',
                        'help' => 'Pembagian peran tim. Contoh: "PM: Budi, Developer: Tim IT, User: Bagian Layanan"'
                    ],
                    'jadwal_pelaksanaan' => [
                        'label' => 'Jadwal Pelaksanaan',
                        'help' => 'Timeline pengembangan. Contoh: "Januari-Juni 2025", "16 minggu (Feb-Mei 2025)"'
                    ],
                    'rencana_aksi' => [
                        'label' => 'Rencana Aksi',
                        'help' => 'Langkah-langkah detail. Contoh: "Sprint 1: Desain (2 minggu), Sprint 2: Development..."'
                    ],
                    'keamanan_informasi' => [
                        'label' => 'Keamanan Informasi',
                        'help' => 'Strategi pengamanan data. Contoh: "Enkripsi, SSL/TLS, Authentication, Backup harian"'
                    ],
                    'sumber_daya' => [
                        'label' => 'Sumber Daya',
                        'help' => 'Kebutuhan SDM, anggaran, infrastruktur. Contoh: "Budget: Rp 500jt, 3 Developer, 1 Server"'
                    ],
                    'indikator_keberhasilan' => [
                        'label' => 'Indikator Keberhasilan',
                        'help' => 'KPI kesuksesan. Contoh: "Response time <3 detik, 90% user satisfaction, 100 transaksi/hari"'
                    ],
                    'alih_pengetahuan' => [
                        'label' => 'Alih Pengetahuan',
                        'help' => 'Transfer knowledge ke tim. Contoh: "Training 3 hari, Dokumentasi teknis, Workshop user"'
                    ],
                    'pemantauan_pelaporan' => [
                        'label' => 'Pemantauan & Pelaporan',
                        'help' => 'Mekanisme monitoring. Contoh: "Weekly report, Monthly review, Dashboard monitoring"'
                    ],
                ];
            @endphp

            @foreach ($fields as $field => $data)
                <div class="mt-4">
                    <label class="block font-semibold mb-1">{{ $data['label'] }} <span class="text-red-500">*</span></label>
                    <textarea name="{{ $field }}" id="editor_{{ $field }}" class="w-full border rounded p-2 editor-field" rows="3"></textarea>
                    <div id="wordcount_{{ $field }}" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 {{ $data['help'] }}</p>
                </div>
            @endforeach

            {{-- Manajemen Risiko --}}
            <hr class="my-6">
            <h2 class="text-lg font-bold mb-4">Manajemen Risiko</h2>

            {{-- Petunjuk Pengisian Manajemen Risiko --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">📋 Petunjuk Pengisian Manajemen Risiko</h3>
                        <div class="text-sm text-blue-700 space-y-2">
                            <p>Sebelum mengisi form manajemen risiko, silakan pelajari pedoman berikut:</p>
                            <div class="bg-white p-3 rounded border border-blue-200 space-y-3">
                                <div>
                                    <p class="font-semibold mb-2">📖 Pedoman Manajemen Risiko Provinsi Kalimantan Utara</p>
                                    <a href="https://drive.google.com/file/d/1xQXin3YnIOiQybDU8O90MobXx6pb-uEf/view?usp=sharing"
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Buka Pedoman Manajemen Risiko
                                    </a>
                                </div>
                                <div class="border-t border-blue-100 pt-3">
                                    <p class="font-semibold mb-2">🌐 Microsite Manajemen Risiko SPBE Kaltara</p>
                                    <a href="https://s.id/manrisk-spbe-kaltara"
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                        Kunjungi Microsite Manajemen Risiko
                                    </a>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="font-semibold mb-1">💡 Catatan Penting:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Silakan merujuk pada pedoman di atas untuk memahami kategori dan jenis risiko</li>
                                    <li>Anda dapat mengidentifikasi risiko yang sudah tercantum dalam pedoman</li>
                                    <li><strong>Anda juga dapat menambahkan risiko baru</strong> yang belum ada dalam pedoman jika relevan dengan aplikasi Anda</li>
                                    <li>Pastikan setiap risiko memiliki penilaian level kemungkinan dan dampak yang akurat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="risiko-wrapper" class="space-y-4"></div>

            {{-- Warning untuk risiko wajib --}}
            <div id="risiko-warning" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 mt-3 mb-3" role="alert">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Wajib menambahkan minimal 1 risiko!</span>
                </div>
                <p class="text-sm mt-1">Klik tombol "+ Tambah Risiko" di bawah untuk menambahkan data risiko.</p>
            </div>

            <button type="button" onclick="addRisiko()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded">
                + Tambah Risiko
            </button>

            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">
                    Simpan Permohonan
                </button>
            </div>
        </form>
    </div>

    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

    <script>
        // Store editor instances
        const editorInstances = {};
        let editorsInitialized = 0;
        let totalEditors = 0;

        // Initialize CKEditor for all editor fields with word limit
        document.addEventListener('DOMContentLoaded', function() {
            const MAX_WORDS = 2000;
            const editorFields = document.querySelectorAll('.editor-field');
            totalEditors = editorFields.length;

            editorFields.forEach(function(textarea) {
                const editorId = textarea.id;
                const wordCountId = 'wordcount_' + editorId.replace('editor_', '');

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
                                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                            ]
                        }
                    })
                    .then(editor => {
                        // Store editor instance
                        editorInstances[editorId] = editor;
                        editorsInitialized++;

                        // Function to count words
                        function countWords(text) {
                            const strippedText = text.replace(/<[^>]*>/g, '').trim();
                            if (!strippedText) return 0;
                            return strippedText.split(/\s+/).length;
                        }

                        // Update word count display
                        function updateWordCount() {
                            const data = editor.getData();
                            const wordCount = countWords(data);
                            const wordCountElement = document.getElementById(wordCountId);

                            if (wordCountElement) {
                                wordCountElement.textContent = `Jumlah kata: ${wordCount} / ${MAX_WORDS}`;

                                if (wordCount > MAX_WORDS) {
                                    wordCountElement.style.color = '#dc2626'; // red
                                    wordCountElement.textContent += ' - Maksimal 2000 kata!';
                                } else if (wordCount > MAX_WORDS * 0.9) {
                                    wordCountElement.style.color = '#f59e0b'; // yellow/warning
                                } else {
                                    wordCountElement.style.color = '#6b7280'; // gray
                                }
                            }
                        }

                        // Listen to changes
                        editor.model.document.on('change:data', () => {
                            updateWordCount();

                            // Prevent typing if word limit exceeded
                            const data = editor.getData();
                            const wordCount = countWords(data);

                            if (wordCount > MAX_WORDS) {
                                // Optionally prevent further input (commented out to allow user to delete)
                                // editor.enableReadOnlyMode('word-limit');
                            } else {
                                editor.disableReadOnlyMode('word-limit');
                            }
                        });

                        // Initial count
                        updateWordCount();
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                        editorsInitialized++; // Still count it to avoid blocking
                    });
            });

            // Sync CKEditor data to textarea before form submission
            const form = document.getElementById('form-rekomendasi');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Check if at least one risk is added
                    const risikoItems = document.querySelectorAll('.risiko-item').length;
                    if (risikoItems === 0) {
                        e.preventDefault();
                        alert('Anda harus menambahkan minimal 1 risiko!\n\nKlik tombol "+ Tambah Risiko" untuk menambahkan data risiko.');

                        // Scroll to risk section
                        const risikoWrapper = document.getElementById('risiko-wrapper');
                        if (risikoWrapper) {
                            risikoWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return false;
                    }

                    // Update all textareas with CKEditor data
                    for (const [editorId, editor] of Object.entries(editorInstances)) {
                        const textarea = document.getElementById(editorId);
                        if (textarea && editor) {
                            textarea.value = editor.getData();
                        }
                    }

                    // Validate required CKEditor fields
                    const requiredEditorFields = [
                        { id: 'editor_dasar_hukum', name: 'Dasar Hukum' },
                        { id: 'editor_permasalahan_kebutuhan', name: 'Permasalahan Kebutuhan' },
                        { id: 'editor_stakeholder_eksternal', name: 'Stakeholder Eksternal' },
                        { id: 'editor_maksud_tujuan', name: 'Maksud & Tujuan' },
                        { id: 'editor_ruang_lingkup', name: 'Ruang Lingkup' },
                        { id: 'editor_analisis_biaya_manfaat', name: 'Analisis Biaya Manfaat' },
                        { id: 'editor_analisis_risiko', name: 'Analisis Risiko' },
                        { id: 'editor_perencanaan_ruang_lingkup', name: 'Perencanaan Ruang Lingkup' },
                        { id: 'editor_perencanaan_proses_bisnis', name: 'Perencanaan Proses Bisnis' },
                        { id: 'editor_kerangka_kerja', name: 'Kerangka Kerja' },
                        { id: 'editor_pelaksana_pembangunan', name: 'Pelaksana Pembangunan' },
                        { id: 'editor_peran_tanggung_jawab', name: 'Peran & Tanggung Jawab' },
                        { id: 'editor_jadwal_pelaksanaan', name: 'Jadwal Pelaksanaan' },
                        { id: 'editor_rencana_aksi', name: 'Rencana Aksi' },
                        { id: 'editor_keamanan_informasi', name: 'Keamanan Informasi' },
                        { id: 'editor_sumber_daya', name: 'Sumber Daya' },
                        { id: 'editor_indikator_keberhasilan', name: 'Indikator Keberhasilan' },
                        { id: 'editor_alih_pengetahuan', name: 'Alih Pengetahuan' },
                        { id: 'editor_pemantauan_pelaporan', name: 'Pemantauan & Pelaporan' }
                    ];

                    for (const field of requiredEditorFields) {
                        const editor = editorInstances[field.id];
                        if (editor) {
                            const content = editor.getData().replace(/<[^>]*>/g, '').trim();
                            if (!content) {
                                e.preventDefault();
                                alert(`Field "${field.name}" wajib diisi!`);
                                editor.focus();
                                return false;
                            }
                        }
                    }

                    // Allow form to submit
                    return true;
                });
            }

            // Initial check for warning visibility
            updateRisikoWarning();
        });

        let risikoIndex = 0;

        // Function to update warning visibility based on risk count
        function updateRisikoWarning() {
            const risikoItems = document.querySelectorAll('.risiko-item').length;
            const warningElement = document.getElementById('risiko-warning');
            if (warningElement) {
                if (risikoItems > 0) {
                    warningElement.style.display = 'none';
                } else {
                    warningElement.style.display = 'block';
                }
            }
        }

        function addRisiko() {
            const wrapper = document.getElementById('risiko-wrapper');
            const html = `
            <div class="risiko-item border p-4 rounded bg-gray-50">
                <label class="block font-semibold">Jenis Risiko SPBE</label>
                <select name="risiko[${risikoIndex}][jenis_risiko]" class="w-full border rounded p-2" required>
                    <option value="">-- Pilih Jenis Risiko SPBE --</option>
                    <option value="Risiko SPBE Positif">Risiko SPBE Positif</option>
                    <option value="Risiko SPBE Negatif">Risiko SPBE Negatif</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Risiko SPBE Positif: peluang yang menguntungkan | Risiko SPBE Negatif: ancaman/kerugian</p>

                <label class="block font-semibold mt-3">Kategori Risiko SPBE</label>
                <select name="risiko[${risikoIndex}][kategori_risiko_spbe]" class="w-full border rounded p-2">
                    <option value="">-- Pilih Kategori Risiko SPBE --</option>
                    <option value="Rencana Induk SPBE Nasional">1. Rencana Induk SPBE Nasional</option>
                    <option value="Arsitektur SPBE">2. Arsitektur SPBE</option>
                    <option value="Peta Rencana SPBE">3. Peta Rencana SPBE</option>
                    <option value="Proses Bisnis">4. Proses Bisnis</option>
                    <option value="Rencana dan Anggaran">5. Rencana dan Anggaran</option>
                    <option value="Inovasi">6. Inovasi</option>
                    <option value="Kepatuhan terhadap Peraturan">7. Kepatuhan terhadap Peraturan</option>
                    <option value="Pengadaan Barang dan Jasa">8. Pengadaan Barang dan Jasa</option>
                    <option value="Proyek Pembangunan/Pengembangan Sistem">9. Proyek Pembangunan/Pengembangan Sistem</option>
                    <option value="Data dan Informasi">10. Data dan Informasi</option>
                    <option value="Infrastruktur SPBE">11. Infrastruktur SPBE</option>
                    <option value="Aplikasi SPBE">12. Aplikasi SPBE</option>
                    <option value="Keamanan SPBE">13. Keamanan SPBE</option>
                    <option value="Layanan SPBE">14. Layanan SPBE</option>
                    <option value="Sumber Daya Manusia SPBE">15. Sumber Daya Manusia SPBE</option>
                    <option value="Bencana Alam">16. Bencana Alam</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih kategori sesuai Tabel 3.6 Pedoman Manajemen Risiko SPBE</p>

                <label class="block font-semibold mt-3">Area Dampak Risiko SPBE</label>
                <select name="risiko[${risikoIndex}][area_dampak_risiko_spbe]" class="w-full border rounded p-2">
                    <option value="">-- Pilih Area Dampak --</option>
                    <option value="Finansial">Finansial - Aspek keuangan</option>
                    <option value="Reputasi">Reputasi - Tingkat kepercayaan pemangku kepentingan</option>
                    <option value="Kinerja">Kinerja - Pencapaian sasaran SPBE</option>
                    <option value="Layanan Organisasi">Layanan Organisasi - Pemenuhan kebutuhan/jasa kepada pemangku kepentingan</option>
                    <option value="Operasional dan Aset TIK">Operasional dan Aset TIK - Kegiatan operasional TIK dan pengelolaan aset TIK</option>
                    <option value="Hukum dan Regulasi">Hukum dan Regulasi - Peraturan perundang-undangan dan kebijakan</option>
                    <option value="Sumber Daya Manusia">Sumber Daya Manusia - Fisik dan mental pegawai</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih area yang terkena dampak dari risiko SPBE</p>

                <label class="block font-semibold mt-3">Uraian Kejadian</label>
                <textarea name="risiko[${risikoIndex}][uraian_risiko]" class="w-full border rounded p-2" placeholder="Contoh: Sistem mengalami response time lambat >5 detik saat concurrent user >100"></textarea>
                <p class="text-xs text-gray-500 mt-1">Deskripsi kejadian/peristiwa yang menimbulkan risiko SPBE (dari riwayat atau prediksi masa depan)</p>

                <label class="block font-semibold mt-2">Penyebab</label>
                <input type="text" name="risiko[${risikoIndex}][penyebab]" class="w-full border rounded p-2" placeholder="Contoh: Database query tidak optimal, server capacity terbatas">
                <p class="text-xs text-gray-500 mt-1">Root cause yang memicu risiko</p>

                <label class="block font-semibold mt-2">Dampak</label>
                <input type="text" name="risiko[${risikoIndex}][dampak]" class="w-full border rounded p-2" placeholder="Contoh: Produktivitas user turun, customer complaint meningkat"> 
                <p class="text-xs text-gray-500 mt-1">Konsekuensi jika risiko terjadi</p>

                <label class="block font-semibold mt-2">Level Kemungkinan</label>
                <select name="risiko[${risikoIndex}][level_kemungkinan]" id="level_kemungkinan_${risikoIndex}" class="w-full border rounded p-2" onchange="hitungBesaranRisiko(${risikoIndex})">
                    <option value="">-- Pilih Kemungkinan --</option>
                    <option value="1">1 - Hampir Tidak Terjadi</option>
                    <option value="2">2 - Jarang Terjadi</option>
                    <option value="3">3 - Kadang-Kadang Terjadi</option>
                    <option value="4">4 - Sering Terjadi</option>
                    <option value="5">5 - Hampir Pasti Terjadi</option>
                </select>

                <label class="block font-semibold mt-2">Level Dampak</label>
                <select name="risiko[${risikoIndex}][level_dampak]" id="level_dampak_${risikoIndex}" class="w-full border rounded p-2" onchange="hitungBesaranRisiko(${risikoIndex})">
                    <option value="">-- Pilih Dampak --</option>
                    <option value="1">1 - Tidak Signifikan</option>
                    <option value="2">2 - Kurang Signifikan</option>
                    <option value="3">3 - Cukup Signifikan</option>
                    <option value="4">4 - Signifikan</option>
                    <option value="5">5 - Sangat Signifikan</option>
                </select>

                <label class="block font-semibold mt-2">Besaran Risiko</label>
                <input type="text" name="risiko[${risikoIndex}][besaran_risiko]" id="besaran_risiko_${risikoIndex}" class="w-full border rounded p-2 bg-gray-100" readonly>
                <p class="text-xs text-gray-500 mt-1">Otomatis dihitung: Kemungkinan × Dampak</p>

                <label class="block font-semibold mt-2">Perlu Penanganan?</label>
                <select name="risiko[${risikoIndex}][perlu_penanganan]" class="w-full border rounded p-2">
                    <option value="1" selected>Ya</option>
                    <option value="0">Tidak</option>
                </select>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Apakah risiko ini perlu ditangani? Ya=perlu mitigasi, Tidak=dapat diterima/diabaikan</p>

                <label class="block font-semibold mt-2">Opsi Penanganan</label>
                <textarea name="risiko[${risikoIndex}][opsi_penanganan]" class="w-full border rounded p-2" placeholder="Contoh: Mitigasi dengan optimasi database"></textarea>
                 <p class="text-xs text-gray-500 mt-1 italic">💡 Strategi: Hindari (avoid), Mitigasi (mitigate), Transfer (ke pihak lain), Terima (accept)</p>

                <label class="block font-semibold mt-2">Rencana Aksi</label>
                <textarea name="risiko[${risikoIndex}][rencana_aksi]" class="w-full border rounded p-2" placeholder="Contoh: Optimasi query DB, Upgrade RAM server dari 8GB ke 16GB, Implementasi Redis cache"></textarea>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Langkah konkret untuk menangani risiko</p>

                <label class="block font-semibold mt-2">Jadwal Implementasi</label>
                <input type="text" name="risiko[${risikoIndex}][jadwal_implementasi]" class="w-full border rounded p-2" placeholder="Contoh: Minggu ke-2 Sprint 1 atau 15-20 Maret 2025">
                <p class="text-xs text-gray-500 mt-1 italic">💡 Kapan tindakan mitigasi akan dilakukan</p>

                <label class="block font-semibold mt-2">Penanggung Jawab</label>
                <input type="text" name="risiko[${risikoIndex}][penanggung_jawab]" class="w-full border rounded p-2" placeholder="Contoh: Database Administrator atau Nama: Ahmad Zaki">
                 <p class="text-xs text-gray-500 mt-1 italic">💡 PIC yang bertanggung jawab menangani risiko ini</p>

                <label class="block font-semibold mt-2">Risiko Residual?</label>
                <select name="risiko[${risikoIndex}][risiko_residual]" class="w-full border rounded p-2">
                    <option value="0" selected>Tidak</option>
                    <option value="1">Ya</option>
                </select>
                <p class="text-xs text-gray-500 mt-1 italic">💡 Apakah masih ada sisa risiko setelah mitigasi dilakukan?</p>

                <button type="button" onclick="removeRisiko(this)" class="mt-3 px-3 py-1 bg-red-500 text-white rounded text-sm">
                    Hapus Risiko
                </button>
            </div>`;

            wrapper.insertAdjacentHTML('beforeend', html);
            risikoIndex++;
            updateRisikoWarning();
        }

        function removeRisiko(button) {
            button.closest('.risiko-item').remove();
            updateRisikoWarning();
        }

        function hitungBesaranRisiko(index) {
            const kemungkinanEl = document.getElementById(`level_kemungkinan_${index}`);
            const dampakEl = document.getElementById(`level_dampak_${index}`);
            const outputEl = document.getElementById(`besaran_risiko_${index}`);

            if (kemungkinanEl && dampakEl && outputEl) {
                const kemungkinan = kemungkinanEl.value;
                const dampak = dampakEl.value;

                if (kemungkinan && dampak) {
                    const nilai = parseInt(kemungkinan) * parseInt(dampak);
                    outputEl.value = nilai;

                    // Optional: Add risk level indication
                    let riskLevel = '';
                    if (nilai >= 1 && nilai <= 4) {
                        riskLevel = ' (Risiko Rendah)';
                    } else if (nilai >= 5 && nilai <= 12) {
                        riskLevel = ' (Risiko Sedang)';
                    } else if (nilai >= 13 && nilai <= 25) {
                        riskLevel = ' (Risiko Tinggi)';
                    }
                    outputEl.value = nilai + riskLevel;
                } else {
                    outputEl.value = '';
                }
            }
        }

        // Add initial risk form when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-add one risk form by default
            addRisiko();
        });
    </script>
@endsection
