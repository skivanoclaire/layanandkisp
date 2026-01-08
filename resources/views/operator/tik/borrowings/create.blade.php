@extends('layouts.authenticated')

@section('title', '- Buat Peminjaman')
@section('header-title', 'Buat Peminjaman')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Buat Peminjaman</h1>

    <form method="POST" action="{{ route('op.tik.borrow.store') }}" enctype="multipart/form-data"
        class="bg-white p-4 rounded shadow space-y-4">
        @csrf

        <div class="flex items-center justify-between flex-wrap gap-3">
            {{-- NEW: kotak pencarian --}}
            <div class="flex items-center gap-2">
                <label for="asset-search" class="text-sm text-gray-600">Cari barang:</label>
                <input id="asset-search" type="text" placeholder="Ketik nama/kategori/kode…"
                    class="border rounded px-3 py-2 w-72 max-w-full" autocomplete="off" />
                <button type="button" id="asset-search-clear" class="px-3 py-2 border rounded text-sm">Bersihkan</button>
            </div>

            <div class="flex gap-2">
                {{-- <button type="button" id="btn-open-scan" class="px-3 py-2 rounded bg-blue-600 text-white">Mode Scan QR</button> --}}
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
            </div>
        </div>

        {{-- Hidden input for barcode scanner --}}
        <input type="text" id="barcode-input" autocomplete="off"
               class="opacity-0 absolute -left-9999 w-1 h-1">
        <div id="barcode-status" class="text-sm text-gray-600 hidden">
            <span class="inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></span>
            Scanner aktif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto" id="asset-table">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Barang</th>
                        <th class="px-3 py-2 text-left">Foto Barang</th>
                        <th class="px-3 py-2 text-left">Kategori</th>
                        <th class="px-3 py-2 text-left">Tersedia</th>
                        <th class="px-3 py-2 text-left">Ambil</th>
                    </tr>
                </thead>
                <tbody id="asset-tbody">
                    @foreach ($assets as $a)
                        <tr class="border-b" data-asset-id="{{ $a->id }}" data-available="{{ $a->available }}"
                            {{-- NEW: siapkan field pencarian di data-* agar filtering cepat --}} data-name="{{ Str::of($a->name)->lower() }}"
                            data-category="{{ Str::of($a->category?->name)->lower() }}"
                            data-code="{{ Str::of($a->code ?? '')->lower() }}">
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $a->name }}</div>
                                @if ($a->code)
                                    <div class="text-xs text-gray-500">Kode: {{ $a->code }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @if ($a->photo_url)
                                    <a href="{{ $a->photo_url }}" target="_blank" title="Lihat Foto">
                                        <img src="{{ $a->photo_url }}" alt="Foto {{ $a->name }}"
                                            class="h-12 w-12 object-cover rounded hover:opacity-80 transition">
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $a->category?->name }}</td>
                            <td class="px-3 py-2">{{ $a->available }}</td>
                            <td class="px-3 py-2">
                                @if ($a->available > 0)
                                    <input type="checkbox" class="take mr-2" name="asset_id[]" value="{{ $a->id }}">
                                    <input type="number" class="qty border rounded p-1 w-24" name="qty[]" min="1"
                                        max="{{ $a->available }}" value="1" disabled>
                                @else
                                    <span class="text-gray-400">Habis</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- NEW: baris “tidak ada hasil” --}}
                <tfoot>
                    <tr id="no-result-row" class="hidden">
                        <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                            Tidak ada hasil untuk pencarian ini.
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div>
            <label class="block text-sm mb-1">Catatan (opsional)</label>
            <textarea name="notes" rows="3" class="border rounded p-2 w-full">{{ old('notes') }}</textarea>
        </div>

        <div>
            <label class="block text-sm mb-1">Foto saat mengambil (bisa multi)</label>
            <input type="file" name="photos[]" accept="image/*" multiple class="border rounded p-2 w-full">
        </div>

        <div class="flex gap-3">
            <a href="{{ route('op.tik.borrow.index') }}" class="px-4 py-2 border rounded">Batal</a>
            <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
        </div>
    </form>

    {{-- Modal scan --}}
    <div id="scan-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
        <div class="bg-white rounded shadow p-4 w-full max-w-md">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-semibold">Scan QR Aset</h3>
                <div class="flex gap-2">
                    <button type="button" id="btn-switch" class="px-2 py-1 border rounded">Ganti Kamera</button>
                    <button type="button" id="btn-torch" class="px-2 py-1 border rounded">Senter</button>
                    <button type="button" id="btn-close-scan" class="px-2 py-1 border rounded">Tutup</button>
                </div>
            </div>

            <div id="qr-reader" class="w-full h-64 bg-gray-100 rounded"></div>
            <div id="scan-status" class="text-sm text-gray-600 mt-2">Arahkan kamera ke QR…</div>
        </div>
    </div>

    {{-- html5-qrcode --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function() {
            // ===== NEW: Pencarian / filter baris =====
            const qInput = document.getElementById('asset-search');
            const qClear = document.getElementById('asset-search-clear');
            const tbody = document.getElementById('asset-tbody');
            const noRow = document.getElementById('no-result-row');

            function normalize(s) {
                return (s || '').toString().toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function applyFilter() {
                const q = normalize(qInput.value);
                let shown = 0;

                for (const tr of tbody.querySelectorAll('tr')) {
                    if (!tr.dataset.assetId) continue; // Skip non-asset rows

                    const name = normalize(tr.dataset.name || '');
                    const cat = normalize(tr.dataset.category || '');
                    const code = normalize(tr.dataset.code || '');
                    const hay = name + ' ' + cat + ' ' + code;

                    const match = q === '' || hay.includes(q);
                    tr.classList.toggle('hidden', !match);
                    if (match) shown++;
                }

                noRow.classList.toggle('hidden', shown > 0);
            }

            // debounce agar efisien
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

            // Prevent barcode scanner from stealing focus when typing in search
            qInput.addEventListener('focus', () => {
                qInput.dataset.focused = 'true';
            });
            qInput.addEventListener('blur', () => {
                qInput.dataset.focused = 'false';
            });

            // inisialisasi awal
            applyFilter();

            // ===== Hardware Barcode Scanner =====
            const barcodeInput = document.getElementById('barcode-input');
            const barcodeStatus = document.getElementById('barcode-status');
            let scanBuffer = '';
            let scanTimeout = null;

            if (barcodeInput) {
                barcodeInput.focus();
                barcodeStatus.classList.remove('hidden');

                // Maintain focus (but not if user is typing in search box)
                document.addEventListener('click', (e) => {
                    const isSearchInput = e.target === qInput || e.target === qClear || qInput.contains(e.target);
                    const modalOpen = modal && modal.classList.contains('flex');
                    if (!modalOpen && !isSearchInput && document.activeElement !== qInput) {
                        setTimeout(() => barcodeInput.focus(), 50);
                    }
                });

                // Handle input
                barcodeInput.addEventListener('input', function(e) {
                    clearTimeout(scanTimeout);
                    scanBuffer += e.target.value;
                    e.target.value = '';

                    scanTimeout = setTimeout(() => {
                        if (scanBuffer.trim()) processBarcode(scanBuffer.trim());
                        scanBuffer = '';
                    }, 100);
                });

                barcodeInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(scanTimeout);
                        if (scanBuffer.trim()) processBarcode(scanBuffer.trim());
                        scanBuffer = '';
                        this.value = '';
                    }
                });
            }

            async function processBarcode(code) {
                try {
                    // Parse JSON QR code if needed
                    let assetCode = code;
                    try {
                        const parsed = JSON.parse(code);
                        if (parsed && parsed.code) {
                            assetCode = parsed.code;
                        }
                    } catch (e) {
                        // Not JSON, use as-is
                    }

                    barcodeStatus.textContent = `Memproses: ${assetCode}...`;

                    const response = await fetch(`{{ route('op.tik.borrow.lookup.code') }}?code=${encodeURIComponent(assetCode)}`);
                    const data = await response.json();

                    if (data.success && data.asset) {
                        markRow(data.asset.id);
                        barcodeStatus.textContent = `✓ ${data.asset.name}`;
                        barcodeStatus.className = 'text-sm text-green-600';
                    } else {
                        barcodeStatus.textContent = `✗ Kode ${code} tidak ditemukan`;
                        barcodeStatus.className = 'text-sm text-red-600';
                        beep(800, 200);
                    }
                } catch (error) {
                    barcodeStatus.textContent = `✗ Error: ${code}`;
                    barcodeStatus.className = 'text-sm text-red-600';
                    beep(800, 200);
                }

                setTimeout(() => {
                    barcodeStatus.textContent = 'Scanner aktif';
                    barcodeStatus.className = 'text-sm text-gray-600';
                    barcodeInput?.focus();
                }, 3000);
            }

            // ===== Sinkronisasi checkbox <-> qty =====
            document.querySelectorAll('tr [type=checkbox].take').forEach((cb) => {
                const row = cb.closest('tr');
                const qty = row.querySelector('input.qty');
                const avail = parseInt(row.dataset.available || '0', 10);

                function setState(checked) {
                    qty.disabled = !checked;
                    if (checked && (!qty.value || parseInt(qty.value, 10) < 1))
                        qty.value = Math.min(1, Math.max(1, avail));
                }
                cb.addEventListener('change', () => setState(cb.checked));
                setState(cb.checked);
            });

            // ===== Modal & Scanner (tetap seperti sebelumnya) =====
            const modal = document.getElementById('scan-modal');
            const openBtn = document.getElementById('btn-open-scan');
            const closeBtn = document.getElementById('btn-close-scan');
            const switchBtn = document.getElementById('btn-switch');
            const torchBtn = document.getElementById('btn-torch');
            const statusEl = document.getElementById('scan-status');

            let qr = null,
                busy = false;
            let cameras = [],
                camIdx = -1;
            let torchOn = false;

            let audioCtx;

            function beep(freq = 1100, duration = 120) {
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
                    g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration / 1000);
                    setTimeout(() => o.stop(), duration + 60);
                } catch (e) {
                    /* no audio */
                }
            }

            function computeConfig() {
                const vw = Math.min(window.innerWidth, 900);
                const vh = window.innerHeight;
                const minEdge = Math.min(vw, vh);
                const boxSize = Math.max(180, Math.min(Math.floor(minEdge * 0.8), 420));
                return {
                    fps: 12,
                    qrbox: {
                        width: boxSize,
                        height: boxSize
                    },
                    aspectRatio: vw / vh,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    },
                    rememberLastUsedCamera: true
                };
            }

            function parseAssetId(text) {
                try {
                    const d = JSON.parse(text);
                    if (d && (d.id || d.asset_id)) return parseInt(d.id || d.asset_id);
                } catch (e) {}
                const m = String(text).match(/ASSET\|(\d+)/i);
                return m ? parseInt(m[1]) : null;
            }

            function markRow(assetId) {
                const row = document.querySelector(`tr[data-asset-id="${assetId}"]`);
                if (!row) {
                    statusEl.textContent = `QR dikenali (id=${assetId}) tapi aset tidak ada di daftar.`;
                    return;
                }
                const avail = parseInt(row.dataset.available || '0', 10);
                if (avail < 1) {
                    statusEl.textContent = `Aset id=${assetId} stok habis.`;
                    return;
                }
                const cb = row.querySelector('input.take[type="checkbox"]');
                const qty = row.querySelector('input.qty[type="number"]');
                if (!cb.checked) {
                    cb.checked = true;
                    qty.disabled = false;
                    qty.value = Math.max(1, parseInt(qty.value || '1', 10));
                } else {
                    qty.value = Math.min(avail, parseInt(qty.value || '1', 10) + 1);
                }
                row.classList.add('bg-yellow-50');
                setTimeout(() => row.classList.remove('bg-yellow-50'), 600);
                statusEl.textContent = `✔ Aset ${assetId} dipilih, qty ${qty.value}/${avail}.`;
                beep();
            }

            async function startScan(cameraId = null) {
                if (qr) return;
                qr = new Html5Qrcode("qr-reader", {
                    verbose: false
                });

                if (!cameras.length) {
                    try {
                        cameras = await Html5Qrcode.getCameras();
                    } catch (e) {
                        cameras = [];
                    }
                }
                let cameraConfig = cameraId ? {
                    deviceId: {
                        exact: cameraId
                    }
                } : {
                    facingMode: {
                        exact: "environment"
                    }
                };
                if (!cameraId && !('mediaDevices' in navigator)) cameraConfig = {
                    facingMode: "environment"
                };

                try {
                    await qr.start(
                        cameraConfig,
                        computeConfig(),
                        (decodedText) => {
                            if (busy) return;
                            busy = true;
                            const id = parseAssetId(decodedText);
                            if (id) markRow(id);
                            else statusEl.textContent = "Format QR tidak dikenali.";
                            setTimeout(() => busy = false, 450);
                        },
                        () => {}
                    );
                    statusEl.textContent = "Kamera aktif. Arahkan ke QR…";
                    if (cameras.length && cameraId) {
                        camIdx = Math.max(0, cameras.findIndex(d => d.id === cameraId));
                    } else if (cameras.length && camIdx < 0) {
                        const prefer = cameras.findIndex(d => /back|rear|environment/i.test(d.label));
                        camIdx = prefer >= 0 ? prefer : 0;
                    }
                } catch (err) {
                    statusEl.textContent = "Gagal mengakses kamera. Izinkan kamera & pastikan HTTPS.";
                }
            }

            function stopScan() {
                if (!qr) return;
                qr.stop().then(() => {
                    qr.clear();
                    qr = null;
                }).catch(() => {});
            }

            async function switchCamera() {
                if (!cameras.length) {
                    try {
                        cameras = await Html5Qrcode.getCameras();
                    } catch (e) {
                        cameras = [];
                    }
                }
                if (!cameras.length) {
                    statusEl.textContent = "Tidak ada kamera lain.";
                    return;
                }
                camIdx = (camIdx + 1) % cameras.length;
                const next = cameras[camIdx];
                stopScan();
                startScan(next.id);
                statusEl.textContent = "Berpindah kamera…";
            }

            async function toggleTorch() {
                if (!qr || !qr.getRunningTrackCapabilities) {
                    statusEl.textContent = "Torch tidak didukung.";
                    return;
                }
                const caps = qr.getRunningTrackCapabilities();
                if (!caps || !('torch' in caps)) {
                    statusEl.textContent = "Torch tidak tersedia di kamera ini.";
                    return;
                }
                try {
                    const on = torchBtn.classList.toggle('bg-yellow-200');
                    await qr.applyVideoConstraints({
                        advanced: [{
                            torch: on
                        }]
                    });
                    statusEl.textContent = on ? "Torch ON" : "Torch OFF";
                } catch (e) {
                    statusEl.textContent = "Gagal menyalakan torch.";
                }
            }

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                barcodeInput?.blur();
                barcodeStatus?.classList.add('hidden');
                startScan();
            }

            function closeModal() {
                stopScan();
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                barcodeInput?.focus();
                barcodeStatus?.classList.remove('hidden');
            }

            if (openBtn) openBtn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (modal) modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            if (switchBtn) switchBtn.addEventListener('click', switchCamera);
            if (torchBtn) torchBtn.addEventListener('click', toggleTorch);

            window.addEventListener('orientationchange', () => {
                if (qr) {
                    stopScan();
                    startScan(cameras[camIdx]?.id);
                }
            });
            window.addEventListener('resize', () => {
                if (qr) {
                    stopScan();
                    startScan(cameras[camIdx]?.id);
                }
            });
        })();
    </script>
@endsection
