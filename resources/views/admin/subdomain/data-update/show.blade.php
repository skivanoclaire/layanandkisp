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
                <p class="text-gray-500 text-sm">Pemohon: {{ $request->user->name ?? '-' }} &middot; Diajukan: {{ $request->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>@include('user.subdomain.data-update._status', ['status' => $request->status])</div>
        </div>

        @if ($request->reason)
            <div>
                <p class="font-semibold text-gray-700">Catatan Pemohon:</p>
                <p class="text-gray-600 text-sm mt-1">{{ $request->reason }}</p>
            </div>
        @endif

        @if ($request->admin_notes)
            <div class="bg-gray-50 border border-gray-300 text-gray-800 px-4 py-3 rounded">
                <p class="font-semibold">Catatan Admin:</p>
                <p class="text-sm mt-1">{{ $request->admin_notes }}</p>
                @if ($request->processedBy)
                    <p class="text-xs text-gray-500 mt-1">oleh {{ $request->processedBy->name }} &middot; {{ optional($request->processed_at)->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        @endif

        @if ($request->hasStatusProposal())
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded">
                <p class="font-semibold">⚠ Usulan Status: {{ $request->statusProposalLabel() }}</p>
                <p class="text-sm mt-1">
                    Status saat ini:
                    <strong>{{ $request->webMonitor && $request->webMonitor->is_decommissioned ? 'Non-aktif (pensiun)' : 'Aktif' }}</strong>.
                    Jika disetujui, penanda non-aktif akan diterapkan. <strong>Data tidak dihapus</strong> dan tetap tersimpan.
                </p>
            </div>
        @endif

        <div>
            <h3 class="font-semibold text-gray-800 mb-3">Perbandingan Data (Data Saat Ini vs Usulan)</h3>
            @php
                $currentData = $request->webMonitor
                    ? collect(\App\Models\SubdomainDataUpdateRequest::EDITABLE_FIELDS)
                        ->mapWithKeys(fn ($f) => [$f => $request->webMonitor->{$f}])->all()
                    : ($request->original_data ?? []);
            @endphp
            @include('user.subdomain.data-update._diff', [
                'original' => $currentData,
                'proposed' => $request->proposed_data ?? [],
                'originalLabel' => 'Data Saat Ini (WebMonitor)',
                'proposedLabel' => 'Usulan Pemohon',
            ])
        </div>

        @if ($request->status === 'pending')
            <div class="border-t pt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Setujui --}}
                <form method="POST" action="{{ route('admin.subdomain.data-update.approve', $request->id) }}"
                      class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-2"
                      onsubmit="return confirm('Setujui dan terapkan usulan ini ke data subdomain?');">
                    @csrf
                    <h4 class="font-semibold text-green-800">Setujui</h4>
                    <p class="text-xs text-green-700">Data usulan akan langsung diterapkan ke subdomain.</p>
                    <textarea name="admin_notes" rows="2" placeholder="Catatan (opsional)"
                              class="w-full px-3 py-2 border border-gray-300 rounded text-sm"></textarea>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded">Setujui & Terapkan</button>
                </form>

                {{-- Revisi --}}
                <form method="POST" action="{{ route('admin.subdomain.data-update.revisi', $request->id) }}"
                      class="bg-orange-50 border border-orange-200 rounded-lg p-4 space-y-2">
                    @csrf
                    <h4 class="font-semibold text-orange-800">Minta Revisi</h4>
                    <p class="text-xs text-orange-700">Dikembalikan ke pemohon untuk diperbaiki.</p>
                    <textarea name="admin_notes" rows="2" required placeholder="Catatan revisi (wajib)"
                              class="w-full px-3 py-2 border border-gray-300 rounded text-sm"></textarea>
                    <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 rounded">Minta Revisi</button>
                </form>

                {{-- Tolak --}}
                <form method="POST" action="{{ route('admin.subdomain.data-update.reject', $request->id) }}"
                      class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-2"
                      onsubmit="return confirm('Tolak permohonan ini?');">
                    @csrf
                    <h4 class="font-semibold text-red-800">Tolak</h4>
                    <p class="text-xs text-red-700">Permohonan ditutup dengan alasan.</p>
                    <textarea name="admin_notes" rows="2" required placeholder="Alasan penolakan (wajib)"
                              class="w-full px-3 py-2 border border-gray-300 rounded text-sm"></textarea>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded">Tolak</button>
                </form>
            </div>
        @else
            <div class="border-t pt-4 text-sm text-gray-500">
                Permohonan ini sudah diproses ({{ $request->status }}) dan tidak memerlukan tindakan lebih lanjut.
            </div>
        @endif

        <div>
            <a href="{{ route('admin.subdomain.data-update.index') }}" class="text-gray-600 hover:text-gray-800">&larr; Kembali ke daftar</a>
        </div>
    </div>
@endsection
