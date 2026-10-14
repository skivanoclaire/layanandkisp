{{--
    Isian kuesioner design factors, dipakai halaman admin dan halaman responden.
    Variabel: $definisi, $isian (koleksi DesignFactor per df_kode), $readonly (bool),
    $rataRata (callable|null: fn($kode) => koleksi isian responden, untuk kolom rata-rata di nilai akhir)
--}}
@php $ro = $readonly ?? false; @endphp
@foreach ($definisi as $kode => $df)
    @php
        $nilai = old("nilai.$kode", $isian[$kode]->nilai ?? []);
        $resp = isset($rataRata) && $rataRata ? $rataRata($kode) : collect();
    @endphp
    <div class="bg-white rounded-lg shadow-sm p-6 mb-4 overflow-x-auto">
        <h3 class="font-semibold text-gray-900">{{ $kode }} — {{ $df['nama'] }}
            @if ($namaId = \App\Services\TataKelolaKeamanan\Istilah::dfNama($kode))
                <span class="font-normal text-gray-600">({{ $namaId }})</span>
            @endif
        </h3>
        @if (! empty($df['penjelasan']))
            <p class="text-sm text-gray-700 mt-1">{{ $df['penjelasan'] }}</p>
        @endif
        <p class="text-xs text-gray-500 mb-3">
            @switch($df['tipe'])
                @case('bobot') Isi angka {{ $df['skala'][0] }}–{{ $df['skala'][1] }} untuk setiap baris. {{ $df['keterangan'] ?? '' }} @break
                @case('persen') Isi persentase; jumlah semua baris harus 100. @break
                @case('risiko') Isi dampak dan kemungkinan, masing-masing {{ $df['skala'][0] }}–{{ $df['skala'][1] }}. @break
                @case('pilihan') Pilih satu. @break
            @endswitch
        </p>

        @if ($df['tipe'] === 'pilihan')
            @foreach ($df['butir'] as $i => $b)
                <label class="mr-6 text-sm"><input type="radio" name="nilai[{{ $kode }}][pilihan]" value="{{ $i }}" @disabled($ro) @checked(isset($nilai['pilihan']) && (int) $nilai['pilihan'] === $i)>
                    {{ \App\Services\TataKelolaKeamanan\Istilah::dfButir($kode, $i) ?? $b }} <span class="text-xs text-gray-500">({{ $b }})</span></label>
            @endforeach
        @else
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-1 pr-4">Butir</th>
                        @if ($df['tipe'] === 'risiko')
                            <th class="py-1 px-2 w-24">Dampak</th>
                            <th class="py-1 px-2 w-24">Kemungkinan</th>
                        @else
                            <th class="py-1 px-2 w-28">Nilai</th>
                        @endif
                        @if ($resp->isNotEmpty())
                            <th class="py-1 px-2 text-right">Rata-rata responden ({{ $resp->count() }})</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($df['butir'] as $i => $b)
                        <tr>
                            @php
                                $butirId = \App\Services\TataKelolaKeamanan\Istilah::dfButir($kode, $i);
                                $penjelasanButir = \App\Services\TataKelolaKeamanan\Istilah::dfPenjelasanButir($kode, $i);
                            @endphp
                            <td class="py-1 pr-4">
                                <span class="text-gray-900">{{ $butirId ?? $b }}</span>
                                @if ($butirId)
                                    <span class="text-xs text-gray-500 italic">({{ $b }})</span>
                                @endif
                                @if ($penjelasanButir)
                                    <br><span class="text-xs text-gray-500">{{ $penjelasanButir }}</span>
                                @endif
                            </td>
                            @if ($df['tipe'] === 'risiko')
                                <td class="py-1 px-2"><input type="number" min="{{ $df['skala'][0] }}" max="{{ $df['skala'][1] }}" name="nilai[{{ $kode }}][dampak][{{ $i }}]" value="{{ $nilai['dampak'][$i] ?? '' }}" @disabled($ro) class="w-20 rounded border-gray-300 text-sm"></td>
                                <td class="py-1 px-2"><input type="number" min="{{ $df['skala'][0] }}" max="{{ $df['skala'][1] }}" name="nilai[{{ $kode }}][kemungkinan][{{ $i }}]" value="{{ $nilai['kemungkinan'][$i] ?? '' }}" @disabled($ro) class="w-20 rounded border-gray-300 text-sm"></td>
                            @else
                                <td class="py-1 px-2"><input type="number"
                                           min="{{ $df['tipe'] === 'persen' ? 0 : $df['skala'][0] }}" max="{{ $df['tipe'] === 'persen' ? 100 : $df['skala'][1] }}"
                                           name="nilai[{{ $kode }}][{{ $i }}]" value="{{ $nilai[$i] ?? '' }}" @disabled($ro) class="w-24 rounded border-gray-300 text-sm"></td>
                            @endif
                            @if ($resp->isNotEmpty())
                                <td class="py-1 px-2 text-right text-gray-600">
                                    @if ($df['tipe'] === 'risiko')
                                        @php
                                            $d = $resp->map(fn ($x) => $x->nilai['dampak'][$i] ?? null)->filter(fn ($v) => $v !== null);
                                            $k = $resp->map(fn ($x) => $x->nilai['kemungkinan'][$i] ?? null)->filter(fn ($v) => $v !== null);
                                        @endphp
                                        {{ $d->isNotEmpty() ? number_format($d->avg(), 1, ',', '') : '-' }} / {{ $k->isNotEmpty() ? number_format($k->avg(), 1, ',', '') : '-' }}
                                    @else
                                        @php $v = $resp->map(fn ($x) => $x->nilai[$i] ?? null)->filter(fn ($v) => $v !== null); @endphp
                                        {{ $v->isNotEmpty() ? number_format($v->avg(), 1, ',', '') : '-' }}
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <textarea name="catatan[{{ $kode }}]" rows="1" placeholder="Catatan (opsional)" @disabled($ro) class="mt-3 w-full rounded-lg border-gray-300 text-sm">{{ old("catatan.$kode", $isian[$kode]->catatan ?? '') }}</textarea>
    </div>
@endforeach
