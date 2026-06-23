@extends('layouts.authenticated')

@section('title', '- Detail Pembaruan Data Subdomain')
@section('header-title', 'Detail Pembaruan Data Subdomain')

@section('content')
    <div class="bg-white shadow rounded-lg p-6 space-y-6">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">Permohonan <span class="font-mono text-base text-gray-500">{{ $request->ticket_number }}</span></h2>
                <p class="text-gray-600">Subdomain: <span class="text-green-700 font-semibold">{{ $request->webMonitor->subdomain ?? '-' }}</span></p>
                <p class="text-gray-500 text-sm">Diajukan: {{ $request->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="text-right space-y-2">
                @include('user.subdomain.data-update._status', ['status' => $request->status])
                @if ($request->isEditable())
                    <div>
                        <a href="{{ route('user.subdomain.data-update.edit', $request->id) }}"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition">
                            Edit & Ajukan Ulang
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if ($request->admin_notes)
            <div class="bg-{{ $request->status === 'ditolak' ? 'red' : 'orange' }}-50 border border-{{ $request->status === 'ditolak' ? 'red' : 'orange' }}-300 text-{{ $request->status === 'ditolak' ? 'red' : 'orange' }}-800 px-4 py-3 rounded">
                <p class="font-semibold">Catatan Admin:</p>
                <p class="text-sm mt-1">{{ $request->admin_notes }}</p>
            </div>
        @endif

        @if ($request->reason)
            <div>
                <p class="font-semibold text-gray-700">Catatan Anda:</p>
                <p class="text-gray-600 text-sm mt-1">{{ $request->reason }}</p>
            </div>
        @endif

        @if ($request->hasStatusProposal())
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded">
                <p class="font-semibold">Usulan Status:</p>
                <p class="text-sm mt-1">{{ $request->statusProposalLabel() }} <span class="text-red-600">(data tidak dihapus)</span></p>
            </div>
        @endif

        <div>
            <h3 class="font-semibold text-gray-800 mb-3">Perbandingan Data</h3>
            @include('user.subdomain.data-update._diff', [
                'original' => $request->original_data ?? [],
                'proposed' => $request->proposed_data ?? [],
                'originalLabel' => 'Data Sebelumnya',
                'proposedLabel' => 'Usulan Anda',
            ])
        </div>

        <div>
            <a href="{{ route('user.subdomain.data-update.index') }}" class="text-gray-600 hover:text-gray-800">&larr; Kembali ke daftar</a>
        </div>
    </div>
@endsection
