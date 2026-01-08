@extends('layouts.authenticated')

@section('title', '- User Management')
@section('header-title', 'User Management')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Manajemen User</h1>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Add User Button --}}
        <div class="mb-4 flex justify-end">
            <a href="{{ route('admin.users.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-semibold">
                + Tambah User
            </a>
        </div>

        {{-- Search and Filter Form --}}
        <form method="GET" action="{{ route('admin.users') }}" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari (Nama / NIP / NIK / Email / Telepon)</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Masukkan kata kunci..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Filter Role</label>
                    <select
                        id="role"
                        name="role"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Semua Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-12 py-2 rounded-lg text-sm font-medium whitespace-nowrap">
                        Filter
                    </button>
                    <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center bg-gray-500 hover:bg-gray-600 text-white px-12 py-2 rounded-lg text-sm font-medium whitespace-nowrap">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <table id="users-table" class="min-w-full table-auto border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left border border-gray-300">Nama</th>
                    <th class="px-4 py-2 text-left border border-gray-300">NIP</th>
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
                        <td class="px-4 py-2 border border-gray-300">{{ $user->nip ?? '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->nik ?? '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->phone ?? '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->email }}</td>
                        <td class="px-4 py-2 border border-gray-300">
                            @if($user->roles->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="inline-block px-2 py-1 text-xs rounded
                                            {{ $role->name === 'Admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $role->name === 'User' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $role->name === 'Operator-Vidcon' ? 'bg-green-100 text-green-800' : '' }}
                                        ">
                                            {{ $role->display_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">Tidak ada role</span>
                            @endif
                        </td>

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
            // Jika pakai DataTables, disable built-in search karena kita sudah punya form filter
            $('#users-table').DataTable({
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                searching: false, // Disable DataTables search, use our custom filter instead
                language: {
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Tidak ditemukan data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
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
