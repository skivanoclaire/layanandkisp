@extends('layouts.authenticated')

@section('title', '- Master Data Subdomain')
@section('header-title', 'Master Data Subdomain')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">Master Data Subdomain</h1>
        <div class="flex gap-2">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex gap-3 mb-4">
            <a href="{{ route('admin.web-monitor.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Website
            </a>

            <form action="{{ route('admin.web-monitor.sync-cloudflare') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center"
                        onclick="return confirm('Sinkronisasi dengan Cloudflare DNS Records?')">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sync Cloudflare
                </button>
            </form>

            <form action="{{ route('admin.web-monitor.check-all-status') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Cek Semua Status
                </button>
            </form>

            <a href="{{ route('admin.web-monitor.check-ip-publik') }}"
               class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                Cek IP Publik
            </a>

            <button type="button" id="toggleShowAll"
                    class="{{ isset($showAll) && $showAll ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                {{ isset($showAll) && $showAll ? 'Sembunyikan VM Tanpa Subdomain' : 'Tampilkan Semua (Termasuk VM IP Only)' }}
            </button>
        </div>

        @if(isset($statistics) && count($statistics) > 0)
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(App\Models\WebMonitor::jenisOptions() as $jenis)
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ $jenis }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $statistics[$jenis] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            @if($jenis === 'Website Resmi') bg-blue-100
                            @elseif($jenis === 'Aplikasi Layanan Publik') bg-green-100
                            @elseif($jenis === 'Aplikasi Administrasi Pemerintah') bg-purple-100
                            @else bg-orange-100
                            @endif">
                            <svg class="w-6 h-6
                                @if($jenis === 'Website Resmi') text-blue-600
                                @elseif($jenis === 'Aplikasi Layanan Publik') text-green-600
                                @elseif($jenis === 'Aplikasi Administrasi Pemerintah') text-purple-600
                                @else text-orange-600
                                @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        @if (!isset($showAll) || !$showAll)
        <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">
                        <strong>Filter Aktif:</strong> Menampilkan hanya subdomain yang memiliki nilai ({{ $data->count() }} data).
                        VM tanpa subdomain disembunyikan.
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-900">
                        <strong>Menampilkan Semua Data:</strong> Termasuk VM tanpa subdomain ({{ $data->count() }} data total).
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table id="monitorTable" class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-3 text-left">No</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Nama Instansi</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Subdomain</th>
                        <th class="border border-gray-300 px-4 py-3 text-center">Status</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Keterangan</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Jenis</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">IP Address</th>
                        <th class="border border-gray-300 px-4 py-3 text-center">Cloudflare</th>
                        <th class="border border-gray-300 px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border border-gray-300 px-4 py-2 font-semibold">{{ $item->nama_instansi }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="https://{{ $item->subdomain }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $item->subdomain }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($item->status === 'active')
                                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="Aktif"></div>
                                    <span class="text-green-700 font-semibold">Aktif</span>
                                @else
                                    <div class="w-3 h-3 bg-red-500 rounded-full" title="Tidak Aktif"></div>
                                    <span class="text-red-700 font-semibold">Tidak Aktif</span>
                                @endif
                            </div>
                            @if($item->last_checked_at)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $item->last_checked_at->diffForHumans() }}
                                </div>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-sm">
                            {{ $item->keterangan ?? '-' }}
                            @if($item->check_error)
                                <div class="text-xs text-red-600 mt-1">
                                    Error: {{ Str::limit($item->check_error, 50) }}
                                </div>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                @if($item->jenis === 'Website Resmi') bg-blue-100 text-blue-800
                                @elseif($item->jenis === 'Aplikasi Layanan Publik') bg-green-100 text-green-800
                                @elseif($item->jenis === 'Aplikasi Administrasi Pemerintah') bg-purple-100 text-purple-800
                                @else bg-orange-100 text-orange-800
                                @endif">
                                {{ $item->jenis }}
                            </span>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-mono">
                            {{ $item->ip_address ?? '-' }}
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            @if($item->cloudflare_record_id)
                                <span class="inline-flex items-center gap-1 text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded" title="Terhubung dengan Cloudflare">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13.5 8.5l-4 4m0 0l-4-4m4 4V3"/>
                                    </svg>
                                    CF
                                </span>
                                @if($item->is_proxied)
                                    <span class="block text-xs text-orange-600 mt-1">Proxied</span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="flex items-center gap-2 justify-center">
                                <a href="{{ route('admin.web-monitor.show', $item) }}"
                                   class="text-green-600 hover:text-green-800"
                                   title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                <form action="{{ route('admin.web-monitor.check-status', $item) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-purple-600 hover:text-purple-800"
                                            title="Cek Status">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                </form>

                                <a href="{{ route('admin.web-monitor.edit', $item) }}"
                                   class="text-blue-600 hover:text-blue-800"
                                   title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                <form action="{{ route('admin.web-monitor.destroy', $item) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus {{ $item->nama_instansi }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800"
                                            title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                            Belum ada data website. <a href="{{ route('admin.web-monitor.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-semibold text-blue-900 mb-2">Legenda Status:</h3>
            <div class="flex gap-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm">Aktif (website dapat diakses)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-sm">Tidak Aktif (website tidak dapat diakses)</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        @if($data->count() > 0)
        $('#monitorTable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']], // Sort by No (ID) ascending
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                zeroRecords: "Tidak ada data yang cocok"
            }
        });
        @endif

        // Toggle show all records including those without subdomain
        $('#toggleShowAll').on('click', function() {
            const currentUrl = new URL(window.location.href);
            const showAll = currentUrl.searchParams.get('show_all');

            if (showAll === 'true') {
                // Currently showing all, switch to filtered view
                currentUrl.searchParams.delete('show_all');
            } else {
                // Currently filtered, switch to show all
                currentUrl.searchParams.set('show_all', 'true');
            }

            window.location.href = currentUrl.toString();
        });
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@endsection
