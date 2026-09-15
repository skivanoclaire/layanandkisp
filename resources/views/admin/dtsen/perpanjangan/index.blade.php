@extends('layouts.authenticated')

@section('title', '- Perpanjangan Akses DTSEN')
@section('header-title', 'Perpanjangan Masa Akses DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Permohonan Perpanjangan Masa Akses</h1>
        <p class="text-gray-600 mt-1">Form 4.4 — keputusan narahubung layanan DTSEN (DKISP).</p>
    </div>

    @include('partials.dtsen.errors')

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenExtensionRequest::statusLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.perpanjangan.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="space-y-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">
                            <a href="{{ route('admin.dtsen.permohonan.show', $item->dtsen_data_request_id) }}"
                               class="font-mono text-blue-600 hover:underline">{{ $item->dataRequest->ticket_no ?? '-' }}</a>
                            — {{ $item->dataRequest->nama_program ?? '' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $item->dataRequest->unitKerja->nama ?? '-' }} · diajukan {{ $item->user->name ?? '-' }},
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-sm text-gray-700 mt-2"><span class="text-gray-500">Alasan:</span> {{ $item->alasan }}</p>
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="text-gray-500">Durasi diminta:</span> {{ $item->durasi_hari }} hari kalender
                            @if ($item->token)
                                · token berakhir {{ $item->token->expires_at?->format('d/m/Y') ?? '-' }}
                            @endif
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                @if ($item->status === \App\Models\DtsenExtensionRequest::STATUS_DIAJUKAN)
                    <form action="{{ route('admin.dtsen.perpanjangan.decide', $item->id) }}" method="POST"
                          class="mt-4 flex flex-wrap items-end gap-2 border-t pt-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Durasi Disetujui (hari)</label>
                            <input type="number" name="durasi_hari" min="1" max="{{ \App\Models\DtsenExtensionRequest::MAX_DURASI_HARI }}"
                                   value="{{ $item->durasi_hari }}"
                                   class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                            <input type="text" name="catatan" placeholder="Wajib bila ditolak"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <button name="status" value="disetujui"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Setujui</button>
                        <button name="status" value="ditolak"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Tolak</button>
                    </form>
                @elseif ($item->catatan)
                    <p class="mt-3 text-sm text-gray-600 border-t pt-3">
                        <span class="text-gray-500">Catatan:</span> {{ $item->catatan }}
                        <span class="text-xs text-gray-400 ml-1">— {{ $item->decidedBy->name ?? '-' }}, {{ $item->decided_at?->format('d/m/Y H:i') }}</span>
                    </p>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center text-gray-500">Tidak ada permohonan perpanjangan.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
