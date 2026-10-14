{{--
    Kotak petunjuk pengisian yang dapat dilipat.
    Variabel: $judul (string), $poin (array label => teks/HTML aman), $buka (bool, default true)
    Isi $poin ditulis oleh pengembang (bukan input pengguna), sehingga boleh memuat HTML sederhana.
--}}
<details class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-gray-800" @if ($buka ?? true) open @endif>
    <summary class="cursor-pointer font-semibold text-blue-800">Petunjuk: {{ $judul }}</summary>
    <dl class="mt-3 space-y-2">
        @foreach ($poin as $label => $teks)
            <div class="grid grid-cols-1 md:grid-cols-5 gap-1">
                <dt class="font-medium text-blue-900">{{ $label }}</dt>
                <dd class="md:col-span-4">{!! $teks !!}</dd>
            </div>
        @endforeach
    </dl>
</details>
