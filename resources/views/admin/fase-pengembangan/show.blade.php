@extends('layouts.authenticated')

@section('title', '- Monitoring Fase Pengembangan')
@section('header-title', 'Monitoring Fase Pengembangan - ' . $proposal->nama_aplikasi)

@section('content')
    <div class="mb-6">
        <!-- Header Section -->
        <div class="bg-white p-6 rounded-lg shadow-sm mb-4">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $proposal->nama_aplikasi }}</h1>
                    <p class="text-sm text-gray-600 mt-1">Tiket: {{ $proposal->ticket_number }}</p>
                </div>
                <div class="flex gap-2">
                    <!-- Phase Badge -->
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                        Fase Pengembangan
                    </span>
                    <!-- Ministry Status Badge -->
                    @if ($proposal->statusKementerian)
                        @php
                            $status = $proposal->statusKementerian->status;
                            $badgeColor = match($status) {
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-3 py-1 {{ $badgeColor }} text-sm font-semibold rounded-full">
                            {{ ucfirst($status) }} Kementerian
                        </span>
                    @endif
                </div>
            </div>

            <!-- Proposal Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                <div>
                    <p class="text-sm text-gray-600">Unit Kerja</p>
                    <p class="font-medium">{{ $proposal->unitKerja->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pemohon</p>
                    <p class="font-medium">{{ $proposal->user->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $proposal->user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tanggal Diajukan</p>
                    <p class="font-medium">{{ $proposal->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('admin.fase-pengembangan.index') }}"
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

    <!-- Add Note Section -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <form action="{{ route('admin.fase-pengembangan.note.add', $proposal->id) }}" method="POST">
            @csrf
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Catatan Admin</label>
                    <textarea name="catatan" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Masukkan catatan atau instruksi untuk pemohon..." required></textarea>
                    <button type="submit" class="mt-2 bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm">
                        Tambah Catatan
                    </button>
                </div>
            </div>
        </form>
    </div>

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
            @include('admin.fase-pengembangan.partials.phase-tab-admin', [
                'fase' => 'rancang_bangun',
                'title' => 'Rancang Bangun',
                'documents' => $dokumenByFase->get('rancang_bangun', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Implementasi -->
        <div x-show="activeTab === 'implementasi'" class="p-6">
            @include('admin.fase-pengembangan.partials.phase-tab-admin', [
                'fase' => 'implementasi',
                'title' => 'Implementasi',
                'documents' => $dokumenByFase->get('implementasi', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Uji Kelaikan -->
        <div x-show="activeTab === 'uji_kelaikan'" class="p-6">
            @include('admin.fase-pengembangan.partials.phase-tab-admin', [
                'fase' => 'uji_kelaikan',
                'title' => 'Uji Kelaikan',
                'documents' => $dokumenByFase->get('uji_kelaikan', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Pemeliharaan -->
        <div x-show="activeTab === 'pemeliharaan'" class="p-6">
            @include('admin.fase-pengembangan.partials.phase-tab-admin', [
                'fase' => 'pemeliharaan',
                'title' => 'Pemeliharaan',
                'documents' => $dokumenByFase->get('pemeliharaan', collect()),
                'proposalId' => $proposal->id
            ])
        </div>

        <!-- Tab Content: Evaluasi -->
        <div x-show="activeTab === 'evaluasi'" class="p-6">
            @include('admin.fase-pengembangan.partials.phase-tab-admin', [
                'fase' => 'evaluasi',
                'title' => 'Evaluasi',
                'documents' => $dokumenByFase->get('evaluasi', collect()),
                'proposalId' => $proposal->id
            ])
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Hapus Dokumen</h3>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penghapusan *</label>
                        <textarea name="alasan" rows="3" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Jelaskan alasan penghapusan dokumen ini (minimal 10 karakter)..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Alasan akan tercatat dalam log aktivitas</p>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Hapus Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(url) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endsection
