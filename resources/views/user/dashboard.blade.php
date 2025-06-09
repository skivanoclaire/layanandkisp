@extends('layouts.user')

@section('title', 'Dashboard User')

@section('content')
    <div class="bg-green-100 p-6 rounded-lg shadow border border-green-200 mb-6">
        <h2 class="text-2xl font-bold text-green-800 mb-2">Dashboard User</h2>
        <p>Selamat datang, <span class="font-semibold">{{ Auth::user()->name }}</span>! Di sini Anda dapat
            mengajukan permohonan layanan.</p>
    </div>
    <div class="bg-white shadow rounded-lg p-6">

        <!-- Form Upload Permohonan -->
        <div class="bg-gray-50 p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold mb-4">Ajukan Permohonan Baru</h2>
            <form action="{{ route('user.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="service" class="block font-medium">Jenis Layanan</label>
                    <select name="service" id="service" class="w-full border p-2 rounded" required>
                        <option value="">-- Pilih Layanan --</option>
                        <option value="Rekomendasi">Rekomendasi TIK</option>
                        <option value="Subdomain">Subdomain</option>
                        <option value="Hosting">Hosting</option>
                        <option value="Email">Email</option>
                        <option value="Cloud Storage">Cloud Storage</option>
                        <option value="SPLP">SPLP</option>
                        <option value="Internet">Jaringan Internet</option>
                        <option value="VPN">VPN</option>
                        <option value="Wifi Publik">Wifi Publik</option>
                        <option value="Videotron">Videotron</option>
                        <option value="SPLP">SPLP</option>
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
                    <button type="submit" class="inline-flex items-center gap-2
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
            <table class="min-w-full table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Layanan</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Surat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $request->service }}</td>
                            <td class="px-4 py-2">{{ $request->status }}</td>
                            <td class="px-4 py-2">
                                @if ($request->file)
                                    <a href="{{ asset('storage/' . $request->file) }}" target="_blank"
                                        class="text-emerald-500 underline">Lihat Surat</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection