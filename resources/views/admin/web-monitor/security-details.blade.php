@extends('layouts.authenticated')

@section('title', '- Detail Security Events')
@section('header-title', 'Detail Security Events')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Security Events</h1>
            <p class="text-gray-600 mt-1">Domain: {{ $zoneName }} | Periode: {{ $monthName }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.web-monitor.traffic-report', ['month' => $month]) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Laporan
            </a>
        </div>
    </div>

    @if($syncedAt)
    <div class="mb-4 text-sm text-gray-500">
        <span class="inline-flex items-center">
            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Data tersinkron: {{ \Carbon\Carbon::parse($syncedAt)->locale('id')->isoFormat('D MMM YYYY HH:mm') }}
        </span>
    </div>
    @endif

    <!-- Summary Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Security Events</p>
                <p class="text-4xl font-bold text-red-600">{{ number_format($security['total_events']) }}</p>
                <p class="text-sm text-gray-500 mt-1">Events terdeteksi dan diblokir oleh Cloudflare</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Events by Action -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Events by Action
            </h3>
            @if(!empty($security['by_action']))
            <div class="space-y-3">
                @php $totalActions = array_sum($security['by_action']); @endphp
                @foreach($security['by_action'] as $action => $count)
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">
                            @switch($action)
                                @case('block')
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                        Block
                                    </span>
                                    @break
                                @case('challenge')
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                                        Challenge
                                    </span>
                                    @break
                                @case('jschallenge')
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                                        JS Challenge
                                    </span>
                                    @break
                                @case('managed_challenge')
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                                        Managed Challenge
                                    </span>
                                    @break
                                @case('log')
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span>
                                        Log
                                    </span>
                                    @break
                                @case('skip')
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Skip
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                        {{ ucfirst($action) }}
                                    </span>
                            @endswitch
                        </span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                    </div>
                    @php $percentage = $totalActions > 0 ? ($count / $totalActions) * 100 : 0; @endphp
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full
                            @switch($action)
                                @case('block') bg-red-500 @break
                                @case('challenge') bg-yellow-500 @break
                                @case('jschallenge') bg-orange-500 @break
                                @case('managed_challenge') bg-purple-500 @break
                                @case('log') bg-gray-500 @break
                                @case('skip') bg-green-500 @break
                                @default bg-blue-500
                            @endswitch
                        " style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-4">Tidak ada data</p>
            @endif
        </div>

        <!-- Events by Source -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Events by Source
            </h3>
            @if(!empty($security['by_source']))
            <div class="space-y-3">
                @php $totalSources = array_sum($security['by_source']); @endphp
                @foreach($security['by_source'] as $source => $count)
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $source)) }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                    </div>
                    @php $percentage = $totalSources > 0 ? ($count / $totalSources) * 100 : 0; @endphp
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-4">Tidak ada data</p>
            @endif
        </div>
    </div>

    <!-- Events by Country -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Events by Country
        </h3>
        @if(!empty($security['by_country']))
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-3 text-left">No</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Country</th>
                        <th class="border border-gray-300 px-4 py-3 text-right">Events</th>
                        <th class="border border-gray-300 px-4 py-3 text-center" style="width: 30%;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCountry = array_sum($security['by_country']); @endphp
                    @foreach($security['by_country'] as $country => $events)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border border-gray-300 px-4 py-2 font-medium">
                            <span class="inline-flex items-center">
                                {{ $country }}
                            </span>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-red-600">
                            {{ number_format($events) }}
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            @php $percentage = $totalCountry > 0 ? ($events / $totalCountry) * 100 : 0; @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-16 text-right">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">Tidak ada data security events by country</p>
        @endif
    </div>

    <!-- Information Box -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-900">Informasi Security Events</p>
                <ul class="text-sm text-blue-800 mt-2 list-disc list-inside space-y-1">
                    <li><strong>Block:</strong> Request diblokir sepenuhnya</li>
                    <li><strong>Challenge:</strong> User diminta verifikasi CAPTCHA</li>
                    <li><strong>JS Challenge:</strong> User diminta verifikasi JavaScript</li>
                    <li><strong>Managed Challenge:</strong> Cloudflare menentukan jenis tantangan otomatis</li>
                    <li><strong>Log:</strong> Event dicatat tapi tidak diblokir</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
