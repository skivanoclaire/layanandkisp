@extends('layouts.authenticated')

@section('title', '- Role & Permissions')
@section('header-title', 'Kelola Kewenangan')

@push('styles')
<style>
    .rp-scroll { max-height: calc(100vh - 290px); min-height: 360px; overflow: auto; }
    .rp-table { border-collapse: separate; border-spacing: 0; }
    .rp-table thead th { position: sticky; top: 0; z-index: 20; background: #f3f4f6; }
    .rp-table .rp-first { position: sticky; left: 0; z-index: 10; background: inherit; }
    .rp-table thead th.rp-first { z-index: 30; }
    .rp-table tr.rp-section > td { background: #eef2ff; }
    .rp-table tr.rp-row { background: #fff; }
    .rp-table tr.rp-row:hover { background: #f9fafb; }
    .rp-role-hidden { display: none; }
    .rp-row-changed .rp-first { box-shadow: inset 4px 0 0 #f59e0b; }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 max-w-full">
    <div class="mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Kelola Kewenangan Role</h1>
        <p class="text-gray-600 mt-2">
            Centang permission untuk setiap role. Urutan bagian mengikuti menu di sidebar.
        </p>
        @if($adminOnlyPages)
            <p class="text-xs text-gray-500 mt-1">
                Khusus role Admin dan tidak bisa diberikan lewat halaman ini:
                {{ implode(', ', $adminOnlyPages) }}.
            </p>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-4 space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" id="rp-search" placeholder="Cari permission (nama atau kode)..."
                   style="min-width: 220px;" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
            <button type="button" id="rp-expand-all" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Buka semua</button>
            <button type="button" id="rp-collapse-all" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Tutup semua</button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 mr-1">Tampilkan role:</span>
            <button type="button" id="rp-roles-all" class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50">Semua</button>
            @foreach($roles as $role)
                <label class="inline-flex items-center gap-1 px-2 py-1 text-xs border border-gray-200 rounded cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="rp-role-toggle rounded text-green-600" value="{{ $role->id }}" checked>
                    {{ $role->display_name }}
                </label>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('admin.role-permissions.update') }}" id="permissions-form">
        @csrf
        {{-- Diisi JavaScript saat submit: permissions[role_id] = "id,id,id" --}}
        <div id="rp-payload"></div>

        <div class="bg-white rounded-lg shadow-md rp-scroll">
            <table class="rp-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="rp-first px-4 py-3 text-left font-semibold text-gray-700 border-b-2 border-r-2 border-gray-300" style="min-width: 320px;">
                            Permission / Halaman
                        </th>
                        @foreach($roles as $role)
                            @php
                                $badgeClass = match($role->name) {
                                    'Admin' => 'bg-purple-500',
                                    'User' => 'bg-blue-500',
                                    'Operator-Vidcon' => 'bg-green-500',
                                    'Operator-Sandi' => 'bg-yellow-500',
                                    'Admin-Vidcon' => 'bg-emerald-600',
                                    default => 'bg-gray-500'
                                };
                            @endphp
                            <th class="rp-role-{{ $role->id }} px-3 py-3 text-center border-b-2 border-r border-gray-300" style="min-width: 110px;">
                                <div class="inline-block px-2 py-1 rounded text-white text-xs font-semibold {{ $badgeClass }}">
                                    {{ $role->display_name }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $role->users_count }} user</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                @foreach($sections as $index => $section)
                    <tbody class="rp-section-body" data-section="{{ $index }}">
                        <tr class="rp-section">
                            <td class="rp-first px-4 py-2 border-b border-r-2 border-gray-300">
                                <button type="button" class="rp-section-toggle w-full flex items-center text-left" data-section="{{ $index }}">
                                    <svg class="rp-chevron w-4 h-4 mr-2 transition-transform" style="transform: rotate(90deg)" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-semibold text-gray-800">{{ $section['label'] }}</span>
                                    <span class="ml-2 text-xs text-gray-500">({{ count($section['rows']) }})</span>
                                </button>
                            </td>
                            @foreach($roles as $role)
                                <td class="rp-role-{{ $role->id }} px-3 py-2 text-center border-b border-r border-gray-200">
                                    <input type="checkbox"
                                           class="rp-section-check w-4 h-4 rounded border-gray-400 text-indigo-600 cursor-pointer"
                                           data-section="{{ $index }}" data-role="{{ $role->id }}"
                                           title="Centang/hapus semua di bagian {{ $section['label'] }} untuk {{ $role->display_name }}">
                                </td>
                            @endforeach
                        </tr>

                        @foreach($section['rows'] as $row)
                            @php $permission = $row['permission']; @endphp
                            <tr class="rp-row" data-section="{{ $index }}"
                                data-search="{{ strtolower($section['label'].' '.$row['sub'].' '.$permission->display_name.' '.$permission->name) }}">
                                <td class="rp-first px-4 py-2 border-b border-r-2 border-gray-200">
                                    <div class="flex flex-col pl-6">
                                        <span class="text-gray-800">
                                            @if($row['sub'] && $row['sub'] !== $section['label'])
                                                <span style="font-size: 11px;" class="inline-block px-1.5 py-0.5 mr-1 rounded bg-gray-100 text-gray-600">{{ $row['sub'] }}</span>
                                            @endif
                                            {{ $permission->display_name }}
                                        </span>
                                        <span class="text-xs text-gray-400 font-mono mt-0.5">{{ $permission->name }}</span>
                                    </div>
                                </td>
                                @foreach($roles as $role)
                                    <td class="rp-role-{{ $role->id }} px-3 py-2 text-center border-b border-r border-gray-100">
                                        <input type="checkbox"
                                               class="rp-check w-5 h-5 text-green-600 border-gray-300 rounded cursor-pointer"
                                               data-role="{{ $role->id }}" data-section="{{ $index }}" value="{{ $permission->id }}"
                                               @checked($role->permissions->contains($permission->id))>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
            <p id="rp-empty" class="hidden p-6 text-center text-gray-500">Tidak ada permission yang cocok.</p>
        </div>

        <div class="sticky bottom-0 bg-white border-t-2 border-gray-300 shadow-lg mt-4 p-4 rounded-lg z-30">
            <div class="flex flex-wrap justify-between items-center gap-3">
                <div class="text-sm text-gray-600">
                    <span class="font-semibold">Total:</span> {{ $totalPermissions }} permissions × {{ $roles->count() }} roles
                    <span id="rp-changed" class="ml-3 hidden font-semibold text-amber-600"></span>
                </div>
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold text-lg shadow-md transition">
                    💾 Simpan Semua Kewenangan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('permissions-form');
    const checks = Array.from(document.querySelectorAll('.rp-check'));
    const sectionChecks = Array.from(document.querySelectorAll('.rp-section-check'));
    const roleIds = Array.from(document.querySelectorAll('.rp-role-toggle')).map(el => el.value);
    const initial = new Map(checks.map(cb => [cb, cb.checked]));
    const collapsed = new Set();
    const STORE_KEY = 'rp-visible-roles';

    // ----- Status centang per bagian (centang / sebagian / kosong) -----
    function rowsVisible(cb) {
        return cb.closest('tr').style.display !== 'none';
    }
    function refreshSectionCheck(section, role) {
        const box = sectionChecks.find(s => s.dataset.section === section && s.dataset.role === role);
        if (!box) return;
        const items = checks.filter(cb => cb.dataset.section === section && cb.dataset.role === role && rowsVisible(cb));
        const on = items.filter(cb => cb.checked).length;
        box.checked = items.length > 0 && on === items.length;
        box.indeterminate = on > 0 && on < items.length;
    }
    function refreshAllSectionChecks() {
        sectionChecks.forEach(s => refreshSectionCheck(s.dataset.section, s.dataset.role));
    }

    // ----- Penanda perubahan yang belum disimpan -----
    function refreshChanged() {
        let changed = 0;
        document.querySelectorAll('tr.rp-row').forEach(tr => {
            const dirty = Array.from(tr.querySelectorAll('.rp-check')).some(cb => cb.checked !== initial.get(cb));
            tr.classList.toggle('rp-row-changed', dirty);
        });
        checks.forEach(cb => { if (cb.checked !== initial.get(cb)) changed++; });
        const label = document.getElementById('rp-changed');
        label.textContent = changed ? changed + ' perubahan belum disimpan' : '';
        label.classList.toggle('hidden', changed === 0);
    }

    checks.forEach(cb => cb.addEventListener('change', () => {
        refreshSectionCheck(cb.dataset.section, cb.dataset.role);
        refreshChanged();
    }));

    sectionChecks.forEach(box => box.addEventListener('change', () => {
        checks
            .filter(cb => cb.dataset.section === box.dataset.section && cb.dataset.role === box.dataset.role && rowsVisible(cb))
            .forEach(cb => { cb.checked = box.checked; });
        refreshSectionCheck(box.dataset.section, box.dataset.role);
        refreshChanged();
    }));

    // ----- Buka/tutup bagian -----
    function applyCollapse(section) {
        const searching = document.getElementById('rp-search').value.trim() !== '';
        const isCollapsed = collapsed.has(section) && !searching;
        const body = document.querySelector('tbody[data-section="' + section + '"]');
        body.querySelector('.rp-chevron').style.transform = isCollapsed ? '' : 'rotate(90deg)';
        body.querySelectorAll('tr.rp-row').forEach(tr => {
            tr.style.display = (isCollapsed || tr.dataset.hiddenBySearch === '1') ? 'none' : '';
        });
    }
    document.querySelectorAll('.rp-section-toggle').forEach(btn => btn.addEventListener('click', () => {
        const s = btn.dataset.section;
        collapsed.has(s) ? collapsed.delete(s) : collapsed.add(s);
        applyCollapse(s);
        refreshAllSectionChecks();
    }));
    document.getElementById('rp-expand-all').addEventListener('click', () => {
        collapsed.clear();
        document.querySelectorAll('tbody[data-section]').forEach(b => applyCollapse(b.dataset.section));
        refreshAllSectionChecks();
    });
    document.getElementById('rp-collapse-all').addEventListener('click', () => {
        document.querySelectorAll('tbody[data-section]').forEach(b => {
            collapsed.add(b.dataset.section);
            applyCollapse(b.dataset.section);
        });
        refreshAllSectionChecks();
    });

    // ----- Pencarian -----
    document.getElementById('rp-search').addEventListener('input', e => {
        const terms = e.target.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
        let anyVisible = false;
        document.querySelectorAll('tbody[data-section]').forEach(body => {
            let sectionHit = false;
            body.querySelectorAll('tr.rp-row').forEach(tr => {
                const hit = terms.every(t => tr.dataset.search.includes(t));
                tr.dataset.hiddenBySearch = hit ? '0' : '1';
                sectionHit = sectionHit || hit;
            });
            body.style.display = sectionHit ? '' : 'none';
            anyVisible = anyVisible || sectionHit;
            applyCollapse(body.dataset.section);
        });
        document.getElementById('rp-empty').classList.toggle('hidden', anyVisible);
        refreshAllSectionChecks();
    });

    // ----- Pilih role yang ditampilkan (disimpan di browser) -----
    function applyRoleVisibility() {
        const visible = new Set(
            Array.from(document.querySelectorAll('.rp-role-toggle')).filter(t => t.checked).map(t => t.value)
        );
        roleIds.forEach(id => {
            document.querySelectorAll('.rp-role-' + id).forEach(el => el.classList.toggle('rp-role-hidden', !visible.has(id)));
        });
        try { localStorage.setItem(STORE_KEY, JSON.stringify(Array.from(visible))); } catch (e) {}
    }
    try {
        const saved = JSON.parse(localStorage.getItem(STORE_KEY) || 'null');
        if (Array.isArray(saved) && saved.length) {
            document.querySelectorAll('.rp-role-toggle').forEach(t => { t.checked = saved.includes(t.value); });
        }
    } catch (e) {}
    document.querySelectorAll('.rp-role-toggle').forEach(t => t.addEventListener('change', applyRoleVisibility));
    document.getElementById('rp-roles-all').addEventListener('click', () => {
        document.querySelectorAll('.rp-role-toggle').forEach(t => { t.checked = true; });
        applyRoleVisibility();
    });

    // ----- Kirim: satu field per role (semua role, termasuk yang disembunyikan) -----
    form.addEventListener('submit', () => {
        const payload = document.getElementById('rp-payload');
        payload.innerHTML = '';
        roleIds.forEach(id => {
            const ids = checks.filter(cb => cb.dataset.role === id && cb.checked).map(cb => cb.value);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'permissions[' + id + ']';
            input.value = ids.join(',');
            payload.appendChild(input);
        });
    });

    window.addEventListener('beforeunload', e => {
        if (form.dataset.submitting) return;
        if (checks.some(cb => cb.checked !== initial.get(cb))) { e.preventDefault(); e.returnValue = ''; }
    });
    form.addEventListener('submit', () => { form.dataset.submitting = '1'; });

    applyRoleVisibility();
    refreshAllSectionChecks();
})();
</script>
@endpush
