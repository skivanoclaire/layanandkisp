@extends('layouts.authenticated')

@section('title', isset($pseUpdate) ? 'Edit Permohonan Update Data' : 'Buat Permohonan Update Data')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('user.pse-update.index') }}"
           class="text-green-600 hover:text-green-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Permohonan
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ isset($pseUpdate) ? 'Edit Permohonan Update Data Kategori Sistem Elektronik dan Klasifikasi Data' : 'Buat Permohonan Update Data Kategori Sistem Elektronik dan Klasifikasi Data' }}
        </h1>
        <p class="text-gray-600 mt-1">Subdomain: <span class="font-semibold">{{ $webMonitor->subdomain }}</span></p>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-medium mb-2">Terdapat kesalahan dalam pengisian form:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($pseUpdate) && $pseUpdate->revision_notes)
        <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-6 rounded">
            <p class="font-medium mb-1">Catatan Revisi dari Admin:</p>
            <p>{{ $pseUpdate->revision_notes }}</p>
        </div>
    @endif

    <form action="{{ isset($pseUpdate) ? route('user.pse-update.update', $pseUpdate->id) : route('user.pse-update.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @if(isset($pseUpdate))
            @method('PUT')
        @endif

        <input type="hidden" name="web_monitor_id" value="{{ $webMonitor->id }}">
        <input type="hidden" name="update_esc" value="1">
        <input type="hidden" name="update_dc" value="1">

        {{-- ESC Section --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                Kategori Sistem Elektronik
            </h2>

            @if($webMonitor->esc_category)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-blue-800 mb-2">Data Kategori Sistem Elektronik Saat Ini:</h3>
                    <div class="text-sm text-blue-700">
                        <p><span class="font-medium">Kategori:</span> {{ $webMonitor->esc_category }}</p>
                        <p><span class="font-medium">Skor Total:</span> {{ $webMonitor->esc_total_score }} dari 50</p>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600 italic">Kategori Sistem Elektronik belum pernah diisi sebelumnya</p>
                </div>
            @endif

            <div class="space-y-6">
                @php
                    $escQuestions = [
                        1 => 'Apakah sistem elektronik Anda menyimpan data pribadi, data rahasia negara, atau data yang memerlukan perlindungan khusus?',
                        2 => 'Apakah sistem elektronik Anda digunakan untuk transaksi keuangan atau perbankan?',
                        3 => 'Apakah sistem elektronik Anda terhubung dengan sistem infrastruktur kritis (misalnya: listrik, air, telekomunikasi)?',
                        4 => 'Apakah sistem elektronik Anda menangani lebih dari 1 juta pengguna aktif?',
                        5 => 'Apakah gangguan pada sistem elektronik Anda dapat berdampak signifikan terhadap keamanan nasional?',
                        6 => 'Apakah sistem elektronik Anda menangani data kesehatan atau informasi medis?',
                        7 => 'Apakah sistem elektronik Anda digunakan untuk layanan publik yang kritikal?',
                        8 => 'Apakah sistem elektronik Anda memproses data lintas batas negara?',
                        9 => 'Apakah sistem elektronik Anda menggunakan teknologi cloud computing?',
                        10 => 'Apakah sistem elektronik Anda memiliki integrasi dengan sistem elektronik lain yang kritikal?',
                    ];
                    $oldAnswers = old('esc_answers', isset($pseUpdate) ? $pseUpdate->esc_answers : []);
                @endphp

                @foreach($escQuestions as $num => $question)
                    <div class="border-b border-gray-200 pb-4">
                        <p class="text-gray-800 font-medium mb-3">{{ $num }}. {{ $question }}</p>
                        <div class="space-y-2 ml-4">
                            @foreach(['A' => 'Ya, sangat relevan', 'B' => 'Sebagian/Kadang-kadang', 'C' => 'Tidak'] as $value => $label)
                                <label class="flex items-center">
                                    <input type="radio"
                                           name="esc_answers[1_{{ $num }}]"
                                           value="{{ $value }}"
                                           @change="calculateEscScore"
                                           {{ (isset($oldAnswers['1_' . $num]) && $oldAnswers['1_' . $num] === $value) ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                                    <span class="ml-3 text-gray-700">{{ $value }}. {{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Preview Hasil Kategori Sistem Elektronik:</h3>
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Total Skor:</span>
                        <span id="esc-preview-score" class="text-lg font-bold text-blue-600 ml-2">0</span>
                        <span class="text-sm text-gray-600">dari 50</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Kategori:</span>
                        <span id="esc-preview-category" class="text-lg font-bold text-blue-600 ml-2">-</span>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Dokumen Pendukung (Opsional)
                </label>
                <input type="file"
                       name="esc_document"
                       accept=".pdf,.doc,.docx"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX. Maksimal 10MB.</p>
                @if(isset($pseUpdate) && $pseUpdate->esc_document_path)
                    <p class="text-sm text-gray-600 mt-2">
                        Dokumen saat ini: <a href="{{ Storage::url($pseUpdate->esc_document_path) }}" target="_blank" class="text-green-600 hover:text-green-800">{{ basename($pseUpdate->esc_document_path) }}</a>
                    </p>
                @endif
            </div>
        </div>

        {{-- DC Section --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-database text-purple-500 mr-2"></i>
                Klasifikasi Data
            </h2>

            @if($webMonitor->dc_data_name)
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-purple-800 mb-2">Data Klasifikasi Data Saat Ini:</h3>
                    <div class="text-sm text-purple-700 space-y-1">
                        <p><span class="font-medium">Nama Data:</span> {{ $webMonitor->dc_data_name }}</p>
                        <p><span class="font-medium">Kerahasiaan:</span> {{ $webMonitor->dc_confidentiality }}</p>
                        <p><span class="font-medium">Integritas:</span> {{ $webMonitor->dc_integrity }}</p>
                        <p><span class="font-medium">Ketersediaan:</span> {{ $webMonitor->dc_availability }}</p>
                        <p><span class="font-medium">Skor Total:</span> {{ $webMonitor->dc_total_score }} dari 15</p>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600 italic">Klasifikasi Data belum pernah diisi sebelumnya</p>
                </div>
            @endif

            {{-- Petunjuk Dampak Potensial Button --}}
            <div class="flex justify-center mb-6">
                <button type="button" onclick="showDcInfoModal()" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg text-base font-medium shadow-md transition-all hover:shadow-lg">
                    <i class="fas fa-info-circle mr-2"></i> Petunjuk Pengisian Dampak Potensial
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Data <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="dc_data_name"
                           value="{{ old('dc_data_name', $pseUpdate->dc_data_name ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Contoh: Data Pegawai, Data Keuangan, dll">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Atribut Data</label>
                    <textarea name="dc_data_attributes"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              placeholder="Contoh: NIK, Nama, Alamat, No. HP, Email">{{ old('dc_data_attributes', $pseUpdate->dc_data_attributes ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kerahasiaan (Confidentiality) <span class="text-red-500">*</span>
                        </label>
                        <select name="dc_confidentiality"
                                @change="calculateDcScore"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">Pilih...</option>
                            @foreach(['Rendah', 'Sedang', 'Tinggi'] as $level)
                                <option value="{{ $level }}" {{ old('dc_confidentiality', $pseUpdate->dc_confidentiality ?? '') === $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Tingkat kerahasiaan data</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Integritas (Integrity) <span class="text-red-500">*</span>
                        </label>
                        <select name="dc_integrity"
                                @change="calculateDcScore"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">Pilih...</option>
                            @foreach(['Rendah', 'Sedang', 'Tinggi'] as $level)
                                <option value="{{ $level }}" {{ old('dc_integrity', $pseUpdate->dc_integrity ?? '') === $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Tingkat integritas data</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Ketersediaan (Availability) <span class="text-red-500">*</span>
                        </label>
                        <select name="dc_availability"
                                @change="calculateDcScore"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">Pilih...</option>
                            @foreach(['Rendah', 'Sedang', 'Tinggi'] as $level)
                                <option value="{{ $level }}" {{ old('dc_availability', $pseUpdate->dc_availability ?? '') === $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Tingkat ketersediaan data</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Preview Hasil Klasifikasi Data:</h3>
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Total Skor:</span>
                        <span id="dc-preview-score" class="text-lg font-bold text-purple-600 ml-2">0</span>
                        <span class="text-sm text-gray-600">dari 15</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Kategori:</span>
                        <span id="dc-preview-category" class="text-lg font-bold text-purple-600 ml-2">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-4">
            <button type="submit"
                    name="action"
                    value="draft"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                <i class="fas fa-save mr-2"></i>Simpan Draft
            </button>
            <button type="submit"
                    name="action"
                    value="submit"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                <i class="fas fa-paper-plane mr-2"></i>Submit Permohonan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    calculateEscScore();
    calculateDcScore();
});

// ESC Score Calculation
const escScoreMap = { 'A': 5, 'B': 2, 'C': 1 };

function calculateEscScore() {
    const answers = document.querySelectorAll('input[name^="esc_answers"]:checked');
    let total = 0;
    answers.forEach(ans => {
        total += escScoreMap[ans.value] || 0;
    });

    document.getElementById('esc-preview-score').textContent = total;

    let category = '-';
    let categoryClass = 'text-gray-600';
    if (answers.length === 10) {
        if (total >= 36) {
            category = 'Strategis';
            categoryClass = 'text-red-600';
        } else if (total >= 16) {
            category = 'Tinggi';
            categoryClass = 'text-orange-600';
        } else {
            category = 'Rendah';
            categoryClass = 'text-green-600';
        }
    }

    const categoryEl = document.getElementById('esc-preview-category');
    categoryEl.textContent = category;
    categoryEl.className = 'text-lg font-bold ml-2 ' + categoryClass;
}

// DC Score Calculation
const dcScoreMap = { 'Rendah': 1, 'Sedang': 3, 'Tinggi': 5 };

function calculateDcScore() {
    const conf = document.querySelector('[name="dc_confidentiality"]')?.value;
    const integ = document.querySelector('[name="dc_integrity"]')?.value;
    const avail = document.querySelector('[name="dc_availability"]')?.value;

    const total = (dcScoreMap[conf] || 0) + (dcScoreMap[integ] || 0) + (dcScoreMap[avail] || 0);

    document.getElementById('dc-preview-score').textContent = total;

    let category = '-';
    let categoryClass = 'text-gray-600';
    if (conf && integ && avail) {
        if (total >= 13) {
            category = 'Tinggi';
            categoryClass = 'text-red-600';
        } else if (total >= 9) {
            category = 'Sedang';
            categoryClass = 'text-orange-600';
        } else {
            category = 'Rendah';
            categoryClass = 'text-green-600';
        }
    }

    const categoryEl = document.getElementById('dc-preview-category');
    categoryEl.textContent = category;
    categoryEl.className = 'text-lg font-bold ml-2 ' + categoryClass;
}

// Attach event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name^="esc_answers"]').forEach(radio => {
        radio.addEventListener('change', calculateEscScore);
    });

    ['dc_confidentiality', 'dc_integrity', 'dc_availability'].forEach(name => {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.addEventListener('change', calculateDcScore);
    });

    // Initial calculation
    setTimeout(() => {
        calculateEscScore();
        calculateDcScore();
    }, 100);
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
                                <li>Operasi dan layanan organisasi secara rutin</li>
                            </ul></div>
                            <div><p class="font-semibold text-blue-700">Individu</p><ul class="list-disc list-inside space-y-1">
                                <li>Finansial pribadi dan urusan pribadi</li>
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
@endsection
