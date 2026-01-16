@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                @php
                    $phaseNames = [
                        'rancang_bangun' => 'Rancang Bangun',
                        'implementasi' => 'Implementasi',
                        'uji_kelaikan' => 'Uji Kelaikan',
                        'pemeliharaan' => 'Pemeliharaan',
                        'evaluasi' => 'Evaluasi',
                    ];
                @endphp
                <h1 class="text-2xl font-bold text-gray-800">Fase {{ $phaseNames[$fase->fase] }}</h1>
                <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi }}</p>
            </div>
            <a href="{{ route('user.rekomendasi.fase.index', $proposal->id) }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                Kembali
            </a>
        </div>

        <!-- Phase Status Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Progress Update Form -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Progress</h3>
                    <form method="POST" action="{{ route('user.rekomendasi.fase.progress', [$proposal->id, $fase->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="progress_persen" class="block text-sm font-medium text-gray-700 mb-1">
                                Progress (%) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="progress_persen" name="progress_persen" min="0" max="100"
                                value="{{ old('progress_persen', $fase->progress_persen) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('progress_persen')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">
                                Keterangan
                            </label>
                            <textarea id="keterangan" name="keterangan" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('keterangan', $fase->keterangan) }}</textarea>
                            @error('keterangan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            Update Progress
                        </button>
                    </form>

                    @if($fase->status !== 'selesai' && $fase->progress_persen == 100)
                        <form method="POST" action="{{ route('user.rekomendasi.fase.complete', [$proposal->id, $fase->id]) }}" class="mt-3">
                            @csrf
                            <button type="submit" onclick="return confirm('Tandai fase ini sebagai selesai?')"
                                class="w-full bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-check-circle mr-2"></i>Tandai Selesai
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Status Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Status</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-600">Status:</span>
                            @php
                                $statusBadge = [
                                    'belum_mulai' => 'bg-gray-100 text-gray-800',
                                    'sedang_berjalan' => 'bg-blue-100 text-blue-800',
                                    'selesai' => 'bg-green-100 text-green-800',
                                ];
                            @endphp
                            <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge[$fase->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $fase->status)) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Tanggal Mulai:</span>
                            <span class="ml-2 text-sm font-medium text-gray-800">
                                {{ $fase->tanggal_mulai ? $fase->tanggal_mulai->format('d M Y') : 'Belum dimulai' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Tanggal Selesai:</span>
                            <span class="ml-2 text-sm font-medium text-gray-800">
                                {{ $fase->tanggal_selesai ? $fase->tanggal_selesai->format('d M Y') : 'Belum selesai' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Progress:</span>
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-blue-600 h-4 rounded-full transition-all duration-300 flex items-center justify-center" style="width: {{ $fase->progress_persen }}%">
                                        <span class="text-xs text-white font-semibold">{{ $fase->progress_persen }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('dokumen')" id="tab-dokumen"
                        class="tab-button px-6 py-3 border-b-2 border-blue-600 text-blue-600 font-medium">
                        <i class="fas fa-file-alt mr-2"></i>Dokumen
                    </button>
                    <button onclick="showTab('milestone')" id="tab-milestone"
                        class="tab-button px-6 py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                        <i class="fas fa-tasks mr-2"></i>Milestone
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Dokumen Tab -->
                <div id="content-dokumen" class="tab-content">
                    <div class="mb-6">
                        <button onclick="openUploadModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-upload mr-2"></i>Upload Dokumen
                        </button>
                    </div>

                    @if($fase->dokumenPengembangan->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Dokumen</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama File</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ukuran</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Upload</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($fase->dokumenPengembangan as $dokumen)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $dokumen->jenis_dokumen }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    {{ ucfirst($dokumen->kategori) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $dokumen->nama_file }}
                                                @if($dokumen->keterangan)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $dokumen->keterangan }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($dokumen->file_size / 1024, 2) }} KB
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $dokumen->created_at->format('d M Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form method="POST" action="{{ route('user.rekomendasi.fase.dokumen.delete', [$proposal->id, $fase->id, $dokumen->id]) }}"
                                                    class="inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-file-alt text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500">Belum ada dokumen. Upload dokumen untuk fase ini.</p>
                        </div>
                    @endif
                </div>

                <!-- Milestone Tab -->
                <div id="content-milestone" class="tab-content hidden">
                    <div class="mb-6">
                        <button onclick="openMilestoneModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-plus mr-2"></i>Tambah Milestone
                        </button>
                    </div>

                    @if($fase->milestones->count() > 0)
                        <div class="space-y-4">
                            @foreach($fase->milestones as $milestone)
                                @php
                                    $milestoneStatusColors = [
                                        'not_started' => 'gray',
                                        'in_progress' => 'blue',
                                        'completed' => 'green',
                                    ];
                                    $color = $milestoneStatusColors[$milestone->status];
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h4 class="text-lg font-semibold text-gray-800">{{ $milestone->nama_milestone }}</h4>
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800">
                                                    {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <p><i class="fas fa-calendar mr-2"></i>Target: {{ $milestone->target_tanggal->format('d M Y') }}</p>
                                                @if($milestone->keterangan)
                                                    <p><i class="fas fa-info-circle mr-2"></i>{{ $milestone->keterangan }}</p>
                                                @endif
                                                @if($milestone->file_bukti)
                                                    <p>
                                                        <i class="fas fa-paperclip mr-2"></i>
                                                        <a href="{{ asset('storage/' . $milestone->file_bukti) }}" target="_blank"
                                                            class="text-blue-600 hover:underline">
                                                            Lihat Bukti
                                                        </a>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="openUpdateMilestoneModal({{ $milestone->id }}, '{{ $milestone->status }}', '{{ $milestone->keterangan }}')"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('user.rekomendasi.fase.milestone.delete', [$proposal->id, $fase->id, $milestone->id]) }}"
                                                class="inline" onsubmit="return confirm('Hapus milestone ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-tasks text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500">Belum ada milestone. Tambahkan milestone untuk tracking progress.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Dokumen Modal -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Upload Dokumen</h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('user.rekomendasi.fase.dokumen.upload', [$proposal->id, $fase->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="jenis_dokumen" required
                        placeholder="Contoh: BRD, SRS, ERD, dll."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="kategori" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="dokumentasi">Dokumentasi</option>
                        <option value="timeline">Timeline</option>
                        <option value="tim">Tim</option>
                        <option value="pengembangan">Pengembangan</option>
                        <option value="instalasi">Instalasi & Konfigurasi</option>
                        <option value="antarmuka">Antarmuka</option>
                        <option value="sosialisasi">Sosialisasi</option>
                        <option value="serah_terima">Serah Terima</option>
                        <option value="testing">Testing</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        File <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG. Maksimal 10MB</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Keterangan
                    </label>
                    <textarea name="keterangan" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeUploadModal()" class="flex-1 bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Milestone Modal -->
<div id="milestoneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Milestone</h3>
            <button onclick="closeMilestoneModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('user.rekomendasi.fase.milestone.create', [$proposal->id, $fase->id]) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Milestone <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_milestone" required
                        placeholder="Contoh: Selesai Database Design"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Target Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="target_tanggal" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Keterangan
                    </label>
                    <textarea name="keterangan" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeMilestoneModal()" class="flex-1 bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Milestone Modal -->
<div id="updateMilestoneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Update Milestone</h3>
            <button onclick="closeUpdateMilestoneModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="updateMilestoneForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="milestone_status" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        File Bukti
                    </label>
                    <input type="file" name="file_bukti" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Upload bukti penyelesaian (opsional). Maksimal 5MB</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Keterangan
                    </label>
                    <textarea name="keterangan" id="milestone_keterangan" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeUpdateMilestoneModal()" class="flex-1 bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Tab switching
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });

    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Add active class to selected button
    const activeBtn = document.getElementById('tab-' + tabName);
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
    activeBtn.classList.add('border-blue-600', 'text-blue-600');
}

// Upload Modal
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

// Milestone Modal
function openMilestoneModal() {
    document.getElementById('milestoneModal').classList.remove('hidden');
}

function closeMilestoneModal() {
    document.getElementById('milestoneModal').classList.add('hidden');
}

// Update Milestone Modal
function openUpdateMilestoneModal(milestoneId, status, keterangan) {
    const form = document.getElementById('updateMilestoneForm');
    form.action = "{{ route('user.rekomendasi.fase.milestone.update', [$proposal->id, $fase->id, ':id']) }}".replace(':id', milestoneId);

    document.getElementById('milestone_status').value = status;
    document.getElementById('milestone_keterangan').value = keterangan || '';

    document.getElementById('updateMilestoneModal').classList.remove('hidden');
}

function closeUpdateMilestoneModal() {
    document.getElementById('updateMilestoneModal').classList.add('hidden');
}

// Close modals on outside click
window.onclick = function(event) {
    if (event.target.id === 'uploadModal') {
        closeUploadModal();
    }
    if (event.target.id === 'milestoneModal') {
        closeMilestoneModal();
    }
    if (event.target.id === 'updateMilestoneModal') {
        closeUpdateMilestoneModal();
    }
}
</script>
@endpush
@endsection
