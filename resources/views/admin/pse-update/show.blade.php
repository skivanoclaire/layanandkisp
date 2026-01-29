@extends('layouts.authenticated')

@section('title', 'Detail Permohonan - ' . $pseUpdate->ticket_no)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.pse-update.index') }}"
           class="text-green-600 hover:text-green-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Permohonan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Header Info --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $pseUpdate->ticket_no }}</h1>
                <p class="text-gray-600 mt-1">Permohonan Update Data Kategori Sistem Elektronik dan Klasifikasi Data</p>
            </div>
            <div>
                @php
                    $statusConfig = [
                        'draft' => ['label' => 'Draft', 'color' => 'bg-gray-100 text-gray-700'],
                        'diajukan' => ['label' => 'Diajukan', 'color' => 'bg-yellow-100 text-yellow-700'],
                        'diproses' => ['label' => 'Diproses', 'color' => 'bg-blue-100 text-blue-700'],
                        'perlu_revisi' => ['label' => 'Perlu Revisi', 'color' => 'bg-orange-100 text-orange-700'],
                        'disetujui' => ['label' => 'Disetujui', 'color' => 'bg-green-100 text-green-700'],
                        'ditolak' => ['label' => 'Ditolak', 'color' => 'bg-red-100 text-red-700'],
                    ];
                    $config = $statusConfig[$pseUpdate->status] ?? ['label' => $pseUpdate->status, 'color' => 'bg-gray-100 text-gray-700'];
                @endphp
                <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $config['color'] }}">
                    {{ $config['label'] }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm border-t border-gray-200 pt-4">
            <div>
                <span class="text-gray-500">Pemohon:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->user->name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Unit Kerja:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->user->unitKerja->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Subdomain:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $webMonitor->subdomain }}</span>
            </div>
            <div>
                <span class="text-gray-500">Aplikasi:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $webMonitor->nama_aplikasi ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Tanggal Dibuat:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->created_at->format('d F Y, H:i') }}</span>
            </div>
            @if($pseUpdate->submitted_at)
                <div>
                    <span class="text-gray-500">Tanggal Diajukan:</span>
                    <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->submitted_at->format('d F Y, H:i') }}</span>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex gap-2">
                <span class="text-sm text-gray-600">Scope Update:</span>
                @if($pseUpdate->update_esc)
                    <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-700">ESC</span>
                @endif
                @if($pseUpdate->update_dc)
                    <span class="px-2 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-700">DC</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ESC Comparison --}}
    @if($pseUpdate->update_esc)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                Perbandingan Kategori Sistem Elektronik
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pertanyaan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai Lama</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai Baru</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $escQuestions = [
                                '1_1' => 'Menyimpan data pribadi/rahasia',
                                '1_2' => 'Transaksi keuangan/perbankan',
                                '1_3' => 'Terhubung infrastruktur kritis',
                                '1_4' => 'Lebih dari 1 juta pengguna',
                                '1_5' => 'Dampak keamanan nasional',
                                '1_6' => 'Data kesehatan/medis',
                                '1_7' => 'Layanan publik kritikal',
                                '1_8' => 'Proses data lintas negara',
                                '1_9' => 'Teknologi cloud computing',
                                '1_10' => 'Integrasi sistem kritikal',
                            ];
                            $oldAnswers = $webMonitor->esc_answers ?? [];
                            $newAnswers = $pseUpdate->esc_answers ?? [];
                            $scoreMap = ['A' => 5, 'B' => 2, 'C' => 1];
                        @endphp

                        @foreach($escQuestions as $qId => $question)
                            @php
                                $qNum = (int) substr($qId, 2); // Extract number from '1_X'
                                $oldValue = $oldAnswers[$qId] ?? '-';
                                $newValue = $newAnswers[$qId] ?? '-';
                                $oldScore = isset($oldAnswers[$qId]) ? ($scoreMap[$oldAnswers[$qId]] ?? 0) : 0;
                                $newScore = isset($newAnswers[$qId]) ? ($scoreMap[$newAnswers[$qId]] ?? 0) : 0;
                                $delta = $newScore - $oldScore;
                                $changed = $oldValue !== $newValue;
                            @endphp
                            <tr class="{{ $changed ? 'bg-yellow-50' : '' }}">
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $qNum }}. {{ $question }}</td>
                                <td class="px-4 py-3 text-sm {{ $oldValue === '-' ? 'text-gray-400 italic' : 'font-medium text-gray-800' }}">
                                    {{ $oldValue === '-' ? 'Belum diisi' : $oldValue }}
                                    @if($oldValue !== '-')
                                        <span class="text-gray-500">({{ $oldScore }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-blue-600">
                                    {{ $newValue }}
                                    @if($newValue !== '-')
                                        <span class="text-gray-500">({{ $newScore }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($delta > 0)
                                        <span class="text-green-600 font-medium">+{{ $delta }}</span>
                                    @elseif($delta < 0)
                                        <span class="text-red-600 font-medium">{{ $delta }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">Total Skor</td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $webMonitor->esc_total_score ?? 0 }} dari 50
                            </td>
                            <td class="px-4 py-3 text-sm text-blue-600">
                                {{ $pseUpdate->esc_total_score ?? 0 }} dari 50
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php $totalDelta = ($pseUpdate->esc_total_score ?? 0) - ($webMonitor->esc_total_score ?? 0); @endphp
                                @if($totalDelta > 0)
                                    <span class="text-green-600">+{{ $totalDelta }}</span>
                                @elseif($totalDelta < 0)
                                    <span class="text-red-600">{{ $totalDelta }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">Kategori</td>
                            <td class="px-4 py-3 text-sm">
                                @if($webMonitor->esc_category)
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $webMonitor->esc_category === 'Strategis' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $webMonitor->esc_category === 'Tinggi' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $webMonitor->esc_category === 'Rendah' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ $webMonitor->esc_category }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Belum diisi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $pseUpdate->esc_category === 'Strategis' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $pseUpdate->esc_category === 'Tinggi' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $pseUpdate->esc_category === 'Rendah' ? 'bg-green-100 text-green-700' : '' }}">
                                    {{ $pseUpdate->esc_category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($webMonitor->esc_category !== $pseUpdate->esc_category)
                                    <i class="fas fa-arrow-right text-yellow-600"></i>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($pseUpdate->esc_document_path)
                <div class="mt-4 p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                        Dokumen Pendukung:
                        <a href="{{ Storage::url($pseUpdate->esc_document_path) }}"
                           target="_blank"
                           class="text-green-600 hover:text-green-800 font-medium ml-1">
                            {{ basename($pseUpdate->esc_document_path) }}
                        </a>
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- DC Comparison --}}
    @if($pseUpdate->update_dc)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-database text-purple-500 mr-2"></i>
                Perbandingan Klasifikasi Data
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai Lama</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai Baru</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="{{ $webMonitor->dc_data_name !== $pseUpdate->dc_data_name ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">Nama Data</td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $webMonitor->dc_data_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-purple-600">{{ $pseUpdate->dc_data_name }}</td>
                        </tr>
                        <tr class="{{ $webMonitor->dc_data_attributes !== $pseUpdate->dc_data_attributes ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800">Atribut Data</td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $webMonitor->dc_data_attributes ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-purple-600">{{ $pseUpdate->dc_data_attributes ?? '-' }}</td>
                        </tr>
                        @php
                            $dcScoreMap = ['Rendah' => 1, 'Sedang' => 3, 'Tinggi' => 5];
                        @endphp
                        @foreach(['confidentiality' => 'Kerahasiaan', 'integrity' => 'Integritas', 'availability' => 'Ketersediaan'] as $field => $label)
                            @php
                                $oldField = "dc_{$field}";
                                $oldValue = $webMonitor->$oldField ?? '-';
                                $newValue = $pseUpdate->$oldField;
                                $changed = $oldValue !== $newValue;
                            @endphp
                            <tr class="{{ $changed ? 'bg-yellow-50' : '' }}">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $label }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">
                                    {{ $oldValue === '-' ? '-' : $oldValue }}
                                    @if($oldValue !== '-')
                                        <span class="text-gray-500">({{ $dcScoreMap[$oldValue] }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-purple-600">
                                    {{ $newValue }}
                                    <span class="text-gray-500">({{ $dcScoreMap[$newValue] }})</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">Total Skor</td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $webMonitor->dc_total_score ?? 0 }} dari 15
                            </td>
                            <td class="px-4 py-3 text-sm text-purple-600">
                                {{ $pseUpdate->dc_total_score ?? 0 }} dari 15
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- Activity Log --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-history text-gray-500 mr-2"></i>
            Riwayat Aktivitas
        </h2>

        @if($pseUpdate->logs->count() > 0)
            <div class="space-y-3">
                @foreach($pseUpdate->logs as $log)
                    <div class="flex items-start border-l-4 border-gray-300 pl-4 py-2">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">{{ ucfirst($log->action) }}</p>
                            @if($log->note)
                                <p class="text-sm text-gray-600 mt-1">{{ $log->note }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $log->actor ? $log->actor->name : 'System' }} •
                                {{ $log->created_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 italic">Belum ada aktivitas.</p>
        @endif
    </div>

    {{-- Action Form --}}
    @if(in_array($pseUpdate->status, ['diajukan', 'diproses']))
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-tasks text-gray-500 mr-2"></i>
                Tindakan
            </h2>

            <div x-data="{ action: '' }">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Tindakan:</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="action === 'approve' ? 'border-green-500 bg-green-50' : 'border-gray-300'">
                            <input type="radio"
                                   x-model="action"
                                   value="approve"
                                   class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Setujui - Data akan langsung diupdate ke Master Data Subdomain
                            </span>
                        </label>

                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="action === 'revision' ? 'border-orange-500 bg-orange-50' : 'border-gray-300'">
                            <input type="radio"
                                   x-model="action"
                                   value="revision"
                                   class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                                Minta Revisi - Pemohon dapat mengedit dan mengajukan kembali
                            </span>
                        </label>

                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                               :class="action === 'reject' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                            <input type="radio"
                                   x-model="action"
                                   value="reject"
                                   class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                Tolak - Permohonan ditolak dan tidak dapat diubah
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Approve Form --}}
                <form x-show="action === 'approve'"
                      action="{{ route('admin.pse-update.update-status', $pseUpdate->id) }}"
                      method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin menyetujui permohonan ini? Data akan langsung diupdate ke Web Monitor.')">
                    @csrf
                    <input type="hidden" name="action" value="approve">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="admin_notes"
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                  placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                        <i class="fas fa-check-circle mr-2"></i>Setujui Permohonan
                    </button>
                </form>

                {{-- Revision Form --}}
                <form x-show="action === 'revision'"
                      action="{{ route('admin.pse-update.update-status', $pseUpdate->id) }}"
                      method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin meminta revisi untuk permohonan ini?')">
                    @csrf
                    <input type="hidden" name="action" value="revision">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Revisi <span class="text-red-500">*</span></label>
                        <textarea name="revision_notes"
                                  rows="4"
                                  required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                  placeholder="Jelaskan apa yang perlu direvisi oleh pemohon..."></textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Minta Revisi
                    </button>
                </form>

                {{-- Reject Form --}}
                <form x-show="action === 'reject'"
                      action="{{ route('admin.pse-update.update-status', $pseUpdate->id) }}"
                      method="POST"
                      onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan ini? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    <input type="hidden" name="action" value="reject">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason"
                                  rows="4"
                                  required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                  placeholder="Jelaskan alasan penolakan permohonan ini..."></textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                        <i class="fas fa-times-circle mr-2"></i>Tolak Permohonan
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
