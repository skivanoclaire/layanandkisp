@extends('layouts.user')
@section('title', 'Edit Permohonan Email')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-green-700">Edit Permohonan Email ({{ $item->ticket_no }})</h1>

        <form action="{{ route('user.email.update', $item->id) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm mb-1">Nama</label>
                <input type="text" name="nama" value="{{ old('nama', $item->nama) }}" required
                    class="w-full border rounded p-2">
                @error('nama')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            {{-- ====== LIST INSTANSI ====== --}}
            @php
                $instansiList = [
                    // Sekretariat Daerah
                    'SEKRETARIAT DAERAH',

                    // Inspektorat
                    'INSPEKTORAT',

                    // Badan (urut abjad)
                    'BADAN KESATUAN BANGSA DAN POLITIK',
                    'BADAN KEPEGAWAIAN DAERAH',
                    'BADAN KEUANGAN DAN ASET DAERAH',
                    'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH',
                    'BADAN PENGELOLA PERBATASAN DAERAH',
                    'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
                    'BADAN PENGHUBUNG',
                    'BADAN PENANGGULANGAN BENCANA DAERAH',
                    'BADAN PERENCANAAN PEMBANGUNAN DAERAH DAN LITBANG',
                    'BADAN PENDAPATAN DAERAH',

                    // Dinas (urut abjad)
                    'DINAS ENERGI DAN SUMBER DAYA MINERAL',
                    'DINAS KELAUTAN DAN PERIKANAN',
                    'DINAS KEHUTANAN',
                    'DINAS KESEHATAN',
                    'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL',
                    'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
                    'DINAS LINGKUNGAN HIDUP',
                    'DINAS PARIWISATA',
                    'DINAS PEMUDA DAN OLAHRAGA',
                    'DINAS PEMBERDAYAAN MASYARAKAT DAN DESA',
                    'DINAS PEMBERDAYAAN PEREMPUAN, PERLINDUNGAN ANAK, PENGENDALIAN PENDUDUK DAN KELUARGA BERENCANA',
                    'DINAS PENDIDIKAN DAN KEBUDAYAAN',
                    'DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU',
                    'DINAS PERTANIAN DAN KETAHANAN PANGAN',
                    'DINAS PERHUBUNGAN',
                    'DINAS PERINDUSTRIAN, PERDAGANGAN, KOPERASI, USAHA KECIL DAN MENENGAH',
                    'DINAS PERPUSTAKAAN DAN KEARSIPAN',
                    'DINAS PEKERJAAN UMUM, PENATAAN RUANG, PERUMAHAN DAN KAWASAN PERMUKIMAN',
                    'DINAS SOSIAL',
                    'DINAS TENAGA KERJA DAN TRANSMIGRASI',

                    // Biro (urut abjad)
                    'BIRO ADMINISTRASI PEMBANGUNAN',
                    'BIRO ADMINISTRASI PIMPINAN',
                    'BIRO HUKUM',
                    'BIRO KESEJAHTERAAN RAKYAT',
                    'BIRO ORGANISASI',
                    'BIRO PEMERINTAHAN DAN OTONOMI DAERAH',
                    'BIRO PENGADAAN BARANG DAN JASA',
                    'BIRO PEREKONOMIAN',
                    'BIRO UMUM',

                    // SATPOL PP
                    'SATUAN POLISI PAMONG PRAJA',

                    // Sekretariat Dewan
                    'SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH',

                    // RSUD
                    'RUMAH SAKIT UMUM DAERAH PROVINSI KALIMANTAN UTARA',

                    // UPT/UPTD/Cabang Dinas (urut abjad)
                    'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH MALINAU',
                    'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH NUNUKAN',
                    'CABANG DINAS PENDIDIKAN DAN KEBUDAYAAN WILAYAH TARAKAN',
                    'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI BULUNGAN',
                    'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI MALINAU',
                    'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI NUNUKAN',
                    'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI TARAKAN',
                    'UPT BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH KELAS A DI TIDENG PALE',
                    'UPT INSTALASI FARMASI',
                    'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN BULUNGAN',
                    'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN MALINAU',
                    'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN NUNUKAN',
                    'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KABUPATEN TANA TIDUNG',
                    'UPT KESATUAN PENGELOLAAN HUTAN KELAS A KOTA TARAKAN',
                    'UPT LABORATORIUM KESEHATAN HEWAN DAN KESEHATAN MASYARAKAT VETERINER',
                    'UPT PANTI SOSIAL TRISNA WERDHA MARGA RAHAYU',
                    'UPT PELABUHAN PERIKANAN TENGKAYU II',
                    'UPT PENERAPAN MUTU HASIL PERIKANAN',
                    'UPT PERIKANAN BUDIDAYA LAUT DAN PAYAU',
                    'UPT TAMAN BUDAYA KALTARA',
                    'UPT TEKNOLOGI, INFORMASI DAN KOMUNIKASI PENDIDIKAN',
                    'UPTD LABORATORIUM LINGKUNGAN HIDUP',
                    'UPTD PELABUHAN LIEM HIE DJUNG NUNUKAN',
                    'UPTD PELABUHAN TENGKAYU I',
                    'UPTD PERLINDUNGAN PEREMPUAN DAN ANAK',

                    // SMA (urut abjad)
                    'SMA NEGERI 1 BUNYU',
                    'SMA NEGERI 1 KRAYAN',
                    'SMA NEGERI 1 KRAYAN SELATAN',
                    'SMA NEGERI 1 LUMBIS',
                    'SMA NEGERI 1 MALINAU',
                    'SMA NEGERI 10 MALINAU',
                    'SMA NEGERI 11 MALINAU',
                    'SMA NEGERI 12 MALINAU',
                    'SMA NEGERI 13 MALINAU',
                    'SMA NEGERI 14 MALINAU',
                    'SMA NEGERI 15 MALINAU',
                    'SMA NEGERI 16 MALINAU',
                    'SMA NEGERI 1 NUNUKAN',
                    'SMA NEGERI 1 NUNUKAN SELATAN',
                    'SMA NEGERI 1 PESO',
                    'SMA NEGERI 1 SEBATIK',
                    'SMA NEGERI 1 SEBATIK TENGAH',
                    'SMA NEGERI 1 SEKATAK',
                    'SMA NEGERI 1 SEMBAKUNG',
                    'SMA NEGERI 1 TANJUNG PALAS',
                    'SMA NEGERI 1 TANJUNG PALAS BARAT',
                    'SMA NEGERI 1 TANJUNG PALAS TENGAH',
                    'SMA NEGERI 1 TANJUNG PALAS TIMUR',
                    'SMA NEGERI 1 TANJUNG PALAS UTARA',
                    'SMA NEGERI 1 TANJUNG SELOR',
                    'SMA NEGERI 1 TANA TIDUNG',
                    'SMA NEGERI 2 MALINAU',
                    'SMA NEGERI 2 NUNUKAN',
                    'SMA NEGERI 2 NUNUKAN SELATAN',
                    'SMA NEGERI 2 TANJUNG SELOR',
                    'SMA NEGERI 2 TANA TIDUNG',
                    'SMA NEGERI 2 TARAKAN',
                    'SMA NEGERI 3 MALINAU',
                    'SMA NEGERI 3 TARAKAN',
                    'SMA NEGERI 4 MALINAU',
                    'SMA NEGERI 4 TARAKAN',
                    'SMA NEGERI 5 MALINAU',
                    'SMA NEGERI 6 MALINAU',
                    'SMA NEGERI 7 MALINAU',
                    'SMA NEGERI 8 MALINAU',
                    'SMA NEGERI 9 MALINAU',
                    'SMA NEGERI TERPADU UNGGULAN 1 TANA TIDUNG',

                    // SMK (urut abjad)
                    'SMK NEGERI 1 BUNYU',
                    'SMK NEGERI 1 KRAYAN',
                    'SMK NEGERI 1 MALINAU',
                    'SMK NEGERI 1 NUNUKAN',
                    'SMK NEGERI 1 SEBATIK BARAT',
                    'SMK NEGERI 1 SEI MENGGARIS',
                    'SMK NEGERI 1 SEMBAKUNG ATULAI',
                    'SMK NEGERI 1 TANJUNG PALAS',
                    'SMK NEGERI 1 TANJUNG PALAS TIMUR',
                    'SMK NEGERI 1 TANJUNG PALAS UTARA',
                    'SMK NEGERI 1 TANJUNG SELOR',
                    'SMK NEGERI 1 TANA TIDUNG',
                    'SMK NEGERI 1 TARAKAN',
                    'SMK NEGERI 1 TULIN ONSOI',
                    'SMK NEGERI 2 MALINAU',
                    'SMK NEGERI 2 TANJUNG SELOR',
                    'SMK NEGERI 2 TARAKAN',
                    'SMK NEGERI 3 TANJUNG SELOR',
                    'SMK NEGERI 3 TARAKAN',
                    'SMK NEGERI 4 TARAKAN',
                    'SMK SPP MALINAU',

                    // SLB (urut abjad)
                    'SLB NEGERI MALINAU',
                    'SLB NEGERI NUNUKAN',
                    'SLB NEGERI TANJUNG SELOR',
                    'SLB NEGERI TANA TIDUNG',
                    'SLB NEGERI TARAKAN',
                ];
            @endphp

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $item->nip) }}"
                        class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Instansi</label>
                    <select name="instansi" required class="w-full border rounded p-2">
                        <option value="" disabled {{ old('instansi') ? '' : 'selected' }}>-- Pilih Instansi --
                        </option>
                        @foreach ($instansiList as $opt)
                            <option value="{{ $opt }}" {{ old('instansi') === $opt ? 'selected' : '' }}>
                                {{ $opt }}
                            </option>
                        @endforeach
                    </select>
                    @error('instansi')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Username (@kaltaraprov.go.id)</label>
                    <input type="text" name="username" value="{{ old('username', $item->username) }}" required
                        class="w-full border rounded p-2">
                    @error('username')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Email Alternatif</label>
                    <input type="email" name="email_alternatif"
                        value="{{ old('email_alternatif', $item->email_alternatif) }}" class="w-full border rounded p-2">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Nomor Kontak (HP)</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $item->no_hp) }}" required
                        class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Password - Minimal 8 karakter serta kombinasi huruf, angka dan
                        simbol</label>
                    <input type="text" name="password" value="" placeholder="Kosongkan jika tidak diubah"
                        class="w-full border rounded p-2">
                    @error('password')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="consent_true" value="1"
                    {{ old('consent_true', $item->consent_true) ? 'checked' : '' }} required>
                <span class="text-sm">Saya menyampaikan data sebenar-benarnya dan menyetujui <a
                        href="{{ url('/syarat-email') }}" target="_blank" class="text-green-700 underline">persyaratan
                        layanan</a>.</span>
            </div>

            <div class="pt-4 flex gap-3">
                <a href="{{ route('user.email.index') }}" class="px-4 py-2 rounded border">Batal</a>
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
