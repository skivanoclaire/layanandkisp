<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Rekomendasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-xl p-8">
            {{-- Header --}}
            <div class="text-center mb-8">
                <img src="{{ asset('logokaltarafix.png') }}" alt="Logo Kaltara" class="w-24 h-24 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-gray-800">PEMERINTAH PROVINSI KALIMANTAN UTARA</h1>
                <h2 class="text-lg font-semibold text-gray-700">DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN</h2>
                <p class="text-sm text-gray-600 mt-2">Sistem Verifikasi Dokumen Resmi</p>
            </div>

            <hr class="border-2 border-blue-600 mb-6">

            @if ($found)
                {{-- Document Valid --}}
                <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-green-800 mb-2">Dokumen SAH dan VALID</h3>
                            <p class="text-green-700">Dokumen ini adalah surat resmi yang dikeluarkan oleh Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara.</p>
                        </div>
                    </div>
                </div>

                {{-- Document Details --}}
                <div class="mt-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Detail Dokumen</h3>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1 text-sm font-semibold text-gray-600">Jenis Dokumen:</div>
                        <div class="col-span-2 text-sm text-gray-800">Surat Rekomendasi Aplikasi</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Nomor Surat:</div>
                        <div class="col-span-2 text-sm text-gray-800 font-mono">{{ $document->letter_number }}</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Nomor Tiket:</div>
                        <div class="col-span-2 text-sm text-gray-800 font-mono">{{ $document->ticket_number }}</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Judul Aplikasi:</div>
                        <div class="col-span-2 text-sm text-gray-800 font-semibold">{{ $document->judul_aplikasi }}</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Pemohon:</div>
                        <div class="col-span-2 text-sm text-gray-800">{{ $document->user->name }}</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Tanggal Disetujui:</div>
                        <div class="col-span-2 text-sm text-gray-800">{{ $document->approved_at->format('d F Y') }}</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Disetujui Oleh:</div>
                        <div class="col-span-2 text-sm text-gray-800">{{ $document->approvedBy->name ?? 'Admin' }}</div>

                        <div class="col-span-1 text-sm font-semibold text-gray-600">Status:</div>
                        <div class="col-span-2">
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                DISETUJUI
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded">
                    <p class="text-sm text-blue-800">
                        <strong>Catatan:</strong> Dokumen ini telah diverifikasi dan sah sesuai dengan database resmi
                        Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara.
                    </p>
                </div>

            @else
                {{-- Document Invalid --}}
                <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-red-800 mb-2">Dokumen TIDAK VALID</h3>
                            <p class="text-red-700">{{ $message }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">
                        <strong>Peringatan:</strong> QR Code yang Anda scan tidak terdaftar dalam sistem kami.
                        Pastikan Anda men-scan QR Code dari dokumen resmi yang dikeluarkan oleh
                        Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara.
                    </p>
                </div>
            @endif

            {{-- Footer --}}
            <div class="mt-8 text-center text-sm text-gray-600 border-t pt-6">
                <p>Jika Anda memiliki pertanyaan, silakan hubungi:</p>
                <p class="mt-2">
                    <strong>Email:</strong> <a href="mailto:diskominfo@kaltaraprov.go.id" class="text-blue-600 hover:underline">diskominfo@kaltaraprov.go.id</a><br>
                    <strong>Website:</strong> <a href="http://diskominfo.kaltaraprov.go.id" target="_blank" class="text-blue-600 hover:underline">diskominfo.kaltaraprov.go.id</a>
                </p>
                <p class="mt-4 text-xs text-gray-500">
                    &copy; {{ date('Y') }} Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara
                </p>
            </div>
        </div>
    </div>
</body>
</html>
