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

        @if ($request->isCompleted())
            <div class="border-t pt-6">
                <h3 class="font-semibold text-gray-800 mb-3">Berita Acara</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
                    @if ($request->hasBeritaAcara())
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold">Berita acara sudah tersedia.</p>
                                @if ($request->berita_acara_uploaded_at)
                                    <p class="text-xs text-blue-600 mt-1">Diunggah: {{ $request->berita_acara_uploaded_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <a href="{{ route('admin.subdomain.data-update.berita-acara.download', $request->id) }}"
                               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                </svg>
                                Unduh Berita Acara
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-blue-800">Belum ada berita acara yang diunggah untuk permohonan ini.</p>
                    @endif

                    <form method="POST" action="{{ route('admin.subdomain.data-update.berita-acara.upload', $request->id) }}"
                          enctype="multipart/form-data" class="flex flex-wrap items-end gap-3 pt-2 border-t border-blue-100">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 mb-1">
                                {{ $request->hasBeritaAcara() ? 'Ganti berkas (PDF/DOCX)' : 'Unggah berkas (PDF/DOCX)' }}
                            </label>
                            <input type="file" name="file_berita_acara" accept=".pdf,.doc,.docx" required
                                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white">
                            <p class="text-xs text-gray-500 mt-1">Maksimal 10 MB.</p>
                            @error('file_berita_acara')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-lg">
                            {{ $request->hasBeritaAcara() ? 'Ganti' : 'Unggah' }}
                        </button>
                    </form>
                </div>
            </div>
        @endif

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
