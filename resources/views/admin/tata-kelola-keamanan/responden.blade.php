@extends('layouts.authenticated')

@section('title', '- Responden')
@section('header-title', 'Tata Kelola Keamanan: Responden dan Informan')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @include('admin.tata-kelola-keamanan._nav')

        @include('admin.tata-kelola-keamanan._petunjuk', [
            'judul' => 'Mendaftarkan responden',
            'poin' => [
                'Tujuan' => 'Responden internal instansi mengisi kuesioner dari akun portal masing-masing, sehingga identitas dan waktu pengisian tercatat dan Anda tidak perlu mengetik ulang.',
                'Siapa didaftarkan' => '<strong>Pimpinan</strong> (Kepala Dinas, Sekretaris, Kepala Bidang) → centang <em>isi design factors</em>. <strong>Informan RACI</strong> (pejabat dan pelaksana yang menjalankan proses keamanan) → pilih objektif yang mereka kuasai. Satu orang boleh mendapat keduanya.',
                'Contoh peran' => 'Kepala Bidang yang membidangi persandian dan keamanan informasi → CISO → APO12, APO13, DSS05. Pengelola pusat data/jaringan → Head of IT Operations → DSS04, DSS05. Kepala Dinas → Board/Executive → design factors, EDM03.',
                'Syarat akun' => 'Akun harus sudah terverifikasi di portal. Cari dengan nama, email, atau NIP (minimal 3 huruf). Bila belum punya akun, minta responden mendaftar dan diverifikasi terlebih dahulu, atau isikan jawabannya secara manual.',
                'Peserta FGD' => 'Informan yang menangani keamanan informasi, aplikasi, dan infrastruktur TIK juga menjadi peserta FGD validasi model di tab <em>4. Validasi Model</em>. Kehadirannya dicatat pengelola; peserta tidak perlu mengisi apa pun dari akunnya.',
                'Berikutnya' => 'Beri tahu responden untuk membuka menu <em>Layanan Digital → Kuesioner Tata Kelola Keamanan</em>. Pantau kemajuannya di tabel di bawah. Kuesioner aktivitas baru muncul untuk informan setelah Anda memasukkan aktivitas di tab 5a.',
            ],
        ])

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 overflow-x-auto">
            <h3 class="font-semibold text-gray-900 mb-3">Responden terdaftar ({{ $respondens->count() }})</h3>
            @if ($respondens->isEmpty())
                <p class="text-sm text-gray-500">Belum ada responden.</p>
            @else
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="py-2 pr-3">Nama / jabatan</th>
                            <th class="py-2 px-3">Peran RACI</th>
                            <th class="py-2 px-3 text-center">Design factors</th>
                            <th class="py-2 px-3">Objektif aktivitas</th>
                            <th class="py-2 px-3 text-center">Aktivitas dijawab</th>
                            <th class="py-2 px-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y align-top">
                        @foreach ($respondens as $r)
                            <tr>
                                <td class="py-2 pr-3">{{ $r->user->name ?? '-' }}<br><span class="text-xs text-gray-500">{{ $r->jabatan }} · {{ $r->user->email ?? '' }}</span></td>
                                <td class="py-2 px-3">{{ $r->peran_raci ?? '-' }}</td>
                                <td class="py-2 px-3 text-center">{{ $r->isi_df ? $progres[$r->id]['df'].' dari '.count($definisi).' lengkap' : '-' }}</td>
                                <td class="py-2 px-3">{{ implode(', ', $r->objektif ?? []) ?: '-' }}</td>
                                <td class="py-2 px-3 text-center">{{ $r->objektif ? $r->jawabans_count.' dari '.$progres[$r->id]['aktivitas_tugas'] : '-' }}</td>
                                <td class="py-2 px-3">
                                    <details>
                                        <summary class="cursor-pointer text-xs text-green-700">ubah</summary>
                                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.responden.update', [$evaluasi, $r]) }}" class="mt-2 space-y-2 w-72">
                                            @csrf @method('PUT')
                                            <input name="jabatan" value="{{ $r->jabatan }}" required class="w-full rounded border-gray-300 text-sm">
                                            <input name="peran_raci" value="{{ $r->peran_raci }}" placeholder="Peran RACI" class="w-full rounded border-gray-300 text-sm">
                                            <label class="block text-sm"><input type="hidden" name="isi_df" value="0"><input type="checkbox" name="isi_df" value="1" @checked($r->isi_df)> isi design factors</label>
                                            <div class="text-xs">
                                                @foreach ($objektifs as $o)
                                                    <label class="mr-2"><input type="checkbox" name="objektif[]" value="{{ $o->kode }}" @checked(in_array($o->kode, $r->objektif ?? [], true))> {{ $o->kode }}</label>
                                                @endforeach
                                            </div>
                                            <button class="px-3 py-1 text-sm rounded bg-green-600 text-white">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.tata-kelola-keamanan.responden.destroy', [$evaluasi, $r]) }}" class="mt-2" onsubmit="return confirm('Hapus responden ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs text-red-600 hover:underline">hapus</button>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Tambah responden dari akun portal</h3>
            <form method="GET" action="{{ route('admin.tata-kelola-keamanan.responden', $evaluasi) }}" class="flex gap-2 mb-4">
                <input name="q" value="{{ $q }}" placeholder="Cari nama, email, atau NIP (minimal 3 huruf)" class="w-full md:w-96 rounded-lg border-gray-300 text-sm">
                <button class="px-4 py-2 text-sm rounded-lg border border-gray-300">Cari</button>
            </form>

            @if ($q !== '' && $hasilCari->isEmpty())
                <p class="text-sm text-gray-500">Tidak ada akun terverifikasi yang cocok (akun yang sudah terdaftar tidak ditampilkan).</p>
            @endif

            @foreach ($hasilCari as $u)
                <form method="POST" action="{{ route('admin.tata-kelola-keamanan.responden.store', $evaluasi) }}" class="mb-3 p-3 rounded-lg border grid grid-cols-1 md:grid-cols-6 gap-2 items-start text-sm">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                    <div class="md:col-span-2">
                        <p class="font-medium">{{ $u->name }}</p>
                        <p class="text-xs text-gray-500">{{ $u->email }} · {{ $u->unitKerja->nama ?? '' }}</p>
                    </div>
                    <input name="jabatan" required placeholder="Jabatan (mis. Kabid Persandian)" class="rounded border-gray-300 text-sm">
                    <input name="peran_raci" placeholder="Peran RACI (mis. CISO)" class="rounded border-gray-300 text-sm">
                    <div class="text-xs">
                        <label class="block"><input type="checkbox" name="isi_df" value="1"> isi design factors</label>
                        @forelse ($objektifs as $o)
                            <label class="mr-2"><input type="checkbox" name="objektif[]" value="{{ $o->kode }}"> {{ $o->kode }}</label>
                        @empty
                            <span class="text-gray-400">objektif belum dipilih (Tahap 2)</span>
                        @endforelse
                    </div>
                    <button class="px-3 py-2 text-sm rounded-lg bg-green-600 text-white hover:bg-green-700">Daftarkan</button>
                </form>
            @endforeach
        </div>
    </div>
@endsection
