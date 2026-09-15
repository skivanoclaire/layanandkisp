@extends('layouts.authenticated')

@section('title', '- Insiden Keamanan DTSEN')
@section('header-title', 'Penanganan Insiden Keamanan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan Insiden Keamanan Data</h1>
        <p class="text-gray-600 mt-1">
            Form 5.3 — kewajiban lapor maksimal 3x24 jam hari kerja; keterlambatan dieskalasi ke
            Petugas Pelindung DTSEN (Kepala DKISP) dan Prosesor.
        </p>
    </div>

    @include('partials.dtsen.errors')

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        @foreach ([
            'Total Insiden' => ['value' => $ringkasan['total'], 'class' => 'text-gray-800'],
            'Belum Selesai' => ['value' => $ringkasan['belum_selesai'], 'class' => 'text-orange-600'],
            'Dilaporkan Terlambat' => ['value' => $ringkasan['terlambat'], 'class' => 'text-red-600'],
        ] as $label => $meta)
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1 {{ $meta['class'] }}">{{ $meta['value'] }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenIncidentReport::statusLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm pb-2">
            <input type="checkbox" name="terlambat" value="1" @checked(request()->boolean('terlambat'))>
            <span>Hanya yang terlambat</span>
        </label>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.laporan.insiden') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="space-y-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow-sm border p-6 {{ $item->terlambat ? 'border-l-4 border-l-red-500' : '' }}">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">
                            <span class="font-mono">{{ $item->ticket_no }}</span> — {{ $item->jenis_label }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $item->unitKerja->nama ?? '-' }} · Pelapor {{ $item->user->name ?? '-' }} ·
                            Diketahui {{ $item->waktu_diketahui->format('d/m/Y H:i') }}
                            @if ($item->dataRequest)
                                · Permohonan <span class="font-mono">{{ $item->dataRequest->ticket_no }}</span>
                            @endif
                        </p>
                        @if ($item->terlambat)
                            <p class="text-xs text-red-600 font-semibold mt-1">
                                Melewati batas pelaporan ({{ $item->batas_pelaporan?->format('d/m/Y H:i') }})
                                @if ($item->escalated_at) · dieskalasi {{ $item->escalated_at->format('d/m/Y H:i') }} @endif
                            </p>
                        @endif
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                </div>

                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-gray-500">Kronologi</dt><dd class="whitespace-pre-line">{{ $item->kronologi }}</dd></div>
                    <div><dt class="text-gray-500">Dampak / Data Terdampak</dt><dd class="whitespace-pre-line">{{ $item->dampak }}</dd></div>
                    <div><dt class="text-gray-500">Tindakan Awal OPD</dt><dd class="whitespace-pre-line">{{ $item->tindakan_awal }}</dd></div>
                </dl>

                @if ($item->file_path)
                    <p class="mt-3 text-sm"><a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Lampiran</a></p>
                @endif

                <form action="{{ route('admin.dtsen.laporan.insiden.handle', $item->id) }}" method="POST" class="mt-4 border-t pt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tindak Lanjut <span class="text-red-500">*</span></label>
                        <textarea name="tindak_lanjut" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->tindak_lanjut }}</textarea>
                    </div>
                    <div class="flex flex-wrap items-end gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach (\App\Models\DtsenIncidentReport::statusLabels() as $val => $label)
                                    <option value="{{ $val }}" @selected($item->status === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Simpan Penanganan</button>
                        @if ($item->handledBy)
                            <span class="text-xs text-gray-400 pb-2">Terakhir: {{ $item->handledBy->name }}, {{ $item->handled_at?->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center text-gray-500">Belum ada laporan insiden.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
