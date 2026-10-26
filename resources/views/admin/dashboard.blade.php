@extends('layouts.authenticated')
@section('title', '- Dashboard Admin')
@section('header-title', 'Dashboard Admin')

@php
    $admin = auth()->user();
    $menu = app(\App\Services\SidebarMenuService::class);
    $canSeePending = !empty($menu->pendingCounts());
    $pendingItems = $menu->pendingItems();
    $pendingTotal = $menu->pendingCounts()['total'] ?? 0;
    $canManageSubdomain = $admin->hasPermission('Manajemen Subdomain Terpadu');

    $subdomainService = app(\App\Services\SubdomainAggregatorService::class);
    $subdomainStats = $subdomainService->getStats();
    $problematicWebsites = $subdomainStats['down_websites'] > 0 ? $subdomainService->getProblematicWebsites() : collect();
    $chartData = $subdomainService->getChartData(6);

    $jam = (int) now()->format('G');
    $sapaan = match (true) {
        $jam < 11 => 'Selamat pagi',
        $jam < 15 => 'Selamat siang',
        $jam < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $namaDepan = \Illuminate\Support\Str::of($admin->name)->before(' ')->value() ?: $admin->name;

    $persen = fn ($n) => $total > 0 ? round($n / $total * 100) : 0;
    $statusCards = [
        ['label' => 'Total Permohonan', 'value' => $total, 'icon' => 'inbox', 'tone' => 'bg-slate-100 text-slate-700', 'note' => 'seluruh formulir digital'],
        ['label' => 'Menunggu', 'value' => $waiting, 'icon' => 'clock', 'tone' => 'bg-amber-100 text-amber-700', 'note' => $persen($waiting) . '% dari total'],
        ['label' => 'Dalam Proses', 'value' => $processing, 'icon' => 'arrow-path', 'tone' => 'bg-sky-100 text-sky-700', 'note' => $persen($processing) . '% dari total'],
        ['label' => 'Ditolak', 'value' => $rejected, 'icon' => 'x-circle', 'tone' => 'bg-red-100 text-red-700', 'note' => $persen($rejected) . '% dari total'],
        ['label' => 'Selesai', 'value' => $finished, 'icon' => 'check-circle', 'tone' => 'bg-emerald-100 text-emerald-700', 'note' => $persen($finished) . '% dari total'],
    ];
@endphp

@section('content')
    <div class="max-w-full overflow-hidden space-y-6">

        <!-- Sapaan -->
        <div class="rounded-xl bg-gradient-to-r from-emerald-700 to-green-600 p-5 text-white md:p-6">
            <p class="text-sm text-emerald-100">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h2 class="mt-1 text-xl font-bold md:text-2xl">{{ $sapaan }}, {{ $namaDepan }}!</h2>
            <p class="mt-1 text-sm text-emerald-50/90">
                @if (!$canSeePending)
                    Ringkasan permohonan layanan E-Layanan DKISP.
                @elseif ($pendingTotal > 0)
                    Ada <strong class="font-semibold text-white">{{ number_format($pendingTotal) }} permohonan</strong> yang menunggu tindak lanjut Anda.
                @else
                    Semua permohonan di layanan yang Anda kelola sudah ditindaklanjuti.
                @endif
            </p>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                <strong class="font-semibold">Sukses!</strong> {{ session('success') }}
            </div>
        @endif

        <!-- Perlu tindakan -->
        @if ($canSeePending)
            <section>
                <div class="mb-3">
                    <h2 class="text-base font-semibold text-gray-900">Perlu Tindakan</h2>
                    <p class="text-xs text-gray-500">Permohonan berstatus menunggu pada layanan yang Anda kelola.</p>
                </div>
                @if ($pendingItems)
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($pendingItems as $pending)
                            <a href="{{ $pending['url'] }}"
                                class="group flex items-center gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-amber-300 hover:bg-amber-50/40">
                                <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                    <x-nav-icon :name="$pending['icon']" class="w-5 h-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-gray-900">{{ $pending['label'] }}</span>
                                    <span class="block truncate text-xs text-gray-500">{{ $pending['group'] ?? 'Tinjau permohonan' }}</span>
                                </span>
                                <span class="text-2xl font-bold text-amber-600">{{ $pending['badge'] }}</span>
                                <x-nav-icon name="chevron-right" class="w-4 h-4 flex-shrink-0 text-gray-300 group-hover:text-amber-600" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        <x-nav-icon name="check-circle" class="w-6 h-6 flex-shrink-0 text-emerald-600" />
                        Tidak ada permohonan yang menunggu. Kerja bagus!
                    </div>
                @endif
            </section>
        @endif

        <!-- Ringkasan status permohonan -->
        <section>
            <h2 class="mb-3 text-base font-semibold text-gray-900">Ringkasan Permohonan</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                @foreach ($statusCards as $card)
                    <div class="rounded-xl border border-gray-200 p-4 {{ $loop->first ? 'col-span-2 md:col-span-1' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg {{ $card['tone'] }}">
                                <x-nav-icon :name="$card['icon']" class="w-5 h-5" />
                            </span>
                            <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                        <p class="text-xs text-gray-400">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Akun pengguna -->
        <section class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <x-nav-icon name="users" class="w-5 h-5" />
                    </span>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Akun Pengguna Terverifikasi</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($userStats['verified']) }}
                            <span class="text-sm font-normal text-gray-400">dari {{ number_format($userStats['total']) }} akun</span>
                        </p>
                    </div>
                </div>
                <dl class="mt-3 space-y-1 border-t border-gray-100 pt-3 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-600">ASN ber-NIP, unit kerja internal Kaltara</dt>
                        <dd class="font-semibold text-gray-800">{{ number_format($userStats['verified_internal_nip']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-600">Instansi Pusat/Lainnya</dt>
                        <dd class="font-semibold text-gray-800">{{ number_format($userStats['verified_pusat']) }}</dd>
                    </div>
                    @if ($userStats['verified_other'] > 0)
                        <div class="flex justify-between gap-2 text-xs text-gray-400">
                            <dt>Lainnya (tanpa NIP / tanpa unit kerja)</dt>
                            <dd>{{ number_format($userStats['verified_other']) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="flex flex-col justify-between rounded-xl border p-4 {{ $userStats['unverified'] > 0 ? 'border-amber-200 bg-amber-50/50' : 'border-gray-200' }}">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <x-nav-icon name="user" class="w-5 h-5" />
                    </span>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Pengguna Menunggu Verifikasi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($userStats['unverified']) }}</p>
                    </div>
                </div>
                @if ($admin->hasRole('Admin'))
                    <a href="{{ route('admin.users') }}"
                        class="mt-3 inline-flex items-center gap-1 self-start text-sm font-medium text-emerald-700 hover:text-emerald-800">
                        Verifikasi di Kelola Pengguna <x-nav-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                @else
                    <p class="mt-3 text-xs text-gray-500">Belum diverifikasi admin.</p>
                @endif
            </div>
        </section>

        <!-- Subdomain -->
        <section>
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-gray-900">Subdomain &amp; Monitoring Website</h2>
                @if ($canManageSubdomain)
                    <a href="{{ route('admin.unified-subdomain.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:text-emerald-800">
                        Kelola subdomain <x-nav-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500">Subdomain Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $subdomainStats['active_monitors'] }}</p>
                    <p class="text-xs text-gray-400">dari {{ $subdomainStats['total_monitors'] }} subdomain</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500">Permohonan Pending</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $subdomainStats['pending_requests'] }}</p>
                    <p class="text-xs text-gray-400">menunggu persetujuan</p>
                </div>
                <div class="rounded-xl border p-4 {{ $subdomainStats['down_websites'] > 0 ? 'border-red-200 bg-red-50/60' : 'border-gray-200' }}">
                    <p class="text-xs font-medium text-gray-500">Website Down</p>
                    <p class="mt-1 text-2xl font-bold {{ $subdomainStats['down_websites'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $subdomainStats['down_websites'] }}</p>
                    <p class="text-xs {{ $subdomainStats['down_websites'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $subdomainStats['down_websites'] > 0 ? 'Perlu perhatian!' : 'Semua normal' }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500">Perlu Pengecekan</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $subdomainStats['websites_needing_check'] }}</p>
                    <p class="text-xs text-gray-400">&gt;24 jam tidak dicek</p>
                </div>
            </div>

            @if ($problematicWebsites->count() > 0)
                <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-start gap-3">
                        <x-nav-icon name="exclamation" class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-600" />
                        <div class="min-w-0 flex-1">
                            <h3 class="mb-2 text-sm font-semibold text-red-800">Website Bermasalah</h3>
                            <div class="space-y-1">
                                @foreach ($problematicWebsites->take(5) as $website)
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <span class="truncate font-mono text-red-700">{{ $website->subdomain }}</span>
                                        <span class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">{{ ucfirst($website->status) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @if ($canManageSubdomain)
                                <a href="{{ route('admin.unified-subdomain.index', ['source' => 'all', 'status_monitoring' => 'active']) }}"
                                    class="mt-2 inline-block text-xs font-medium text-red-600 hover:text-red-700">
                                    Lihat semua &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <!-- Grafik -->
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="min-w-0 rounded-xl border border-gray-200 p-4 xl:col-span-2">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Pertumbuhan Subdomain</h2>
                        <p class="text-xs text-gray-500" id="periodLabel">6 bulan terakhir</p>
                    </div>
                    <div class="inline-flex flex-wrap rounded-lg bg-gray-100 p-1" role="group" aria-label="Periode grafik">
                        @foreach ([3 => '3 Bln', 6 => '6 Bln', 12 => '1 Thn', 24 => '2 Thn', 36 => '3 Thn', 48 => '4 Thn', 60 => '5 Thn'] as $months => $label)
                            <button type="button" onclick="changeChartPeriod({{ $months }}, this)" data-months="{{ $months }}"
                                class="period-btn rounded-md px-2.5 py-1 text-xs font-medium {{ $months === 6 ? 'is-active' : '' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="max-w-full" style="height: 300px;">
                    <canvas id="chartSubdomain"></canvas>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h2 class="text-base font-semibold text-gray-900">Status Permohonan</h2>
                <p class="mb-3 text-xs text-gray-500">Bulan {{ now()->translatedFormat('F Y') }}</p>
                <div class="max-w-full" style="height: 300px;">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </section>

        <!-- Progress formulir digital -->
        <section class="rounded-xl border border-gray-200 p-4">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Progress Formulir Digital</h2>
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-medium">Jenis Formulir</th>
                            <th class="px-3 py-2.5 text-center font-medium">Total</th>
                            <th class="px-3 py-2.5 text-center font-medium">Menunggu</th>
                            <th class="px-3 py-2.5 text-center font-medium">Proses</th>
                            <th class="px-3 py-2.5 text-center font-medium">Ditolak</th>
                            <th class="px-3 py-2.5 text-center font-medium">Selesai</th>
                            <th class="px-4 py-2.5 text-left font-medium">Penyelesaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($digitalFormStats as $key => $stats)
                            @php
                                $progressPercent = $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100) : 0;
                            @endphp
                            <tr class="{{ $stats['total'] > 0 ? 'hover:bg-gray-50' : 'text-gray-400' }}">
                                <td class="whitespace-nowrap px-4 py-2.5 font-medium {{ $stats['total'] > 0 ? 'text-gray-900' : '' }}">{{ $stats['label'] }}</td>
                                <td class="px-3 py-2.5 text-center font-semibold">{{ $stats['total'] }}</td>
                                @foreach (['menunggu' => 'bg-amber-100 text-amber-800', 'proses' => 'bg-sky-100 text-sky-800', 'ditolak' => 'bg-red-100 text-red-800', 'selesai' => 'bg-emerald-100 text-emerald-800'] as $col => $tone)
                                    <td class="px-3 py-2.5 text-center">
                                        @if ($stats[$col] > 0)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tone }}">{{ $stats[$col] }}</span>
                                        @else
                                            <span class="text-gray-300">–</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-28 rounded-full bg-gray-200">
                                            <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">{{ $progressPercent }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .period-btn { color: #4b5563; background: transparent; border: none !important; }
        .period-btn:hover { color: #111827; }
        .period-btn.is-active { background: #fff; color: #047857; box-shadow: 0 1px 2px rgba(0, 0, 0, .08); }
    </style>
@endpush

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Grafik pertumbuhan subdomain
        let chartSubdomain = new Chart(document.getElementById('chartSubdomain').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: {!! json_encode($chartData['datasets']) !!}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12 } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                elements: {
                    line: { tension: 0.4 }
                }
            }
        });

        const periodLabels = {
            3: '3 bulan terakhir',
            6: '6 bulan terakhir',
            12: '1 tahun terakhir',
            24: '2 tahun terakhir',
            36: '3 tahun terakhir',
            48: '4 tahun terakhir',
            60: '5 tahun terakhir'
        };

        function changeChartPeriod(months, button) {
            document.querySelectorAll('.period-btn').forEach(btn => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            document.getElementById('periodLabel').textContent = periodLabels[months];

            fetch(`{{ route('admin.dashboard.chart-data') }}?months=${months}`)
                .then(response => response.json())
                .then(data => {
                    chartSubdomain.data.labels = data.labels;
                    chartSubdomain.data.datasets = data.datasets;
                    chartSubdomain.update();
                })
                .catch(error => console.error('Gagal memuat data grafik:', error));
        }

        // Grafik status permohonan bulan ini
        new Chart(document.getElementById('chartStatus').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Menunggu', 'Dalam Proses', 'Ditolak', 'Selesai'],
                datasets: [{
                    data: [
                        {{ $summary['Menunggu'] ?? 0 }},
                        {{ $summary['Dalam Proses'] ?? 0 }},
                        {{ $summary['Ditolak'] ?? 0 }},
                        {{ $summary['Selesai'] ?? 0 }},
                    ],
                    backgroundColor: ['#f59e0b', '#0ea5e9', '#ef4444', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } }
                }
            }
        });
    </script>
@endpush
