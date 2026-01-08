@extends('layouts.authenticated')

@section('title', '- Kelola Subdomain Terpadu')
@section('header-title', 'Kelola Subdomain Terpadu')

@section('content')
<div class="container mx-auto px-4 max-w-full">
    <!-- Header with Stats -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Kelola Subdomain Terpadu</h1>
        <p class="text-gray-600 mt-2">Manajemen terpadu untuk semua subdomain (permohonan & manual entry)</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Subdomain</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_monitors'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending Approval</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_requests'] }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Aktif</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['active_monitors'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Down / Error</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['down_websites'] }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('admin.unified-subdomain.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       placeholder="Ticket, subdomain, nama..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Source Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sumber</label>
                <select name="source" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all" {{ ($filters['source'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="permohonan" {{ ($filters['source'] ?? '') == 'permohonan' ? 'selected' : '' }}>Dari Permohonan</option>
                    <option value="manual" {{ ($filters['source'] ?? '') == 'manual' ? 'selected' : '' }}>Manual Entry</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ ($filters['status'] ?? '') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="proses" {{ ($filters['status'] ?? '') == 'proses' ? 'selected' : '' }}>Proses</option>
                    <option value="selesai" {{ ($filters['status'] ?? '') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ ($filters['status'] ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.unified-subdomain.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center mb-4">
        <div class="text-sm text-gray-600">
            Menampilkan {{ $subdomains->count() }} dari {{ $total }} total subdomain
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.unified-subdomain.export-all', request()->query()) }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sumber</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket/ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdomain</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monitoring</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($subdomains as $index => $subdomain)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ ($currentPage - 1) * $perPage + $index + 1 }}</td>

                        <!-- Source Badge -->
                        <td class="px-4 py-3">
                            @if($subdomain['source'] == 'permohonan')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                    Permohonan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                    </svg>
                                    Manual
                                </span>
                            @endif
                        </td>

                        <!-- Ticket/ID -->
                        <td class="px-4 py-3">
                            @if($subdomain['ticket_no'])
                                <span class="text-sm font-mono font-medium text-blue-600">{{ $subdomain['ticket_no'] }}</span>
                            @else
                                <span class="text-sm text-gray-400">#{{ $subdomain['id'] }}</span>
                            @endif
                        </td>

                        <!-- Subdomain -->
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                @if($subdomain['is_proxied'])
                                    <svg class="w-4 h-4 text-orange-500 mr-1" fill="currentColor" viewBox="0 0 20 20" title="Cloudflare Proxied">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $subdomain['subdomain'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $subdomain['subdomain_full'] }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- IP Address -->
                        <td class="px-4 py-3">
                            <span class="text-sm font-mono text-gray-700">{{ $subdomain['ip_address'] ?? '-' }}</span>
                        </td>

                        <!-- Instansi -->
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-900">{{ $subdomain['nama_instansi'] ?? '-' }}</p>
                            @if($subdomain['nama_pemohon'])
                                <p class="text-xs text-gray-500">{{ $subdomain['nama_pemohon'] }}</p>
                            @endif
                        </td>

                        <!-- Status Permohonan -->
                        <td class="px-4 py-3">
                            @if($subdomain['status_permohonan'])
                                @php
                                    $statusColors = [
                                        'menunggu' => 'bg-yellow-100 text-yellow-800',
                                        'proses' => 'bg-blue-100 text-blue-800',
                                        'selesai' => 'bg-green-100 text-green-800',
                                        'ditolak' => 'bg-red-100 text-red-800',
                                    ];
                                    $color = $statusColors[$subdomain['status_permohonan']] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucfirst($subdomain['status_permohonan']) }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- Status Monitoring -->
                        <td class="px-4 py-3">
                            @if($subdomain['status_monitoring'])
                                @php
                                    $monitorColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-gray-100 text-gray-800',
                                        'down' => 'bg-red-100 text-red-800',
                                        'no-domain' => 'bg-orange-100 text-orange-800',
                                    ];
                                    $monColor = $monitorColors[$subdomain['status_monitoring']] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $monColor }}">
                                        {{ $subdomain['is_active'] ? '● ' : '○ ' }}{{ ucfirst($subdomain['status_monitoring']) }}
                                    </span>
                                    @if($subdomain['last_checked_at'])
                                        <p class="text-xs text-gray-400 mt-1">{{ $subdomain['last_checked_at']->diffForHumans() }}</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Belum dimonitor</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.unified-subdomain.show', ['id' => $subdomain['id'], 'type' => $subdomain['type']]) }}"
                                   class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>

                                @if($subdomain['type'] == 'request' && in_array($subdomain['status_permohonan'], ['menunggu', 'proses']))
                                    <button onclick="quickApprove({{ $subdomain['id'] }})"
                                            class="text-green-600 hover:text-green-900" title="Quick Approve">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                @endif

                                @if($subdomain['web_monitor_id'])
                                    <button onclick="checkStatus({{ $subdomain['web_monitor_id'] }}, '{{ $subdomain['type'] }}')"
                                            class="text-purple-600 hover:text-purple-900" title="Check Status">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="mt-2">Tidak ada data subdomain</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($total > $perPage)
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Halaman <span class="font-medium">{{ $currentPage }}</span> dari <span class="font-medium">{{ ceil($total / $perPage) }}</span>
                </div>
                <div class="flex gap-2">
                    @if($currentPage > 1)
                        <a href="?page={{ $currentPage - 1 }}&{{ http_build_query(request()->except('page')) }}"
                           class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">Previous</a>
                    @endif
                    @if($currentPage < ceil($total / $perPage))
                        <a href="?page={{ $currentPage + 1 }}&{{ http_build_query(request()->except('page')) }}"
                           class="px-3 py-1 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">Next</a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Quick Approve Modal (using Alpine.js) -->
<div x-data="{ showApproveModal: false, approveId: null }" x-cloak>
    <div x-show="showApproveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click.self="showApproveModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Approval</h3>
                <form :action="`/admin/unified-subdomain/${approveId}/approve`" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                        <textarea name="admin_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                  placeholder="Catatan approval..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Setujui
                        </button>
                        <button type="button" @click="showApproveModal = false"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function quickApprove(id) {
            Alpine.store('modal', { showApproveModal: true, approveId: id });
        }

        function checkStatus(id, type) {
            fetch(`/admin/unified-subdomain/${id}/check-status?type=${type}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Status: ${data.status}\nLast checked: ${data.last_checked_at}`);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error checking status');
                console.error(error);
            });
        }
    </script>
</div>

<!-- Alpine.js for modal -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
