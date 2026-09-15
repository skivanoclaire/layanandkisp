@extends('layouts.authenticated')

@section('title', '- Detail Akun DTSEN')
@section('header-title', 'Detail Permohonan Akun DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <a href="{{ route('user.dtsen.akun.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $item->unitKerja->nama ?? 'Perangkat Daerah' }}</h2>
                        <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Nomor Surat</dt><dd class="font-medium">{{ $item->nomor_surat }}</dd></div>
                    <div><dt class="text-gray-500">Tanggal Surat</dt><dd class="font-medium">{{ $item->tanggal_surat?->format('d/m/Y') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Sifat Surat</dt><dd class="font-medium">{{ \App\Models\DtsenAccountRequest::sifatSuratLabels()[$item->sifat_surat] ?? $item->sifat_surat }}</dd></div>
                    <div><dt class="text-gray-500">Jumlah Lampiran</dt><dd class="font-medium">{{ $item->jumlah_lampiran ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Narahubung Teknis</dt><dd class="font-medium">{{ $item->narahubung_nama }}</dd></div>
                    <div><dt class="text-gray-500">Kontak Narahubung</dt><dd class="font-medium">{{ $item->narahubung_kontak }} — {{ $item->narahubung_email }}</dd></div>
                    <div><dt class="text-gray-500">Diajukan</dt><dd class="font-medium">{{ $item->submitted_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Diverifikasi</dt><dd class="font-medium">{{ $item->verified_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                </dl>

                @if ($item->surat_path)
                    <div class="mt-4 text-sm">
                        <a href="{{ Storage::url($item->surat_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Surat Permohonan Akun</a>
                    </div>
                @endif

                @if ($item->catatan_perbaikan)
                    <div class="mt-4 rounded-lg border border-orange-300 bg-orange-50 p-3 text-sm text-orange-900">
                        <p class="font-semibold">Catatan verifikator</p>
                        <p class="mt-1 whitespace-pre-line">{{ $item->catatan_perbaikan }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Pengguna Akun Terdaftar</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Nama</th>
                                <th class="px-3 py-2 text-left">NIP</th>
                                <th class="px-3 py-2 text-left">Jabatan</th>
                                <th class="px-3 py-2 text-left">Kontak</th>
                                <th class="px-3 py-2 text-left">Surel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($item->members as $member)
                                <tr>
                                    <td class="px-3 py-2">{{ $member->nama }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $member->nip ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $member->jabatan ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $member->no_hp ?: '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ $member->email }}
                                        @unless ($member->usesGovernmentEmail())
                                            <span class="ml-1 text-xs text-orange-600">(non .go.id)</span>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">Belum ada personel terdaftar.</td></tr>
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
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Keaktifan Akun</h3>
                @if ($item->status !== \App\Models\DtsenAccountRequest::STATUS_DISETUJUI)
                    <p class="text-sm text-gray-600">Akun aktif setelah permohonan disetujui Admin DKISP.</p>
                @elseif ($item->is_active)
                    <p class="text-sm"><span class="font-semibold text-green-700">Aktif</span> sejak {{ $item->activated_at?->format('d/m/Y') }}.</p>
                    <p class="text-sm text-gray-600 mt-2">Terakhir digunakan: {{ $item->last_used_at?->format('d/m/Y H:i') ?? 'belum pernah' }}</p>
                    @if (($sisa = $item->idleDaysLeft()) !== null)
                        <div class="mt-3 rounded-lg border p-3 text-sm
                            {{ $sisa <= \App\Models\DtsenAccountRequest::WARN_BEFORE_DAYS ? 'border-orange-300 bg-orange-50 text-orange-900' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                            Akan dinonaktifkan otomatis dalam <strong>{{ max($sisa, 0) }} hari</strong>
                            ({{ $item->idleDeadline()?->format('d/m/Y') }}) bila tidak digunakan.
                        </div>
                    @endif
                @else
                    <p class="text-sm"><span class="font-semibold text-red-600">Nonaktif</span>
                        @if ($item->deactivated_at) sejak {{ $item->deactivated_at->format('d/m/Y') }} @endif.
                    </p>

                    @php($pendingReaktivasi = $item->reactivations->firstWhere('status', \App\Models\DtsenAccountReactivation::STATUS_DIAJUKAN))
                    @if ($pendingReaktivasi)
                        <div class="mt-3 rounded-lg border border-yellow-300 bg-yellow-50 p-3 text-sm text-yellow-900">
                            Permintaan aktivasi ulang sedang menunggu keputusan DKISP
                            (diajukan {{ $pendingReaktivasi->created_at->format('d/m/Y') }}).
                        </div>
                    @else
                        <form action="{{ route('user.dtsen.akun.reaktivasi', $item->id) }}" method="POST" class="mt-4 space-y-2">
                            @csrf
                            <label class="block text-sm font-medium text-gray-700">Alasan Aktivasi Ulang <span class="text-red-500">*</span></label>
                            <textarea name="alasan" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                      placeholder="mis. akan mengajukan permintaan data untuk program bantuan sosial TA berjalan"></textarea>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                                Ajukan Aktivasi Ulang
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            @if ($item->reactivations->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Riwayat Aktivasi Ulang</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach ($item->reactivations as $r)
                            <li class="border-l-2 border-gray-200 pl-3">
                                <p class="text-gray-500 text-xs">{{ $r->created_at->format('d/m/Y H:i') }}</p>
                                <p class="font-medium">{{ $r->status_label }}</p>
                                <p class="text-gray-600">{{ $r->alasan }}</p>
                                @if ($r->catatan)
                                    <p class="text-gray-500 text-xs mt-1">Catatan: {{ $r->catatan }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($item->isUsable())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Langkah Berikutnya</h3>
                    <p class="text-sm text-gray-600 mb-3">Akun Anda aktif. Lanjutkan ke Tahap 2 dengan mengajukan permintaan data.</p>
                    <a href="{{ route('user.dtsen.permohonan.create') }}"
                       class="block text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                        Ajukan Permintaan Data
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
