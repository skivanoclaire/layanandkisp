@extends('layouts.authenticated')

@section('title', '- Permohonan Admin')
@section('header-title', 'Permohonan Admin')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Daftar Permohonan Layanan</h1>

        <table id="permohonan-table" class="min-w-full table-auto border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border border-gray-300">Nama User</th>
                    <th class="px-4 py-2 border border-gray-300">Nomor Tiket</th>
                    <th class="px-4 py-2 border border-gray-300">Layanan</th>
                    <th class="px-4 py-2 border border-gray-300">Status</th>
                    <th class="px-4 py-2 border border-gray-300">Surat</th>
                    <th class="px-4 py-2 border border-gray-300">Dibuat</th>
                    <th class="px-4 py-2 border border-gray-300">Diperbarui</th>
                    <th class="px-4 py-2 border border-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border border-gray-300">{{ $request->user->name }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $request->ticket_number }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $request->service }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $request->status }}</td>
                        <td class="px-4 py-2 border border-gray-300">
                            @if ($request->file)
                                <a href="{{ asset('storage/' . $request->file) }}" target="_blank"
                                    class="text-emerald-500 underline">Lihat Surat</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border border-gray-300">{{ $request->created_at->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $request->updated_at->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <form method="POST" action="{{ route('admin.update-status', $request) }}">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="border p-1 rounded">
                                    <option {{ $request->status == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option {{ $request->status == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                    <option {{ $request->status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                                    <option {{ $request->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </form>
                            <form method="POST" action="{{ route('admin.delete-request', $request) }}"
                                onsubmit="return confirm('Hapus permohonan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <!-- jQuery dan DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#permohonan-table').DataTable({
                pageLength: 25, // Default 25 entri
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ], // Pilihan dropdown
                language: {
                    lengthMenu: "Tampilkan _MENU_ entri per halaman",
                    zeroRecords: "Tidak ditemukan data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari total _MAX_ entri)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "›",
                        previous: "‹"
                    }
                }
            });
        });
    </script>
@endpush
