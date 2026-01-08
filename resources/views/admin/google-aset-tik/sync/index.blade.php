@extends('layouts.authenticated')
@section('title', '- Manajemen Sinkronisasi')
@section('header-title', 'Manajemen Sinkronisasi Aset TIK')
@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Sinkronisasi</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.google-aset-tik.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Dashboard</a>
            <a href="{{ route('admin.google-aset-tik.sync.logs') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Riwayat Sync</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    {{-- Connection Status --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Status Koneksi Google Sheets API</h3>
                <p class="text-sm text-gray-600">Spreadsheet ID: {{ config('google-aset-tik.google.spreadsheet_id') }}</p>
            </div>
            <div>
                @if($isConnected)
                    <span class="px-4 py-2 text-sm font-semibold rounded bg-green-100 text-green-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Connected
                    </span>
                @else
                    <span class="px-4 py-2 text-sm font-semibold rounded bg-red-100 text-red-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        Disconnected
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Hardware (HAM-Register)</h3>
            <div class="space-y-3">
                <div class="flex justify-between"><span class="text-gray-600">Total di Database:</span><span class="font-semibold">{{ number_format($stats['hardware']['total_in_db']) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Pending Export:</span><span class="font-semibold text-yellow-600">{{ number_format($stats['hardware']['pending_export']) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Terakhir Sync:</span><span class="font-semibold">{{ $stats['hardware']['last_sync'] ? $stats['hardware']['last_sync']->format('d/m/Y H:i') : 'Belum pernah' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Status:</span><span class="font-semibold">{{ $stats['hardware']['last_sync_status'] ?? '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Software (SAM-Register)</h3>
            <div class="space-y-3">
                <div class="flex justify-between"><span class="text-gray-600">Total di Database:</span><span class="font-semibold">{{ number_format($stats['software']['total_in_db']) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Pending Export:</span><span class="font-semibold text-yellow-600">{{ number_format($stats['software']['pending_export']) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Terakhir Sync:</span><span class="font-semibold">{{ $stats['software']['last_sync'] ? $stats['software']['last_sync']->format('d/m/Y H:i') : 'Belum pernah' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Status:</span><span class="font-semibold">{{ $stats['software']['last_sync_status'] ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                Import dari Google Sheets
            </h3>
            <form method="POST" action="{{ route('admin.google-aset-tik.sync.import') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-gray-700 mb-2">Tipe Data</label>
                    <select name="type" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="all">All (HAM + SAM + Kategori)</option>
                        <option value="ham">HAM - Hardware</option>
                        <option value="sam">SAM - Software</option>
                        <option value="kategori">Kategori</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="dry_run" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm">
                    <label class="ml-2 text-sm text-gray-700">Dry Run (Preview tanpa save)</label>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Import Sekarang</button>
            </form>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Export ke Google Sheets
            </h3>
            <form method="POST" action="{{ route('admin.google-aset-tik.sync.export') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-gray-700 mb-2">Tipe Data</label>
                    <select name="type" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="all">All (HAM + SAM)</option>
                        <option value="ham">HAM - Hardware</option>
                        <option value="sam">SAM - Software</option>
                    </select>
                </div>
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">⚠️ Fitur export masih dalam pengembangan</p>
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold" disabled>Export Sekarang</button>
            </form>
        </div>
    </div>

    {{-- Recent Logs --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Riwayat Sync Terakhir</h3>
            <a href="{{ route('admin.google-aset-tik.sync.logs') }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">Lihat Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Direction</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Updated</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Failed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentLogs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ strtoupper($log->register_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->sync_type == 'import' ? '⬇️ Import' : '⬆️ Export' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $badgeClass = match($log->status) {
                                    'success' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'partial' => 'bg-yellow-100 text-yellow-800',
                                    'running' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $badgeClass }}">{{ $log->status }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ $log->rows_created }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ $log->rows_updated }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ $log->rows_failed }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->started_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Belum ada riwayat sinkronisasi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
