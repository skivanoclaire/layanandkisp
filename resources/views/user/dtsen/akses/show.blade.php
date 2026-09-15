@extends('layouts.authenticated')

@section('title', '- Akses Data DTSEN')
@section('header-title', 'Akses Data DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <a href="{{ route('user.dtsen.permohonan.show', $item->id) }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke detail permohonan</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $item->nama_program }}</h1>
                <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }} · {{ $item->level_label }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $token->statusBadgeClass() }}">{{ $token->statusLabel() }}</span>
        </div>

        @php($sisa = $token->daysLeft())
        <div class="rounded-lg border p-4 mb-4
            {{ ! $token->isUsable() ? 'border-gray-300 bg-gray-50 text-gray-700'
                : ($sisa !== null && $sisa <= \App\Models\DtsenAccessToken::WARN_BEFORE_DAYS ? 'border-orange-300 bg-orange-50 text-orange-900' : 'border-green-300 bg-green-50 text-green-900') }}">
            @if ($token->isUsable())
                <p class="text-lg font-bold">{{ $sisa !== null ? max($sisa, 0) . ' hari tersisa' : 'Berlaku tanpa batas waktu' }}</p>
                <p class="text-sm mt-0.5">Masa aktif berakhir {{ $token->expires_at?->format('d/m/Y H:i') ?? '-' }}</p>
            @else
                <p class="text-lg font-bold">Akses tidak tersedia</p>
                <p class="text-sm mt-0.5">
                    {{ $token->isRevoked() ? 'Token dicabut oleh DKISP.' : ($token->isExpired() ? 'Masa aktif token telah berakhir.' : 'Batas jumlah unduhan tercapai.') }}
                    Ajukan perpanjangan masa akses pada halaman detail permohonan.
                </p>
            @endif
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-4">
            <div><dt class="text-gray-500">Metode Penyaluran</dt><dd class="font-medium">{{ \App\Models\DtsenDataRequest::metodeAksesLabels()[$token->metode] ?? $token->metode }}</dd></div>
            <div><dt class="text-gray-500">Diterbitkan</dt><dd class="font-medium">{{ $token->issued_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Jumlah Unduhan</dt><dd class="font-medium">{{ $token->download_count }}@if($token->max_download) / {{ $token->max_download }}@endif</dd></div>
            <div><dt class="text-gray-500">Berkas</dt><dd class="font-medium">{{ $token->nama_berkas ?: '-' }}</dd></div>
        </dl>

        @if ($token->metode === 'excel_terenkripsi' && $token->file_path)
            <a href="{{ route('user.dtsen.akses.download', $token->token) }}"
               class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold
                      {{ $token->isUsable() ? '' : 'pointer-events-none opacity-50' }}">
                Unduh Data
            </a>
        @elseif ($token->parameter)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <p class="text-sm font-semibold text-gray-700 mb-1">Parameter Kanal Penyaluran</p>
                <pre class="text-xs text-gray-700 whitespace-pre-wrap font-mono">{{ $token->parameter }}</pre>
            </div>
        @endif

        @if ($token->catatan)
            <p class="mt-4 text-sm text-gray-600"><span class="text-gray-500">Catatan DKISP:</span> {{ $token->catatan }}</p>
        @endif

        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900">
            <p class="font-semibold">Kewajiban pemegang data</p>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                <li>Data hanya digunakan sesuai tujuan dan variabel yang disetujui pada KAK.</li>
                <li>Terapkan teknik pelindungan data yang dinyatakan dan batasi akses pada personel yang ditunjuk.</li>
                <li>Laporkan pemanfaatan minimal satu kali per enam bulan.</li>
                <li>Musnahkan data setelah masa retensi berakhir dan sampaikan berita acaranya ke DKISP maksimal 14 hari kalender.</li>
                <li>Laporkan indikasi kebocoran/penyalahgunaan maksimal 3x24 jam hari kerja sejak diketahui.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="font-bold text-gray-800 mb-3">Log Unduhan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Waktu</th>
                        <th class="px-3 py-2 text-left">Pengguna</th>
                        <th class="px-3 py-2 text-left">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($downloads as $log)
                        <tr>
                            <td class="px-3 py-2">{{ $log->downloaded_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">{{ $log->user->name ?? '-' }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $log->ip_address ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-gray-500">Belum ada unduhan tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
