@extends('layouts.authenticated')

@section('title', '- Master Data Instansi')
@section('header-title', 'Master Data Instansi')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Master Data Instansi</h1>
            <p class="text-gray-600 mt-1">Kelola data instansi / perangkat daerah</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
            <a href="{{ route('admin.unit-kerja.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Instansi
            </a>
        </div>

        <div class="overflow-x-auto">
            <table id="unitKerjaTable" class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-3 text-left w-20">No</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Nama Instansi</th>
                        <th class="border border-gray-300 px-4 py-3 text-left w-64">Tipe</th>
                        <th class="border border-gray-300 px-4 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitKerjas as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $loop->iteration }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="font-semibold">{{ $item->nama }}</div>
                            @if($item->singkatan)
                                <div class="text-xs text-gray-500">{{ $item->singkatan }}</div>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                @if($item->tipe === 'Induk Perangkat Daerah') bg-blue-100 text-blue-800
                                @elseif($item->tipe === 'Cabang Perangkat Daerah') bg-purple-100 text-purple-800
                                @elseif($item->tipe === 'Sekolah') bg-green-100 text-green-800
                                @elseif($item->tipe === 'Instansi Pusat/Lainnya') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $item->tipe }}
                            </span>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="flex items-center gap-2 justify-center">
                                <a href="{{ route('admin.unit-kerja.edit', $item) }}"
                                   class="text-blue-600 hover:text-blue-800"
                                   title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                <form action="{{ route('admin.unit-kerja.destroy', $item) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus {{ $item->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800"
                                            title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                            Belum ada data instansi. <a href="{{ route('admin.unit-kerja.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-semibold text-blue-900 mb-2">Kategori Tipe:</h3>
            <ul class="text-sm space-y-1">
                <li><span class="font-semibold">Induk Perangkat Daerah:</span> Badan, Dinas, Sekretariat, Inspektorat, Satpol PP, RSUD, Biro</li>
                <li><span class="font-semibold">Cabang Perangkat Daerah:</span> UPT, UPTD, Cabang Dinas</li>
                <li><span class="font-semibold">Sekolah:</span> SMA, SMK, SLB</li>
                <li><span class="font-semibold">Instansi Pusat/Lainnya:</span> Kementerian, Lembaga Pusat, atau instansi lainnya di luar kategori di atas</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#unitKerjaTable').DataTables({
            pageLength: 50,
            order: [[2, 'asc'], [1, 'asc']], // Sort by Tipe, then Nama
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                zeroRecords: "Tidak ada data yang cocok"
            }
        });
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@endsection
