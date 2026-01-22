@extends('layouts.authenticated')

@section('title', '- Statistik Survei Kepuasan')
@section('header-title', 'Statistik Survei Kepuasan')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Statistik & Analitik Survei Kepuasan</h1>
                <p class="text-sm text-gray-600 mt-1">Analisis mendalam tentang kepuasan pengguna terhadap layanan digital</p>
            </div>
            <a href="{{ route('admin.survei-kepuasan.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>

        {{-- Average Ratings by Category --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Rata-rata Rating per Kategori</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $categories = [
                        ['label' => 'Kecepatan', 'value' => $avgRatings['kecepatan'], 'color' => 'blue'],
                        ['label' => 'Kemudahan', 'value' => $avgRatings['kemudahan'], 'color' => 'green'],
                        ['label' => 'Kualitas', 'value' => $avgRatings['kualitas'], 'color' => 'purple'],
                        ['label' => 'Responsif', 'value' => $avgRatings['responsif'], 'color' => 'yellow'],
                        ['label' => 'Keamanan', 'value' => $avgRatings['keamanan'], 'color' => 'red'],
                        ['label' => 'Keseluruhan', 'value' => $avgRatings['keseluruhan'], 'color' => 'indigo'],
                    ];
                @endphp

                @foreach ($categories as $category)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $category['label'] }}</span>
                            <span class="text-2xl font-bold text-{{ $category['color'] }}-600">{{ $category['value'] ?? 'N/A' }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-{{ $category['color'] }}-500 rounded-full h-2" style="width: {{ ($category['value'] / 5) * 100 }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-gray-500">1</span>
                            <span class="text-xs text-gray-500">5</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top Rated Subdomains --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Subdomain dengan Rating Tertinggi</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ranking</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subdomain</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aplikasi/Instansi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Survei</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($topSubdomains as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        @if($index < 3)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $index === 1 ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $index === 2 ? 'bg-orange-100 text-orange-800' : '' }}
                                                font-bold">
                                                {{ $index + 1 }}
                                            </span>
                                        @else
                                            <span class="text-gray-600 font-medium">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $item->webMonitor->subdomain }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600">
                                        {{ $item->webMonitor->nama_aplikasi ?? $item->webMonitor->nama_instansi ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900">{{ $item->survey_count }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <span class="text-lg font-bold text-green-600 mr-2">{{ number_format($item->avg_rating, 2) }}</span>
                                        <div class="flex">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= round($item->avg_rating) ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Belum ada data survei
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Feedback --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Feedback Terbaru</h2>
            <div class="space-y-4">
                @forelse ($recentFeedback as $feedback)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $feedback->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $feedback->webMonitor->subdomain }} • {{ $feedback->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center">
                                <span class="text-lg font-bold text-green-600 mr-1">{{ $feedback->average_rating }}</span>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @if($feedback->kelebihan)
                                <div class="bg-green-50 border-l-4 border-green-400 p-3">
                                    <p class="text-xs font-medium text-green-800 mb-1">Kelebihan</p>
                                    <p class="text-sm text-green-700">{{ $feedback->kelebihan }}</p>
                                </div>
                            @endif
                            @if($feedback->kekurangan)
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3">
                                    <p class="text-xs font-medium text-yellow-800 mb-1">Kekurangan</p>
                                    <p class="text-sm text-yellow-700">{{ $feedback->kekurangan }}</p>
                                </div>
                            @endif
                            @if($feedback->saran)
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-3">
                                    <p class="text-xs font-medium text-blue-800 mb-1">Saran</p>
                                    <p class="text-sm text-blue-700">{{ $feedback->saran }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-8">Belum ada feedback</p>
                @endforelse
            </div>
        </div>

        {{-- Monthly Trend --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tren Bulanan (6 Bulan Terakhir)</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Survei</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($monthlyTrend as $trend)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::create($trend->year, $trend->month)->isoFormat('MMMM YYYY') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $trend->count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <span class="text-sm font-bold text-green-600 mr-2">{{ number_format($trend->avg_rating, 2) }}</span>
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 rounded-full h-2" style="width: {{ ($trend->avg_rating / 5) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
