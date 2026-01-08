@extends('layouts.authenticated')

@section('title', '- Pengembalian')
@section('header-title', 'Pengembalian')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Pengembalian {{ $borrowing->code }}</h1>

    {{-- Scan Progress --}}
    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="font-semibold text-blue-900">Status Scanning</h3>
                <p class="text-sm text-blue-700">
                    <span id="scanned-count">0</span> dari <span id="total-count">{{ $borrowing->items->sum('qty') }}</span> item
                </p>
            </div>
            <div id="barcode-status-return" class="text-sm text-gray-600">
                <span class="inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></span>
                Scanner aktif
            </div>
        </div>
        <div class="bg-blue-100 rounded-full h-3">
            <div id="progress-bar" class="bg-blue-600 h-full transition-all" style="width: 0%"></div>
        </div>
    </div>

    <input type="text" id="barcode-input-return" autocomplete="off"
           class="opacity-0 absolute -left-9999 w-1 h-1">

    {{-- Items Table --}}
    <div class="bg-white p-4 rounded shadow mb-4">
        <h2 class="font-semibold mb-3">Scan barang yang dikembalikan</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Barang</th>
                        <th class="px-3 py-2 text-left">Foto</th>
                        <th class="px-3 py-2 text-center">Qty Dipinjam</th>
                        <th class="px-3 py-2 text-center">Qty Di-scan</th>
                        <th class="px-3 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($borrowing->items as $item)
                        <tr class="border-b return-row"
                            data-asset-code="{{ $item->asset->code }}"
                            data-qty-borrowed="{{ $item->qty }}"
                            data-qty-scanned="0">
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $item->asset->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->asset->code }}</div>
                            </td>
                            <td class="px-3 py-2">
                                @if ($item->asset->photo_url)
                                    <img src="{{ $item->asset->photo_url }}"
                                         class="h-12 w-12 object-cover rounded">
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center font-semibold">{{ $item->qty }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="qty-scanned text-lg font-bold text-blue-600">0</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="status-badge px-3 py-1 rounded-full text-sm bg-gray-200">
                                    Belum scan
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('op.tik.borrow.return.do', $borrowing->id) }}"
          enctype="multipart/form-data" id="return-form" class="bg-white p-4 rounded shadow space-y-4">
        @csrf
        <input type="hidden" name="all_scanned" id="all-scanned-input" value="0">

        <div>
            <label class="block text-sm mb-1">Foto saat mengembalikan</label>
            <input type="file" name="photos[]" accept="image/*" multiple class="border rounded p-2 w-full">
        </div>

        <div>
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="border rounded p-2 w-full"></textarea>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('op.tik.borrow.show', $borrowing->id) }}"
               class="px-4 py-2 border rounded">Batal</a>
            <button type="submit" id="submit-btn" disabled
                    class="px-4 py-2 bg-green-600 text-white rounded disabled:bg-gray-400">
                <span id="submit-text">Scan semua barang dulu</span>
            </button>
        </div>
    </form>

    <script>
        const barcodeInput = document.getElementById('barcode-input-return');
        const barcodeStatus = document.getElementById('barcode-status-return');
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const progressBar = document.getElementById('progress-bar');
        const scannedCountEl = document.getElementById('scanned-count');
        const allScannedInput = document.getElementById('all-scanned-input');

        let scanBuffer = '';
        let scanTimeout = null;
        let totalItems = {{ $borrowing->items->sum('qty') }};
        let scannedItems = 0;

        // Parse QR code (supports both JSON and plain text formats)
        function parseAssetCode(text) {
            try {
                const data = JSON.parse(text);
                return data.code || text;
            } catch (e) {
                // If not JSON, assume it's the code itself
                return text;
            }
        }

        // Audio feedback
        let audioCtx;
        function beep(freq = 1100, dur = 120) {
            try {
                audioCtx = audioCtx || new(window.AudioContext || window.webkitAudioContext)();
                const o = audioCtx.createOscillator();
                const g = audioCtx.createGain();
                o.type = 'sine';
                o.frequency.value = freq;
                o.connect(g);
                g.connect(audioCtx.destination);
                g.gain.setValueAtTime(0.0001, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.2, audioCtx.currentTime + 0.01);
                o.start();
                g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + dur/1000);
                setTimeout(() => o.stop(), dur + 60);
            } catch(e) {}
        }

        // Focus management
        barcodeInput.focus();
        document.addEventListener('click', () => setTimeout(() => barcodeInput.focus(), 50));

        // Input handling
        barcodeInput.addEventListener('input', function(e) {
            clearTimeout(scanTimeout);
            scanBuffer += e.target.value;
            e.target.value = '';

            scanTimeout = setTimeout(() => {
                if (scanBuffer.trim()) {
                    const code = parseAssetCode(scanBuffer.trim());
                    processReturnScan(code);
                }
                scanBuffer = '';
            }, 100);
        });

        barcodeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(scanTimeout);
                if (scanBuffer.trim()) {
                    const code = parseAssetCode(scanBuffer.trim());
                    processReturnScan(code);
                }
                scanBuffer = '';
                this.value = '';
            }
        });

        function processReturnScan(code) {
            const row = document.querySelector(`.return-row[data-asset-code="${code}"]`);

            if (!row) {
                barcodeStatus.textContent = `✗ ${code} tidak ada dalam peminjaman`;
                barcodeStatus.className = 'text-sm text-red-600';
                beep(800, 200);
                setTimeout(resetStatus, 3000);
                return;
            }

            const qtyBorrowed = parseInt(row.dataset.qtyBorrowed);
            const qtyScanned = parseInt(row.dataset.qtyScanned);

            if (qtyScanned >= qtyBorrowed) {
                barcodeStatus.textContent = `⚠ ${code} sudah lengkap`;
                barcodeStatus.className = 'text-sm text-yellow-600';
                beep(900, 150);
                setTimeout(resetStatus, 2000);
                return;
            }

            // Increment
            const newQty = qtyScanned + 1;
            row.dataset.qtyScanned = newQty;
            row.querySelector('.qty-scanned').textContent = newQty;

            const badge = row.querySelector('.status-badge');
            if (newQty >= qtyBorrowed) {
                badge.textContent = '✓ Lengkap';
                badge.className = 'status-badge px-3 py-1 rounded-full text-sm bg-green-200 text-green-800';
                row.classList.add('bg-green-50');
            } else {
                badge.textContent = `${newQty}/${qtyBorrowed}`;
                badge.className = 'status-badge px-3 py-1 rounded-full text-sm bg-blue-200 text-blue-800';
            }

            scannedItems++;
            updateProgress();

            barcodeStatus.textContent = `✓ ${code} (${newQty}/${qtyBorrowed})`;
            barcodeStatus.className = 'text-sm text-green-600';
            beep();
            setTimeout(resetStatus, 2000);
        }

        function updateProgress() {
            scannedCountEl.textContent = scannedItems;
            progressBar.style.width = `${(scannedItems/totalItems)*100}%`;

            const allComplete = Array.from(document.querySelectorAll('.return-row')).every(row => {
                return parseInt(row.dataset.qtyScanned) >= parseInt(row.dataset.qtyBorrowed);
            });

            if (allComplete) {
                submitBtn.disabled = false;
                submitText.textContent = 'Selesai & Kembalikan';
                allScannedInput.value = '1';
                barcodeStatus.textContent = '🎉 Semua barang telah di-scan!';
                barcodeStatus.className = 'text-sm text-green-700 font-semibold';
                beep(1300, 100);
                setTimeout(() => beep(1500, 100), 150);
            }
        }

        function resetStatus() {
            barcodeStatus.textContent = 'Scanner aktif';
            barcodeStatus.className = 'text-sm text-gray-600';
            barcodeInput.focus();
        }

        // Validate on submit
        document.getElementById('return-form').addEventListener('submit', function(e) {
            if (allScannedInput.value !== '1') {
                e.preventDefault();
                alert('Scan semua barang terlebih dahulu!');
            }
        });
    </script>
@endsection
