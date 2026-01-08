@extends('layouts.authenticated')
@section('title', '- Detail Permohonan Video Conference')
@section('header-title', 'Detail Permohonan Video Conference')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Video Conference</h1>
        <a href="{{ route('admin.vidcon.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <!-- Ticket & Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Tiket:</label>
                <p class="text-gray-800 font-mono">{{ $item->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                <p>{!! $item->status_badge !!}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $item->submitted_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        @if($item->status === 'proses')
            <!-- Progress Indicator -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-blue-800">
                        Progress: {{ $item->getProgressPercentage() }}% Complete
                    </p>
                    @if($item->isStale())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Stale ({{ $item->daysSinceLastUpdate() }} days)
                        </span>
                    @endif
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $item->getProgressPercentage() }}%"></div>
                </div>
                <div class="mt-2 text-xs text-gray-600">
                    <p>Updated {{ $item->info_update_count }} times</p>
                    @if($item->last_info_updated_at)
                        <p>Last update: {{ $item->last_info_updated_at->diffForHumans() }}
                           by {{ $item->lastUpdatedBy->name ?? 'Unknown' }}</p>
                    @endif
                </div>
            </div>

            <!-- Completion Checklist -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Checklist Kelengkapan</h3>
                <ul class="space-y-2">
                    <li class="flex items-center">
                        @if($item->link_meeting)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-green-700">Link Meeting sudah diisi</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-500">Link Meeting belum diisi</span>
                        @endif
                    </li>
                    <li class="flex items-center">
                        @if($item->meeting_id)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-green-700">Meeting ID sudah diisi</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-500">Meeting ID belum diisi</span>
                        @endif
                    </li>
                    <li class="flex items-center">
                        @if($item->meeting_password)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-green-700">Passcode Meeting sudah diisi</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-500">Passcode Meeting belum diisi</span>
                        @endif
                    </li>
                </ul>
            </div>
        @endif

        <!-- Pemohon Info -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $item->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $item->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $item->unitKerja->nama ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email:</label>
                    <p class="text-gray-800">{{ $item->email_pemohon }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP:</label>
                    <p class="text-gray-800">{{ $item->no_hp }}</p>
                </div>
            </div>
        </div>

        <!-- Kegiatan Info -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Kegiatan</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Kegiatan:</label>
                    <p class="text-gray-800">{{ $item->judul_kegiatan }}</p>
                </div>
                @if($item->deskripsi_kegiatan)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi:</label>
                        <p class="text-gray-800">{{ $item->deskripsi_kegiatan }}</p>
                    </div>
                @endif
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai:</label>
                        <p class="text-gray-800">{{ $item->tanggal_mulai->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai:</label>
                        <p class="text-gray-800">{{ $item->tanggal_selesai->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Mulai:</label>
                        <p class="text-gray-800">{{ $item->jam_mulai }} WITA</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Selesai:</label>
                        <p class="text-gray-800">{{ $item->jam_selesai }} WITA</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Platform:</label>
                        <p class="text-gray-800">{{ $item->platform_display }}</p>
                    </div>
                    @if($item->jumlah_peserta)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Peserta:</label>
                            <p class="text-gray-800">{{ $item->jumlah_peserta }} orang</p>
                        </div>
                    @endif
                </div>
                @if($item->keperluan_khusus)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Keperluan Khusus:</label>
                        <p class="text-gray-800">{{ $item->keperluan_khusus }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Meeting Info (if approved) -->
        @if($item->status === 'selesai' && $item->link_meeting)
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-green-800 mb-4">Informasi Meeting</h2>
                <div class="space-y-3">
                    @if($item->link_meeting)
                        <div>
                            <label class="block text-sm font-semibold text-green-700 mb-1">Link Meeting:</label>
                            <a href="{{ $item->link_meeting }}" target="_blank"
                               class="text-blue-600 hover:text-blue-800 underline break-all">
                                {{ $item->link_meeting }}
                            </a>
                        </div>
                    @endif
                    @if($item->meeting_id)
                        <div>
                            <label class="block text-sm font-semibold text-green-700 mb-1">Meeting ID:</label>
                            <p class="text-gray-800">{{ $item->meeting_id }}</p>
                        </div>
                    @endif
                    @if($item->meeting_password)
                        <div>
                            <label class="block text-sm font-semibold text-green-700 mb-1">Password:</label>
                            <p class="text-gray-800">{{ $item->meeting_password }}</p>
                        </div>
                    @endif
                    @if($item->informasi_tambahan)
                        <div>
                            <label class="block text-sm font-semibold text-green-700 mb-1">Informasi Tambahan:</label>
                            <p class="text-gray-800">{{ $item->informasi_tambahan }}</p>
                        </div>
                    @endif
                    @if($item->operators->count() > 0)
                        <div>
                            <label class="block text-sm font-semibold text-green-700 mb-1">Operator Ditugaskan:</label>
                            <ul class="list-disc list-inside text-gray-800">
                                @foreach($item->operators as $operator)
                                    <li>{{ $operator->name }} ({{ $operator->email }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Admin Notes -->
        @if($item->admin_notes)
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Admin:</label>
                <p class="text-gray-800 bg-gray-50 p-3 rounded">{{ $item->admin_notes }}</p>
            </div>
        @endif

        <!-- Processing Info -->
        @if($item->processed_by)
            <div class="text-sm text-gray-600 mb-4">
                Diproses oleh: {{ $item->processedBy->name ?? 'Unknown' }}
                @if($item->processing_at) pada {{ $item->processing_at->format('d/m/Y H:i') }} @endif
                @if($item->completed_at) • Selesai: {{ $item->completed_at->format('d/m/Y H:i') }} @endif
                @if($item->rejected_at) • Ditolak: {{ $item->rejected_at->format('d/m/Y H:i') }} @endif
            </div>
        @endif
    </div>

    <!-- Action Forms -->
    @if($item->status === 'menunggu')
        <!-- Set to Process -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-blue-800 mb-3">Proses Permohonan</h3>
            <form action="{{ route('admin.vidcon.process', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan (Opsional):</label>
                    <textarea name="admin_notes" rows="2"
                              class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Tambahkan catatan jika diperlukan"></textarea>
                </div>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                    Set Status: Diproses
                </button>
            </form>
        </div>

        <!-- Approve Form -->
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-green-800 mb-3">Setujui Permohonan</h3>
            <form action="{{ route('admin.vidcon.approve', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Link Meeting <span class="text-red-500">*</span></label>
                    <input type="text" name="link_meeting" required
                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="https://zoom.us/j/123456789">
                    @error('link_meeting')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-semibold text-green-700 mb-1">Meeting ID:</label>
                        <input type="text" name="meeting_id"
                               class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="123 456 789">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-green-700 mb-1">Password:</label>
                        <input type="text" name="meeting_password"
                               class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="abc123">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-green-700">Operator Ditugaskan:</label>
                        <button type="button" id="aiRecommendBtn"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Rekomendasi AI
                        </button>
                    </div>
                    <div class="border border-green-300 rounded-lg p-3 bg-white max-h-64 overflow-y-auto">
                        @if($operators->count() > 0)
                            @foreach($operators as $op)
                                <label class="flex items-start py-2 hover:bg-green-50 rounded px-2 cursor-pointer operator-checkbox">
                                    <input type="checkbox"
                                           name="operators[]"
                                           value="{{ $op->id }}"
                                           data-operator-id="{{ $op->id }}"
                                           class="w-4 h-4 mt-1 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <span class="ml-3 flex-1">
                                        <span class="block">
                                            <span class="font-medium text-gray-900">{{ $op->name }}</span>
                                            <span class="text-gray-500 text-xs">({{ $op->email }})</span>
                                        </span>
                                        <span class="block text-xs text-gray-600 mt-0.5">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif: <strong>{{ $op->active_vidcon_workload ?? 0 }}</strong>
                                            </span>
                                            <span class="mx-1">•</span>
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Total: <strong>{{ $op->vidcon_workload ?? 0 }}</strong>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada operator vidcon terdaftar</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Pilih satu atau lebih operator (opsional). Klik "Rekomendasi AI" untuk pemilihan otomatis berdasarkan beban kerja.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Informasi Tambahan:</label>
                    <textarea name="informasi_tambahan" rows="3"
                              class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                              placeholder="Informasi penting lainnya untuk peserta"></textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Catatan Admin:</label>
                    <textarea name="admin_notes" rows="2"
                              class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                              placeholder="Catatan internal"></textarea>
                </div>
                <button type="submit"
                        onclick="return confirm('Setujui permohonan ini dan kirim link meeting ke pemohon?')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                    Setujui & Kirim Link
                </button>
            </form>
        </div>

        <!-- Reject Form -->
        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
            <h3 class="font-semibold text-red-800 mb-3">Tolak Permohonan</h3>
            <form action="{{ route('admin.vidcon.reject', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-red-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="admin_notes" rows="3" required
                              class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              placeholder="Jelaskan alasan penolakan..."></textarea>
                    @error('admin_notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        onclick="return confirm('Tolak permohonan ini?')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                    Tolak Permohonan
                </button>
            </form>
        </div>
    @elseif($item->status === 'proses')
        <!-- Update Info Form -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-blue-800 mb-3">Update Informasi Meeting</h3>
            <form action="{{ route('admin.vidcon.update-info', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Link Meeting:</label>
                    <input type="text" name="link_meeting"
                           value="{{ $item->link_meeting }}"
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-1">Meeting ID:</label>
                        <input type="text" name="meeting_id"
                               value="{{ $item->meeting_id }}"
                               class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-1">Password:</label>
                        <input type="text" name="meeting_password"
                               value="{{ $item->meeting_password }}"
                               class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-blue-700">Operator Ditugaskan:</label>
                        <button type="button" id="aiRecommendBtnUpdate"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Rekomendasi AI
                        </button>
                    </div>
                    <div class="border border-blue-300 rounded-lg p-3 bg-white max-h-64 overflow-y-auto">
                        @if($operators->count() > 0)
                            @foreach($operators as $op)
                                <label class="flex items-start py-2 hover:bg-blue-50 rounded px-2 cursor-pointer operator-checkbox-update">
                                    <input type="checkbox"
                                           name="operators[]"
                                           value="{{ $op->id }}"
                                           data-operator-id="{{ $op->id }}"
                                           {{ $item->operators->contains($op->id) ? 'checked' : '' }}
                                           class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 flex-1">
                                        <span class="block">
                                            <span class="font-medium text-gray-900">{{ $op->name }}</span>
                                            <span class="text-gray-500 text-xs">({{ $op->email }})</span>
                                        </span>
                                        <span class="block text-xs text-gray-600 mt-0.5">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif: <strong>{{ $op->active_vidcon_workload ?? 0 }}</strong>
                                            </span>
                                            <span class="mx-1">•</span>
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Total: <strong>{{ $op->vidcon_workload ?? 0 }}</strong>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada operator vidcon terdaftar</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Pilih satu atau lebih operator (opsional). Klik "Rekomendasi AI" untuk pemilihan otomatis berdasarkan beban kerja.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Informasi Tambahan:</label>
                    <textarea name="informasi_tambahan" rows="3"
                              class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $item->informasi_tambahan }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Admin:</label>
                    <textarea name="admin_notes" rows="2"
                              class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $item->admin_notes }}</textarea>
                </div>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                    Update Informasi
                </button>
            </form>
        </div>

        <!-- Approve Form (untuk status proses) -->
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-green-800 mb-3">Setujui Permohonan</h3>
            <form action="{{ route('admin.vidcon.approve', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Link Meeting <span class="text-red-500">*</span></label>
                    <input type="text" name="link_meeting" required
                           value="{{ $item->link_meeting }}"
                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="https://zoom.us/j/123456789">
                    @error('link_meeting')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-semibold text-green-700 mb-1">Meeting ID <span class="text-red-500">*</span></label>
                        <input type="text" name="meeting_id" required
                               value="{{ $item->meeting_id }}"
                               class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="123 456 789">
                        @error('meeting_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-green-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="text" name="meeting_password" required
                               value="{{ $item->meeting_password }}"
                               class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="abc123">
                        @error('meeting_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-green-700">Operator Ditugaskan:</label>
                        <button type="button" id="aiRecommendBtnApprove"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Rekomendasi AI
                        </button>
                    </div>
                    <div class="border border-green-300 rounded-lg p-3 bg-white max-h-64 overflow-y-auto">
                        @if($operators->count() > 0)
                            @foreach($operators as $op)
                                <label class="flex items-start py-2 hover:bg-green-50 rounded px-2 cursor-pointer operator-checkbox-approve">
                                    <input type="checkbox"
                                           name="operators[]"
                                           value="{{ $op->id }}"
                                           data-operator-id="{{ $op->id }}"
                                           {{ $item->operators->contains($op->id) ? 'checked' : '' }}
                                           class="w-4 h-4 mt-1 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <span class="ml-3 flex-1">
                                        <span class="block">
                                            <span class="font-medium text-gray-900">{{ $op->name }}</span>
                                            <span class="text-gray-500 text-xs">({{ $op->email }})</span>
                                        </span>
                                        <span class="block text-xs text-gray-600 mt-0.5">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif: <strong>{{ $op->active_vidcon_workload ?? 0 }}</strong>
                                            </span>
                                            <span class="mx-1">•</span>
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Total: <strong>{{ $op->vidcon_workload ?? 0 }}</strong>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada operator vidcon terdaftar</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600 mt-1">Pilih satu atau lebih operator (opsional). Klik "Rekomendasi AI" untuk pemilihan otomatis berdasarkan beban kerja.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Informasi Tambahan:</label>
                    <textarea name="informasi_tambahan" rows="3"
                              class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ $item->informasi_tambahan }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-green-700 mb-1">Catatan Admin:</label>
                    <textarea name="admin_notes" rows="2"
                              class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ $item->admin_notes }}</textarea>
                </div>
                <button type="submit"
                        onclick="return confirm('Setujui permohonan ini dan kirim link meeting ke pemohon?')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                    Setujui & Kirim Link
                </button>
            </form>
        </div>

        <!-- Reject Form -->
        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
            <h3 class="font-semibold text-red-800 mb-3">Tolak Permohonan</h3>
            <form action="{{ route('admin.vidcon.reject', $item->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-semibold text-red-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="admin_notes" rows="3" required
                              class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              placeholder="Jelaskan alasan penolakan..."></textarea>
                    @error('admin_notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        onclick="return confirm('Tolak permohonan ini?')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                    Tolak Permohonan
                </button>
            </form>
        </div>
    @endif

    <!-- Activity Log -->
    @if($item->activities && $item->activities->count() > 0)
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Riwayat Aktivitas</h3>
            <div class="space-y-4">
                @foreach($item->activities()->orderBy('created_at', 'desc')->get() as $activity)
                    <div class="border-l-4 border-gray-300 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">
                                    @if($activity->action === 'info_updated')
                                        Informasi Meeting Diupdate
                                    @elseif($activity->action === 'status_changed')
                                        Status Berubah
                                    @elseif($activity->action === 'approved')
                                        Request Disetujui
                                    @else
                                        {{ $activity->action }}
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">
                                    oleh {{ $activity->user->name ?? 'Unknown' }} &bull; {{ $activity->created_at->diffForHumans() }}
                                </p>
                                @if($activity->notes)
                                    <p class="text-sm text-gray-500 mt-1">{{ $activity->notes }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $activity->created_at->format('d M Y H:i') }}</span>
                        </div>

                        @if($activity->old_values || $activity->new_values)
                            <details class="mt-2">
                                <summary class="text-xs text-blue-600 cursor-pointer">Lihat Detail Perubahan</summary>
                                <div class="mt-2 text-xs bg-gray-50 p-2 rounded">
                                    @if($activity->old_values)
                                        <p class="font-medium">Sebelum:</p>
                                        <pre class="text-xs whitespace-pre-wrap">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                    @if($activity->new_values)
                                        <p class="font-medium mt-2">Sesudah:</p>
                                        <pre class="text-xs whitespace-pre-wrap">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @endif
                                </div>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AI recommendation data from backend
    const recommendedOperatorIds = @json($recommendedOperatorIds ?? []);

    // Handle AI Recommend button for Approve form
    const aiRecommendBtn = document.getElementById('aiRecommendBtn');
    if (aiRecommendBtn) {
        aiRecommendBtn.addEventListener('click', function() {
            // Uncheck all checkboxes first
            document.querySelectorAll('.operator-checkbox input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check recommended operators
            recommendedOperatorIds.forEach(operatorId => {
                const checkbox = document.querySelector(`.operator-checkbox input[data-operator-id="${operatorId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    // Add brief highlight animation
                    const label = checkbox.closest('.operator-checkbox');
                    label.classList.add('bg-purple-100');
                    setTimeout(() => {
                        label.classList.remove('bg-purple-100');
                    }, 1000);
                }
            });

            // Show feedback
            if (recommendedOperatorIds.length > 0) {
                const message = `AI memilih ${recommendedOperatorIds.length} operator dengan beban kerja paling ringan`;
                showNotification(message, 'success');
            } else {
                showNotification('Tidak ada operator tersedia untuk rekomendasi', 'info');
            }
        });
    }

    // Handle AI Recommend button for Update form
    const aiRecommendBtnUpdate = document.getElementById('aiRecommendBtnUpdate');
    if (aiRecommendBtnUpdate) {
        aiRecommendBtnUpdate.addEventListener('click', function() {
            // Uncheck all checkboxes first
            document.querySelectorAll('.operator-checkbox-update input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check recommended operators
            recommendedOperatorIds.forEach(operatorId => {
                const checkbox = document.querySelector(`.operator-checkbox-update input[data-operator-id="${operatorId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    // Add brief highlight animation
                    const label = checkbox.closest('.operator-checkbox-update');
                    label.classList.add('bg-purple-100');
                    setTimeout(() => {
                        label.classList.remove('bg-purple-100');
                    }, 1000);
                }
            });

            // Show feedback
            if (recommendedOperatorIds.length > 0) {
                const message = `AI memilih ${recommendedOperatorIds.length} operator dengan beban kerja paling ringan`;
                showNotification(message, 'success');
            } else {
                showNotification('Tidak ada operator tersedia untuk rekomendasi', 'info');
            }
        });
    }

    // Handle AI Recommend button for Approve form (status proses)
    const aiRecommendBtnApprove = document.getElementById('aiRecommendBtnApprove');
    if (aiRecommendBtnApprove) {
        aiRecommendBtnApprove.addEventListener('click', function() {
            // Uncheck all checkboxes first
            document.querySelectorAll('.operator-checkbox-approve input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check recommended operators
            recommendedOperatorIds.forEach(operatorId => {
                const checkbox = document.querySelector(`.operator-checkbox-approve input[data-operator-id="${operatorId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    // Add brief highlight animation
                    const label = checkbox.closest('.operator-checkbox-approve');
                    label.classList.add('bg-purple-100');
                    setTimeout(() => {
                        label.classList.remove('bg-purple-100');
                    }, 1000);
                }
            });

            // Show feedback
            if (recommendedOperatorIds.length > 0) {
                const message = `AI memilih ${recommendedOperatorIds.length} operator dengan beban kerja paling ringan`;
                showNotification(message, 'success');
            } else {
                showNotification('Tidak ada operator tersedia untuk rekomendasi', 'info');
            }
        });
    }

    // Simple notification function
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-100 border-green-500 text-green-800',
            info: 'bg-blue-100 border-blue-500 text-blue-800',
            warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} border-l-4 rounded-lg p-4 shadow-lg z-50 transition-all duration-300`;
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
</script>
@endsection
