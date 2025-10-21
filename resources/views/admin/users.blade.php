@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Manajemen User</h1>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <table id="users-table" class="min-w-full table-auto border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left border border-gray-300">Nama</th>
                    <th class="px-4 py-2 text-left border border-gray-300">NIK</th>
                    <th class="px-4 py-2 text-left border border-gray-300">No. Telepon</th>
                    <th class="px-4 py-2 text-left border border-gray-300">Email</th>
                    <th class="px-4 py-2 text-left border border-gray-300">Role</th>
                    <th class="px-4 py-2 text-left border border-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b">
                        <td class="px-4 py-2 border border-gray-300">{{ $user->name }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->nik ?? '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->phone ?? '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->email }}</td>
                        <td class="px-4 py-2 border border-gray-300 capitalize">{{ $user->role }}</td>

                        <td class="px-4 py-2 border border-gray-300">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Badge status verifikasi --}}
                                @if ($user->is_verified)
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-3 py-1 text-xs font-semibold">
                                        ✔ Terverifikasi
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-yellow-100 text-yellow-800 px-3 py-1 text-xs font-semibold">
                                        ⏳ Belum Diverifikasi
                                    </span>
                                @endif

                                {{-- Tombol Verifikasi / Batalkan --}}
                                @if (!$user->is_verified)
                                    <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                                        onsubmit="return confirm('Verifikasi user ini?');">
                                        @csrf
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                            Verifikasi
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.unverify', $user) }}"
                                        onsubmit="return confirm('Batalkan verifikasi user ini?');">
                                        @csrf
                                        <button type="submit"
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif

                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    Edit
                                </a>

                                {{-- Hapus --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Jika pakai DataTables, pastikan sudah load jQuery & plugin-nya di layout admin
            $('#users-table').DataTable({
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
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
