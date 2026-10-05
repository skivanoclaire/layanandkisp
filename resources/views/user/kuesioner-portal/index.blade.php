@extends('layouts.authenticated')

@section('title', '- Kuesioner Kualitas Layanan Portal')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-3xl">
        @include('user.kuesioner-portal._flash')

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-xl font-semibold text-gray-900">{{ config('kuesioner_portal.judul') }}</h1>
            <p class="text-sm text-gray-500 mt-1">Pemerintah Provinsi Kalimantan Utara</p>

            @if (! $periode)
                <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700">
                    Belum ada periode kuesioner yang dibuka. Silakan kembali lagi nanti.
                </div>
            @elseif ($response?->status === \App\Models\KuesionerPortalResponse::STATUS_SELESAI)
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    Anda sudah mengisi kuesioner periode <strong>{{ $periode->nama }}</strong> pada
                    {{ $response->submitted_at->translatedFormat('d F Y H:i') }}. Terima kasih atas partisipasinya.
                </div>
                <a href="{{ route('kuesioner-portal.lihat') }}"
                   class="inline-block mt-4 px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
                    Lihat jawaban saya
                </a>
            @elseif (! $memenuhi)
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                    Kuesioner ini ditujukan bagi ASN dan operator perangkat daerah yang pernah mengajukan minimal satu
                    permohonan layanan melalui portal. Akun Anda belum memenuhi kriteria tersebut.
                </div>
            @elseif ($response?->status === \App\Models\KuesionerPortalResponse::STATUS_PERSETUJU)
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    Anda sudah memberikan persetujuan, tetapi kuesioner belum dikirim.
                </div>
                <a href="{{ route('kuesioner-portal.isi') }}"
                   class="inline-block mt-4 px-5 py-2.5 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">
                    Lanjutkan pengisian
                </a>
            @else
                <p class="mt-1 text-sm text-gray-600">Periode: <strong>{{ $periode->nama }}</strong></p>
                <div class="mt-5 text-sm text-gray-700 leading-relaxed space-y-3">
                    <p>Bapak/Ibu yang terhormat,</p>
                    <p>{{ config('kuesioner_portal.pengantar') }}</p>
                    <p>Kuesioner terdiri atas lima bagian: A. Profil responden, B. Tingkat kepentingan,
                        C. Tingkat kinerja dan kepuasan, D. Reaksi terhadap kondisi layanan (kuesioner Kano),
                        dan E. Pertanyaan terbuka.</p>
                </div>

                @if ($response?->status === \App\Models\KuesionerPortalResponse::STATUS_MENOLAK)
                    <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600">
                        Sebelumnya Anda memilih tidak bersedia. Anda tetap dapat berubah pikiran.
                    </div>
                @endif

                <form method="POST" action="{{ route('kuesioner-portal.persetujuan') }}" class="mt-6 border-t pt-5">
                    @csrf
                    <p class="text-sm font-medium text-gray-900">Persetujuan responden</p>
                    <p class="text-sm text-gray-600 mt-1">Dengan melanjutkan pengisian, saya menyatakan bersedia menjadi responden secara sukarela.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="submit" name="setuju" value="ya"
                                class="px-5 py-2.5 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">
                            Ya, saya bersedia
                        </button>
                        <button type="submit" name="setuju" value="tidak"
                                class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Tidak bersedia
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
