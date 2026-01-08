{{-- resources/views/operator/tik/borrowings/edit.blade.php --}}
@extends('layouts.authenticated')

@section('title', '- Edit Peminjaman')
@section('header-title', 'Edit Peminjaman')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">
        Edit Peminjaman {{ $borrowing->code }}
    </h1>

    <form id="edit-form" action="{{ route('op.tik.borrow.update', $borrowing->id) }}" method="POST"
        enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium">Tanggal Pinjam</label>
            <input type="date" name="borrow_date"
                value="{{ old('borrow_date', optional($borrowing->started_at)->format('Y-m-d')) }}"
                class="border rounded p-2 w-full">
        </div>

        <div>
            <div class="flex items-center justify-between flex-wrap gap-3 mb-2">
                <label class="block text-sm font-medium">Checklist Barang</label>

                {{-- Pencarian barang --}}
                <div class="flex items-center gap-2">
                    <label for="asset-search" class="text-sm text-gray-600">Cari barang:</label>
                    <input id="asset-search" type="text" placeholder="Ketik nama/kategori/kode…"
                        class="border rounded px-3 py-2 w-72 max-w-full" autocomplete="off">
                    <button type="button" id="asset-search-clear"
                        class="px-3 py-2 border rounded text-sm">Bersihkan</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto" id="asset-table">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Barang</th>
                            <th class="px-3 py-2 text-left">Foto Barang</th>
                            <th class="px-3 py-2 text-left">Kategori</th>
                            <th class="px-3 py-2 text-left">Ambil</th>
                            <th class="px-3 py-2 text-left">Qty</th>
                        </tr>
                    </thead>
                    <tbody id="asset-tbody">
                        @php
                            // map qty existing per asset id
                            $existing = $borrowing->items->pluck('qty', 'tik_asset_id')->toArray();
                        @endphp

                        @foreach ($assets as $asset)
                            @php
                                $checked = array_key_exists($asset->id, $existing);
                                $qtyVal = $checked ? $existing[$asset->id] : 1;
                            @endphp
                            <tr class="border-b" data-asset-id="{{ $asset->id }}" {{-- field untuk pencarian cepat --}}
                                data-name="{{ \Illuminate\Support\Str::of($asset->name)->lower() }}"
                                data-category="{{ \Illuminate\Support\Str::of($asset->category?->name)->lower() }}"
                                data-code="{{ \Illuminate\Support\Str::of($asset->code ?? '')->lower() }}">
                                <td class="px-3 py-2">
                                    <div class="font-medium">{{ $asset->name }}</div>
                                    @if ($asset->code)
                                        <div class="text-xs text-gray-500">Kode: {{ $asset->code }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($asset->photo_url)
                                        <a href="{{ $asset->photo_url }}" target="_blank" title="Lihat Foto">
                                            <img src="{{ $asset->photo_url }}" alt="Foto {{ $asset->name }}"
                                                class="h-12 w-12 object-cover rounded hover:opacity-80 transition">
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2">{{ $asset->category?->name }}</td>
                                <td class="px-3 py-2">
                                    <input type="checkbox" class="take" name="asset_id[]" value="{{ $asset->id }}"
                                        {{ $checked ? 'checked' : '' }}>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" class="qty border rounded p-1 w-24" name="qty[]" min="1"
                                        value="{{ old('qty.' . $loop->index, $qtyVal) }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr id="no-result-row" class="hidden">
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                Tidak ada hasil untuk pencarian ini.
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Catatan (opsional)</label>
            <textarea name="notes" rows="3" class="border rounded p-2 w-full">{{ old('notes', $borrowing->notes) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium">Foto tambahan (opsional, multi)</label>
            <input type="file" name="photos[]" multiple accept="image/*" class="border rounded p-2 w-full">
        </div>

        <div class="flex gap-3">
            <a href="{{ route('op.tik.borrow.show', $borrowing->id) }}" class="px-4 py-2 border rounded">Batal</a>
            <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan Perubahan</button>
        </div>
    </form>

    {{-- Script filter + sinkronisasi checkbox ↔ qty --}}
    <script>
        (function() {
            // === Pencarian realtime ===
            const qInput = document.getElementById('asset-search');
            const qClear = document.getElementById('asset-search-clear');
            const tbody = document.getElementById('asset-tbody');
            const noRow = document.getElementById('no-result-row');

            function normalize(s) {
                return (s || '').toString().toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function applyFilter() {
                const q = normalize(qInput.value.trim());
                let shown = 0;

                for (const tr of tbody.querySelectorAll('tr')) {
                    const name = tr.dataset.name || '';
                    const cat = tr.dataset.category || '';
                    const code = tr.dataset.code || '';
                    const hay = name + ' ' + cat + ' ' + code;

                    const match = q === '' || hay.includes(q);
                    tr.classList.toggle('hidden', !match);
                    if (match) shown++;
                }

                noRow.classList.toggle('hidden', shown > 0);
            }

            let t;
            qInput.addEventListener('input', () => {
                clearTimeout(t);
                t = setTimeout(applyFilter, 120);
            });
            qClear.addEventListener('click', () => {
                qInput.value = '';
                applyFilter();
                qInput.focus();
            });
            applyFilter();

            // === Sinkronisasi checkbox ↔ qty (non-checked => qty disabled) ===
            document.querySelectorAll('tr .take').forEach((cb) => {
                const row = cb.closest('tr');
                const qty = row.querySelector('input.qty');

                function setState() {
                    qty.disabled = !cb.checked;
                }
                cb.addEventListener('change', setState);
                setState();
            });

            // Pastikan saat submit, qty yang tidak dicentang tidak ikut terkirim
            document.getElementById('edit-form').addEventListener('submit', () => {
                document.querySelectorAll('tr').forEach((row) => {
                    const cb = row.querySelector('input.take[type="checkbox"]');
                    const qty = row.querySelector('input.qty[type="number"]');
                    if (cb && qty) qty.disabled = !cb.checked;
                });
            });
        })();
    </script>
@endsection
