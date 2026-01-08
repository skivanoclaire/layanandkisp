@extends('layouts.authenticated')

@section('title', '- Detail Laporan Peminjaman')
@section('header-title', 'Detail Laporan Peminjaman')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-green-700">Detail {{ $borrowing->code }}</h1>

        @if($borrowing->status === 'ongoing')
            <button onclick="openForceCloseModal()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Paksa Tutup Peminjaman
            </button>
        @endif
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Force Close Badge --}}
    @if($borrowing->closed_by)
        <div class="mb-4 px-4 py-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="font-semibold">Peminjaman Ditutup Secara Paksa oleh Admin</p>
                    <p class="text-sm mt-1"><strong>Admin:</strong> {{ \App\Models\User::find($borrowing->closed_by)?->name ?? 'Unknown' }}</p>
                    <p class="text-sm"><strong>Waktu:</strong> {{ $borrowing->closed_at?->format('d M Y H:i') }}</p>
                    <p class="text-sm"><strong>Alasan:</strong> {{ $borrowing->closed_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="font-semibold">Operator</dt>
                    <dd>{{ $borrowing->operator?->name }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Status</dt>
                    <dd class="capitalize">{{ $borrowing->status }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Mulai</dt>
                    <dd>{{ optional($borrowing->started_at)->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Selesai</dt>
                    <dd>{{ optional($borrowing->finished_at)->format('Y-m-d H:i') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Catatan</dt>
                    <dd>{{ $borrowing->notes ?: '—' }}</dd>
                </div>
            </dl>

            <h2 class="mt-4 font-semibold">Barang</h2>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($borrowing->items as $it)
                    <li>{{ $it->asset->name }} × {{ $it->qty }} ({{ $it->asset->category?->name }})</li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Foto</h2>
                <div class="grid grid-cols-3 gap-2">
                    @forelse ($borrowing->photos as $p)
                        <div class="border rounded overflow-hidden">
                            @if ($p->path)
                                <a href="{{ asset('storage/' . $p->path) }}" target="_blank" title="Lihat Foto">
                                    <img src="{{ asset('storage/' . $p->path) }}" alt="Foto {{ $p->phase }}"
                                        class="object-cover w-full h-32 cursor-pointer hover:opacity-80 transition">
                                </a>
                            @else
                                <div class="h-32 bg-gray-100 flex items-center justify-center">
                                    <span class="text-gray-400">No image</span>
                                </div>
                            @endif
                            <div class="text-xs text-center py-1 bg-gray-50">{{ $p->phase }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Tidak ada foto</div>
                    @endforelse
                </div>
        </div>
        
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.tik.borrow.index') }}" class="px-4 py-2 border rounded">Kembali</a>
    </div>

    {{-- Modal Force Close --}}
    @if($borrowing->status === 'ongoing')
    <div id="forceCloseModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Paksa Tutup Peminjaman</h3>

                <div class="mt-2 px-4 py-3">
                    <p class="text-sm text-gray-600 mb-3">
                        Anda akan menutup peminjaman <strong>{{ $borrowing->code }}</strong> secara paksa.
                        Stok aset akan dikembalikan ke inventaris.
                    </p>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Peringatan:</strong> Pastikan barang yang dipinjam sudah dikembalikan secara fisik atau sudah dicatat kehilangan/kerusakannya.
                        </p>
                    </div>

                    <form id="forceCloseForm" method="POST" action="{{ route('admin.tik.borrow.forceClose', $borrowing->id) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="closed_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Penutupan Paksa <span class="text-red-600">*</span>
                            </label>
                            <textarea id="closed_reason" name="closed_reason" rows="4" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                      placeholder="Contoh: Barang tidak dikembalikan oleh operator, dicatat sebagai hilang"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter, maksimal 1000 karakter</p>
                        </div>

                        <div class="flex gap-3 justify-end">
                            <button type="button" onclick="closeForceCloseModal()"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded font-medium">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-medium">
                                Ya, Tutup Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openForceCloseModal() {
            document.getElementById('forceCloseModal').classList.remove('hidden');
        }

        function closeForceCloseModal() {
            document.getElementById('forceCloseModal').classList.add('hidden');
            document.getElementById('closed_reason').value = '';
        }

        // Close modal when clicking outside
        document.getElementById('forceCloseModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeForceCloseModal();
            }
        });
    </script>
    @endif
@endsection
