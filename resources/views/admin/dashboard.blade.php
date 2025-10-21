@extends('layouts.admin')




@section('content')
    <div class="max-w-7xl mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-8">Dashboard Admin - Permohonan Layanan</h1>

        <!-- Kartu Ringkasan -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
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

        <!-- Grafik Kinerja -->
        <div class="bg-white p-6 rounded-lg shadow mb-10">
            <p class="text-lg font-semibold mb-4 text-gray-700">Grafik Kinerja Permohonan Bulan Ini</p>
            <canvas id="chartStatus" height="100"></canvas>
        </div>
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        <!-- Tabel Permohonan -->
        <div class="bg-white shadow p-6 rounded-lg">
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
            $('#requests-table').DataTable();
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
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
