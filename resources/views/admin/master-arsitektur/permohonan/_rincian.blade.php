{{-- Rincian satu permohonan pembaruan data arsitektur; dipakai halaman admin dan pengguna --}}
@php
    $perubahan = $permohonan->perubahan();
    $ubah = $permohonan->aksi === \App\Models\MasterArsitektur\Permohonan::AKSI_UBAH;
@endphp

<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <div>
            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis Usulan</dt>
            <dd class="text-gray-800 mt-0.5">{{ \App\Models\MasterArsitektur\Permohonan::LABEL_AKSI[$permohonan->aksi] ?? $permohonan->aksi }} · {{ $permohonan->label_jenis }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</dt>
            <dd class="mt-0.5">
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ \App\Models\MasterArsitektur\Permohonan::WARNA_STATUS[$permohonan->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ \App\Models\MasterArsitektur\Permohonan::LABEL_STATUS[$permohonan->status] ?? $permohonan->status }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Instansi</dt>
            <dd class="text-gray-800 mt-0.5">{{ $permohonan->unitKerja?->nama ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Diusulkan oleh</dt>
            <dd class="text-gray-800 mt-0.5">{{ $permohonan->user?->name ?? '-' }} · {{ $permohonan->created_at?->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</dd>
        </div>
        @if ($permohonan->catatan_pemohon)
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan Pemohon</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $permohonan->catatan_pemohon }}</dd>
            </div>
        @endif
        @if (! $permohonan->menunggu())
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Diproses oleh</dt>
                <dd class="text-gray-800 mt-0.5">{{ $permohonan->pemroses?->name ?? '-' }} · {{ $permohonan->diproses_pada?->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan Admin</dt>
                <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ $permohonan->catatan_admin ?: '-' }}</dd>
            </div>
        @endif
    </dl>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-800">{{ $ubah ? 'Perubahan yang Diusulkan' : 'Data yang Diusulkan' }}</h2>
        <p class="text-xs text-gray-500">{{ count($perubahan) }} kolom {{ $ubah ? 'berubah' : 'diisi' }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left w-1/4">Kolom</th>
                    @if ($ubah)
                        <th class="px-4 py-2 text-left">Sebelum</th>
                    @endif
                    <th class="px-4 py-2 text-left">{{ $ubah ? 'Usulan' : 'Isi' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($perubahan as $baris)
                    <tr class="border-t border-gray-100 align-top">
                        <th class="px-4 py-2 text-left font-semibold text-gray-600 bg-gray-50">{{ $baris['label'] }}</th>
                        @if ($ubah)
                            <td class="px-4 py-2 text-gray-600 whitespace-pre-line break-words bg-red-50">@if ($baris['lama'] === null)<span class="text-gray-400">(kosong)</span>@else{{ $baris['lama'] }}@endif</td>
                        @endif
                        <td class="px-4 py-2 text-gray-800 whitespace-pre-line break-words {{ $ubah ? 'bg-green-50' : '' }}">@if ($baris['baru'] === null)<span class="text-gray-400">(dikosongkan)</span>@else{{ $baris['baru'] }}@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Tidak ada perubahan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
