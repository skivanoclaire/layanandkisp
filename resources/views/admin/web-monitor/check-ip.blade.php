@extends('layouts.authenticated')

@section('title', '- Cek IP Publik')
@section('header-title', 'Cek IP Publik')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">Pengecekan IP Publik</h1>
        <a href="{{ route('admin.web-monitor.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-700 font-semibold">Range IP</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $range }}</p>
                </div>
                <svg class="w-12 h-12 text-blue-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
        </div>

        <div class="bg-purple-100 border-l-4 border-purple-500 rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-purple-700 font-semibold">Total Range</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $total_range }}</p>
                    <p class="text-xs text-purple-600">IP Address</p>
                </div>
                <svg class="w-12 h-12 text-purple-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
            </div>
        </div>

        <div class="bg-red-100 border-l-4 border-red-500 rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-700 font-semibold">IP Terpakai</p>
                    <p class="text-2xl font-bold text-red-900">{{ $total_used }}</p>
                    <p class="text-xs text-red-600">{{ number_format(($total_used / $total_range) * 100, 1) }}% dari total</p>
                </div>
                <svg class="w-12 h-12 text-red-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>

        <div class="bg-green-100 border-l-4 border-green-500 rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-700 font-semibold">IP Tersedia</p>
                    <p class="text-2xl font-bold text-green-900">{{ $total_available }}</p>
                    <p class="text-xs text-green-600">{{ number_format(($total_available / $total_range) * 100, 1) }}% dari total</p>
                </div>
                <svg class="w-12 h-12 text-green-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- IP Tables -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Used IPs -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-red-700 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    IP Terpakai ({{ $total_used }})
                </h2>
                <a href="{{ route('admin.web-monitor.ip-terpakai.create') }}?from=check-ip"
                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-semibold inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah IP
                </a>
            </div>

            <div class="mb-3">
                <input type="text" id="searchUsed" placeholder="Cari IP atau Instansi..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="overflow-y-auto" style="max-height: 600px;">
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-red-50 sticky top-0">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">IP Address</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">Keterangan</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="usedIpsTable">
                        @foreach ($used_ips as $index => $item)
                        <tr class="hover:bg-red-50 used-ip-row">
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm font-mono font-semibold text-red-700">
                                {{ $item['ip'] }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $item['description'] }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <a href="{{ route('admin.web-monitor.ip-terpakai.edit', $item['id']) }}?from=check-ip"
                                   class="text-blue-600 hover:text-blue-800 mr-2 text-sm">
                                    Edit
                                </a>
                                |
                                <form action="{{ route('admin.web-monitor.ip-terpakai.destroy', $item['id']) }}"
                                      method="POST" class="inline ml-2"
                                      onsubmit="return confirm('Yakin hapus IP {{ $item['ip'] }} untuk {{ $item['description'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-sm">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Available IPs -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-green-700 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                IP Tersedia ({{ $total_available }})
            </h2>

            <div class="mb-3">
                <input type="text" id="searchAvailable" placeholder="Cari IP..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="overflow-y-auto" style="max-height: 600px;">
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-green-50 sticky top-0">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">IP Address</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody id="availableIpsTable">
                        @foreach ($available_ips as $index => $ip)
                        <tr class="hover:bg-green-50 available-ip-row">
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm font-mono font-semibold text-green-700">
                                {{ $ip }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">
                                    Kosong
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search for used IPs
    const searchUsed = document.getElementById('searchUsed');
    searchUsed.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('.used-ip-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Search for available IPs
    const searchAvailable = document.getElementById('searchAvailable');
    searchAvailable.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('.available-ip-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
</script>
@endsection
