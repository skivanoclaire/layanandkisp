@extends('layouts.authenticated')

@section('title', '- Design Factors')
@section('header-title', 'Tata Kelola Keamanan: Design Factors COBIT 2019')

@section('content')
    @php
        $rataRata = $responden === ''
            ? fn ($kode) => ($semua[$kode] ?? collect())->where('responden', '!=', '')
            : null;
    @endphp
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Tahap 1 — Kuesioner design factors',
            'poin' => [
                'Tujuan' => 'Menggambarkan kondisi instansi dengan 11 design factors COBIT 2019. Hasilnya menentukan objektif mana yang paling penting (Tahap 2).',
                'Siapa mengisi' => 'Pimpinan instansi (mis. Kepala Dinas, Sekretaris, para Kepala Bidang) secara individual. Cara termudah: daftarkan akun mereka di tab <strong>Responden</strong> dan centang "isi design factors"; mereka mengisi sendiri dari menu <em>Kuesioner Tata Kelola Keamanan</em>. Jika ada yang mengisi di kertas, Anda dapat mengentrinya di sini dengan tombol "Tambah" (nama responden manual).',
                'Nilai akhir (FGD)' => 'Setelah semua responden mengisi, adakan FGD. Gunakan kolom <em>rata-rata responden</em> sebagai bahan diskusi, lalu isi nilai yang disepakati pada pilihan <strong>Nilai akhir (FGD)</strong>. Nilai akhir inilah yang disalin ke COBIT 2019 Design Toolkit (file Excel ISACA).',
                'Contoh' => 'DF5 Threat landscape: Normal 60, High 40 (jumlah harus 100). DF7 Role of IT: Factory 4 bila layanan SPBE langsung terhenti saat TI gangguan.',
                'Kesalahan umum' => 'Jumlah persentase tidak 100 (DF5, DF6, DF8, DF9, DF10); mengisi nilai akhir dengan rata-rata tanpa diskusi; lupa mengisi DF11.',
                'Berikutnya' => 'Buka Design Toolkit, masukkan nilai akhir, lalu salin skor kepentingan 40 objektif ke Tahap 2.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-700">Isian:</span>
                <a href="{{ route('admin.tata-kelola-keamanan.design-factors', $evaluasi) }}"
                   class="px-3 py-1 text-sm rounded-full border {{ $responden === '' ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-700' }}">Nilai akhir (FGD)</a>
                @foreach ($daftarResponden as $r)
                    <a href="{{ route('admin.tata-kelola-keamanan.design-factors', [$evaluasi, 'responden' => $r->responden]) }}"
                       class="px-3 py-1 text-sm rounded-full border {{ $responden === $r->responden ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 text-gray-700' }}">
                        {{ $r->responden }} @if ($r->responden_id) <span class="text-xs">(akun)</span> @endif
                    </a>
                @endforeach
                <form method="GET" action="{{ route('admin.tata-kelola-keamanan.design-factors', $evaluasi) }}" class="flex gap-1">
                    <input name="responden" placeholder="Responden manual (mis. Sekretaris)" class="rounded-lg border-gray-300 text-sm">
                    <button class="px-3 py-1 text-sm rounded-lg border border-gray-300">Tambah</button>
                </form>
            </div>
        </div>

        @if ($isianAkun)
            <p class="mb-3 p-3 rounded-lg bg-gray-100 text-sm text-gray-700">Isian ini diisi responden sendiri melalui akunnya, sehingga hanya dapat dibaca.</p>
            @include('admin.tata-kelola-keamanan._form-df', ['definisi' => $definisi, 'isian' => $isian, 'readonly' => true, 'rataRata' => null])
        @else
            <form method="POST" action="{{ route('admin.tata-kelola-keamanan.design-factors.store', $evaluasi) }}">
                @csrf
                <input type="hidden" name="responden" value="{{ $responden }}">
                <p class="mb-3 text-sm font-semibold text-gray-800">Sedang mengisi: {{ $responden === '' ? 'Nilai akhir (FGD)' : 'Responden '.$responden }}</p>
                @include('admin.tata-kelola-keamanan._form-df', ['definisi' => $definisi, 'isian' => $isian, 'readonly' => false, 'rataRata' => $rataRata])
                <button class="px-5 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan isian</button>
            </form>

            @if ($responden !== '')
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.design-factors.destroy-responden', $evaluasi) }}" class="mt-4"
                      onsubmit="return confirm('Hapus seluruh jawaban responden ini?')">
                    @csrf @method('DELETE')
                    <input type="hidden" name="responden" value="{{ $responden }}">
                    <button class="text-sm text-red-600 hover:underline">Hapus jawaban responden "{{ $responden }}"</button>
                </form>
            @endif
        @endif
    </div>
@endsection
