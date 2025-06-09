@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Manajemen User</h1>

        <table id="users-table" class="min-w-full table-auto border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left border border-gray-300">Nama</th>
                    <th class="px-4 py-2 text-left border border-gray-300">NIK</th> <!-- Tambahan -->
                    <th class="px-4 py-2 text-left border border-gray-300">No. Telepon</th> <!-- Tambahan -->
                    <th class="px-4 py-2 text-left border border-gray-300">Email</th>
                    <th class="px-4 py-2 text-left border border-gray-300">Role</th>
                    <th class="px-4 py-2 text-left border border-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b">
                        <td class="px-4 py-2 border border-gray-300">{{ $user->name }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->nik ?? '-' }}</td> <!-- Tambahan -->
                        <td class="px-4 py-2 border border-gray-300">{{ $user->phone ?? '-' }}</td> <!-- Tambahan -->
                        <td class="px-4 py-2 border border-gray-300">{{ $user->email }}</td>
                        <td class="px-4 py-2 border border-gray-300 capitalize">{{ $user->role }}</td>
                        <td class="px-4 py-2 border border-gray-300 flex gap-2">
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#users-table').DataTable({
                pageLength: 25, // default tampil 25 data
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // pilihan dropdown
                language: {
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Tidak ditemukan data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
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