@extends('layouts.authenticated')

@section('title', 'Detail Permohonan Update Data')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('user.pse-update.index') }}"
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
                <p class="text-gray-600 mt-1">Dibuat pada {{ $pseUpdate->created_at->format('d F Y, H:i') }}</p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Subdomain:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $webMonitor->subdomain }}</span>
            </div>
            <div>
                <span class="text-gray-500">Aplikasi:</span>
                <span class="font-medium text-gray-800 ml-2">{{ $webMonitor->nama_aplikasi ?? '-' }}</span>
            </div>
            @if($pseUpdate->submitted_at)
                <div>
                    <span class="text-gray-500">Diajukan:</span>
                    <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->submitted_at->format('d F Y, H:i') }}</span>
                </div>
            @endif
            @if($pseUpdate->approved_at)
                <div>
                    <span class="text-gray-500">Disetujui:</span>
                    <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->approved_at->format('d F Y, H:i') }}</span>
                </div>
            @endif
            @if($pseUpdate->rejected_at)
                <div>
                    <span class="text-gray-500">Ditolak:</span>
                    <span class="font-medium text-gray-800 ml-2">{{ $pseUpdate->rejected_at->format('d F Y, H:i') }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Revision Notes --}}
    @if($pseUpdate->revision_notes)
        <div class="bg-orange-50 border-l-4 border-orange-500 p-6 mb-6 rounded-lg">
            <h3 class="text-lg font-semibold text-orange-800 mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>Catatan Revisi
            </h3>
            <p class="text-orange-700">{{ $pseUpdate->revision_notes }}</p>
            @if($pseUpdate->revisionRequestedBy)
                <p class="text-sm text-orange-600 mt-2">
                    Diminta oleh: {{ $pseUpdate->revisionRequestedBy->name }} pada {{ $pseUpdate->revision_requested_at->format('d F Y, H:i') }}
                </p>
            @endif
        </div>
    @endif

    {{-- Rejection Reason --}}
    @if($pseUpdate->rejection_reason)
        <div class="bg-red-50 border-l-4 border-red-500 p-6 mb-6 rounded-lg">
            <h3 class="text-lg font-semibold text-red-800 mb-2">
                <i class="fas fa-times-circle mr-2"></i>Alasan Penolakan
            </h3>
            <p class="text-red-700">{{ $pseUpdate->rejection_reason }}</p>
            @if($pseUpdate->rejectedBy)
                <p class="text-sm text-red-600 mt-2">
                    Ditolak oleh: {{ $pseUpdate->rejectedBy->name }} pada {{ $pseUpdate->rejected_at->format('d F Y, H:i') }}
                </p>
            @endif
        </div>
    @endif

    {{-- Admin Notes --}}
    @if($pseUpdate->admin_notes)
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mb-6 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Catatan Admin
            </h3>
            <p class="text-blue-700">{{ $pseUpdate->admin_notes }}</p>
        </div>
    @endif

    {{-- Action Buttons --}}
    @if(in_array($pseUpdate->status, ['draft', 'perlu_revisi']) || $pseUpdate->status === 'draft')
        <div class="flex gap-4 mb-6">
            @if(in_array($pseUpdate->status, ['draft', 'perlu_revisi']))
                <a href="{{ route('user.pse-update.edit', $pseUpdate->id) }}"
                   class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit Permohonan
                </a>
            @endif

            @if(in_array($pseUpdate->status, ['draft', 'perlu_revisi']))
                <form action="{{ route('user.pse-update.submit', $pseUpdate->id) }}"
                      method="POST"
                      class="flex-1"
                      onsubmit="return confirm('{{ $pseUpdate->status === 'perlu_revisi' ? 'Apakah Anda yakin ingin mengajukan ulang permohonan ini setelah revisi?' : 'Apakah Anda yakin ingin mengajukan permohonan ini?' }}')">
                    @csrf
                    <button type="submit"
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                        <i class="fas fa-paper-plane mr-2"></i>{{ $pseUpdate->status === 'perlu_revisi' ? 'Submit Ulang Permohonan' : 'Submit Permohonan' }}
                    </button>
                </form>
            @endif

            @if($pseUpdate->status === 'draft')
                <form action="{{ route('user.pse-update.destroy', $pseUpdate->id) }}"
                      method="POST"
                      class="flex-1"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200">
                        <i class="fas fa-trash mr-2"></i>Hapus Permohonan
                    </button>
                </form>
            @endif
        </div>
    @endif

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
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $webMonitor->esc_category === 'Strategis' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $webMonitor->esc_category === 'Tinggi' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $webMonitor->esc_category === 'Rendah' ? 'bg-green-100 text-green-700' : '' }}">
                                    {{ $webMonitor->esc_category ?? '-' }}
                                </span>
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
                        @php
                            $oldDcTotal = $webMonitor->dc_total_score ?? 0;
                            $newDcTotal = $pseUpdate->dc_total_score ?? 0;

                            $oldDcCategory = 'Rendah';
                            $oldDcCategoryColor = 'bg-green-100 text-green-700';
                            if ($oldDcTotal >= 13) {
                                $oldDcCategory = 'Tinggi';
                                $oldDcCategoryColor = 'bg-red-100 text-red-700';
                            } elseif ($oldDcTotal >= 9) {
                                $oldDcCategory = 'Sedang';
                                $oldDcCategoryColor = 'bg-orange-100 text-orange-700';
                            }

                            $newDcCategory = 'Rendah';
                            $newDcCategoryColor = 'bg-green-100 text-green-700';
                            if ($newDcTotal >= 13) {
                                $newDcCategory = 'Tinggi';
                                $newDcCategoryColor = 'bg-red-100 text-red-700';
                            } elseif ($newDcTotal >= 9) {
                                $newDcCategory = 'Sedang';
                                $newDcCategoryColor = 'bg-orange-100 text-orange-700';
                            }
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">Kategori</td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit px-2 py-1 rounded text-xs font-semibold {{ $oldDcCategoryColor }}">
                                        {{ $oldDcCategory }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        &ge;13: Tinggi | 9-12: Sedang | &le;8: Rendah
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-purple-600">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit px-2 py-1 rounded text-xs font-semibold {{ $newDcCategoryColor }}">
                                        {{ $newDcCategory }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        &ge;13: Tinggi | 9-12: Sedang | &le;8: Rendah
                                    </span>
                                </div>
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
</div>
@endsection
