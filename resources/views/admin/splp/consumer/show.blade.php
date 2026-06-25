@extends('layouts.authenticated')

@section('title', '- Detail Akses Konsumen SPLP')
@section('header-title', 'Detail Permohonan — Akses Konsumen (SPLP)')

@section('content')
<div class="container mx-auto p-6">
    <a href="{{ route('admin.splp.consumer.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded my-4">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Akses ke: {{ $item->service->nama_layanan ?? '-' }}</h2>
                        <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Pemohon</dt><dd class="font-medium">{{ $item->nama }} ({{ $item->nip ?? '-' }})</dd></div>
                    <div><dt class="text-gray-500">Instansi Pemohon</dt><dd class="font-medium">{{ $item->unitKerja->nama ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Instansi Konsumen</dt><dd class="font-medium">{{ $item->instansiKonsumen->nama ?? $item->unitKerja->nama ?? '-' }} @if($item->is_eksternal)<span class="text-orange-600">(Eksternal)</span>@endif</dd></div>
                    <div><dt class="text-gray-500">OPD Pemilik Layanan</dt><dd class="font-medium">{{ $item->service->opdPemilik->nama ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Klasifikasi Layanan</dt><dd class="font-medium">{{ ucfirst($item->service->klasifikasi_data ?? '-') }}</dd></div>
                    <div><dt class="text-gray-500">Estimasi Volume</dt><dd class="font-medium">{{ $item->estimasi_volume ?? '-' }} {{ str_replace('_', ' ', $item->volume_satuan ?? '') }}</dd></div>
                    <div><dt class="text-gray-500">Kredensial Diminta</dt><dd class="font-medium">{{ str_replace('_', ' ', $item->credential_pref) }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">IP / Domain Sumber</dt><dd class="font-mono break-all">{{ $item->ip_domain }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Tujuan Penggunaan</dt><dd>{{ $item->tujuan_penggunaan }}</dd></div>
                </dl>

                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    @if ($item->surat_permohonan_path)<a href="{{ Storage::url($item->surat_permohonan_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Surat Permohonan</a>@endif
                    @if ($item->pks_path)<a href="{{ Storage::url($item->pks_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 PKS / Surat Kuasa</a>@endif
                    @if ($item->hasil_uji_path)<a href="{{ Storage::url($item->hasil_uji_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Hasil Uji Sandbox</a>@endif
                </div>

                @if ($item->splp_consumer_id)
                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                        ✓ Konsumen terdaftar di Master Data (ID #{{ $item->splp_consumer_id }}). Kredensial mentah diserahkan via kanal aman, tidak disimpan di aplikasi.
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Riwayat Aktivitas</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($item->logs->sortByDesc('created_at') as $log)
                        <li class="flex gap-3 border-l-2 border-gray-200 pl-3">
                            <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            <span class="font-medium">{{ $log->action }}</span>
                            <span class="text-gray-600">{{ $log->note }} @if($log->actor)— {{ $log->actor->name }}@endif</span>
                        </li>
                    @empty
                        <li class="text-gray-500">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-4">Manajemen Status</h3>
                <form action="{{ route('admin.splp.consumer.status', $item->id) }}" method="POST" class="space-y-4"
                      x-data="{ status: '{{ $item->status }}' }">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Status</label>
                        <select name="status" x-model="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach (\App\Models\SplpConsumerRequest::statusLabels() as $val => $label)
                                @continue($val === 'draft')
                                <option value="{{ $val }}" @selected($item->status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="border rounded-lg p-3 bg-gray-50 space-y-2">
                        <p class="text-xs font-semibold text-gray-600">Checklist Verifikasi</p>
                        @foreach (['check_administrasi' => 'Administrasi lengkap', 'check_koordinasi_opd' => 'Koordinasi/persetujuan OPD penyedia', 'check_teknis' => 'Verifikasi teknis (kapasitas, IP/domain)', 'check_legalitas_data' => 'Legalitas data'] as $field => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="{{ $field }}" value="1" @checked($item->{$field})>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Provisioning kredensial (saat Selesai) --}}
                    <div x-show="status === 'selesai'" x-cloak class="border rounded-lg p-3 bg-teal-50 space-y-2">
                        <p class="text-xs font-semibold text-gray-600">Metadata Kredensial (provisioning)</p>
                        <select name="credential_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">— Jenis kredensial (default: mengikuti layanan) —</option>
                            <option value="apikey">API Key</option>
                            <option value="oauth2">OAuth2</option>
                        </select>
                        <input type="text" name="credential_ref" placeholder="Referensi/ID kredensial (bukan secret)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="text" name="rate_limit" placeholder="Rate limit (mis. 1000/menit)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="text" name="acl" placeholder="ACL / group" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Masa berlaku (opsional)</label>
                            <input type="date" name="expires_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Verifikasi</label>
                        <textarea name="catatan_verifikasi" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->catatan_verifikasi }}</textarea>
                    </div>

                    <div x-show="status === 'ditolak'" x-cloak>
                        <label class="block text-sm font-medium text-red-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" rows="2" class="w-full px-3 py-2 border border-red-300 rounded-lg text-sm">{{ $item->rejection_reason }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (log)</label>
                        <input type="text" name="note" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold">Simpan Perubahan</button>
                    <p class="text-xs text-gray-500">Saat status <strong>Selesai</strong>, konsumen otomatis dicatat ke Master Data SPLP.</p>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6 text-sm">
                <h3 class="font-bold text-gray-800 mb-3">Lini Masa</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500">Diajukan</dt><dd>{{ optional($item->submitted_at)->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Verif. Administrasi</dt><dd>{{ optional($item->verif_admin_at)->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Verif. Teknis</dt><dd>{{ optional($item->verif_teknis_at)->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Keputusan</dt><dd>{{ optional($item->decided_at)->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Selesai</dt><dd>{{ optional($item->completed_at)->format('d/m/Y H:i') ?? '-' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
