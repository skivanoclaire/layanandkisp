@extends('layouts.authenticated')
@section('title', '- Formulir Email')
@section('header-title', 'Formulir Email')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Formulir Pendaftaran Layanan Email Resmi ASN</h1>

        <div class="mb-4 text-sm">
            Dengan mengisi formulir ini, Anda menyatakan data benar dan menyetujui
            <a href="{{ url('/syarat-email') }}" class="text-green-700 underline" target="_blank">Persyaratan/Perjanjian
                Layanan Email Resmi Pemerintah Provinsi Kalimantan Utara</a>.
        </div>

        {{-- ====== LIST INSTANSI ====== --}}
        @php
            $instansiList = [
                // Sekretariat Daerah
                'SEKRETARIAT DAERAH',

                // Inspektorat
                'INSPEKTORAT',

                // Badan (urut abjad)
                'BADAN KESATUAN BANGSA DAN POLITIK',
                'BADAN KEPEGAWAIAN DAERAH',
                'BADAN KEUANGAN DAN ASET DAERAH',
                'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH',
                'BADAN PENGELOLA PERBATASAN DAERAH',
                'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
                'BADAN PENGHUBUNG',
                'BADAN PENANGGULANGAN BENCANA DAERAH',
                'BADAN PERENCANAAN PEMBANGUNAN DAERAH DAN LITBANG',
                'BADAN PENDAPATAN DAERAH',

                // Dinas (urut abjad)
                'DINAS ENERGI DAN SUMBER DAYA MINERAL',
                'DINAS KELAUTAN DAN PERIKANAN',
                'DINAS KEHUTANAN',
                'DINAS KESEHATAN',
                'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL',
                'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
                'DINAS LINGKUNGAN HIDUP',
                'DINAS PARIWISATA',
                'DINAS PEMUDA DAN OLAHRAGA',
                'DINAS PEMBERDAYAAN MASYARAKAT DAN DESA',
                'DINAS PEMBERDAYAAN PEREMPUAN, PERLINDUNGAN ANAK, PENGENDALIAN PENDUDUK DAN KELUARGA BERENCANA',
                'DINAS PENDIDIKAN DAN KEBUDAYAAN',
                'DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU',
                'DINAS PERTANIAN DAN KETAHANAN PANGAN',
                'DINAS PERHUBUNGAN',
                'DINAS PERINDUSTRIAN, PERDAGANGAN, KOPERASI, USAHA KECIL DAN MENENGAH',
                'DINAS PERPUSTAKAAN DAN KEARSIPAN',
                'DINAS PEKERJAAN UMUM, PENATAAN RUANG, PERUMAHAN DAN KAWASAN PERMUKIMAN',
                'DINAS SOSIAL',
                'DINAS TENAGA KERJA DAN TRANSMIGRASI',

                // Biro (urut abjad)
                'BIRO ADMINISTRASI PEMBANGUNAN',
                'BIRO ADMINISTRASI PIMPINAN',
                'BIRO HUKUM',
                'BIRO KESEJAHTERAAN RAKYAT',
                'BIRO ORGANISASI',
                'BIRO PEMERINTAHAN DAN OTONOMI DAERAH',
                'BIRO PENGADAAN BARANG DAN JASA',
                'BIRO PEREKONOMIAN',
                'BIRO UMUM',

                // SATPOL PP
                'SATUAN POLISI PAMONG PRAJA',

                // Sekretariat Dewan
                'SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH',

                // RSUD
                'RUMAH SAKIT UMUM DAERAH PROVINSI KALIMANTAN UTARA',

                // UPT/UPTD/Cabang Dinas (urut abjad)
                'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH MALINAU',
                'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH NUNUKAN',
                'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH TARAKAN',
                'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI BULUNGAN',
                'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI MALINAU',
                'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI NUNUKAN',
                'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI TARAKAN',
                'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI TIDENG PALE',
                'UPT INSTALASI FARMASI',
                'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN BULUNGAN',
                'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN MALINAU',
                'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN NUNUKAN',
                'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN TANA TIDUNG',
                'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KOTA TARAKAN',
                'UPT LABORATORIUM KESEHATAN HEWAN DAN KESEHATAN MASYARAKAT VETERINER',
                'UPT PANTI SOSIAL TRISNA WERDHA MARGA RAHAYU',
                'UPT PELABUHAN PERIKANAN TENGKAYU II',
                'UPT PENERAPAN MUTU HASIL PERIKANAN',
                'UPT PERIKANAN BUDIDAYA LAUT DAN PAYAU',
                'UPT TAMAN BUDAYA KALTARA',
                'UPT TEKNOLOGI, INFORMASI DAN KOMUNIKASI PENDIDIKAN',
                'UPTD LABORATORIUM LINGKUNGAN HIDUP',
                'UPTD PELABUHAN LIEM HIE DJUNG NUNUKAN',
                'UPTD PELABUHAN TENGKAYU I',
                'UPTD PERLINDUNGAN PEREMPUAN DAN ANAK',

                // SMA (urut abjad)
                'SMA NEGERI 1 BUNYU',
                'SMA NEGERI 1 KRAYAN',
                'SMA NEGERI 1 KRAYAN SELATAN',
                'SMA NEGERI 1 LUMBIS',
                'SMA NEGERI 1 MALINAU',
                'SMA NEGERI 10 MALINAU',
                'SMA NEGERI 11 MALINAU',
                'SMA NEGERI 12 MALINAU',
                'SMA NEGERI 13 MALINAU',
                'SMA NEGERI 14 MALINAU',
                'SMA NEGERI 15 MALINAU',
                'SMA NEGERI 16 MALINAU',
                'SMA NEGERI 1 NUNUKAN',
                'SMA NEGERI 1 NUNUKAN SELATAN',
                'SMA NEGERI 1 PESO',
                'SMA NEGERI 1 SEBATIK',
                'SMA NEGERI 1 SEBATIK TENGAH',
                'SMA NEGERI 1 SEKATAK',
                'SMA NEGERI 1 SEMBAKUNG',
                'SMA NEGERI 1 TANJUNG PALAS',
                'SMA NEGERI 1 TANJUNG PALAS BARAT',
                'SMA NEGERI 1 TANJUNG PALAS TENGAH',
                'SMA NEGERI 1 TANJUNG PALAS TIMUR',
                'SMA NEGERI 1 TANJUNG PALAS UTARA',
                'SMA NEGERI 1 TANJUNG SELOR',
                'SMA NEGERI 1 TANA TIDUNG',
                'SMA NEGERI 2 MALINAU',
                'SMA NEGERI 2 NUNUKAN',
                'SMA NEGERI 2 NUNUKAN SELATAN',
                'SMA NEGERI 2 TANJUNG SELOR',
                'SMA NEGERI 2 TANA TIDUNG',
                'SMA NEGERI 2 TARAKAN',
                'SMA NEGERI 3 MALINAU',
                'SMA NEGERI 3 TARAKAN',
                'SMA NEGERI 4 MALINAU',
                'SMA NEGERI 4 TARAKAN',
                'SMA NEGERI 5 MALINAU',
                'SMA NEGERI 6 MALINAU',
                'SMA NEGERI 7 MALINAU',
                'SMA NEGERI 8 MALINAU',
                'SMA NEGERI 9 MALINAU',
                'SMA NEGERI TERPADU UNGGULAN 1 TANA TIDUNG',

                // SMK (urut abjad)
                'SMK NEGERI 1 BUNYU',
                'SMK NEGERI 1 KRAYAN',
                'SMK NEGERI 1 MALINAU',
                'SMK NEGERI 1 NUNUKAN',
                'SMK NEGERI 1 SEBATIK BARAT',
                'SMK NEGERI 1 SEI MENGGARIS',
                'SMK NEGERI 1 SEMBAKUNG ATULAI',
                'SMK NEGERI 1 TANJUNG PALAS',
                'SMK NEGERI 1 TANJUNG PALAS TIMUR',
                'SMK NEGERI 1 TANJUNG PALAS UTARA',
                'SMK NEGERI 1 TANJUNG SELOR',
                'SMK NEGERI 1 TANA TIDUNG',
                'SMK NEGERI 1 TARAKAN',
                'SMK NEGERI 1 TULIN ONSOI',
                'SMK NEGERI 2 MALINAU',
                'SMK NEGERI 2 TANJUNG SELOR',
                'SMK NEGERI 2 TARAKAN',
                'SMK NEGERI 3 TANJUNG SELOR',
                'SMK NEGERI 3 TARAKAN',
                'SMK NEGERI 4 TARAKAN',
                'SMK SPP MALINAU',

                // SLB (urut abjad)
                'SLB NEGERI MALINAU',
                'SLB NEGERI NUNUKAN',
                'SLB NEGERI TANJUNG SELOR',
                'SLB NEGERI TANA TIDUNG',
                'SLB NEGERI TARAKAN',
            ];
        @endphp


        <form action="{{ route('user.email.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm mb-1">Nama</label>
                <input type="text" name="nama" value="{{ old('nama', auth()->user()->name) }}" required
                    class="w-full border rounded p-2">
                @error('nama')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">NIP</label>
                    <input type="text" name="nip" value="{{ auth()->user()->nip ?? '' }}"
                        disabled
                        class="w-full border rounded p-2 bg-gray-100 text-gray-700 cursor-not-allowed">
                    <div id="nip-availability-message" class="mt-1 text-sm"></div>
                    @error('nip')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Instansi</label>
                    <select name="instansi" required class="w-full border rounded p-2">
                        <option value="" disabled {{ old('instansi') ? '' : 'selected' }}>-- Pilih Instansi --
                        </option>
                        @foreach ($unitKerjaList as $unitKerja)
                            <option value="{{ $unitKerja->nama }}" {{ old('instansi') === $unitKerja->nama ? 'selected' : '' }}>
                                {{ $unitKerja->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('instansi')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Username Email yang Diajukan</label>
                    <div class="flex items-stretch">
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            class="flex-1 border rounded-l p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="misal: budi.susanto">
                        <span
                            class="inline-flex items-center px-3 border border-l-0 rounded-r bg-gray-100 text-gray-700 text-sm">
                            @kaltaraprov.go.id
                        </span>
                    </div>
                    <div id="email-availability-message" class="mt-1 text-sm"></div>
                    @error('username')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Email Alternatif (Gmail/Yahoo/Dll)</label>
                    <input type="email" name="email_alternatif"
                        value="{{ old('email_alternatif', auth()->user()->email) }}" class="w-full border rounded p-2">
                    @error('email_alternatif')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Nomor Kontak (HP)</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', auth()->user()->phone ?? '') }}" required
                        class="w-full border rounded p-2">
                    @error('no_hp')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Password
                        (Digunakan untuk login akun email)</label>
                    <input type="text" name="password" id="password-input" value="{{ old('password') }}" required
                        class="w-full border rounded p-2">

                    <!-- Password Strength Meter -->
                    <div class="mt-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-600">Kekuatan Password:</span>
                            <span id="strength-text" class="text-xs font-semibold">-</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div id="strength-bar" class="h-full rounded-full transition-all duration-300 ease-in-out"
                                style="width: 0%; background-color: #e5e7eb;">
                            </div>
                        </div>
                        <div id="password-requirements" class="mt-2 text-xs space-y-1">
                            <div id="req-length" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Minimal 15 karakter</span>
                            </div>
                            <div id="req-uppercase" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Huruf besar (A-Z)</span>
                            </div>
                            <div id="req-lowercase" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Huruf kecil (a-z)</span>
                            </div>
                            <div id="req-number" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Angka (0-9)</span>
                            </div>
                            <div id="req-symbol" class="flex items-center gap-1 text-gray-500">
                                <span class="requirement-icon">○</span>
                                <span>Simbol (!@#$%^&*)</span>
                            </div>
                        </div>
                    </div>

                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="consent_true" value="1" {{ old('consent_true') ? 'checked' : '' }}
                    required>
                <span class="text-sm">
                    Saya menyampaikan data sebenar-benarnya dan menyetujui persyaratan layanan.
                </span>
            </div>
            @error('consent_true')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror

            <div class="pt-4">
                <button type="submit" id="submit-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Kirim Permohonan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let checkTimeout;
            const usernameInput = document.getElementById('username');
            const messageDiv = document.getElementById('email-availability-message');
            const submitBtn = document.getElementById('submit-btn');
            const nipMessageDiv = document.getElementById('nip-availability-message');

            // Check NIP availability when page loads
            window.addEventListener('DOMContentLoaded', function() {
                checkNipAvailability();
            });

            usernameInput.addEventListener('input', function() {
                clearTimeout(checkTimeout);
                const username = this.value.trim();

                if (username.length < 3) {
                    messageDiv.textContent = '';
                    messageDiv.className = 'mt-1 text-sm';
                    return;
                }

                // Show checking message
                messageDiv.textContent = 'Memeriksa ketersediaan...';
                messageDiv.className = 'mt-1 text-sm text-gray-500';

                // Delay check by 500ms to avoid too many requests
                checkTimeout = setTimeout(() => {
                    checkEmailAvailability(username);
                }, 500);
            });

            async function checkEmailAvailability(username) {
                try {
                    const response = await fetch('{{ route('user.email.check-email') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            username: username
                        })
                    });

                    const data = await response.json();

                    if (data.available) {
                        messageDiv.textContent = data.message;
                        messageDiv.className = 'mt-1 text-sm text-green-600 font-semibold';
                        submitBtn.disabled = false;
                        usernameInput.classList.remove('border-red-500');
                        usernameInput.classList.add('border-green-500');
                    } else {
                        messageDiv.textContent = data.message;
                        messageDiv.className = 'mt-1 text-sm text-red-600 font-semibold';
                        submitBtn.disabled = true;
                        usernameInput.classList.remove('border-green-500');
                        usernameInput.classList.add('border-red-500');
                    }
                } catch (error) {
                    console.error('Error checking email availability:', error);
                    messageDiv.textContent = 'Gagal memeriksa ketersediaan email';
                    messageDiv.className = 'mt-1 text-sm text-red-600';
                }
            }

            // Password Strength Meter
            const passwordInput = document.getElementById('password-input');
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            const reqLength = document.getElementById('req-length');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqNumber = document.getElementById('req-number');
            const reqSymbol = document.getElementById('req-symbol');

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                checkPasswordStrength(password);
            });

            function checkPasswordStrength(password) {
                // Check requirements
                const hasLength = password.length >= 15;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

                // Update requirement indicators
                updateRequirement(reqLength, hasLength);
                updateRequirement(reqUppercase, hasUppercase);
                updateRequirement(reqLowercase, hasLowercase);
                updateRequirement(reqNumber, hasNumber);
                updateRequirement(reqSymbol, hasSymbol);

                // Calculate strength score (0-100)
                let score = 0;
                if (hasLength) score += 20;
                if (hasUppercase) score += 20;
                if (hasLowercase) score += 20;
                if (hasNumber) score += 20;
                if (hasSymbol) score += 20;

                // Update strength bar with animation
                strengthBar.style.width = score + '%';

                // Update color and text based on score
                if (score === 0) {
                    strengthBar.style.backgroundColor = '#e5e7eb';
                    strengthText.textContent = '-';
                    strengthText.className = 'text-xs font-semibold text-gray-500';
                } else if (score <= 40) {
                    strengthBar.style.backgroundColor = '#ef4444'; // red
                    strengthText.textContent = 'Sangat Lemah';
                    strengthText.className = 'text-xs font-semibold text-red-600';
                } else if (score <= 60) {
                    strengthBar.style.backgroundColor = '#f59e0b'; // orange
                    strengthText.textContent = 'Lemah';
                    strengthText.className = 'text-xs font-semibold text-orange-600';
                } else if (score <= 80) {
                    strengthBar.style.backgroundColor = '#eab308'; // yellow
                    strengthText.textContent = 'Sedang';
                    strengthText.className = 'text-xs font-semibold text-yellow-600';
                } else if (score < 100) {
                    strengthBar.style.backgroundColor = '#3b82f6'; // blue
                    strengthText.textContent = 'Kuat';
                    strengthText.className = 'text-xs font-semibold text-blue-600';
                } else {
                    strengthBar.style.backgroundColor = '#22c55e'; // green
                    strengthText.textContent = 'Sangat Kuat';
                    strengthText.className = 'text-xs font-semibold text-green-600';
                }
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

            // Check NIP availability
            async function checkNipAvailability() {
                try {
                    const response = await fetch('{{ route('user.email.check-nip') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.available) {
                        nipMessageDiv.textContent = '✓ ' + data.message;
                        nipMessageDiv.className = 'mt-1 text-sm text-green-600 font-semibold';
                        submitBtn.disabled = false;
                    } else {
                        nipMessageDiv.textContent = '✗ ' + data.message;
                        nipMessageDiv.className = 'mt-1 text-sm text-red-600 font-semibold';
                        submitBtn.disabled = true;

                        // Also show alert
                        alert('PERHATIAN: ' + data.message + '\n\nAnda tidak dapat mengajukan permohonan email baru karena NIP Anda sudah terdaftar.');
                    }
                } catch (error) {
                    console.error('Error checking NIP availability:', error);
                    nipMessageDiv.textContent = 'Gagal memeriksa ketersediaan NIP';
                    nipMessageDiv.className = 'mt-1 text-sm text-orange-600';
                }
            }
        </script>
    @endpush
@endsection
