@extends('layouts.authenticated')
@section('title', '- Detail Permohonan Pendampingan TTE')
@section('header-title', 'Detail Permohonan Pendampingan TTE')

@section('content')
<div class="container mx-auto px-4 max-w-6xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Permohonan Pendampingan Aktivasi dan Penggunaan TTE</h1>
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
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $tteAssistance->ticket_no }}</h2>
                            <p class="text-sm text-gray-600 mt-1">Diajukan pada {{ $tteAssistance->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <span class="inline-block px-4 py-2 rounded-full {{ $tteAssistance->getStatusBadgeClass() }} font-semibold text-sm">
                            {{ $tteAssistance->getStatusLabel() }}
                        </span>
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Lengkap</label>
                            <p class="text-gray-900 font-medium">{{ $tteAssistance->nama }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">NIP</label>
                            <p class="text-gray-900 font-mono">{{ $tteAssistance->nip }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email Resmi</label>
                            <p class="text-gray-900">{{ $tteAssistance->email_resmi }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">No. HP</label>
                            <p class="text-gray-900">{{ $tteAssistance->no_hp }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Instansi</label>
                            <p class="text-gray-900">{{ $tteAssistance->instansi }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Jabatan</label>
                            <p class="text-gray-900">{{ $tteAssistance->jabatan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Assistance Details -->
                <div class="border-t mt-6 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Pendampingan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Waktu Pendampingan yang Diajukan</label>
                            <p class="text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($tteAssistance->waktu_pendampingan)->format('d M Y H:i') }} WIB
                            </p>
                        </div>
                        @if($tteAssistance->surat_permohonan_path)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Surat Permohonan</label>
                            <a href="{{ Storage::url($tteAssistance->surat_permohonan_path) }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Surat Permohonan
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- User Info -->
                <div class="border-t mt-6 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi User</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                            <p class="text-gray-900">{{ $tteAssistance->user->username ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email User</label>
                            <p class="text-gray-900">{{ $tteAssistance->user->email ?? '-' }}</p>
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
                                <p class="text-sm text-gray-600">{{ $tteAssistance->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        @if($tteAssistance->status == 'proses' && $tteAssistance->updated_at != $tteAssistance->created_at)
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-purple-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Sedang Diproses</p>
                                <p class="text-sm text-gray-600">{{ $tteAssistance->updated_at->format('d M Y H:i') }}</p>
                                @if($tteAssistance->processedBy)
                                <p class="text-sm text-gray-500">oleh {{ $tteAssistance->processedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($tteAssistance->status == 'selesai')
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Selesai</p>
                                <p class="text-sm text-gray-600">{{ $tteAssistance->updated_at->format('d M Y H:i') }}</p>
                                @if($tteAssistance->processedBy)
                                <p class="text-sm text-gray-500">oleh {{ $tteAssistance->processedBy->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($tteAssistance->status == 'ditolak')
                        <div class="flex items-start">
                            <div class="w-3 h-3 bg-red-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="font-medium text-gray-900">Ditolak</p>
                                <p class="text-sm text-gray-600">{{ $tteAssistance->updated_at->format('d M Y H:i') }}</p>
                                @if($tteAssistance->processedBy)
                                <p class="text-sm text-gray-500">oleh {{ $tteAssistance->processedBy->name }}</p>
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

                <form action="{{ route('admin.tte.assistance.update-status', $tteAssistance) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-green-500" required>
                            <option value="menunggu" {{ $tteAssistance->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="proses" {{ $tteAssistance->status == 'proses' ? 'selected' : '' }}>Proses</option>
                            <option value="selesai" {{ $tteAssistance->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ $tteAssistance->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan_admin" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-green-500"
                            placeholder="Keterangan untuk pemohon (opsional)">{{ old('keterangan_admin', $tteAssistance->keterangan_admin) }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Update Status
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t">
                    <a href="{{ route('admin.tte.assistance.index') }}" class="block w-full text-center bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Notes Section (shown below if exists) -->
    @if($tteAssistance->keterangan_admin)
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Keterangan Admin</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $tteAssistance->keterangan_admin }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
