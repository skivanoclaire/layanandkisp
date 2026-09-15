@extends('layouts.authenticated')

@section('title', '- Penilaian Substansi DTSEN')
@section('header-title', 'Penilaian Substansi Permintaan Data')

@section('content')
@php($belumDiputuskan = in_array($item->status, [
    \App\Models\DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
    \App\Models\DtsenDataRequest::STATUS_KLARIFIKASI,
], true))

<div class="container mx-auto p-6">
    <a href="{{ route('admin.dtsen.substansi.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @include('partials.dtsen.request-detail', ['item' => $item, 'wilayahLabels' => $wilayahLabels])

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Riwayat Aktivitas</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($logs as $log)
                        <li class="flex flex-wrap gap-x-3 border-l-2 border-gray-200 pl-3 py-1">
                            <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            <span class="font-medium">{{ $log->action }}</span>
                            <span class="text-gray-600">{{ $log->note }}@if($log->actor) — {{ $log->actor->name }}@endif</span>
                        </li>
                    @empty
                        <li class="text-gray-500">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            @include('partials.dtsen.timeline', ['item' => $item])

            @if ($belumDiputuskan)
                {{-- Form 3.2 — Keputusan substansi --}}
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Keputusan Substansi</h3>
                    <p class="text-xs text-gray-500 mb-4">Form 3.2 — SLA 2 hari kerja.</p>

                    <form action="{{ route('admin.dtsen.substansi.keputusan', $item->id) }}" method="POST" class="space-y-4"
                          x-data="{ hasil: 'diterima' }">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Penilaian</label>
                            <select name="sub_hasil" x-model="hasil" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="diterima">Diterima</option>
                                <option value="klarifikasi">Perlu Klarifikasi</option>
                                <option value="ditolak">Ditolak (final)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Penilaian</label>
                            <textarea name="sub_catatan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->sub_catatan }}</textarea>
                        </div>

                        <div x-show="hasil === 'ditolak'" x-cloak>
                            <label class="block text-sm font-medium text-red-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="sub_alasan_penolakan" rows="3" class="w-full px-3 py-2 border border-red-300 rounded-lg text-sm">{{ $item->sub_alasan_penolakan }}</textarea>
                            <p class="text-xs text-red-600 mt-1">Penolakan bersifat final; permohonan tidak dapat dilanjutkan.</p>
                        </div>

                        <details class="border rounded-lg p-3 bg-gray-50">
                            <summary class="text-sm font-semibold text-gray-700 cursor-pointer">Penilaian per variabel (opsional)</summary>
                            <p class="text-xs text-gray-500 mt-2 mb-3">Centang variabel yang <strong>tidak disetujui</strong>.</p>
                            <div class="space-y-3 max-h-72 overflow-y-auto">
                                @foreach ($item->requestVariables as $rv)
                                    <div class="border-b pb-2">
                                        <label class="flex items-start gap-2 text-sm">
                                            <input type="checkbox" name="variabel_ditolak[]" value="{{ $rv->dtsen_variable_id }}" class="mt-1"
                                                   @checked(! $rv->disetujui)>
                                            <span>
                                                <span class="font-medium">{{ $rv->variable->nama ?? '-' }}</span>
                                                <span class="block font-mono text-[11px] text-gray-400">{{ $rv->variable->kode ?? '' }} · L{{ $rv->variable->level_minimal ?? '-' }}</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ $rv->kegunaan }}</span>
                                            </span>
                                        </label>
                                        <input type="text" name="catatan_variabel[{{ $rv->dtsen_variable_id }}]"
                                               value="{{ $rv->catatan_verifikator }}" placeholder="Catatan variabel"
                                               class="mt-2 ml-6 w-[calc(100%-1.5rem)] px-2 py-1 border border-gray-300 rounded text-xs">
                                    </div>
                                @endforeach
                            </div>
                        </details>

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold">Simpan Keputusan</button>
                    </form>
                </div>

                {{-- Form 3.3 — Berita acara klarifikasi --}}
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Berita Acara Klarifikasi</h3>
                    <p class="text-xs text-gray-500 mb-4">Form 3.3 — diisi bila pemohon diundang klarifikasi.</p>
                    <form action="{{ route('admin.dtsen.substansi.klarifikasi', $item->id) }}" method="POST"
                          enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" required value="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tempat / Media <span class="text-red-500">*</span></label>
                            <input type="text" name="tempat_media" required placeholder="mis. Ruang Rapat Bapperida / Zoom"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Peserta <span class="text-red-500">*</span></label>
                            <textarea name="peserta" rows="2" required placeholder="Tim Pelaksana &amp; perwakilan OPD"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pokok Klarifikasi <span class="text-red-500">*</span></label>
                            <textarea name="pokok_klarifikasi" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil / Kesimpulan</label>
                            <textarea name="hasil" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berita Acara Bertanda Tangan (PDF)</label>
                            <input type="file" name="berita_acara" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg p-2">
                        </div>
                        <button class="w-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Simpan Berita Acara</button>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Keputusan Substansi</h3>
                    <p class="text-sm">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            {{ $item->sub_hasil === 'diterima' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($item->sub_hasil ?? '-') }}
                        </span>
                    </p>
                    @if ($item->sub_catatan)<p class="text-sm text-gray-600 mt-2">{{ $item->sub_catatan }}</p>@endif
                    @if ($item->sub_alasan_penolakan)
                        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            <p class="font-semibold">Alasan penolakan</p>
                            <p class="mt-1">{{ $item->sub_alasan_penolakan }}</p>
                        </div>
                    @endif
                    <p class="text-xs text-gray-500 mt-3">{{ $item->subVerifier->name ?? '-' }} — {{ $item->verif_substansi_at?->format('d/m/Y H:i') }}</p>
                </div>

                {{-- Penyerahan tahap: verifikasi substansi selesai, proses berlanjut di DKISP.
                     Tanpa penunjuk ini halaman terasa buntu setelah keputusan dibuat. --}}
                @if ($item->sub_hasil === 'diterima')
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="font-bold text-gray-800 mb-1">Tahap Berikutnya</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            Verifikasi substansi selesai. Pemrosesan data &amp; QA
                            @if ($item->requiresBast())
                                , pengesahan BAST,
                            @endif
                            hingga penerbitan token akses ditangani DKISP selaku prosesor.
                        </p>

                        @if (auth()->user()?->hasPermission('Kelola Permohonan DTSEN'))
                            <a href="{{ route('admin.dtsen.permohonan.show', $item->id) }}"
                               class="block text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm">
                                Lanjut ke Pemrosesan &amp; QA &rarr;
                            </a>
                        @else
                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                                Menunggu DKISP memproses. Anda akan melihat perkembangannya pada
                                riwayat permohonan ini.
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
