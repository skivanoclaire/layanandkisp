@extends('layouts.authenticated')

@section('title', '- Dasbor Presensi Kegiatan')
@section('header-title', 'Presensi Kegiatan')

@section('content')
@php
    $btn = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold';
    $kartu = [
        ['Konfirmasi hadir di lokasi', $statistik['konfirmasi_hadir'], 'text-blue-700', 'konfirmasi_hadir'],
        ['Konfirmasi hadir online', $statistik['konfirmasi_online'], 'text-cyan-700', 'online'],
        ['Sudah presensi', $statistik['hadir'], 'text-green-700', 'hadir'],
        ['Konfirmasi tapi belum presensi', $statistik['belum_datang'], 'text-amber-600', 'belum_datang'],
        ['Instansi hadir', $statistik['instansi'], 'text-purple-700', null],
    ];
    $tautan = [
        'konfirmasi' => ['Konfirmasi Kehadiran', $kegiatan->urlKonfirmasi(), $kegiatan->konfirmasi_aktif, 'konfirmasi_aktif'],
        'presensi' => ['Presensi di Lokasi', $kegiatan->urlPresensi(), $kegiatan->presensi_aktif, 'presensi_aktif'],
    ];
@endphp
<div class="container mx-auto p-6">
    <a href="{{ route('admin.presensi-kegiatan.index') }}" class="text-sm text-green-700 hover:underline">← Semua kegiatan</a>

    <div class="mt-2 mb-6 flex flex-wrap justify-between items-start gap-4">
        <div class="max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800">{{ $kegiatan->judul }}</h1>
            <p class="text-gray-600 mt-1 text-sm">
                {{ $kegiatan->tanggal_label }}@if($kegiatan->waktu) · {{ $kegiatan->waktu }}@endif · {{ $kegiatan->tempat }}
                @if($kegiatan->kuota_per_instansi) · kuota {{ $kegiatan->kuota_per_instansi }} orang/instansi @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.presensi-kegiatan.edit', $kegiatan) }}" class="{{ $btn }} bg-gray-100 hover:bg-gray-200 text-gray-700">Ubah</a>
            <a href="{{ route('admin.presensi-kegiatan.rekap', [$kegiatan, 'jenis' => 'hadir']) }}" target="_blank" class="{{ $btn }} bg-red-600 hover:bg-red-700 text-white">Cetak Daftar Hadir (SPJ)</a>
            <a href="{{ route('admin.presensi-kegiatan.rekap', [$kegiatan, 'jenis' => 'konfirmasi']) }}" target="_blank" class="{{ $btn }} bg-red-50 hover:bg-red-100 text-red-700">Rekap Konfirmasi (PDF)</a>
            <a href="{{ route('admin.presensi-kegiatan.ekspor', $kegiatan) }}" class="{{ $btn }} bg-emerald-600 hover:bg-emerald-700 text-white">Excel</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Tautan publik & saklar --}}
    <div class="grid md:grid-cols-2 gap-4 mb-6">
        @foreach($tautan as $mode => [$judul, $url, $aktif, $kolom])
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-gray-800">{{ $judul }}</div>
                    <form method="POST" action="{{ route('admin.presensi-kegiatan.toggle', $kegiatan) }}">
                        @csrf
                        <input type="hidden" name="kolom" value="{{ $kolom }}">
                        <button type="submit" class="flex items-center gap-2 text-sm font-semibold {{ $aktif ? 'text-green-700' : 'text-gray-500' }}">
                            <span class="relative inline-block w-10 h-5 rounded-full transition {{ $aktif ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all {{ $aktif ? 'left-5' : 'left-0.5' }}"></span>
                            </span>
                            {{ $aktif ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <input readonly value="{{ $url }}" class="flex-1 min-w-0 text-xs bg-gray-50 border border-gray-200 rounded px-2 py-1.5 text-gray-700" onclick="this.select()">
                    <button type="button" class="text-xs font-semibold text-green-700 hover:underline whitespace-nowrap"
                            onclick="navigator.clipboard.writeText('{{ $url }}'); this.textContent='Tersalin ✓'">Salin</button>
                </div>
                <div class="mt-3 flex gap-3 text-sm">
                    <a href="{{ route('admin.presensi-kegiatan.qr', [$kegiatan, 'mode' => $mode]) }}" target="_blank" class="font-semibold text-green-700 hover:underline">🖨️ Cetak QR Code</a>
                    <a href="{{ $url }}" target="_blank" class="text-gray-600 hover:underline">Buka halaman ↗</a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tautan rapat online via WA --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[16rem]">
            <div class="font-bold text-gray-800">💻 Tautan Rapat Online (WhatsApp)</div>
            @if($kegiatan->tautan_online)
                <div class="text-sm text-gray-600 break-all">{{ $kegiatan->tautan_online }}</div>
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ $statistik['konfirmasi_online'] }} peserta online · {{ $statistik['online_belum_wa'] }} belum menerima tautan
                </div>
            @else
                <div class="text-sm text-amber-700">Belum diisi. Isi di menu <a href="{{ route('admin.presensi-kegiatan.edit', $kegiatan) }}" class="underline">Ubah</a> agar tautan dapat dikirim ke WA peserta online.</div>
            @endif
        </div>
        @if($kegiatan->tautan_online)
            <form method="POST" action="{{ route('admin.presensi-kegiatan.kirim-tautan-online', $kegiatan) }}"
                  onsubmit="return confirm('Kirim tautan ke {{ $statistik['online_belum_wa'] }} peserta online yang belum menerima?')">
                @csrf
                <button class="{{ $btn }} bg-green-600 hover:bg-green-700 text-white disabled:opacity-50" @disabled($statistik['online_belum_wa'] === 0)>
                    Kirim ke yang belum ({{ $statistik['online_belum_wa'] }})
                </button>
            </form>
            <form method="POST" action="{{ route('admin.presensi-kegiatan.kirim-tautan-online', $kegiatan) }}"
                  onsubmit="return confirm('Kirim ULANG tautan ke semua {{ $statistik['konfirmasi_online'] }} peserta online? Gunakan bila tautan berubah.')">
                @csrf
                <input type="hidden" name="ulang" value="1">
                <button class="{{ $btn }} bg-gray-100 hover:bg-gray-200 text-gray-700 disabled:opacity-50" @disabled($statistik['konfirmasi_online'] === 0)>
                    Kirim ulang ke semua
                </button>
            </form>
        @endif
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach($kartu as [$teks, $angka, $warna, $kunci])
            <a @if($kunci) href="{{ route('admin.presensi-kegiatan.show', [$kegiatan, 'filter' => $kunci]) }}" @endif
               class="bg-white rounded-lg shadow-md p-4 block {{ $kunci ? 'hover:ring-2 hover:ring-green-300' : '' }} {{ $filter === $kunci && $kunci ? 'ring-2 ring-green-500' : '' }}">
                <div class="text-3xl font-bold {{ $warna }}">{{ $angka }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ $teks }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Tabel peserta --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md">
            <form method="GET" class="p-4 border-b flex flex-wrap gap-2 items-center">
                <input name="cari" value="{{ $cari }}" placeholder="Cari nama, NIP, instansi…" class="flex-1 min-w-[12rem] border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <select name="filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">Semua peserta</option>
                    <option value="konfirmasi_hadir" @selected($filter === 'konfirmasi_hadir')>Hadir di lokasi</option>
                    <option value="online" @selected($filter === 'online')>Hadir online</option>
                    <option value="hadir" @selected($filter === 'hadir')>Sudah presensi</option>
                    <option value="belum_datang" @selected($filter === 'belum_datang')>Belum presensi</option>
                </select>
                <button class="bg-gray-800 text-white rounded-lg px-4 py-2 text-sm font-semibold">Cari</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Peserta</th>
                            <th class="px-3 py-2">Instansi</th>
                            <th class="px-3 py-2">Konfirmasi</th>
                            <th class="px-3 py-2">Presensi</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($peserta as $i => $p)
                            <tr class="align-top">
                                <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold text-gray-800">{{ $p->nama }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($p->nip)NIP {{ $p->nip }} · @endif{{ $p->no_hp }}
                                    </div>
                                    @if($p->jabatan)<div class="text-xs text-gray-500">{{ $p->jabatan }}</div>@endif
                                    @if($p->sumber === 'akun')
                                        <span class="text-[10px] font-semibold bg-blue-50 text-blue-700 rounded px-1.5">akun</span>
                                    @elseif($p->sumber === 'admin')
                                        <span class="text-[10px] font-semibold bg-purple-50 text-purple-700 rounded px-1.5">panitia</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs">{{ $p->instansi }}</td>
                                <td class="px-3 py-2 text-xs whitespace-nowrap">
                                    @if($p->konfirmasi === 'hadir')
                                        <span class="text-blue-700 font-semibold">Di lokasi</span>
                                    @elseif($p->konfirmasi === 'online')
                                        <span class="text-cyan-700 font-semibold">Online</span>
                                        @if($p->wa_online_terkirim_at)
                                            <div class="text-green-700" title="Tautan dikirim via WA">WA ✓ {{ $p->wa_online_terkirim_at->format('d/m H:i') }}</div>
                                        @else
                                            <div class="text-amber-600">WA belum terkirim</div>
                                        @endif
                                        @if($kegiatan->tautan_online)
                                            <form method="POST" action="{{ route('admin.presensi-kegiatan.peserta.kirim-tautan', [$kegiatan, $p]) }}">
                                                @csrf
                                                <button class="text-green-700 font-semibold hover:underline">{{ $p->wa_online_terkirim_at ? 'Kirim ulang WA' : 'Kirim WA' }}</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-gray-400">–</span>
                                    @endif
                                    @if($p->konfirmasi_at)<div class="text-gray-400">{{ $p->konfirmasi_at->format('d/m H:i') }}</div>@endif
                                    @if($p->catatan)<div class="text-gray-500 italic max-w-[12rem]">“{{ $p->catatan }}”</div>@endif
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    @if($p->hadir_at)
                                        <div class="text-green-700 font-semibold whitespace-nowrap">✓ {{ $p->hadir_at->format('d/m H:i') }}</div>
                                        @if($p->tanda_tangan)
                                            <img src="{{ route('admin.presensi-kegiatan.peserta.ttd', [$kegiatan, $p]) }}" alt="TTD" loading="lazy" class="h-8 mt-1">
                                        @endif
                                    @else
                                        <span class="text-gray-400">Belum</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.presensi-kegiatan.peserta.hadir', [$kegiatan, $p]) }}" class="inline">
                                        @csrf
                                        <button class="text-xs font-semibold {{ $p->hadir_at ? 'text-amber-600' : 'text-green-700' }} hover:underline">
                                            {{ $p->hadir_at ? 'Batal hadir' : 'Tandai hadir' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.presensi-kegiatan.peserta.destroy', [$kegiatan, $p]) }}" class="inline ml-2"
                                          onsubmit="return confirm('Hapus data {{ addslashes($p->nama) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-8 text-center text-gray-500">Belum ada data peserta.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Tambah peserta oleh panitia --}}
            <details class="bg-white rounded-lg shadow-md p-4" @if($errors->any()) open @endif>
                <summary class="font-bold text-gray-800 cursor-pointer">+ Tambah peserta (oleh panitia)</summary>
                <form method="POST" action="{{ route('admin.presensi-kegiatan.peserta.store', $kegiatan) }}" class="mt-4 space-y-3">
                    @csrf
                    <input name="nama" value="{{ old('nama') }}" required placeholder="Nama lengkap *" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <input name="nip" value="{{ old('nip') }}" placeholder="NIP" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <input name="jabatan" value="{{ old('jabatan') }}" placeholder="Jabatan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <input name="instansi" value="{{ old('instansi') }}" required list="admin-instansi" placeholder="Instansi *" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <datalist id="admin-instansi">@foreach($unitKerjas as $uk)<option value="{{ $uk }}">@endforeach</datalist>
                    <input name="no_hp" value="{{ old('no_hp') }}" required placeholder="No. HP *" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="hadir" value="1" checked class="rounded text-green-600"> Langsung tandai hadir
                    </label>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white rounded-lg py-2 text-sm font-semibold">Simpan</button>
                </form>
            </details>

            {{-- Perangkat daerah yang belum mengirim peserta --}}
            <details class="bg-white rounded-lg shadow-md p-4">
                <summary class="font-bold text-gray-800 cursor-pointer">
                    Perangkat daerah belum mengirim peserta
                    <span class="ml-1 text-xs bg-amber-100 text-amber-700 rounded-full px-2 py-0.5">{{ $belumKonfirmasi->count() }}</span>
                </summary>
                <p class="text-xs text-gray-500 mt-2">Unit kerja bertipe Induk &amp; Cabang Perangkat Daerah yang belum memiliki satu pun peserta terdaftar.</p>
                <ol class="mt-3 text-xs text-gray-700 list-decimal pl-5 space-y-1 max-h-96 overflow-y-auto">
                    @foreach($belumKonfirmasi as $nama)<li>{{ $nama }}</li>@endforeach
                </ol>
            </details>
        </div>
    </div>
</div>
@endsection
