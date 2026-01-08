@extends('layouts.authenticated')

@section('title', '- Statistik Video Konferensi')
@section('header-title', 'Statistik Video Konferensi')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Statistik Video Konferensi</h1>

        {{-- Year Filter --}}
        <form method="GET" class="flex gap-2">
            <select name="year" class="border-gray-300 rounded-md shadow-sm">
                @for($y = 2024; $y <= 2026; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
            <div class="text-sm font-medium mb-1">Total Video Konferensi</div>
            <div class="text-3xl font-bold">{{ $totalVidcon }}</div>
            <div class="text-xs mt-1 opacity-80">Tahun {{ $year }}</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
            <div class="text-sm font-medium mb-1">Total Platform</div>
            <div class="text-3xl font-bold">{{ $platformStats->count() }}</div>
            <div class="text-xs mt-1 opacity-80">Platform berbeda</div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
            <div class="text-sm font-medium mb-1">Total Instansi</div>
            <div class="text-3xl font-bold">{{ $instansiStats->count() }}</div>
            <div class="text-xs mt-1 opacity-80">Top 10 instansi</div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow p-6 text-white">
            <div class="text-sm font-medium mb-1">Total Operator</div>
            <div class="text-3xl font-bold">{{ $operatorStats->count() }}</div>
            <div class="text-xs mt-1 opacity-80">Operator aktif</div>
        </div>
    </div>

    {{-- Monthly Chart --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Grafik Bulanan</h2>
        <canvas id="monthlyChart" height="80"></canvas>
    </div>

    {{-- Platform and Instansi Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Platform Stats --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Statistik Platform</h2>
            <canvas id="platformChart"></canvas>
        </div>

        {{-- Instansi Stats --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Top 10 Instansi</h2>
            <div class="space-y-3">
                @foreach($instansiStats as $stat)
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($stat->nama_instansi, 40) }}</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($stat->total / $totalVidcon) * 100 }}%"></div>
                        </div>
                    </div>
                    <div class="ml-4 text-lg font-bold text-blue-600">{{ $stat->total }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Operator Stats --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Statistik Operator</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operator</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persentase</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($operatorStats as $stat)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $stat->operator }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $stat->total }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($stat->total / $totalVidcon) * 100 }}%"></div>
                                </div>
                                <span class="ml-2 text-gray-600">{{ number_format(($stat->total / $totalVidcon) * 100, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Video Konferensi',
                data: @json(array_values($monthlyData)),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Platform Chart
    const platformCtx = document.getElementById('platformChart').getContext('2d');
    new Chart(platformCtx, {
        type: 'doughnut',
        data: {
            labels: @json($platformStats->pluck('platform')),
            datasets: [{
                data: @json($platformStats->pluck('total')),
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)',
                    'rgb(236, 72, 153)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection
