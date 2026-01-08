@extends('layouts.authenticated')

@section('title', '- Permohonan')
@section('header-title', 'Permohonan')

@section('content')
    <div class="bg-green-100 p-6 rounded-lg shadow border border-green-200 mb-6">
        <h2 class="text-2xl font-bold text-green-800 mb-2">Permohonan User</h2>
        <p>Selamat datang, <span class="font-semibold">{{ Auth::user()->name }}</span>! Di sini Anda dapat
            mengajukan permohonan layanan.</p>
    </div>
    <div class="bg-white shadow rounded-lg p-6">

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form Upload Permohonan -->
        <div class="bg-gray-50 p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold mb-4">Ajukan Permohonan Baru</h2>
            <form action="{{ route('user.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="service" class="block font-medium">Jenis Layanan</label>
                    <select name="service" id="service" class="w-full border p-2 rounded" required>
                        <option value="">-- Pilih Layanan --</option>
                        <option value="Rekomendasi">Rekomendasi Belanja Aplikasi</option>
                        <option value="Rekomendasi">Rekomendasi Belanja TIK</option>
                        <option value="Subdomain">Subdomain</option>
                        <option value="Hosting">Hosting</option>
                        <option value="Subdomain">PSE</option>
                        <option value="Email">Email</option>
                        <option value="Cloud Storage">Cloud Storage</option>
                        <option value="SPLP">SPLP</option>
                        <option value="Internet">Jaringan Internet</option>
                        <option value="VPN">VPN</option>
                        <option value="Wifi Publik">Wifi Publik</option>
                        <option value="Videotron">Videotron</option>
                        <option value="Konten">Konten Multimedia</option>
                        <option value="Helpdesk TIK">Helpdesk TIK</option>
                        <option value="TTE">TTE</option>
                        <option value="SMKI">Keamanan Informasi</option>
                        <option value="Vidcon">Zoom/Youtube Livestream</option>
                    </select>
                </div>
                <div>
                    <label for="file" class="block font-medium">Upload Surat Permohonan</label>
                    <input type="file" name="file" id="file" class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex items-center gap-2
                                   bg-green-600 hover:bg-green-700         {{-- fallback --}}
                                   bg-gradient-to-r from-green-500 to-green-600
                                   text-white font-semibold tracking-wide
                                   px-6 py-2.5 rounded-lg
                                   shadow-md hover:shadow-lg
                                   active:scale-95
                                   focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2
                                   transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 stroke-current" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Permohonan User -->
        <div class="bg-gray-50 p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Permohonan Anda</h2>

            @if ($requests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2 text-left">Nomor Tiket</th>
                                <th class="px-4 py-2 text-left">Layanan</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Surat</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono text-sm">{{ $request->ticket_number }}</td>
                                    <td class="px-4 py-2">{{ $request->service }}</td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium
                                            @if ($request->status == 'Menunggu') bg-yellow-100 text-black
                                            @elseif($request->status == 'Dalam Proses') bg-green-800 text-white
                                            @elseif($request->status == 'Ditolak') bg-red-100 text-black                                           
                                            @elseif($request->status == 'Selesai') bg-green-200 text-black
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($request->file)
                                            <a href="{{ asset('storage/' . $request->file) }}" target="_blank"
                                                class="text-emerald-500 hover:text-emerald-600 underline text-sm">
                                                Lihat Surat
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($request->status == 'Menunggu')
                                            <form action="{{ route('user.delete', $request->id) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded hover:bg-red-100 hover:border-red-300 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada permohonan</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan mengajukan permohonan layanan pertama Anda.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- JavaScript untuk konfirmasi delete -->
    <script>
        // Auto hide alert messages after 5 seconds
        setTimeout(function() {
            // Hanya target alert messages di content area, BUKAN menu di sidebar
            const alerts = document.querySelectorAll('.bg-green-100.border-green-400, .bg-red-100.border-red-400');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
@endsection
