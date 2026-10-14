@extends('layouts.authenticated')

@section('title', '- Lembar Validasi Pakar')
@section('header-title', 'Lembar Validasi Pakar')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('user.kuesioner-portal._flash')
        @if ($errors->any())
            <div class="my-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800 text-sm">{{ $errors->first() }}</div>
        @endif

        <a href="{{ route('tata-kelola-keamanan.pakar.index') }}" class="text-sm text-green-700 hover:underline">&larr; Kembali</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Lembar Validasi Pakar</h1>
        <p class="text-sm text-gray-600">{{ $evaluasi->nama }} · {{ $evaluasi->instansi }} · Pakar: {{ $pakar->nama }}</p>

        @if (! $putaran)
            <div class="mt-6 p-6 bg-white rounded-lg shadow-sm text-sm text-gray-700">Saat ini tidak ada putaran penilaian yang dibuka. Terima kasih atas kesediaan Bapak/Ibu.</div>
        @elseif ($butir->isEmpty())
            <div class="mt-6 p-6 bg-white rounded-lg shadow-sm text-sm text-gray-700">Tidak ada butir yang perlu dinilai pada putaran ini.</div>
        @else
            <div class="mt-4">
                @include('admin.tata-kelola-keamanan._petunjuk', [
                    'judul' => 'Putaran '.$putaran->nomor.' — cara menilai',
                    'poin' => [
                        'Pertanyaan' => 'Apakah praktik COBIT 2019, klausul/kontrol ISO/IEC 27001:2022, dan ketentuan regulasi pada butir ini memang saling berkaitan dan tepat dipasangkan?',
                        'Setuju' => 'Bila semua unsur berkaitan dan tidak ada kontrol penting yang terlewat.',
                        'Tidak setuju' => 'Bila ada kontrol yang kurang tepat atau kurang lengkap; tuliskan kontrol mana yang perlu dihapus atau ditambahkan.',
                        'Menyimpan' => 'Jawaban dapat diubah selama putaran masih dibuka. Butir yang belum dinilai boleh dilengkapi nanti.',
                    ],
                ])
            </div>

            <form method="POST" action="{{ route('tata-kelola-keamanan.pakar.simpan', $evaluasi) }}" class="space-y-4">
                @csrf
                @foreach ($butir as $no => $p)
                    @php $j = $jawaban[$p->id] ?? null; @endphp
                    <div class="p-5 bg-white rounded-lg shadow-sm text-sm">
                        <p class="font-semibold">{{ $no + 1 }}. {{ $p->praktik->kode }} {{ $p->praktik->nama }}</p>
                        @php $praktikId = \App\Models\TataKelolaKeamanan\Pemetaan::praktikId($p->praktik->kode); @endphp
                        @if ($praktikId)
                            <p class="text-gray-600 text-xs">{{ $praktikId }}</p>
                        @endif

                        <p class="mt-3 text-gray-500 font-medium">ISO/IEC 27001:2022</p>
                        <ul class="ml-4 list-disc">
                            @foreach ($p->iso_items ?? [] as $kodeIso)
                                @php $info = \App\Models\TataKelolaKeamanan\Pemetaan::infoIso($kodeIso); @endphp
                                <li>{{ $info['label'] }}
                                    @if ($info['id'])<br><span class="text-gray-700">{{ $info['id'] }}</span>@endif
                                    @if ($info['penjelasan'])<br><span class="text-xs text-gray-500">{{ $info['penjelasan'] }}</span>@endif
                                </li>
                            @endforeach
                        </ul>

                        @if ($p->regulasi_items)
                            <p class="mt-3 text-gray-500 font-medium">Ketentuan regulasi</p>
                            <ul class="ml-4 list-disc">
                                @foreach ($p->regulasi_items as $k)
                                    <li>{{ \App\Services\TataKelolaKeamanan\Istilah::regulasi($k) }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if ($p->bukti_diharapkan)
                            <p class="mt-2"><span class="text-gray-500">Contoh bukti:</span> {{ $p->bukti_diharapkan }}</p>
                        @endif
                        @if ($p->catatan_revisi)
                            <p class="mt-1 text-yellow-800"><span class="text-gray-500">Revisi dari putaran sebelumnya:</span> {{ $p->catatan_revisi }}</p>
                        @endif
                        @if (isset($riwayat[$p->id]))
                            <div class="mt-2 p-2 rounded bg-gray-50 text-xs text-gray-600">
                                Putaran sebelumnya: {{ $riwayat[$p->id]['setuju'] }} dari {{ $riwayat[$p->id]['jumlah'] }} pakar setuju.
                                @foreach ($riwayat[$p->id]['saran'] as $s)
                                    <br>• {{ $s }}
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-6">
                            <label><input type="radio" name="setuju[{{ $p->id }}]" value="1" @checked($j?->setuju === true)> Setuju</label>
                            <label><input type="radio" name="setuju[{{ $p->id }}]" value="0" @checked($j?->setuju === false)> Tidak setuju</label>
                        </div>
                        <textarea name="saran[{{ $p->id }}]" rows="2" placeholder="Saran (opsional)" class="mt-2 w-full rounded-lg border-gray-300 text-sm">{{ $j?->saran }}</textarea>
                    </div>
                @endforeach
                <button class="px-6 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Kirim penilaian</button>
            </form>
        @endif
    </div>
@endsection
