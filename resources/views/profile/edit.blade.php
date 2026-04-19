@extends('layouts.authenticated')

@section('title', '- Edit Profile')
@section('header-title', 'Dashboard Pengguna')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow border border-gray-200">
        <div class="flex items-start justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-green-700">Edit Profil Pengguna</h2>
            @if(!empty($user->nik))
                <button type="button" id="btn-simpeg-sync"
                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sinkron Data via SIMPEG
                </button>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <div id="simpeg-flash" class="mb-4 hidden rounded border px-4 py-3 text-sm"></div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <!-- Nama -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    required>
            </div>

            <!-- NIP -->
            <div class="mb-4">
                <label for="nip" class="block text-sm font-medium text-gray-700">NIP (Nomor Induk Pegawai)</label>
                @if(auth()->user()->is_verified && !auth()->user()->hasRole('Admin'))
                    <input id="nip" type="text" value="{{ auth()->user()->nip ?? '-' }}"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed"
                        readonly disabled>
                    <p class="mt-1 text-xs text-gray-500">
                        <span class="text-green-600 font-semibold">🔒 Terkunci</span> - NIP tidak dapat diubah setelah akun terverifikasi. Hubungi admin jika ada kesalahan.
                    </p>
                @else
                    <input id="nip" type="text" name="nip" value="{{ old('nip', auth()->user()->nip) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        placeholder="Masukkan NIP jika pegawai ASN">
                    <p class="mt-1 text-xs text-gray-500">Opsional - hanya untuk pegawai ASN</p>
                @endif
            </div>

            <!-- NIK -->
            <div class="mb-4">
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                @if(auth()->user()->is_verified && !auth()->user()->hasRole('Admin'))
                    <input id="nik" type="text" value="{{ auth()->user()->nik ?? '-' }}"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed"
                        readonly disabled>
                    <p class="mt-1 text-xs text-gray-500">
                        <span class="text-green-600 font-semibold">🔒 Terkunci</span> - NIK tidak dapat diubah setelah akun terverifikasi. Hubungi admin jika ada kesalahan.
                    </p>
                @else
                    <input id="nik" type="text" name="nik" value="{{ old('nik', auth()->user()->nik) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        placeholder="16 digit NIK">
                @endif
            </div>

            <!-- Nomor HP / WA -->
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Nomor HP / WA</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>

            <!-- Instansi / Unit Kerja -->
            <div class="mb-4">
                <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700">Instansi / Unit Kerja</label>
                @if(auth()->user()->is_verified && !auth()->user()->hasRole('Admin'))
                    <input type="text"
                        value="{{ auth()->user()->unitKerja->nama ?? '-' }}"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed"
                        readonly disabled>
                    <p class="mt-1 text-xs text-gray-500">
                        <span class="text-green-600 font-semibold">🔒 Terkunci</span> - Instansi tidak dapat diubah setelah akun terverifikasi. Hubungi admin jika ada kesalahan.
                    </p>
                @else
                    <select id="unit_kerja_id" name="unit_kerja_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($unitKerjas as $uk)
                            <option value="{{ $uk->id }}"
                                {{ old('unit_kerja_id', auth()->user()->unit_kerja_id) == $uk->id ? 'selected' : '' }}>
                                {{ $uk->nama }} ({{ $uk->tipe }})
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->unitKerja)
                        <p class="mt-1 text-xs text-gray-500">
                            Instansi saat ini: <span class="font-semibold">{{ auth()->user()->unitKerja->nama }}</span>
                        </p>
                    @endif
                    <x-input-error :messages="$errors->get('unit_kerja_id')" class="mt-2" />
                @endif
            </div>

            <!-- Jabatan (read-only, sinkron via admin) -->
            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-md">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-800">Jabatan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Data jabatan disinkronkan dari SIMPEG oleh admin — tidak dapat diubah sendiri.</p>
                </div>

                @if($user->jabatan)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm mt-3">
                        <div>
                            <dt class="text-xs text-gray-500">Nama Jabatan</dt>
                            <dd class="text-gray-800">{{ $user->jabatan->nama_jabatan ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Eselon</dt>
                            <dd class="text-gray-800">{{ $user->jabatan->eselon ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">TMT Jabatan</dt>
                            <dd class="text-gray-800">
                                {{ $user->jabatan->tmt_jabatan ? \Carbon\Carbon::parse($user->jabatan->tmt_jabatan)->format('d M Y') : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Unit Kerja (dari Jabatan)</dt>
                            <dd class="text-gray-800">
                                {{ $user->jabatan->unitKerja->nama ?? ($user->jabatan->unit_kerja_legacy ?: '—') }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-2 text-sm text-gray-600 italic">
                        Belum ada data jabatan.
                        @if(!empty($user->nik))
                            Klik tombol <strong>Sinkron Data via SIMPEG</strong> di atas untuk mengisi otomatis.
                        @else
                            Isi NIK terlebih dulu, lalu klik Sinkron Data via SIMPEG.
                        @endif
                    </p>
                @endif
            </div>

            <!-- Current Password -->
            <div class="mt-4">
                <x-input-label for="current_password" :value="__('Password Sekarang')" />
                <x-text-input id="current_password" class="block mt-1 w-full" type="password" name="current_password"
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>

            <!-- New Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password Baru')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm New Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Modal Sinkron SIMPEG --}}
    <div id="simpeg-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-bold text-gray-800">Sinkron Data dari SIMPEG</h3>
                <button type="button" id="simpeg-modal-close" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <div id="simpeg-modal-body" class="overflow-y-auto px-6 py-4 flex-1">
                {{-- Loading state --}}
                <div id="simpeg-loading" class="text-center py-8 text-gray-600">
                    <svg class="animate-spin w-8 h-8 mx-auto mb-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mengambil data dari SIMPEG...
                </div>

                {{-- Error state --}}
                <div id="simpeg-error" class="hidden rounded border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm"></div>

                {{-- Result state — diisi dinamis via JS --}}
                <div id="simpeg-result" class="hidden"></div>
            </div>

            <div class="border-t px-6 py-3 flex items-center justify-end gap-2 bg-gray-50">
                <button type="button" id="simpeg-btn-cancel" class="px-4 py-2 text-sm border rounded hover:bg-gray-100">Batal</button>
                <button type="button" id="simpeg-btn-apply" class="hidden px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold">
                    Terapkan yang Dicentang
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const btn       = document.getElementById('btn-simpeg-sync');
        const modal     = document.getElementById('simpeg-modal');
        const loading   = document.getElementById('simpeg-loading');
        const errorBox  = document.getElementById('simpeg-error');
        const resultBox = document.getElementById('simpeg-result');
        const btnClose  = document.getElementById('simpeg-modal-close');
        const btnCancel = document.getElementById('simpeg-btn-cancel');
        const btnApply  = document.getElementById('simpeg-btn-apply');
        const flashBox  = document.getElementById('simpeg-flash');

        if (!btn) return; // button hidden (user tanpa NIK)

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                       || document.querySelector('input[name="_token"]')?.value;

        let lastSimpeg = null;
        let lastEditable = {};

        function openModal() {
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
        }
        function showFlash(type, message) {
            flashBox.textContent = message;
            flashBox.className = 'mb-4 rounded border px-4 py-3 text-sm '
                + (type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800');
            flashBox.classList.remove('hidden');
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        function esc(s) {
            return (s ?? '').toString()
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function renderResult(simpeg, current, editable) {
            const rows = [
                { key: 'name',          label: 'Nama',        simpegVal: simpeg.nama,     currentVal: current.name },
                { key: 'nip',           label: 'NIP',         simpegVal: simpeg.nip,      currentVal: current.nip },
                { key: 'email',         label: 'Email',       simpegVal: simpeg.email,    currentVal: current.email },
                { key: 'phone',         label: 'Nomor HP',    simpegVal: simpeg.telepon,  currentVal: current.phone },
                { key: 'jabatan',       label: 'Jabatan',     simpegVal: simpeg.jabatan,  currentVal: current.jabatan },
                { key: 'unit_kerja',    label: 'Unit Kerja',  simpegVal: simpeg.instansi, currentVal: current.unit_kerja_nama,
                  extra: simpeg.matched_unit_kerja_id ? '<span class="text-xs text-green-700">✓ Cocok dengan Master Data</span>' : '<span class="text-xs text-yellow-700">⚠ Tidak cocok dengan Master Data</span>' },
            ];

            const rowsHtml = rows.map(r => {
                if (!r.simpegVal) return ''; // skip empty from SIMPEG
                const isEditable = editable[r.key] !== false;
                const same = (r.simpegVal ?? '').toString().trim().toLowerCase()
                          === (r.currentVal ?? '').toString().trim().toLowerCase();
                const disabled = isEditable ? '' : 'disabled';
                const lockNote = isEditable ? '' : '<p class="text-xs text-yellow-700 mt-1">🔒 Field ini dikunci setelah akun diverifikasi. Hubungi admin untuk mengubah.</p>';
                const sameBadge = same ? '<span class="ml-2 text-xs text-gray-500">(sama)</span>' : '';
                return `
                    <label class="flex items-start gap-3 p-3 border rounded hover:bg-gray-50 ${isEditable ? 'cursor-pointer' : 'opacity-75 cursor-not-allowed'}">
                        <input type="checkbox" name="fields[]" value="${r.key}" class="mt-1" ${disabled}>
                        <div class="flex-1 text-sm">
                            <div class="font-semibold text-gray-800">${esc(r.label)}${sameBadge}</div>
                            <div class="text-gray-700 mt-1"><span class="text-xs text-gray-500">SIMPEG:</span> ${esc(r.simpegVal)} ${r.extra ?? ''}</div>
                            <div class="text-gray-500 text-xs mt-0.5">Saat ini: ${esc(r.currentVal) || '—'}</div>
                            ${lockNote}
                        </div>
                    </label>
                `;
            }).join('');

            resultBox.innerHTML = `
                <p class="text-sm text-gray-600 mb-3">Centang field yang ingin diupdate berdasarkan data SIMPEG:</p>
                <div class="space-y-2">${rowsHtml}</div>
            `;
        }

        btn.addEventListener('click', async () => {
            openModal();
            try {
                const res = await fetch('{{ route('profile.simpeg.check') }}', {
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
                lastEditable = body.editable || {};
                renderResult(body.simpeg, body.current, lastEditable);
                resultBox.classList.remove('hidden');
                btnApply.classList.remove('hidden');
            } catch (e) {
                loading.classList.add('hidden');
                errorBox.textContent = 'Gagal menghubungi server: ' + e.message;
                errorBox.classList.remove('hidden');
            }
        });

        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        btnApply.addEventListener('click', async () => {
            const checked = Array.from(resultBox.querySelectorAll('input[name="fields[]"]:checked:not([disabled])')).map(el => el.value);
            if (checked.length === 0) {
                alert('Pilih minimal satu field untuk diupdate.');
                return;
            }

            btnApply.disabled = true;
            btnApply.textContent = 'Menyimpan...';

            try {
                const payload = {
                    fields: checked,
                    name:     lastSimpeg.nama,
                    nip:      lastSimpeg.nip,
                    email:    lastSimpeg.email,
                    phone:    lastSimpeg.telepon,
                    jabatan:  lastSimpeg.jabatan,
                    instansi: lastSimpeg.instansi,
                    unit_kerja_id: lastSimpeg.matched_unit_kerja_id,
                };
                const res = await fetch('{{ route('profile.simpeg.apply') }}', {
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
                    btnApply.textContent = 'Terapkan yang Dicentang';
                    return;
                }

                closeModal();
                showFlash('success', body.message);
                // Reload supaya field form yang baru disimpan re-render dengan nilai terbaru
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                alert('Gagal menyimpan: ' + e.message);
                btnApply.disabled = false;
                btnApply.textContent = 'Terapkan yang Dicentang';
            }
        });
    })();
    </script>
@endsection
