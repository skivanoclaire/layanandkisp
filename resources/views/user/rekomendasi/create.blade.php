@extends('layouts.user')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6">Formulir Rekomendasi Aplikasi</h1>

        <form action="{{ route('user.rekomendasi.aplikasi.store') }}" method="POST">
            @csrf

            {{-- Dokumen Analisis Kebutuhan --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Judul Aplikasi</label>
                    <input type="text" name="judul_aplikasi" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Target Waktu</label>
                    <input type="text" name="target_waktu" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Sasaran Pengguna</label>
                    <input type="text" name="sasaran_pengguna" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Lokasi Implementasi</label>
                    <input type="text" name="lokasi_implementasi" class="w-full border rounded p-2" required>
                </div>
            </div>

            <div class="mt-4">
                <label class="block font-semibold mb-1">Dasar Hukum</label>
                <textarea name="dasar_hukum" class="w-full border rounded p-2" required></textarea>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Permasalahan Kebutuhan</label>
                <textarea name="permasalahan_kebutuhan" class="w-full border rounded p-2"></textarea>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Pihak Terkait</label>
                <textarea name="pihak_terkait" class="w-full border rounded p-2"></textarea>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Maksud & Tujuan</label>
                <textarea name="maksud_tujuan" class="w-full border rounded p-2" required></textarea>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Ruang Lingkup</label>
                <textarea name="ruang_lingkup" class="w-full border rounded p-2" required></textarea>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Analisis Biaya Manfaat</label>
                <textarea name="analisis_biaya_manfaat" class="w-full border rounded p-2"></textarea>
            </div>
            <div class="mt-4">
                <label class="block font-semibold mb-1">Analisis Risiko</label>
                <textarea name="analisis_risiko" class="w-full border rounded p-2"></textarea>
            </div>

            {{-- Dokumen Perencanaan --}}
            <hr class="my-6">
            <h2 class="text-lg font-bold mb-2">Dokumen Perencanaan</h2>

            @php
                $fields = [
                    'perencanaan_ruang_lingkup' => 'Perencanaan Ruang Lingkup',
                    'perencanaan_proses_bisnis' => 'Perencanaan Proses Bisnis',
                    'kerangka_kerja' => 'Kerangka Kerja',
                    'pelaksana_pembangunan' => 'Pelaksana Pembangunan',
                    'peran_tanggung_jawab' => 'Peran & Tanggung Jawab',
                    'jadwal_pelaksanaan' => 'Jadwal Pelaksanaan',
                    'rencana_aksi' => 'Rencana Aksi',
                    'keamanan_informasi' => 'Keamanan Informasi',
                    'sumber_daya' => 'Sumber Daya',
                    'indikator_keberhasilan' => 'Indikator Keberhasilan',
                    'alih_pengetahuan' => 'Alih Pengetahuan',
                    'pemantauan_pelaporan' => 'Pemantauan & Pelaporan',
                ];
            @endphp

            @foreach ($fields as $field => $label)
                <div class="mt-4">
                    <label class="block font-semibold mb-1">{{ $label }}</label>
                    <textarea name="{{ $field }}" class="w-full border rounded p-2"></textarea>
                </div>
            @endforeach

            {{-- Manajemen Risiko --}}
            <hr class="my-6">
            <h2 class="text-lg font-bold mb-2">Manajemen Risiko</h2>
            <div id="risiko-wrapper" class="space-y-4"></div>

            <button type="button" onclick="addRisiko()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded">
                + Tambah Risiko
            </button>

            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">
                    Simpan Permohonan
                </button>
            </div>
        </form>
    </div>

    <script>
        let risikoIndex = 0;

        function addRisiko() {
            const wrapper = document.getElementById('risiko-wrapper');
            const html = `
            <div class="risiko-item border p-4 rounded bg-gray-50">
                <label class="block font-semibold">Jenis Risiko</label>
                <input type="text" name="risiko[${risikoIndex}][jenis_risiko]" class="w-full border rounded p-2" placeholder="Contoh: Risiko Teknis - Performance Issue" required>
                <p class="text-xs text-gray-500 mt-1">Kategori dan deskripsi singkat risiko</p>

                <label class="block font-semibold mt-2">Uraian Risiko</label>
                <textarea name="risiko[${risikoIndex}][uraian_risiko]" class="w-full border rounded p-2" placeholder="Contoh: Sistem mengalami response time lambat >5 detik saat concurrent user >100"></textarea>
                <p class="text-xs text-gray-500 mt-1">Deskripsi detail skenario risiko yang mungkin terjadi</p>

                <label class="block font-semibold mt-2">Penyebab</label>
                <input type="text" name="risiko[${risikoIndex}][penyebab]" class="w-full border rounded p-2" placeholder="Contoh: Database query tidak optimal, server capacity terbatas">
                <p class="text-xs text-gray-500 mt-1">Root cause yang memicu risiko</p>

                <label class="block font-semibold mt-2">Dampak</label>
                <input type="text" name="risiko[${risikoIndex}][dampak]" class="w-full border rounded p-2" placeholder="Contoh: Produktivitas user turun, customer complaint meningkat"> 
                <p class="text-xs text-gray-500 mt-1">Konsekuensi jika risiko terjadi</p>

                <label class="block font-semibold mt-2">Level Kemungkinan</label>
                <select name="risiko[${risikoIndex}][level_kemungkinan]" id="level_kemungkinan_${risikoIndex}" class="w-full border rounded p-2" onchange="hitungBesaranRisiko(${risikoIndex})">
                    <option value="">-- Pilih Kemungkinan --</option>
                    <option value="1">1 - Hampir Tidak Terjadi</option>
                    <option value="2">2 - Jarang Terjadi</option>
                    <option value="3">3 - Kadang-Kadang Terjadi</option>
                    <option value="4">4 - Sering Terjadi</option>
                    <option value="5">5 - Hampir Pasti Terjadi</option>
                </select>

                <label class="block font-semibold mt-2">Level Dampak</label>
                <select name="risiko[${risikoIndex}][level_dampak]" id="level_dampak_${risikoIndex}" class="w-full border rounded p-2" onchange="hitungBesaranRisiko(${risikoIndex})">
                    <option value="">-- Pilih Dampak --</option>
                    <option value="1">1 - Tidak Signifikan</option>
                    <option value="2">2 - Kurang Signifikan</option>
                    <option value="3">3 - Cukup Signifikan</option>
                    <option value="4">4 - Signifikan</option>
                    <option value="5">5 - Sangat Signifikan</option>
                </select>

                <label class="block font-semibold mt-2">Besaran Risiko</label>
                <input type="text" name="risiko[${risikoIndex}][besaran_risiko]" id="besaran_risiko_${risikoIndex}" class="w-full border rounded p-2 bg-gray-100" readonly>
                <p class="text-xs text-gray-500 mt-1">Otomatis dihitung: Kemungkinan × Dampak</p>

                <label class="block font-semibold mt-2">Perlu Penanganan?</label>
                <select name="risiko[${risikoIndex}][perlu_penanganan]" class="w-full border rounded p-2">
                    <option value="1" selected>Ya</option>
                    <option value="0">Tidak</option>
                </select>

                <label class="block font-semibold mt-2">Opsi Penanganan</label>
                <textarea name="risiko[${risikoIndex}][opsi_penanganan]" class="w-full border rounded p-2"></textarea>
                 <p class="text-xs text-gray-500 mt-1">Opsi Penanganan: Hindari, Mitigasi, Transfer, Diterima</p>

                <label class="block font-semibold mt-2">Rencana Aksi</label>
                <textarea name="risiko[${risikoIndex}][rencana_aksi]" class="w-full border rounded p-2"></textarea>
                <p class="text-xs text-gray-500 mt-1">Timeline spesifik</p>

                <label class="block font-semibold mt-2">Jadwal Implementasi</label>
                <input type="text" name="risiko[${risikoIndex}][jadwal_implementasi]" class="w-full border rounded p-2">
                <p class="text-xs text-gray-500 mt-1">Kapan tindakan mitigasi dilakukan</p>

                <label class="block font-semibold mt-2">Penanggung Jawab</label>
                <input type="text" name="risiko[${risikoIndex}][penanggung_jawab]" class="w-full border rounded p-2">
                 <p class="text-xs text-gray-500 mt-1">Role atau nama PIC untuk mitigasi risiko</p>

                <label class="block font-semibold mt-2">Risiko Residual?</label>
                <select name="risiko[${risikoIndex}][risiko_residual]" class="w-full border rounded p-2">
                    <option value="0" selected>Tidak</option>
                    <option value="1">Ya</option>
                </select>

                <button type="button" onclick="removeRisiko(this)" class="mt-3 px-3 py-1 bg-red-500 text-white rounded text-sm">
                    Hapus Risiko
                </button>
            </div>`;

            wrapper.insertAdjacentHTML('beforeend', html);
            risikoIndex++;
        }

        function removeRisiko(button) {
            button.closest('.risiko-item').remove();
        }

        function hitungBesaranRisiko(index) {
            const kemungkinanEl = document.getElementById(`level_kemungkinan_${index}`);
            const dampakEl = document.getElementById(`level_dampak_${index}`);
            const outputEl = document.getElementById(`besaran_risiko_${index}`);

            if (kemungkinanEl && dampakEl && outputEl) {
                const kemungkinan = kemungkinanEl.value;
                const dampak = dampakEl.value;

                if (kemungkinan && dampak) {
                    const nilai = parseInt(kemungkinan) * parseInt(dampak);
                    outputEl.value = nilai;

                    // Optional: Add risk level indication
                    let riskLevel = '';
                    if (nilai >= 1 && nilai <= 4) {
                        riskLevel = ' (Risiko Rendah)';
                    } else if (nilai >= 5 && nilai <= 12) {
                        riskLevel = ' (Risiko Sedang)';
                    } else if (nilai >= 13 && nilai <= 25) {
                        riskLevel = ' (Risiko Tinggi)';
                    }
                    outputEl.value = nilai + riskLevel;
                } else {
                    outputEl.value = '';
                }
            }
        }

        // Add initial risk form when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Uncomment the line below if you want to add one risk form by default
            // addRisiko();
        });
    </script>
@endsection
