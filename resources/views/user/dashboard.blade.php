@extends('layouts.authenticated')
@section('title', '- Dashboard Pengguna')
@section('header-title', 'Dashboard Pengguna')

@php
    $user = auth()->user();
    $layanan = app(\App\Services\SidebarMenuService::class)->sectionItems('layanan');

    $now = now();
    $jam = (int) $now->format('G');
    $sapaan = match (true) {
        $jam < 11 => 'Selamat pagi',
        $jam < 15 => 'Selamat siang',
        $jam < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $namaDepan = \Illuminate\Support\Str::of($user->name)->before(' ')->value() ?: $user->name;

    // Jam layanan DKISP (WITA): Senin–Kamis 07.30–16.00, Jumat 07.30–16.30
    $menit = $jam * 60 + (int) $now->format('i');
    $tutup = match ((int) $now->dayOfWeekIso) {
        1, 2, 3, 4 => 16 * 60,
        5 => 16 * 60 + 30,
        default => null,
    };
    $layananBuka = $tutup !== null && $menit >= 7 * 60 + 30 && $menit < $tutup;

    $statCards = [
        ['label' => 'Total Permohonan', 'value' => $total, 'icon' => 'inbox', 'tone' => 'bg-slate-100 text-slate-700'],
        ['label' => 'Menunggu', 'value' => $waiting, 'icon' => 'clock', 'tone' => 'bg-amber-100 text-amber-700'],
        ['label' => 'Dalam Proses', 'value' => $processing, 'icon' => 'arrow-path', 'tone' => 'bg-sky-100 text-sky-700'],
        ['label' => 'Selesai', 'value' => $finished, 'icon' => 'check-circle', 'tone' => 'bg-emerald-100 text-emerald-700'],
    ];

    $statusBadge = [
        'Menunggu' => 'bg-amber-100 text-amber-800',
        'Dalam Proses' => 'bg-sky-100 text-sky-800',
        'Ditolak' => 'bg-red-100 text-red-800',
        'Selesai' => 'bg-emerald-100 text-emerald-800',
    ];
@endphp

@section('content')
    <div class="max-w-full overflow-hidden space-y-5">

        <!-- Sapaan -->
        <div class="rounded-xl bg-gradient-to-r from-emerald-700 to-green-600 p-5 text-white md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0">
                    <p class="text-sm text-emerald-100">{{ $now->translatedFormat('l, d F Y') }}</p>
                    <h2 class="mt-1 text-xl font-bold md:text-2xl">{{ $sapaan }}, {{ $namaDepan }}!</h2>
                    <p class="mt-1 text-sm text-emerald-50/90">
                        {{ $user->unitKerja?->nama ?? 'Selamat datang di E-Layanan DKISP Kalimantan Utara.' }}
                    </p>
                </div>
                @if ($user->is_verified)
                    <div class="rounded-lg bg-white/15 px-4 py-3 text-sm md:min-w-[16rem]">
                        <p class="flex items-center gap-2 font-semibold">
                            <span class="h-2.5 w-2.5 rounded-full {{ $layananBuka ? 'bg-lime-300' : 'bg-gray-300' }}"></span>
                            {{ $layananBuka ? 'Layanan sedang buka' : 'Di luar jam layanan' }}
                        </p>
                        <p class="mt-1 text-xs text-emerald-50/90">Senin–Kamis 07.30–16.00 &middot; Jumat 07.30–16.30 WITA</p>
                        @unless ($layananBuka)
                            <p class="mt-1 text-xs text-emerald-50/80">Permohonan tetap bisa diajukan dan diproses pada hari kerja berikutnya.</p>
                        @endunless
                    </div>
                @endif
            </div>
        </div>

        <!-- Akun belum diverifikasi -->
        @if (!$user->is_verified)
            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800" role="alert">
                <x-nav-icon name="exclamation" class="w-6 h-6 flex-shrink-0 text-red-500" />
                <div class="text-sm">
                    <p class="font-semibold text-base">Akun Anda belum diverifikasi</p>
                    <p class="mt-1">Untuk mengakses layanan digital, hubungi Administrator DKISP:</p>
                    <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 font-medium">
                        <a href="mailto:bidang.aptika@kaltaraprov.go.id" class="underline decoration-red-300 hover:text-red-900">bidang.aptika@kaltaraprov.go.id</a>
                        <a href="https://wa.me/6282253731353" target="_blank" rel="noopener" class="underline decoration-red-300 hover:text-red-900">+62 822-5373-1353 (WhatsApp)</a>
                    </p>
                </div>
            </div>
        @endif

        @if (session('unverified_block'))
            <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm font-medium text-amber-800" role="alert">
                <x-nav-icon name="exclamation" class="w-5 h-5 flex-shrink-0" />
                {{ session('unverified_block') }}
            </div>
        @endif

        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                <strong class="font-semibold">Sukses!</strong> {{ session('success') }}
            </div>
        @endif

        <!-- Ringkasan permohonan -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ($statCards as $card)
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-4">
                    <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg {{ $card['tone'] }}">
                        <x-nav-icon :name="$card['icon']" class="w-6 h-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Ajukan layanan -->
        @if ($layanan)
            <section>
                <div class="mb-3 flex items-end justify-between gap-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Ajukan Layanan</h2>
                        <p class="text-xs text-gray-500">Pilih layanan untuk melihat riwayat atau membuat permohonan baru.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($layanan as $item)
                        <div class="group flex gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-emerald-300 hover:shadow-sm">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white transition">
                                <x-nav-icon :name="$item['icon']" class="w-5 h-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <a href="{{ $item['url'] }}" class="block font-medium text-gray-900 hover:text-emerald-700">{{ $item['label'] }}</a>
                                @if ($item['children'])
                                    <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1">
                                        @foreach ($item['children'] as $child)
                                            <a href="{{ $child['url'] }}" class="text-xs text-gray-500 hover:text-emerald-700 hover:underline">{{ $child['label'] }}</a>
                                        @endforeach
                                    </div>
                                @else
                                    <a href="{{ $item['url'] }}" class="mt-1 inline-flex items-center gap-1 text-xs text-gray-500 hover:text-emerald-700">
                                        Buka layanan <x-nav-icon name="arrow-right" class="w-3 h-3" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <!-- Permohonan terbaru -->
            <section class="min-w-0 rounded-xl border border-gray-200 p-4 xl:col-span-2">
                <h2 class="mb-3 text-base font-semibold text-gray-900">Permohonan Terbaru</h2>
                <div class="max-w-full overflow-x-auto">
                    <table id="requests-table" class="w-full table-auto text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">Nomor Tiket</th>
                                <th class="px-3 py-2 text-left font-medium">Layanan</th>
                                <th class="px-3 py-2 text-left font-medium">Status</th>
                                <th class="px-3 py-2 text-left font-medium">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-xs">{{ $request->ticket_no ?? $request->ticket_number ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="block text-sm text-gray-800">{{ $request->service_name ?? $request->service ?? '-' }}</span>
                                        <span class="text-[11px] text-gray-400">{{ $request->service_type ?? 'Manual' }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $status = $request->normalized_status ?? $request->status;
                                        @endphp
                                        <span class="inline-block whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge[$status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-600" data-order="{{ $request->created_at->timestamp }}">
                                        {{ $request->created_at->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-10 text-center text-sm text-gray-500">
                                        <x-nav-icon name="inbox" class="mx-auto mb-2 w-8 h-8 text-gray-300" />
                                        Belum ada permohonan. Pilih salah satu layanan di atas untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Grafik status bulan ini -->
            <section class="rounded-xl border border-gray-200 p-4">
                <h2 class="text-base font-semibold text-gray-900">Status Permohonan</h2>
                <p class="mb-3 text-xs text-gray-500">Bulan {{ $now->translatedFormat('F Y') }}</p>
                @if (array_sum($summary))
                    <div class="max-w-full" style="height: 260px;">
                        <canvas id="chartStatus"></canvas>
                    </div>
                @else
                    <div class="flex h-[200px] flex-col items-center justify-center text-center text-sm text-gray-500">
                        <x-nav-icon name="chart-pie" class="mb-2 w-8 h-8 text-gray-300" />
                        Belum ada permohonan bulan ini.
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
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
            // Hanya inisialisasi DataTable bila ada baris data (bukan baris kosong ber-colspan)
            var dataRows = $('#requests-table tbody tr').filter(function() {
                return $(this).find('td[colspan]').length === 0;
            });

            if (dataRows.length > 0) {
                $('#requests-table').DataTable({
                    order: [[3, 'desc']],
                    pageLength: 5,
                    lengthMenu: [5, 10, 25],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_',
                        info: '_START_–_END_ dari _TOTAL_',
                        infoEmpty: '0 data',
                        infoFiltered: '(disaring dari _MAX_)',
                        paginate: { previous: '‹', next: '›' },
                        emptyTable: 'Belum ada permohonan',
                        zeroRecords: 'Tidak ada data yang cocok'
                    }
                });
            }
        });

        const chartCanvas = document.getElementById('chartStatus');
        if (chartCanvas) {
            new Chart(chartCanvas.getContext('2d'), {
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
        }
    </script>
@endpush
