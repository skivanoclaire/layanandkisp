@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Detail Permintaan Reset Password</h1>
                    <p class="text-gray-600 mt-2">Review dan proses permintaan reset password email</p>
                </div>
                <a href="{{ route('admin.email-password-reset.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Status Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Status Permintaan</h2>
                    @if($resetRequest->status === 'pending')
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            Menunggu Proses
                        </span>
                    @elseif($resetRequest->status === 'processed')
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Sudah Diproses
                        </span>
                    @else
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            Ditolak
                        </span>
                    @endif
                </div>
            </div>

            @if($resetRequest->status === 'pending')
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 m-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 font-medium">
                                Permintaan ini menunggu untuk diproses. Silakan review informasi di bawah dan putuskan untuk menerima atau menolak permintaan.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Request Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- User Information -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-green-600 text-white px-6 py-4">
                        <h2 class="text-xl font-semibold">Informasi User</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center">
                                    <span class="text-green-700 font-bold text-xl">
                                        {{ substr($resetRequest->user->name, 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $resetRequest->user->name }}</h3>
                                <dl class="mt-2 space-y-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                        </svg>
                                        <span>User ID: <strong>{{ $resetRequest->user->id }}</strong></span>
                                    </div>
                                    @if($resetRequest->user->email)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $resetRequest->user->email }}</span>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Reset Information -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="bg-green-600 text-white px-6 py-4">
                        <h2 class="text-xl font-semibold">Informasi Reset Email</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-6">
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-2">Email yang akan direset</dt>
                                <dd class="flex items-center text-lg text-gray-900">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-mono font-semibold">{{ $resetRequest->email_address }}</span>
                                </dd>
                            </div>

                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-2">NIP</dt>
                                <dd class="flex items-center text-base text-gray-900">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span class="font-mono">{{ $resetRequest->nip }}</span>
                                </dd>
                            </div>

                            @if($decryptedPassword)
                                <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="h-6 w-6 text-red-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <div class="flex-1">
                                            <dt class="text-sm font-medium text-red-800 mb-2">Password Baru (Ter-dekripsi)</dt>
                                            <dd class="font-mono text-lg font-bold text-red-900 bg-white px-4 py-3 rounded border border-red-300">
                                                {{ $decryptedPassword }}
                                            </dd>
                                            <p class="text-xs text-red-700 mt-2">
                                                <strong>PERHATIAN:</strong> Password ini hanya ditampilkan untuk admin. Pastikan untuk menyalin dan menggunakannya dengan aman.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="border-b border-gray-200 pb-4">
                                <dt class="text-sm font-medium text-gray-500 mb-2">Tanggal Pengajuan</dt>
                                <dd class="flex items-center text-base text-gray-900">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $resetRequest->created_at->format('d F Y, H:i') }} WIB
                                </dd>
                            </div>

                            @if($resetRequest->processed_at)
                                <div class="border-b border-gray-200 pb-4">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Tanggal Diproses</dt>
                                    <dd class="flex items-center text-base text-gray-900">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $resetRequest->processed_at->format('d F Y, H:i') }} WIB
                                    </dd>
                                </div>
                            @endif

                            @if($resetRequest->processedBy)
                                <div class="border-b border-gray-200 pb-4">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Diproses Oleh</dt>
                                    <dd class="flex items-center text-base text-gray-900">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $resetRequest->processedBy->name }}
                                    </dd>
                                </div>
                            @endif

                            @if($resetRequest->admin_notes)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Catatan Admin</dt>
                                    <dd class="text-base text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        {{ $resetRequest->admin_notes }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions -->
            <div class="lg:col-span-1">
                @if($resetRequest->status === 'pending')
                    <!-- Process Form -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                        <div class="bg-green-600 text-white px-6 py-4">
                            <h2 class="text-lg font-semibold">Proses Permintaan</h2>
                        </div>
                        <form action="{{ route('admin.email-password-reset.process', $resetRequest->id) }}" method="POST" class="p-6">
                            @csrf

                            <div class="mb-4">
                                <label for="reset_method" class="block text-sm font-medium text-gray-700 mb-2">
                                    Metode Reset <span class="text-red-500">*</span>
                                </label>
                                <select name="reset_method"
                                        id="reset_method"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="api" selected>Otomatis (via API)</option>
                                    <option value="manual">Manual (via cPanel)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Pilih metode reset password</p>
                            </div>

                            <div class="mb-4">
                                <label for="admin_notes_process" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan (Opsional)
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes_process"
                                          rows="4"
                                          placeholder="Tambahkan catatan jika perlu..."
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                            </div>

                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                <p class="text-xs text-green-700">
                                    <strong>Info:</strong> Setelah menekan tombol ini, status akan berubah menjadi "Diproses".
                                    @if($decryptedPassword)
                                    Gunakan password: <strong class="font-mono">{{ $decryptedPassword }}</strong>
                                    @endif
                                </p>
                            </div>

                            <button type="submit"
                                    class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center justify-center font-semibold">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Proses Permintaan
                            </button>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-red-600 text-white px-6 py-4">
                            <h2 class="text-lg font-semibold">Tolak Permintaan</h2>
                        </div>
                        <form action="{{ route('admin.email-password-reset.reject', $resetRequest->id) }}" method="POST" class="p-6">
                            @csrf

                            <div class="mb-4">
                                <label for="admin_notes_reject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes_reject"
                                          rows="4"
                                          required
                                          placeholder="Jelaskan alasan penolakan..."
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('admin_notes') border-red-500 @enderror"></textarea>
                                @error('admin_notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                <p class="text-xs text-red-700">
                                    <strong>Perhatian:</strong> Permintaan yang ditolak tidak dapat diproses kembali. User perlu mengajukan permintaan baru.
                                </p>
                            </div>

                            <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin menolak permintaan ini?')"
                                    class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center justify-center font-semibold">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tolak Permintaan
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Already Processed -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="bg-gray-600 text-white px-6 py-4">
                            <h2 class="text-lg font-semibold">Status</h2>
                        </div>
                        <div class="p-6">
                            @if($resetRequest->status === 'processed')
                                <div class="text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Sudah Diproses</h3>
                                    <p class="text-sm text-gray-500">
                                        Permintaan ini telah diproses pada {{ $resetRequest->processed_at->format('d F Y, H:i') }} WIB
                                    </p>
                                </div>
                            @else
                                <div class="text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Ditolak</h3>
                                    <p class="text-sm text-gray-500">
                                        Permintaan ini telah ditolak pada {{ $resetRequest->processed_at->format('d F Y, H:i') }} WIB
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Instructions Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                    <h3 class="text-sm font-semibold text-blue-800 mb-2">Panduan Reset Password</h3>
                    <ol class="text-xs text-blue-700 space-y-2 list-decimal list-inside">
                        <li>Review informasi user dan email yang akan direset</li>
                        <li>Salin password yang telah didekripsi</li>
                        <li>Login ke cPanel atau gunakan API</li>
                        <li>Reset password email sesuai dengan password baru</li>
                        <li>Klik tombol "Proses Permintaan" setelah selesai</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
