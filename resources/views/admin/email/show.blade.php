@extends('layouts.authenticated')
@section('title', '- Detail Tiket ' . $item->ticket_no)
@section('header-title', 'Detail Tiket Email')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Tiket {{ $item->ticket_no }}</h1>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Kiri: data --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Data Permohonan</h2>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="font-medium">Nama</dt>
                    <dd>{{ $item->nama }}</dd>
                </div>
                <div>
                    <dt class="font-medium">NIP</dt>
                    <dd>{{ $item->nip ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Instansi</dt>
                    <dd>{{ $item->instansi }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Username</dt>
                    <dd>{{ $item->username }}@kaltaraprov.go.id</dd>
                </div>
                <div>
                    <dt class="font-medium">Email Alternatif</dt>
                    <dd>{{ $item->email_alternatif ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium">No. HP</dt>
                    <dd>{{ $item->no_hp }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Password</dt>
                    <dd class="font-mono flex items-center gap-2">
                        <span id="password-display" class="select-all">••••••••••••••••</span>
                        <button type="button" id="toggle-password" class="text-gray-600 hover:text-gray-900 focus:outline-none" title="Tampilkan/Sembunyikan Password">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </dd>
                </div>
                <div>
                    <dt class="font-medium">Status</dt>
                    <dd class="capitalize">{{ $item->status }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Diajukan</dt>
                    <dd>{{ optional($item->submitted_at)->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-medium">Selesai</dt>
                    <dd>{{ optional($item->completed_at)->format('Y-m-d H:i') ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Kanan: ubah status --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Ubah Status</h2>
            <form action="{{ route('admin.email.status', $item->id) }}" method="POST" class="space-y-3">
                @csrf
                <select name="status" class="border rounded p-2 w-full">
                    @foreach (['menunggu', 'proses', 'ditolak', 'selesai'] as $st)
                        <option value="{{ $st }}" @selected($item->status === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <textarea name="note" class="border rounded p-2 w-full" rows="3" placeholder="Catatan (opsional)"></textarea>
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Simpan</button>
            </form>

            @if (session('status'))
                <div class="mt-3 rounded border border-green-300 bg-green-50 p-3 text-sm">{{ session('status') }}</div>
            @endif

            {{-- Form Update Password --}}
            <div class="mt-6 pt-6 border-t">
                <h2 class="font-semibold mb-3">Update Password</h2>
                <p class="text-xs text-gray-600 mb-3">
                    Gunakan fitur ini jika password yang diajukan tidak memenuhi standar cPanel atau perlu diubah.
                </p>
                <form action="{{ route('admin.email.update-password', $item->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm mb-1">Password Baru</label>
                        <input type="text" name="new_password" id="new-password-input"
                            class="border rounded p-2 w-full font-mono text-sm"
                            placeholder="Masukkan password baru">
                        @error('new_password')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Password Requirements --}}
                        <div id="admin-password-requirements" class="mt-2 text-xs space-y-1">
                            <div id="admin-req-length" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Minimal 15 karakter</span>
                            </div>
                            <div id="admin-req-uppercase" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Huruf besar (A-Z)</span>
                            </div>
                            <div id="admin-req-lowercase" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Huruf kecil (a-z)</span>
                            </div>
                            <div id="admin-req-number" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Angka (0-9)</span>
                            </div>
                            <div id="admin-req-symbol" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Simbol (!@#$%^&*)</span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                        Update Password
                    </button>
                </form>

                @if (session('password_updated'))
                    <div class="mt-3 rounded border border-blue-300 bg-blue-50 p-3 text-sm">{{ session('password_updated') }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="bg-white rounded shadow p-4 mt-6">
        <h2 class="font-semibold mb-3">Riwayat</h2>
        <ul class="text-sm space-y-2">
            @foreach ($item->logs as $log)
                <li>
                    <span class="font-mono">{{ $log->created_at->format('Y-m-d H:i') }}</span> —
                    <strong>{{ $log->action }}</strong>
                    @if ($log->note)
                        : <em>{{ $log->note }}</em>
                    @endif
                    @if ($log->actor)
                        (oleh {{ $log->actor->name }})
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    @push('scripts')
        <script>
            // Password toggle functionality
            const passwordDisplay = document.getElementById('password-display');
            const toggleButton = document.getElementById('toggle-password');
            const eyeIcon = document.getElementById('eye-icon');
            const actualPassword = @json($item->getPlainPassword());
            let isPasswordVisible = false;

            toggleButton.addEventListener('click', function() {
                isPasswordVisible = !isPasswordVisible;

                if (isPasswordVisible) {
                    // Show password
                    passwordDisplay.textContent = actualPassword;
                    // Change to eye-slash icon
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    `;
                } else {
                    // Hide password
                    passwordDisplay.textContent = '••••••••••••••••';
                    // Change back to eye icon
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            });

            // Admin Password Validation
            const newPasswordInput = document.getElementById('new-password-input');
            const adminReqLength = document.getElementById('admin-req-length');
            const adminReqUppercase = document.getElementById('admin-req-uppercase');
            const adminReqLowercase = document.getElementById('admin-req-lowercase');
            const adminReqNumber = document.getElementById('admin-req-number');
            const adminReqSymbol = document.getElementById('admin-req-symbol');

            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    checkAdminPasswordStrength(password);
                });
            }

            function checkAdminPasswordStrength(password) {
                // Check requirements
                const hasLength = password.length >= 15;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

                // Update requirement indicators
                updateRequirement(adminReqLength, hasLength);
                updateRequirement(adminReqUppercase, hasUppercase);
                updateRequirement(adminReqLowercase, hasLowercase);
                updateRequirement(adminReqNumber, hasNumber);
                updateRequirement(adminReqSymbol, hasSymbol);
            }

            function updateRequirement(element, isMet) {
                const icon = element.querySelector('.requirement-icon');
                if (isMet) {
                    element.classList.remove('text-gray-500');
                    element.classList.add('text-green-600', 'font-semibold');
                    icon.textContent = '✓';
                } else {
                    element.classList.remove('text-green-600', 'font-semibold');
                    element.classList.add('text-gray-500');
                    icon.textContent = '○';
                }
            }
        </script>
    @endpush
@endsection
