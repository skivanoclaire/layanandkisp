{{--
    Tabel perbandingan data lama vs usulan.
    Variabel:
      $original (array), $proposed (array)
      $originalLabel, $proposedLabel (string judul kolom)
      $programmingLanguages, $frameworks, $databases, $serverLocations (collections)
--}}
@php
    $plMap = $programmingLanguages->keyBy('id');
    $fwMap = $frameworks->keyBy('id');
    $dbMap = $databases->keyBy('id');
    $locMap = $serverLocations->keyBy('id');

    $fields = [
        'nama_aplikasi' => ['Nama Aplikasi', 'text'],
        'tahun_pembuatan' => ['Tahun Pembuatan', 'text'],
        'description' => ['Deskripsi Website/Aplikasi', 'html'],
        'latar_belakang' => ['Latar Belakang Pembuatan', 'html'],
        'manfaat_aplikasi' => ['Manfaat Aplikasi', 'html'],
        'developer' => ['Developer / Pengembang', 'text'],
        'contact_person' => ['Contact Person', 'text'],
        'contact_phone' => ['No. Telepon', 'text'],
        'programming_language_id' => ['Bahasa Pemrograman', 'pl'],
        'programming_language_version' => ['Versi Bahasa', 'text'],
        'framework_id' => ['Framework', 'fw'],
        'framework_version' => ['Versi Framework', 'text'],
        'database_id' => ['Database', 'db'],
        'database_version' => ['Versi Database', 'text'],
        'frontend_tech' => ['Teknologi Frontend', 'text'],
        'server_ownership' => ['Kepemilikan Server', 'text'],
        'server_owner_name' => ['Nama Pemilik/Provider', 'text'],
        'server_location_id' => ['Lokasi Server', 'loc'],
    ];

    $render = function ($type, $value) use ($plMap, $fwMap, $dbMap, $locMap) {
        if ($value === null || $value === '') return '<span class="text-gray-400 italic">(kosong)</span>';
        return match ($type) {
            'pl'  => e(optional($plMap->get($value))->name ?? $value),
            'fw'  => e(optional($fwMap->get($value))->name ?? $value),
            'db'  => e(optional($dbMap->get($value))->name ?? $value),
            'loc' => e(optional($locMap->get($value))->name ?? $value),
            'html' => $value, // konten CKEditor (model trust sama dgn form subdomain)
            default => e($value),
        };
    };
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full table-auto border border-gray-200 text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-3 py-2 text-left w-1/5">Field</th>
                <th class="px-3 py-2 text-left w-2/5">{{ $originalLabel ?? 'Data Saat Ini' }}</th>
                <th class="px-3 py-2 text-left w-2/5">{{ $proposedLabel ?? 'Usulan' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fields as $key => [$label, $type])
                @php
                    $old = $original[$key] ?? null;
                    $new = $proposed[$key] ?? null;
                    $changed = (string) $old !== (string) $new;
                @endphp
                <tr class="border-t {{ $changed ? 'bg-yellow-50' : '' }}">
                    <td class="px-3 py-2 font-semibold text-gray-700 align-top">
                        {{ $label }}
                        @if ($changed) <span class="ml-1 text-[10px] text-yellow-700 font-bold">DIUBAH</span> @endif
                    </td>
                    <td class="px-3 py-2 align-top text-gray-600">{!! $render($type, $old) !!}</td>
                    <td class="px-3 py-2 align-top {{ $changed ? 'text-green-800 font-medium' : 'text-gray-600' }}">{!! $render($type, $new) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
