@extends('layouts.authenticated')
@section('title', '- Detail Permohonan Reset Passphrase TTE')
@section('header-title', 'Detail Permohonan Reset Passphrase TTE')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Permohonan Reset Passphrase TTE</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <!-- Ticket Info -->
        <div class="border-b pb-4">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">{{ $ttePassphraseReset->ticket_no }}</h2>
                    <p class="text-sm text-gray-600 mt-1">Diajukan pada {{ $ttePassphraseReset->submitted_at->format('d M Y H:i') }}</p>
                </div>
                <span class="inline-block px-4 py-2 rounded-full {{ $ttePassphraseReset->getStatusBadgeClass() }} font-semibold">
                    {{ $ttePassphraseReset->getStatusLabel() }}
                </span>
            </div>
        </div>

        <!-- Personal Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Lengkap</label>
                <p class="text-gray-900 font-medium">{{ $ttePassphraseReset->nama }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">NIP</label>
                <p class="text-gray-900 font-mono">{{ $ttePassphraseReset->nip }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Email Resmi</label>
                <p class="text-gray-900">{{ $ttePassphraseReset->email_resmi }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">No. HP</label>
                <p class="text-gray-900">{{ $ttePassphraseReset->no_hp }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Instansi</label>
                <p class="text-gray-900">{{ $ttePassphraseReset->instansi ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Jabatan</label>
                <p class="text-gray-900">{{ $ttePassphraseReset->jabatan ?? '-' }}</p>
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Timeline Status</h3>
            <div class="space-y-3">
                <div class="flex items-start">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mt-1.5 mr-3"></div>
                    <div>
                        <p class="font-medium text-gray-900">Permohonan Diajukan</p>
                        <p class="text-sm text-gray-600">{{ $ttePassphraseReset->submitted_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                @if($ttePassphraseReset->processing_at)
                <div class="flex items-start">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1.5 mr-3"></div>
                    <div>
                        <p class="font-medium text-gray-900">Sedang Diproses</p>
                        <p class="text-sm text-gray-600">{{ $ttePassphraseReset->processing_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif

                @if($ttePassphraseReset->completed_at)
                <div class="flex items-start">
                    <div class="w-3 h-3 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                    <div>
                        <p class="font-medium text-gray-900">Selesai</p>
                        <p class="text-sm text-gray-600">{{ $ttePassphraseReset->completed_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif

                @if($ttePassphraseReset->rejected_at)
                <div class="flex items-start">
                    <div class="w-3 h-3 bg-red-500 rounded-full mt-1.5 mr-3"></div>
                    <div>
                        <p class="font-medium text-gray-900">Ditolak</p>
                        <p class="text-sm text-gray-600">{{ $ttePassphraseReset->rejected_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Admin Notes -->
        @if($ttePassphraseReset->keterangan_admin)
        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Keterangan dari Admin</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700">{{ $ttePassphraseReset->keterangan_admin }}</p>
            </div>
        </div>
        @endif

        <!-- Back Button -->
        <div class="border-t pt-4">
            <a href="{{ route('user.tte.passphrase-reset.index') }}" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold transition">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
