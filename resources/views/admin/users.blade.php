@extends('layouts.authenticated')

@section('title', '- User Management')
@section('header-title', 'User Management')

@section('content')
<div class="bg-white shadow rounded-lg p-4 md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Manajemen User</h1>
        <a href="{{ route('admin.users.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap">
            + Tambah User
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- Search and Filter Form --}}
    <form method="GET" action="{{ route('admin.users') }}" class="mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" id="search" name="search"
                    value="{{ request('search') }}"
                    placeholder="Nama / NIP / NIK / Email / HP"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="role" class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select id="role" name="role"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="unit_kerja_id" class="block text-xs font-medium text-gray-600 mb-1">Unit Kerja</label>
                <select id="unit_kerja_id" name="unit_kerja_id"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}" {{ request('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                            {{ $uk->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Filter
                </button>
                <a href="{{ route('admin.users') }}"
                    class="flex-1 inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto -mx-4 md:mx-0">
        <table id="users-table" class="min-w-full text-sm border border-gray-200">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap">Nama / Email</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap">NIP / NIK</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap">Jabatan</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap hidden lg:table-cell">Unit Kerja</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap hidden sm:table-cell">No. HP</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap">Role</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 whitespace-nowrap">Status & Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- Nama + Email --}}
                        <td class="px-3 py-2 align-top">
                            <p class="font-semibold text-gray-900 leading-snug">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>
                            {{-- Unit kerja tampil di bawah nama saat layar kecil --}}
                            <p class="text-xs text-blue-600 mt-0.5 lg:hidden">{{ $user->unitKerja->nama ?? '' }}</p>
                        </td>

                        {{-- NIP / NIK --}}
                        <td class="px-3 py-2 align-top">
                            <p class="text-gray-700 font-mono text-xs">{{ $user->nip ?? '-' }}</p>
                            <p class="text-gray-400 font-mono text-xs mt-0.5">{{ $user->nik ?? '-' }}</p>
                        </td>

                        {{-- Jabatan --}}
                        <td class="px-3 py-2 align-top">
                            <span class="text-gray-700 text-xs">{{ $user->jabatan->nama_jabatan ?? '-' }}</span>
                        </td>

                        {{-- Unit Kerja --}}
                        <td class="px-3 py-2 align-top hidden lg:table-cell">
                            <span class="text-gray-700 text-xs">{{ $user->unitKerja->nama ?? '-' }}</span>
                        </td>

                        {{-- No. HP --}}
                        <td class="px-3 py-2 align-top hidden sm:table-cell">
                            <span class="text-gray-700 text-xs">{{ $user->phone ?? '-' }}</span>
                        </td>

                        {{-- Role --}}
                        <td class="px-3 py-2 align-top">
                            @if($user->roles->count() > 0)
                                <div class="flex flex-col gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full
                                            {{ $role->name === 'Admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ str_contains($role->name, 'User') ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ str_contains($role->name, 'Operator') ? 'bg-green-100 text-green-800' : '' }}
                                        ">
                                            {{ $role->display_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Status & Aksi --}}
                        <td class="px-3 py-2 align-top">
                            {{-- Status badge --}}
                            <div class="mb-2">
                                @if ($user->is_verified)
                                    <span class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-2 py-0.5 text-xs font-semibold">
                                        ✔ Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 text-yellow-800 px-2 py-0.5 text-xs font-semibold">
                                        ⏳ Belum Diverifikasi
                                    </span>
                                @endif
                            </div>

                            {{-- Action buttons --}}
                            <div class="flex flex-wrap gap-1">
                                @if (!$user->is_verified)
                                    <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                                        onsubmit="return confirm('Verifikasi user ini?');">
                                        @csrf
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs font-medium">
                                            Verifikasi
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.unverify', $user) }}"
                                        onsubmit="return confirm('Batalkan verifikasi user ini?');">
                                        @csrf
                                        <button type="submit"
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs font-medium">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif

                                @if(!empty($user->nik))
                                    <button type="button"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded text-xs font-medium js-cek-data"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        title="Cek & sinkron data dengan SIMPEG">
                                        Cek Data
                                    </button>
                                @endif

                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-medium">
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
</div>

{{-- Flash hasil sinkron --}}
<div id="cek-data-flash" class="fixed top-4 right-4 z-[60] hidden max-w-sm rounded border px-4 py-3 text-sm shadow-lg"></div>

{{-- Modal Cek Data via SIMPEG --}}
<div id="cek-data-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <h3 class="text-base font-bold text-gray-800">
                Sinkron Data SIMPEG <span id="cek-data-username" class="font-normal text-gray-500 text-sm"></span>
            </h3>
            <button type="button" id="cek-data-close" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="overflow-y-auto px-5 py-4 flex-1">
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

        <div class="border-t px-5 py-3 flex items-center justify-end gap-2 bg-gray-50">
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

    const checkUrl = "{{ url('/admin/simpeg-check/api') }}";
    const applyUrl = "{{ url('/admin/simpeg-check/api') }}";

    let currentUserId = null;
    let lastSimpeg    = null;

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
        lastSimpeg    = null;
    }
    function showFlash(type, message) {
        flashBox.textContent = message;
        flashBox.className = 'fixed top-4 right-4 z-[60] max-w-sm rounded border px-4 py-3 text-sm shadow-lg '
            + (type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800');
        flashBox.classList.remove('hidden');
        setTimeout(() => flashBox.classList.add('hidden'), 5000);
    }
    function esc(s) {
        return (s ?? '').toString()
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function renderResult(simpeg, current) {
        const rows = [
            { key:'name',       label:'Nama',       simpegVal:simpeg.nama,     currentVal:current.name },
            { key:'nip',        label:'NIP',        simpegVal:simpeg.nip,      currentVal:current.nip },
            { key:'email',      label:'Email',      simpegVal:simpeg.email,    currentVal:current.email },
            { key:'phone',      label:'Nomor HP',   simpegVal:simpeg.telepon,  currentVal:current.phone },
            { key:'jabatan',    label:'Jabatan',    simpegVal:simpeg.jabatan,  currentVal:current.jabatan },
            { key:'unit_kerja', label:'Unit Kerja', simpegVal:simpeg.instansi, currentVal:current.unit_kerja_nama,
              extra: simpeg.matched_unit_kerja_id
                ? '<span class="text-xs text-green-700 ml-1">✓ Cocok dengan Master Data</span>'
                : '<span class="text-xs text-yellow-700 ml-1">⚠ Tidak cocok dengan Master Data</span>' },
        ];

        const rowsHtml = rows.map(r => {
            const alwaysShow = r.key === 'jabatan' || r.key === 'unit_kerja';
            if (!r.simpegVal && !alwaysShow) return '';
            if (!r.simpegVal) {
                return `<div class="flex items-start gap-3 p-3 border rounded bg-gray-50">
                    <div class="mt-1 w-4 h-4 flex-shrink-0"></div>
                    <div class="flex-1 text-sm">
                        <div class="font-semibold text-gray-400">${esc(r.label)}</div>
                        <div class="text-gray-400 text-xs mt-1 italic">SIMPEG tidak memiliki data ini.</div>
                        ${r.currentVal ? `<div class="text-gray-500 text-xs mt-0.5">Saat ini: ${esc(r.currentVal)}</div>` : ''}
                    </div></div>`;
            }
            const same = (r.simpegVal??'').toString().trim().toLowerCase()
                      === (r.currentVal??'').toString().trim().toLowerCase();
            const sameBadge = same ? '<span class="ml-2 text-xs text-gray-500">(sama)</span>' : '';
            const autoCheck = same ? '' : (r.key==='unit_kerja' ? (simpeg.matched_unit_kerja_id?'checked':'') : 'checked');
            return `<label class="flex items-start gap-3 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" name="fields[]" value="${r.key}" class="mt-1" ${autoCheck}>
                <div class="flex-1 text-sm">
                    <div class="font-semibold text-gray-800">${esc(r.label)}${sameBadge}</div>
                    <div class="text-gray-700 mt-1"><span class="text-xs text-gray-500">SIMPEG:</span> ${esc(r.simpegVal)} ${r.extra??''}</div>
                    <div class="text-gray-500 text-xs mt-0.5">Saat ini: ${esc(r.currentVal)||'—'}</div>
                </div></label>`;
        }).join('');

        resultBox.innerHTML = `<p class="text-sm text-gray-600 mb-3">Centang field yang ingin diupdate berdasarkan data SIMPEG:</p>
            <div class="space-y-2">${rowsHtml}</div>`;
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-cek-data');
        if (!btn) return;
        e.preventDefault();
        openModal(btn.dataset.userId, btn.dataset.userName);
        try {
            const res  = await fetch(`${checkUrl}/${btn.dataset.userId}/check`, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            });
            const body = await res.json();
            loading.classList.add('hidden');
            if (!body.success) { errorBox.textContent = body.message||'Terjadi kesalahan.'; errorBox.classList.remove('hidden'); return; }
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
        if (checked.length === 0) { alert('Pilih minimal satu field untuk diupdate.'); return; }
        btnApply.disabled = true;
        const orig = btnApply.textContent;
        btnApply.textContent = 'Menyimpan...';
        try {
            const payload = { fields:checked, name:lastSimpeg.nama, nip:lastSimpeg.nip, phone:lastSimpeg.telepon, jabatan:lastSimpeg.jabatan, instansi:lastSimpeg.instansi, unit_kerja_id:lastSimpeg.matched_unit_kerja_id };
            if (checked.includes('email')) payload.email = lastSimpeg.email;
            const res  = await fetch(`${applyUrl}/${currentUserId}/apply`, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                body:JSON.stringify(payload),
            });
            const body = await res.json();
            if (!body.success) { alert(body.message||'Gagal menyimpan.'); btnApply.disabled=false; btnApply.textContent=orig; return; }
            closeModal();
            showFlash('success', body.message);
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            alert('Gagal menyimpan: ' + e.message);
            btnApply.disabled = false;
            btnApply.textContent = orig;
        }
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#users-table').DataTable({
        pageLength: 25,
        scrollX: true,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        searching: false,
        language: {
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ditemukan data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total)",
            paginate: { first:"«", last:"»", next:"›", previous:"‹" }
        }
    });
});
</script>
@endpush
