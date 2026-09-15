@extends('layouts.authenticated')

@section('title', '- Verifikasi Akun DTSEN')
@section('header-title', 'Verifikasi Akun Layanan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <a href="{{ route('admin.dtsen.akun.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $item->unitKerja->nama ?? '-' }}</h2>
                        <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Pengaju</dt><dd class="font-medium">{{ $item->user->name ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Diajukan</dt><dd class="font-medium">{{ $item->submitted_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Nomor Surat</dt><dd class="font-medium">{{ $item->nomor_surat }}</dd></div>
                    <div><dt class="text-gray-500">Tanggal Surat</dt><dd class="font-medium">{{ $item->tanggal_surat?->format('d/m/Y') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Sifat Surat</dt><dd class="font-medium">{{ \App\Models\DtsenAccountRequest::sifatSuratLabels()[$item->sifat_surat] ?? $item->sifat_surat }}</dd></div>
                    <div><dt class="text-gray-500">Jumlah Lampiran</dt><dd class="font-medium">{{ $item->jumlah_lampiran ?: '-' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Narahubung Teknis</dt>
                        <dd class="font-medium">{{ $item->narahubung_nama }} — {{ $item->narahubung_kontak }} — {{ $item->narahubung_email }}</dd></div>
                </dl>

                @if ($item->surat_path)
                    <div class="mt-4 text-sm">
                        <a href="{{ Storage::url($item->surat_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Surat Permohonan Akun (PDF)</a>
                    </div>
                @else
                    <p class="mt-4 text-sm text-red-600">Surat permohonan belum diunggah.</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Calon Pengguna Akun ({{ $item->members->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Nama</th>
                                <th class="px-3 py-2 text-left">NIP</th>
                                <th class="px-3 py-2 text-left">Jabatan</th>
                                <th class="px-3 py-2 text-left">Unit Kerja</th>
                                <th class="px-3 py-2 text-left">Kontak</th>
                                <th class="px-3 py-2 text-left">Surel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($item->members as $m)
                                <tr>
                                    <td class="px-3 py-2">{{ $m->nama }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $m->nip ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $m->jabatan ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $m->unit_kerja ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $m->no_hp ?: '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ $m->email }}
                                        @unless ($m->usesGovernmentEmail())
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-orange-100 text-orange-700">non .go.id</span>
                                        @endunless
                                        @if ($m->user_id)
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-green-100 text-green-700">akun portal cocok</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">Belum ada personel terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

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
            {{-- Form 1.2 — checklist & keputusan verifikasi --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-4">Verifikasi Akun</h3>
                <form action="{{ route('admin.dtsen.akun.verifikasi', $item->id) }}" method="POST" class="space-y-4"
                      x-data="{ status: '{{ $item->status }}' }">
                    @csrf

                    <div class="border rounded-lg p-3 bg-gray-50 space-y-2">
                        <p class="text-xs font-semibold text-gray-600">Checklist Kelengkapan</p>
                        @foreach ([
                            'check_surat_lengkap' => 'Surat permohonan lengkap',
                            'check_ttd_kepala_opd' => 'Ditandatangani Kepala Perangkat Daerah',
                            'check_data_personel' => 'Data personel valid & lengkap',
                        ] as $field => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="{{ $field }}" value="1" @checked($item->{$field})>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Verifikasi</label>
                        <select name="status" x-model="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="disetujui">Disetujui — akun diaktifkan</option>
                            <option value="dikembalikan">Dikembalikan untuk perbaikan</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div x-show="status !== 'disetujui'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Perbaikan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_perbaikan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->catatan_perbaikan }}</textarea>
                    </div>

                    <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold">Simpan Hasil Verifikasi</button>
                </form>
            </div>

            @if ($item->status === \App\Models\DtsenAccountRequest::STATUS_DISETUJUI)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Keaktifan Akun</h3>
                    <p class="text-sm mb-1">
                        Status: <span class="font-semibold {{ $item->is_active ? 'text-green-700' : 'text-red-600' }}">
                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </p>
                    <p class="text-sm text-gray-600">Terakhir digunakan: {{ $item->last_used_at?->format('d/m/Y H:i') ?? 'belum pernah' }}</p>
                    @if ($item->is_active && ($sisa = $item->idleDaysLeft()) !== null)
                        <p class="text-xs text-gray-500 mt-1">Nonaktif otomatis {{ $item->idleDeadline()?->format('d/m/Y') }} ({{ max($sisa, 0) }} hari lagi).</p>
                    @endif

                    <form action="{{ route('admin.dtsen.akun.toggle-aktif', $item->id) }}" method="POST" class="mt-4 space-y-2">
                        @csrf
                        <input type="text" name="alasan" placeholder="Alasan (opsional)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button class="w-full px-4 py-2 rounded-lg font-semibold text-sm text-white
                                {{ $item->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                            {{ $item->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </button>
                    </form>
                </div>
            @endif

            @if ($item->reactivations->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Riwayat Aktivasi Ulang</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach ($item->reactivations as $r)
                            <li class="border-l-2 border-gray-200 pl-3">
                                <p class="text-xs text-gray-500">{{ $r->created_at->format('d/m/Y H:i') }} — {{ $r->user->name ?? '-' }}</p>
                                <p class="font-medium">{{ $r->status_label }}</p>
                                <p class="text-gray-600">{{ $r->alasan }}</p>
                                @if ($r->catatan)<p class="text-xs text-gray-500 mt-1">Catatan: {{ $r->catatan }}</p>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
