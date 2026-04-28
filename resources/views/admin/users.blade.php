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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                <div>
                    <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-2">Filter Unit Kerja</label>
                    <select
                        id="unit_kerja_id"
                        name="unit_kerja_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Semua Unit Kerja --</option>
                        @foreach($unitKerjas as $uk)
                            <option value="{{ $uk->id }}" {{ request('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                                {{ $uk->nama }}
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
                    <th class="px-4 py-2 text-left border border-gray-300">Jabatan</th>
                    <th class="px-4 py-2 text-left border border-gray-300">Unit Kerja</th>
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
                        <td class="px-4 py-2 border border-gray-300">{{ $user->jabatan->nama_jabatan ?? '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $user->unitKerja->nama ?? '-' }}</td>
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

                                {{-- Cek Data via SIMPEG — buka modal AJAX --}}
                                @if(!empty($user->nik))
                                    <button type="button"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm js-cek-data"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        title="Cek & sinkron data user dengan SIMPEG">
                                        Cek Data
                                    </button>
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

    {{-- Flash hasil sinkron (dipakai oleh modal Cek Data) --}}
    <div id="cek-data-flash" class="fixed top-4 right-4 z-[60] hidden max-w-md rounded border px-4 py-3 text-sm shadow-lg"></div>

    {{-- Modal Cek Data via SIMPEG (admin → user lain) --}}
    <div id="cek-data-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-bold text-gray-800">
                    Sinkron Data dari SIMPEG <span id="cek-data-username" class="font-normal text-gray-500"></span>
                </h3>
                <button type="button" id="cek-data-close" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <div class="overflow-y-auto px-6 py-4 flex-1">
                <div id="cek-data-loading" class="text-center py-8 text-gray-600">
                    <svg class="animate-spin w-8 h-8 mx-auto mb-3 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mengambil data dari SIMPEG...
                </div>
                <div id="cek-data-error" class="hidden rounded border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm"></div>
                <div id="cek-data-result" class="hidden"></div>
            </div>

            <div class="border-t px-6 py-3 flex items-center justify-end gap-2 bg-gray-50">
                <button type="button" id="cek-data-cancel" class="px-4 py-2 text-sm border rounded hover:bg-gray-100">Batal</button>
                <button type="button" id="cek-data-apply" class="hidden px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded font-semibold">
                    Terapkan yang Dicentang
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
    (function() {
        const modal     = document.getElementById('cek-data-modal');
        const loading   = document.getElementById('cek-data-loading');
        const errorBox  = document.getElementById('cek-data-error');
        const resultBox = document.getElementById('cek-data-result');
        const btnClose  = document.getElementById('cek-data-close');
        const btnCancel = document.getElementById('cek-data-cancel');
        const btnApply  = document.getElementById('cek-data-apply');
        const usernameLabel = document.getElementById('cek-data-username');
        const flashBox  = document.getElementById('cek-data-flash');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                       || document.querySelector('input[name="_token"]')?.value;

        const checkUrl = "{{ url('/admin/simpeg-check/api') }}"; // + /{userId}/check
        const applyUrl = "{{ url('/admin/simpeg-check/api') }}"; // + /{userId}/apply

        let currentUserId = null;
        let lastSimpeg = null;

        function openModal(userId, userName) {
            currentUserId = userId;
            usernameLabel.textContent = userName ? '— ' + userName : '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            loading.classList.remove('hidden');
            errorBox.classList.add('hidden');
            resultBox.classList.add('hidden');
            btnApply.classList.add('hidden');
        }
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentUserId = null;
            lastSimpeg = null;
        }
        function showFlash(type, message) {
            flashBox.textContent = message;
            flashBox.className = 'fixed top-4 right-4 z-[60] max-w-md rounded border px-4 py-3 text-sm shadow-lg '
                + (type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800');
            flashBox.classList.remove('hidden');
            setTimeout(() => flashBox.classList.add('hidden'), 5000);
        }
        function esc(s) {
            return (s ?? '').toString()
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function renderResult(simpeg, current) {
            const rows = [
                { key: 'name',       label: 'Nama',       simpegVal: simpeg.nama,     currentVal: current.name },
                { key: 'nip',        label: 'NIP',        simpegVal: simpeg.nip,      currentVal: current.nip },
                { key: 'email',      label: 'Email',      simpegVal: simpeg.email,    currentVal: current.email },
                { key: 'phone',      label: 'Nomor HP',   simpegVal: simpeg.telepon,  currentVal: current.phone },
                { key: 'jabatan',    label: 'Jabatan',    simpegVal: simpeg.jabatan,  currentVal: current.jabatan },
                { key: 'unit_kerja', label: 'Unit Kerja', simpegVal: simpeg.instansi, currentVal: current.unit_kerja_nama,
                  extra: simpeg.matched_unit_kerja_id
                    ? '<span class="text-xs text-green-700 ml-1">✓ Cocok dengan Master Data</span>'
                    : '<span class="text-xs text-yellow-700 ml-1">⚠ Tidak cocok dengan Master Data</span>' },
            ];

            const rowsHtml = rows.map(r => {
                // Jabatan & Unit Kerja selalu ditampilkan (info-only jika SIMPEG tidak punya data).
                const alwaysShow = r.key === 'jabatan' || r.key === 'unit_kerja';
                if (!r.simpegVal && !alwaysShow) return '';

                if (!r.simpegVal) {
                    // SIMPEG tidak mengembalikan data field ini untuk pegawai ini.
                    return `
                        <div class="flex items-start gap-3 p-3 border rounded bg-gray-50">
                            <div class="mt-1 w-4 h-4 flex-shrink-0"></div>
                            <div class="flex-1 text-sm">
                                <div class="font-semibold text-gray-400">${esc(r.label)}</div>
                                <div class="text-gray-400 text-xs mt-1 italic">SIMPEG tidak memiliki data ini untuk pegawai ini.</div>
                                ${r.currentVal ? `<div class="text-gray-500 text-xs mt-0.5">Saat ini: ${esc(r.currentVal)}</div>` : ''}
                            </div>
                        </div>
                    `;
                }

                const same = (r.simpegVal ?? '').toString().trim().toLowerCase()
                          === (r.currentVal ?? '').toString().trim().toLowerCase();
                const sameBadge = same ? '<span class="ml-2 text-xs text-gray-500">(sama)</span>' : '';
                // Auto-check kalau beda; untuk unit_kerja hanya auto-check kalau ada match.
                const autoCheck = same ? '' :
                    (r.key === 'unit_kerja' ? (simpeg.matched_unit_kerja_id ? 'checked' : '') : 'checked');
                return `
                    <label class="flex items-start gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="fields[]" value="${r.key}" class="mt-1" ${autoCheck}>
                        <div class="flex-1 text-sm">
                            <div class="font-semibold text-gray-800">${esc(r.label)}${sameBadge}</div>
                            <div class="text-gray-700 mt-1"><span class="text-xs text-gray-500">SIMPEG:</span> ${esc(r.simpegVal)} ${r.extra ?? ''}</div>
                            <div class="text-gray-500 text-xs mt-0.5">Saat ini: ${esc(r.currentVal) || '—'}</div>
                        </div>
                    </label>
                `;
            }).join('');

            resultBox.innerHTML = `
                <p class="text-sm text-gray-600 mb-3">Centang field yang ingin diupdate berdasarkan data SIMPEG:</p>
                <div class="space-y-2">${rowsHtml}</div>
            `;
        }

        // Event delegation supaya tombol di halaman DataTables berikutnya tetap ke-handle
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.js-cek-data');
            if (!btn) return;
            e.preventDefault();

            const userId = btn.dataset.userId;
            const userName = btn.dataset.userName;
            openModal(userId, userName);

            try {
                const res = await fetch(`${checkUrl}/${userId}/check`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const body = await res.json();
                loading.classList.add('hidden');

                if (!body.success) {
                    errorBox.textContent = body.message || 'Terjadi kesalahan.';
                    errorBox.classList.remove('hidden');
                    return;
                }

                lastSimpeg = body.simpeg;
                renderResult(body.simpeg, body.current);
                resultBox.classList.remove('hidden');
                btnApply.classList.remove('hidden');
            } catch (err) {
                loading.classList.add('hidden');
                errorBox.textContent = 'Gagal menghubungi server: ' + err.message;
                errorBox.classList.remove('hidden');
            }
        });

        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        btnApply.addEventListener('click', async () => {
            if (!currentUserId || !lastSimpeg) return;
            const checked = Array.from(resultBox.querySelectorAll('input[name="fields[]"]:checked')).map(el => el.value);
            if (checked.length === 0) {
                alert('Pilih minimal satu field untuk diupdate.');
                return;
            }

            btnApply.disabled = true;
            const originalText = btnApply.textContent;
            btnApply.textContent = 'Menyimpan...';

            try {
                const payload = {
                    fields: checked,
                    name:     lastSimpeg.nama,
                    nip:      lastSimpeg.nip,
                    phone:    lastSimpeg.telepon,
                    jabatan:  lastSimpeg.jabatan,
                    instansi: lastSimpeg.instansi,
                    unit_kerja_id: lastSimpeg.matched_unit_kerja_id,
                };
                // Email hanya dikirim jika dicentang — mencegah validasi gagal
                // saat SIMPEG mengembalikan email multi-alamat yang tidak valid.
                if (checked.includes('email')) {
                    payload.email = lastSimpeg.email;
                }
                const res = await fetch(`${applyUrl}/${currentUserId}/apply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                const body = await res.json();

                if (!body.success) {
                    alert(body.message || 'Gagal menyimpan.');
                    btnApply.disabled = false;
                    btnApply.textContent = originalText;
                    return;
                }

                closeModal();
                showFlash('success', body.message);
                // Reload supaya kolom-kolom di tabel re-render dengan nilai terbaru
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                alert('Gagal menyimpan: ' + e.message);
                btnApply.disabled = false;
                btnApply.textContent = originalText;
            }
        });
    })();
    </script>

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
