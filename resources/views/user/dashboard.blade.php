@extends('layouts.authenticated')
@section('title', '- Dashboard Pengguna')
@section('header-title', 'Dashboard Pengguna')

@section('content')
    <div class="max-w-full overflow-hidden">
        <h1 class="text-xl font-bold mb-3">Dashboard Pengguna</h1>

        <!-- Warning: Akun Belum Diverifikasi -->
        @if (!auth()->user()->is_verified)
            <div class="max-w-full bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg shadow" role="alert">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-3 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="font-bold text-base mb-1">⚠️ Akun Anda Belum Diverifikasi</p>
                        <p class="text-sm mb-2">
                            Untuk mengakses layanan digital, hubungi Administrator DKISP.
                        </p>
                        <div class="bg-white bg-opacity-50 rounded p-2 text-xs">
                            <p class="font-semibold mb-1">Kontak:</p>
                            <p>📧 bidang.aptika@kaltaraprov.go.id • 📱 +6282253731353</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Session Messages -->
        @if (session('unverified_block'))
            <div class="max-w-full bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 mb-4 rounded-r-lg" role="alert">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm font-medium">{{ session('unverified_block') }}</p>
                </div>
            </div>
        @endif

        <!-- Operating Hours Information -->
        @if (auth()->user()->is_verified)
            <div class="max-w-full bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-500 rounded-lg p-4 mb-4 shadow">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-blue-800 mb-2">⏰ Jam Layanan DKISP</h3>
                        <div class="text-blue-700 text-sm space-y-1">
                            <p><span class="font-semibold">Senin s.d. Kamis:</span> 07.30 - 16.00 WITA</p>
                            <p><span class="font-semibold">Jumat:</span> 07.30 - 16.30 WITA</p>
                        </div>
                        <p class="text-xs text-blue-600 mt-2 italic">
                            Permohonan yang masuk di luar jam layanan akan diproses pada hari kerja berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Kartu Ringkasan -->
        <div class="max-w-full grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-green-100 p-3 rounded-lg text-center shadow">
                <p class="text-xs text-gray-700">Total Permohonan</p>
                <p class="text-xl font-bold text-green-800">{{ $total }}</p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-lg text-center shadow">
                <p class="text-xs text-gray-700">Menunggu</p>
                <p class="text-xl font-bold text-yellow-800">{{ $waiting }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg text-center shadow">
                <p class="text-xs text-gray-700">Dalam Proses</p>
                <p class="text-xl font-bold text-blue-800">{{ $processing }}</p>
            </div>
            <div class="bg-emerald-100 p-3 rounded-lg text-center shadow">
                <p class="text-xs text-gray-700">Selesai</p>
                <p class="text-xl font-bold text-emerald-800">{{ $finished }}</p>
            </div>
        </div>

        <!-- Grafik Kinerja -->
        <div class="max-w-full bg-white p-3 rounded-lg shadow mb-4">
            <p class="text-sm font-semibold mb-2 text-gray-700">Grafik Permohonan Bulan {{ now()->translatedFormat('F') }}
            </p>
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
        <div class="max-w-full bg-white shadow p-3 rounded-lg overflow-hidden">
            <h2 class="text-sm font-semibold mb-2 text-gray-700">10 Permohonan Terbaru</h2>
            <div class="max-w-full overflow-x-auto">
                <table id="requests-table" class="w-full table-auto text-sm">
                    <thead>
                        <tr>
                            <th class="px-2 py-2 text-left">Nomor Tiket</th>
                            <th class="px-2 py-2 text-left">Jenis</th>
                            <th class="px-2 py-2 text-left">Layanan</th>
                            <th class="px-2 py-2 text-left">Status</th>
                            <th class="px-2 py-2 text-left">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-2 py-2 font-mono text-xs">{{ $request->ticket_no ?? $request->ticket_number ?? '-' }}</td>
                                <td class="px-2 py-2">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-semibold
                                        {{ $request->service_type == 'Digital' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $request->service_type ?? 'Manual' }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-xs">{{ $request->service_name ?? $request->service ?? '-' }}</td>
                                <td class="px-2 py-2">
                                    <span
                                        class="inline-block px-1.5 py-0.5 rounded text-xs font-semibold
                                        {{ $request->normalized_status == 'Menunggu' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                        {{ $request->normalized_status == 'Dalam Proses' ? 'bg-blue-200 text-blue-800' : '' }}
                                        {{ $request->normalized_status == 'Ditolak' ? 'bg-red-200 text-red-800' : '' }}
                                        {{ $request->normalized_status == 'Selesai' ? 'bg-green-200 text-green-800' : '' }}">
                                        {{ $request->normalized_status ?? $request->status }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-xs whitespace-nowrap">{{ $request->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                        @if($requests->count() == 0)
                            <tr>
                                <td colspan="5" class="px-2 py-6 text-center text-gray-500 text-sm">
                                    Belum ada permohonan yang dibuat
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
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
            // Only initialize DataTable if table has actual data rows (not empty state)
            // Check if there are rows without colspan attribute (data rows)
            var dataRows = $('#requests-table tbody tr').filter(function() {
                return $(this).find('td[colspan]').length === 0;
            });

            if (dataRows.length > 0) {
                $('#requests-table').DataTable({
                    "language": {
                        "emptyTable": "Belum ada permohonan",
                        "zeroRecords": "Tidak ada data yang cocok"
                    }
                });
            }
        });

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
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endpush
