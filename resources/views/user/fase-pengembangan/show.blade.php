@extends('layouts.authenticated')

@section('title', '- Fase Pengembangan')
@section('header-title', 'Fase Pengembangan - ' . $proposal->nama_aplikasi)

@section('content')
    <div class="mb-6">
        <!-- Header Section -->
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $proposal->nama_aplikasi }}</h1>
                <p class="text-sm text-gray-600 mt-1">Tiket: {{ $proposal->ticket_number }}</p>
            </div>
            <div class="flex gap-2">
                <!-- Phase Badge -->
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                    Fase Pengembangan
                </span>
                <!-- Ministry Status Badge -->
                @if ($proposal->statusKementerian && $proposal->statusKementerian->status === 'disetujui')
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                        Disetujui Kementerian
                    </span>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('fase-pengembangan.index') }}"
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

    <!-- Admin Notes & Activity Log -->
    @php
        $adminNotes = $proposal->historiAktivitas()
            ->where('aktivitas', 'admin_add_note_fase')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    @endphp

    @if ($adminNotes->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm mb-4">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Catatan & Instruksi dari Admin
                </h3>
                <p class="text-sm text-gray-600 mt-1">Admin telah memberikan catatan atau instruksi terkait fase pengembangan ini</p>
            </div>
            <div class="p-6 space-y-4">
                @foreach ($adminNotes as $note)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-yellow-900">{{ $note->deskripsi }}</p>
                                <div class="flex items-center mt-2 text-xs text-yellow-700">
                                    <span class="font-medium">{{ $note->user->name ?? 'Admin' }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $note->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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

        @foreach (config('fase_pengembangan.tahap') as $fase => $tahap)
            <div x-show="activeTab === '{{ $fase }}'" class="p-6">
                @include('user.fase-pengembangan.partials.phase-tab', [
                    'fase' => $fase,
                    'title' => $tahap['judul'],
                    'documents' => $dokumenByFase->get($fase, collect()),
                    'proposalId' => $proposal->id,
                ])
            </div>
        @endforeach
    </div>

    <script>
        // File size validation
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                if (this.files[0] && this.files[0].size > maxSize) {
                    alert('Ukuran file maksimal 50MB. File yang Anda pilih: ' +
                        (this.files[0].size / 1024 / 1024).toFixed(2) + ' MB');
                    this.value = '';
                }
            });
        });

        // Delete confirmation
        function confirmDelete(event) {
            if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endsection
