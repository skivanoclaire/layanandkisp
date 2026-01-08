@extends('layouts.authenticated')
@section('title', '- Riwayat Sinkronisasi')
@section('header-title', 'Riwayat Sinkronisasi Aset TIK')
@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Riwayat Sinkronisasi</h1>
        <a href="{{ route('admin.google-aset-tik.sync.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Kembali</a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Register Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sync Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Rows</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Updated</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Failed</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Skipped</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed At</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $log->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                            {{ strtoupper($log->register_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($log->sync_type == 'import')
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">⬇️ Import</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">⬆️ Export</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $badgeClass = match($log->status) {
                                'success' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'partial' => 'bg-yellow-100 text-yellow-800',
                                'running' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $emoji = match($log->status) {
                                'success' => '✅',
                                'failed' => '❌',
                                'partial' => '⚠️',
                                'running' => '⏳',
                                default => '❓'
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $badgeClass }}">
                            {{ $emoji }} {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold">{{ number_format($log->total_rows) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 font-semibold">{{ number_format($log->rows_created) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600 font-semibold">{{ number_format($log->rows_updated) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-semibold">{{ number_format($log->rows_failed) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">{{ number_format($log->rows_skipped) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs font-mono rounded bg-gray-100 text-gray-800">
                            {{ $log->duration ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $log->started_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $log->completed_at ? $log->completed_at->format('d/m/Y H:i:s') : '-' }}</td>
                </tr>
                @if($log->error_details && is_array($log->error_details))
                <tr class="bg-red-50">
                    <td colspan="12" class="px-6 py-3 text-sm">
                        <div class="text-red-800 font-semibold mb-2">❌ Error Details:</div>
                        <div class="text-red-700 text-xs space-y-1">
                            @foreach($log->error_details as $error)
                                <div>Row {{ $error['row'] ?? 'N/A' }}: {{ $error['error'] ?? 'Unknown error' }}</div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-semibold">Belum ada riwayat sinkronisasi</p>
                        <p class="text-sm mt-2">Lakukan import pertama dari Google Sheets untuk memulai</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="mt-6">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
