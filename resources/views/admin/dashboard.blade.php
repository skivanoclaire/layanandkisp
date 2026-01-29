@extends('layouts.authenticated')
@section('title', '- Dashboard Admin')
@section('header-title', 'Dashboard Admin')




@section('content')
    <div class="max-w-full overflow-hidden">
        <h1 class="text-3xl font-bold mb-8">Dashboard Admin - Permohonan Layanan</h1>

        <!-- Kartu Ringkasan -->
        <div class="max-w-full grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-green-100 p-4 rounded-lg text-center shadow">
                <p class="text-sm text-gray-700">Total Permohonan</p>
                <p class="text-2xl font-bold text-green-800">{{ $total }}</p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg text-center shadow">
                <p class="text-sm text-gray-700">Menunggu</p>
                <p class="text-2xl font-bold text-yellow-800">{{ $waiting }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg text-center shadow">
                <p class="text-sm text-gray-700">Dalam Proses</p>
                <p class="text-2xl font-bold text-blue-800">{{ $processing }}</p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg text-center shadow">
                <p class="text-sm text-gray-700">Ditolak</p>
                <p class="text-2xl font-bold text-red-800">{{ $rejected }}</p>
            </div>
            <div class="bg-emerald-100 p-4 rounded-lg text-center shadow">
                <p class="text-sm text-gray-700">Selesai</p>
                <p class="text-2xl font-bold text-emerald-800">{{ $finished }}</p>
            </div>
        </div>

        <!-- Subdomain Statistics -->
        <div class="max-w-full mb-8">
            <div class="max-w-full flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Kelola Subdomain</h2>
                <a href="{{ route('admin.unified-subdomain.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                    Lihat Semua
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            <div class="max-w-full grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @php
                    $subdomainStats = app(\App\Services\SubdomainAggregatorService::class)->getStats();
                @endphp

                <!-- Total Subdomain Aktif -->
                <div class="bg-white border-l-4 border-blue-500 p-4 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Subdomain</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $subdomainStats['active_monitors'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Aktif dari {{ $subdomainStats['total_monitors'] }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Permohonan Pending -->
                <div class="bg-white border-l-4 border-yellow-500 p-4 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Permohonan Pending</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $subdomainStats['pending_requests'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Website Down -->
                <div class="bg-white border-l-4 border-red-500 p-4 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Website Down</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $subdomainStats['down_websites'] }}</p>
                            <p class="text-xs text-red-600 mt-1">
                                @if($subdomainStats['down_websites'] > 0)
                                    Perlu perhatian!
                                @else
                                    Semua normal
                                @endif
                            </p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Need Check -->
                <div class="bg-white border-l-4 border-purple-500 p-4 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Perlu Pengecekan</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $subdomainStats['websites_needing_check'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">&gt;24 jam tidak dicek</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Problematic Websites Alert -->
            @if($subdomainStats['down_websites'] > 0)
                @php
                    $problematicWebsites = app(\App\Services\SubdomainAggregatorService::class)->getProblematicWebsites();
                @endphp
                @if($problematicWebsites->count() > 0)
                    <div class="max-w-full bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800 mb-2">Website Bermasalah</h3>
                                <div class="space-y-1">
                                    @foreach($problematicWebsites->take(5) as $website)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-red-700 font-mono">{{ $website->subdomain }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                {{ ucfirst($website->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('admin.unified-subdomain.index', ['source' => 'all', 'status_monitoring' => 'active']) }}" class="text-xs text-red-600 hover:text-red-700 font-medium mt-2 inline-block">
                                    Lihat Semua →
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Subdomain Growth Chart -->
            <div class="max-w-full bg-white p-6 rounded-lg shadow">
                <div class="max-w-full flex items-center justify-between mb-4 flex-wrap gap-2">
                    <p class="text-lg font-semibold text-gray-700">Pertumbuhan Subdomain <span id="periodLabel">(6 Bulan Terakhir)</span></p>
                    <div class="flex gap-2 flex-wrap">
                        <button onclick="changeChartPeriod(3, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300" style="transition: none;">3 Bulan</button>
                        <button onclick="changeChartPeriod(6, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300 bg-green-600 text-white" style="transition: none;">6 Bulan</button>
                        <button onclick="changeChartPeriod(12, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300" style="transition: none;">1 Tahun</button>
                        <button onclick="changeChartPeriod(24, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300" style="transition: none;">2 Tahun</button>
                        <button onclick="changeChartPeriod(36, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300" style="transition: none;">3 Tahun</button>
                        <button onclick="changeChartPeriod(48, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300" style="transition: none;">4 Tahun</button>
                        <button onclick="changeChartPeriod(60, event)" class="period-btn px-3 py-1 text-xs font-medium rounded border border-gray-300" style="transition: none;">5 Tahun</button>
                    </div>
                </div>
                <div class="max-w-full" style="height: 300px;">
                    <canvas id="chartSubdomain"></canvas>
                </div>
            </div>
        </div>

        <!-- Progress Permohonan per Jenis Layanan -->
        <div class="max-w-full mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Progress Permohonan Berdasarkan Jenis Layanan</h2>
            <div class="max-w-full bg-white p-6 rounded-lg shadow">
                <div class="max-w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Layanan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Menunggu</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Proses</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ditolak</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($serviceTypes as $service)
                                @php
                                    $stats = $serviceStats[$service] ?? [
                                        'total' => 0,
                                        'menunggu' => 0,
                                        'proses' => 0,
                                        'ditolak' => 0,
                                        'selesai' => 0
                                    ];
                                    $progressPercent = $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100) : 0;
                                @endphp
                                <tr class="{{ $stats['total'] > 0 ? 'hover:bg-gray-50' : 'opacity-50' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $service }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-semibold text-gray-900">{{ $stats['total'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['menunggu'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $stats['menunggu'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['proses'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $stats['proses'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['ditolak'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $stats['ditolak'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['selesai'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $stats['selesai'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-700">{{ $progressPercent }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Progress Formulir Digital -->
        <div class="max-w-full mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Progress Formulir Digital</h2>
            <div class="max-w-full bg-white p-6 rounded-lg shadow">
                <div class="max-w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Formulir</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Menunggu</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Proses</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ditolak</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($digitalFormStats as $key => $stats)
                                @php
                                    $progressPercent = $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100) : 0;
                                @endphp
                                <tr class="{{ $stats['total'] > 0 ? 'hover:bg-gray-50' : 'opacity-50' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $stats['label'] }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-semibold text-gray-900">{{ $stats['total'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['menunggu'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $stats['menunggu'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['proses'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $stats['proses'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['ditolak'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $stats['ditolak'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($stats['selesai'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $stats['selesai'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-700">{{ $progressPercent }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Grafik Kinerja -->
        <div class="max-w-full bg-white p-6 rounded-lg shadow mb-10">
            <p class="text-lg font-semibold mb-4 text-gray-700">Grafik Kinerja Permohonan Bulan Ini</p>
            <div class="max-w-full" style="height: 300px;">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="max-w-full mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        <!-- Tabel Permohonan -->
        <div class="max-w-full bg-white shadow p-6 rounded-lg">
            <div class="max-w-full overflow-x-auto">
                <table id="requests-table" class="min-w-full table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Nama User</th>
                        <th class="px-4 py-2">Layanan</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $request->user->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $request->service }}</td>
                            <td class="px-4 py-2">{{ $request->status }}</td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('admin.update-status', $request) }}">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="border p-1 rounded">
                                        <option {{ $request->status == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option {{ $request->status == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses
                                        </option>
                                        <option {{ $request->status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                                        <option {{ $request->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <style>
        /* Disable hover effects on period buttons */
        .period-btn:hover {
            background-color: inherit !important;
            color: inherit !important;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Active button should keep its green background on hover */
        .period-btn.bg-green-600:hover {
            background-color: rgb(22 163 74) !important;
            color: white !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- jQuery dan DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            $('#requests-table').DataTable();
        });

        // Subdomain Growth Chart
        const ctxSubdomain = document.getElementById('chartSubdomain').getContext('2d');
        @php
            $chartData = app(\App\Services\SubdomainAggregatorService::class)->getChartData(6);
        @endphp
        let chartSubdomain = new Chart(ctxSubdomain, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: {!! json_encode($chartData['datasets']) !!}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4
                    }
                }
            }
        });

        // Function to change chart period
        function changeChartPeriod(months, event) {
            console.log('changeChartPeriod called with months:', months);

            // Update active button
            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('bg-green-600', 'text-white');
            });
            event.target.classList.add('bg-green-600', 'text-white');

            // Update label
            const periodLabels = {
                3: '(3 Bulan Terakhir)',
                6: '(6 Bulan Terakhir)',
                12: '(1 Tahun Terakhir)',
                24: '(2 Tahun Terakhir)',
                36: '(3 Tahun Terakhir)',
                48: '(4 Tahun Terakhir)',
                60: '(5 Tahun Terakhir)'
            };
            document.getElementById('periodLabel').textContent = periodLabels[months];

            // Fetch new data via AJAX
            const url = `{{ route('admin.dashboard.chart-data') }}?months=${months}`;
            console.log('Fetching data from:', url);

            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    // Update chart data
                    chartSubdomain.data.labels = data.labels;
                    chartSubdomain.data.datasets = data.datasets;
                    chartSubdomain.update();
                    console.log('Chart updated successfully');
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        }

        // Service Request Chart
        const ctx = document.getElementById('chartStatus').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Menunggu', 'Dalam Proses', 'Ditolak', 'Selesai'],
                datasets: [{
                    label: 'Jumlah Permohonan Bulan {{ now()->translatedFormat('F') }}',
                    data: [
                        {{ $summary['Menunggu'] ?? 0 }},
                        {{ $summary['Dalam Proses'] ?? 0 }},
                        {{ $summary['Ditolak'] ?? 0 }},
                        {{ $summary['Selesai'] ?? 0 }},
                    ],
                    backgroundColor: [
                        '#facc15', // Menunggu
                        '#38bdf8', // Dalam Proses
                        '#ef4444', // Ditolak
                        '#22c55e', // Selesai
                    ],
                    borderRadius: 6,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
