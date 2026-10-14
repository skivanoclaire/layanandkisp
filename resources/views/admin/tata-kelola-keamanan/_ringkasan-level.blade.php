{{-- Tabel pencapaian per tingkat. Variabel: $hasil (output Kapabilitas::hitung) --}}
<table class="min-w-full text-sm">
    <thead>
        <tr class="text-left text-xs text-gray-500 border-b">
            <th class="py-1 pr-3">Tingkat</th>
            <th class="py-1 px-3 text-center">Aktivitas terpenuhi</th>
            <th class="py-1 px-3 text-center">Sudah dinilai</th>
            <th class="py-1 px-3 text-center">Pencapaian</th>
            <th class="py-1 px-3">Rating</th>
        </tr>
    </thead>
    <tbody class="divide-y">
        @foreach ($hasil['per_level'] as $level => $pl)
            <tr>
                <td class="py-1 pr-3">Level {{ $level }}</td>
                <td class="py-1 px-3 text-center">{{ $pl['total'] ? $pl['terpenuhi'].' dari '.$pl['total'] : '-' }}</td>
                <td class="py-1 px-3 text-center">{{ $pl['total'] ? $pl['dinilai'].' dari '.$pl['total'] : '-' }}</td>
                <td class="py-1 px-3 text-center">{{ $pl['persen'] !== null ? number_format($pl['persen'], 1, ',', '').'%' : '-' }}</td>
                <td class="py-1 px-3">
                    @if ($pl['rating'])
                        <span class="px-2 py-0.5 text-xs rounded {{ $pl['rating'] === 'F' ? 'bg-green-100 text-green-800' : ($pl['rating'] === 'L' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ $pl['rating'] }} — {{ \App\Services\TataKelolaKeamanan\Aturan::labelRating($pl['rating']) }}
                        </span>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
