@extends('layouts.authenticated')

@section('title', '- Laporan Traffic')
@section('header-title', 'Laporan Traffic Operasional Bulanan')

@php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
@endphp

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Laporan Traffic Operasional Bulanan</h1>
            <p class="text-gray-600 mt-1">Domain: {{ $zoneName }}</p>
            <p class="text-gray-500 text-sm mt-1">Dokumen ini digenerate secara otomatis dari sistem E-Layanan DKISP (layanan.diskominfo.kaltaraprov.go.id) - Pemerintah Provinsi Kalimantan Utara</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.web-monitor.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Controls -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Periode</label>
                <div class="flex gap-2">
                    @php
                        $selectedYear = (int) substr($month, 0, 4);
                        $selectedMonth = (int) substr($month, 5, 2);
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $currentYear = (int) date('Y');
                    @endphp
                    <select id="selectMonth" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select id="selectYear" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="button" id="btnApplyPeriod" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Tampilkan
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.web-monitor.traffic-report.sync') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="month" id="syncMonth" value="{{ $month }}">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sinkron Data Cloudflare
                </button>
            </form>

            @if($trafficData)
            <a href="{{ route('admin.web-monitor.traffic-report.export-pdf', ['month' => $month]) }}"
               target="_blank"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor PDF
            </a>
            @endif

            @if($trafficData)
            <div class="ml-auto text-sm text-gray-500">
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Data tersinkron: {{ \Carbon\Carbon::parse($trafficData['synced_at'])->locale('id')->isoFormat('D MMM YYYY HH:mm') }}
                </span>
            </div>
            @endif
        </div>
    </div>

    @if($trafficData)
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Requests</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($trafficData['summary']['total_requests']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Bandwidth</p>
                    <p class="text-2xl font-bold text-gray-800">{{ formatBytes($trafficData['summary']['total_bandwidth']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Unique Visitors</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($trafficData['summary']['unique_visitors']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.web-monitor.traffic-report.security-details', ['month' => $month]) }}"
           class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg hover:bg-red-50 transition-all duration-200 cursor-pointer block group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 group-hover:text-red-700">Security Events</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($trafficData['security']['total_events']) }}</p>
                    <p class="text-xs text-gray-400 group-hover:text-red-500 mt-1">Klik untuk detail</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center group-hover:bg-red-200 transition-colors">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Cache Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Cache</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-600">Cached Requests</span>
                        <span class="text-sm font-semibold">{{ number_format($trafficData['summary']['cached_requests']) }} / {{ number_format($trafficData['summary']['total_requests']) }}</span>
                    </div>
                    @php
                        $cacheRatio = $trafficData['summary']['total_requests'] > 0
                            ? ($trafficData['summary']['cached_requests'] / $trafficData['summary']['total_requests']) * 100
                            : 0;
                    @endphp
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $cacheRatio }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($cacheRatio, 1) }}% cache hit ratio</p>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-600">Cached Bandwidth</span>
                        <span class="text-sm font-semibold">{{ formatBytes($trafficData['summary']['cached_bandwidth']) }}</span>
                    </div>
                    @php
                        $bandwidthCacheRatio = $trafficData['summary']['total_bandwidth'] > 0
                            ? ($trafficData['summary']['cached_bandwidth'] / $trafficData['summary']['total_bandwidth']) * 100
                            : 0;
                    @endphp
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $bandwidthCacheRatio }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($bandwidthCacheRatio, 1) }}% bandwidth saved</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Security Events</h3>
            @if($trafficData['security']['total_events'] > 0)
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Security Events</span>
                    <span class="text-xl font-bold text-red-600">{{ number_format($trafficData['security']['total_events']) }}</span>
                </div>
                @if(!empty($trafficData['security']['by_action']))
                <div class="border-t pt-3">
                    <p class="text-sm font-medium text-gray-700 mb-2">By Action:</p>
                    <div class="space-y-1">
                        @foreach(array_slice($trafficData['security']['by_action'], 0, 5, true) as $action => $count)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ ucfirst($action) }}</span>
                            <span class="font-medium">{{ number_format($count) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @else
            <p class="text-gray-500 text-center py-4">Tidak ada security events tercatat</p>
            @endif
        </div>
    </div>

    <!-- Daily Traffic Chart -->
    @if(!empty($trafficData['daily']))
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Traffic Harian</h3>
        <div class="h-64">
            <canvas id="dailyTrafficChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Top Subdomains -->
    @if(!empty($trafficData['hostnames']))
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Traffic per Subdomain</h3>
        <div class="mb-4">
            <input type="text" id="hostnameSearch" placeholder="Cari subdomain..."
                   class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-64 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse" id="hostnameTable">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-3 text-left">No</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Subdomain</th>
                        <th class="border border-gray-300 px-4 py-3 text-right">Total Requests</th>
                        <th class="border border-gray-300 px-4 py-3 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalHostnameRequests = array_sum($trafficData['hostnames']); @endphp
                    @foreach($trafficData['hostnames'] as $hostname => $requests)
                    <tr class="hover:bg-gray-50 hostname-row">
                        <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border border-gray-300 px-4 py-2 font-mono text-sm hostname-name">
                            <a href="https://{{ $hostname }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $hostname }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold">
                            {{ number_format($requests) }}
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            @php $percentage = $totalHostnameRequests > 0 ? ($requests / $totalHostnameRequests) * 100 : 0; @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-16 text-right">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Security Events by Country -->
    @if(!empty($trafficData['security']['by_country']))
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Security Events by Country</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-3 text-left">No</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Country</th>
                        <th class="border border-gray-300 px-4 py-3 text-right">Events</th>
                        <th class="border border-gray-300 px-4 py-3 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalEvents = $trafficData['security']['total_events']; @endphp
                    @foreach(array_slice($trafficData['security']['by_country'], 0, 20, true) as $country => $events)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $country }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold">{{ number_format($events) }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            @php $percentage = $totalEvents > 0 ? ($events / $totalEvents) * 100 : 0; @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-16 text-right">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Insights & Recommendations -->
    @php
        $insightsList = $trafficData['insights']['insights'] ?? [];
        $recommendationsList = $trafficData['insights']['recommendations'] ?? [];
    @endphp
    @if(!empty($insightsList))
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg shadow-md p-6 mb-6 border border-indigo-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Analisis & Rekomendasi
        </h3>

        <!-- Insights Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @foreach($insightsList as $insight)
            @php $insightType = $insight['type'] ?? 'info'; @endphp
            <div class="bg-white rounded-lg p-4 shadow-sm border-l-4
                @if($insightType === 'success') border-green-500
                @elseif($insightType === 'warning') border-yellow-500
                @elseif($insightType === 'danger') border-red-500
                @else border-blue-500
                @endif">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        @if($insightType === 'success')
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @elseif($insightType === 'warning')
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        @elseif($insightType === 'danger')
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @else
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800 text-sm mb-1">{{ $insight['title'] }}</h4>
                        <p class="text-gray-600 text-sm">{{ $insight['message'] }}</p>
                        @if(!empty($insight['recommendation']))
                        <div class="mt-2 p-2 bg-gray-50 rounded text-sm">
                            <span class="font-medium text-indigo-700">Rekomendasi:</span>
                            <span class="text-gray-700">{{ $insight['recommendation'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Recommendations Section -->
        @if(!empty($recommendationsList))
        <div class="bg-white rounded-lg p-4 border border-indigo-200 mb-4">
            <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Rekomendasi Tindakan
            </h4>
            <ul class="space-y-2">
                @foreach($recommendationsList as $recommendation)
                <li class="flex items-start text-sm text-gray-700">
                    <svg class="w-4 h-4 mr-2 text-indigo-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                    {{ $recommendation }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Summary Note -->
        <div class="bg-white rounded-lg p-4 border border-indigo-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Catatan:</span> Analisis ini digenerate secara otomatis berdasarkan data traffic periode {{ $monthName }}.
                    Untuk subdomain dengan traffic rendah, pastikan DNS telah dikonfigurasi melalui Cloudflare proxy (orange cloud).
                </p>
            </div>
        </div>
    </div>
    @endif

    @else
    <!-- No Data State -->
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Data Traffic</h3>
        <p class="text-gray-600 mb-6">Klik tombol "Sinkron Data Cloudflare" untuk mengambil data traffic dari Cloudflare API.</p>
        <form action="{{ route('admin.web-monitor.traffic-report.sync') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sinkron Data Sekarang
            </button>
        </form>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Period selector handler
    function getSelectedMonth() {
        const month = document.getElementById('selectMonth').value.padStart(2, '0');
        const year = document.getElementById('selectYear').value;
        return `${year}-${month}`;
    }

    document.getElementById('btnApplyPeriod').addEventListener('click', function() {
        const month = getSelectedMonth();
        document.getElementById('syncMonth').value = month;
        window.location.href = "{{ route('admin.web-monitor.traffic-report') }}?month=" + month;
    });

    // Update sync form when period changes
    document.getElementById('selectMonth').addEventListener('change', function() {
        document.getElementById('syncMonth').value = getSelectedMonth();
    });
    document.getElementById('selectYear').addEventListener('change', function() {
        document.getElementById('syncMonth').value = getSelectedMonth();
    });

    // Hostname search filter
    document.getElementById('hostnameSearch')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.hostname-row');

        rows.forEach(row => {
            const hostname = row.querySelector('.hostname-name').textContent.toLowerCase();
            if (hostname.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    @if($trafficData && !empty($trafficData['daily']))
    // Daily traffic chart
    const dailyData = @json($trafficData['daily']);
    const labels = Object.keys(dailyData);
    const requestsData = labels.map(date => dailyData[date].requests);
    const visitorsData = labels.map(date => dailyData[date].unique_visitors);

    new Chart(document.getElementById('dailyTrafficChart'), {
        type: 'line',
        data: {
            labels: labels.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            }),
            datasets: [
                {
                    label: 'Requests',
                    data: requestsData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y',
                },
                {
                    label: 'Unique Visitors',
                    data: visitorsData,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Requests'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Visitors'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
    @endif
</script>
@endpush

@push('styles')
<style>
    .hostname-row {
        transition: background-color 0.15s ease;
    }
</style>
@endpush

@endsection
