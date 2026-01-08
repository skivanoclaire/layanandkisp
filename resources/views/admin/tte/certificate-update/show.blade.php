@extends('layouts.authenticated')
@section('title', '- Detail Permohonan Pembaruan Sertifikat TTE')
@section('header-title', 'Detail Permohonan Pembaruan Sertifikat TTE')

@section('content')
<div class="container mx-auto px-4 max-w-6xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Permohonan Pembaruan Sertifikat TTE</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Ticket Info -->
                <div class="border-b pb-4 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $tteCertificateUpdate->ticket_no }}</h2>
                            <p class="text-sm text-gray-600 mt-1">Diajukan pada {{ $tteCertificateUpdate->submitted_at->format('d M Y H:i') }}</p>
                        </div>
                        <span class="inline-block px-4 py-2 rounded-full {{ $tteCertificateUpdate->getStatusBadgeClass() }} font-semibold text-sm">
                            {{ $tteCertificateUpdate->getStatusLabel() }}
                        </span>
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Lengkap</label>
                            <p class="text-gray-900 font-medium">{{ $tteCertificateUpdate->nama }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">NIP</label>
                            <p class="text-gray-900 font-mono">{{ $tteCertificateUpdate->nip }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email Resmi</label>
                            <p class="text-gray-900">{{ $tteCertificateUpdate->email_resmi }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">No. HP</label>
                            <p class="text-gray-900">{{ $tteCertificateUpdate->no_hp }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Instansi</label>
                            <p class="text-gray-900">{{ $tteCertificateUpdate->instansi ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Jabatan</label>
                            <p class="text-gray-900">{{ $tteCertificateUpdate->jabatan ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="border-t mt-6 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi User</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                            <p class="text-gray-900">{{ $tteCertificateUpdate->user->username ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email User</label>
                            <p class="text-gray-900">{{ $tteCertificateUpdate->user->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="border-t mt-6 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Permohonan Diajukan</p>
                                <p class="text-sm text-gray-600">{{ $tteCertificateUpdate->submitted_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        @if($tteCertificateUpdate->processing_at)
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Sedang Diproses</p>
                                <p class="text-sm text-gray-600">{{ $tteCertificateUpdate->processing_at->format('d M Y H:i') }}</p>
                                @if($tteCertificateUpdate->processedBy)
                                <p class="text-sm text-gray-500">oleh {{ $tteCertificateUpdate->processedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($tteCertificateUpdate->completed_at)
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Selesai</p>
                                <p class="text-sm text-gray-600">{{ $tteCertificateUpdate->completed_at->format('d M Y H:i') }}</p>
                                @if($tteCertificateUpdate->processedBy)
                                <p class="text-sm text-gray-500">oleh {{ $tteCertificateUpdate->processedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($tteCertificateUpdate->rejected_at)
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-red-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Ditolak</p>
                                <p class="text-sm text-gray-600">{{ $tteCertificateUpdate->rejected_at->format('d M Y H:i') }}</p>
                                @if($tteCertificateUpdate->processedBy)
                                <p class="text-sm text-gray-500">oleh {{ $tteCertificateUpdate->processedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Status</h3>

                <form action="{{ route('admin.tte.certificate-update.update-status', $tteCertificateUpdate) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instansi</label>
                        <select name="instansi" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Pilih Instansi --</option>
                            @php
                                $unitKerjas = \App\Models\UnitKerja::where('is_active', true)->orderBy('nama')->get();
                            @endphp
                            @foreach($unitKerjas as $uk)
                                <option value="{{ $uk->nama }}" {{ old('instansi', $tteCertificateUpdate->instansi) == $uk->nama ? 'selected' : '' }}>
                                    {{ $uk->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $tteCertificateUpdate->jabatan) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500"
                            placeholder="Masukkan jabatan">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500" required>
                            <option value="menunggu" {{ $tteCertificateUpdate->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="diproses" {{ $tteCertificateUpdate->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ $tteCertificateUpdate->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ $tteCertificateUpdate->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan_admin" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500"
                            placeholder="Keterangan untuk pemohon (opsional)">{{ old('keterangan_admin', $tteCertificateUpdate->keterangan_admin) }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Update Status
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t">
                    <a href="{{ route('admin.tte.certificate-update.index') }}" class="block w-full text-center bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Notes Section (shown below if exists) -->
    @if($tteCertificateUpdate->keterangan_admin)
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Keterangan Admin</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $tteCertificateUpdate->keterangan_admin }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
