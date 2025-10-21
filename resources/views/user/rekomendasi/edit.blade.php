@extends('layouts.user')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Edit Usulan Rekomendasi Aplikasi</h1>

        <form action="{{ route('user.rekomendasi.aplikasi.update', $form->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Dokumen Analisis Kebutuhan -->
            <div class="mb-4">
                <label class="font-semibold">Judul Aplikasi</label>
                <input type="text" name="judul_aplikasi" class="w-full border rounded px-3 py-2"
                    value="{{ old('judul_aplikasi', $form->judul_aplikasi) }}">
            </div>

            <div class="mb-4">
                <label class="font-semibold">Dasar Hukum</label>
                <textarea name="dasar_hukum" class="w-full border rounded px-3 py-2" rows="3">{{ old('dasar_hukum', $form->dasar_hukum) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Permasalahan Kebutuhan</label>
                <textarea name="permasalahan_kebutuhan" class="w-full border rounded px-3 py-2">{{ old('permasalahan_kebutuhan', $form->permasalahan_kebutuhan) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Pihak Terkait</label>
                <textarea name="pihak_terkait" class="w-full border rounded px-3 py-2">{{ old('pihak_terkait', $form->pihak_terkait) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Maksud & Tujuan</label>
                <textarea name="maksud_tujuan" class="w-full border rounded px-3 py-2">{{ old('maksud_tujuan', $form->maksud_tujuan) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Ruang Lingkup</label>
                <textarea name="ruang_lingkup" class="w-full border rounded px-3 py-2">{{ old('ruang_lingkup', $form->ruang_lingkup) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Analisis Biaya Manfaat</label>
                <textarea name="analisis_biaya_manfaat" class="w-full border rounded px-3 py-2">{{ old('analisis_biaya_manfaat', $form->analisis_biaya_manfaat) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Analisis Risiko</label>
                <textarea name="analisis_risiko" class="w-full border rounded px-3 py-2">{{ old('analisis_risiko', $form->analisis_risiko) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="font-semibold">Target Waktu</label>
                    <input type="text" name="target_waktu" class="w-full border rounded px-3 py-2"
                        value="{{ old('target_waktu', $form->target_waktu) }}">
                </div>
                <div>
                    <label class="font-semibold">Sasaran Pengguna</label>
                    <input type="text" name="sasaran_pengguna" class="w-full border rounded px-3 py-2"
                        value="{{ old('sasaran_pengguna', $form->sasaran_pengguna) }}">
                </div>
                <div>
                    <label class="font-semibold">Lokasi Implementasi</label>
                    <input type="text" name="lokasi_implementasi" class="w-full border rounded px-3 py-2"
                        value="{{ old('lokasi_implementasi', $form->lokasi_implementasi) }}">
                </div>
            </div>

            <!-- Dokumen Perencanaan -->
            <hr class="my-6">
            <h2 class="text-lg font-bold mb-2">Dokumen Perencanaan</h2>

            @php
                $fields = [
                    'perencanaan_ruang_lingkup' => 'Ruang Lingkup',
                    'perencanaan_proses_bisnis' => 'Proses Bisnis',
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
                <div class="mb-4">
                    <label class="font-semibold">{{ $label }}</label>
                    @if (Str::startsWith($field, ['kerangka_kerja', 'pelaksana_pembangunan', 'jadwal_pelaksanaan']))
                        <input type="text" name="{{ $field }}" class="w-full border rounded px-3 py-2"
                            value="{{ old($field, $form->$field) }}">
                    @else
                        <textarea name="{{ $field }}" class="w-full border rounded px-3 py-2">{{ old($field, $form->$field) }}</textarea>
                    @endif
                </div>
            @endforeach

            <!-- Manajemen Risiko -->
            <hr class="my-8">
            <h2 class="text-lg font-bold mb-4">Edit Manajemen Risiko</h2>

            <div id="risiko-wrapper" class="space-y-6">
                @foreach ($form->risikoItems as $index => $item)
                    <div class="risiko-item border p-4 rounded mb-6 bg-gray-50" data-original-id="{{ $item->id }}">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Risiko #<span class="risiko-number">{{ $index + 1 }}</span></h3>
                            <button type="button" onclick="removeRisiko(this)"
                                class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                                Hapus Risiko
                            </button>
                        </div>

                        <input type="hidden" name="risiko[{{ $index }}][id]" value="{{ $item->id }}">

                        <div class="mb-3">
                            <label class="font-semibold">Jenis Risiko</label>
                            <input type="text" name="risiko[{{ $index }}][jenis_risiko]"
                                class="w-full border rounded px-3 py-2"
                                value="{{ old("risiko.$index.jenis_risiko", $item->jenis_risiko) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="font-semibold">Uraian Risiko</label>
                            <textarea name="risiko[{{ $index }}][uraian_risiko]" class="w-full border rounded px-3 py-2">{{ old("risiko.$index.uraian_risiko", $item->uraian_risiko) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="font-semibold">Penyebab</label>
                                <input type="text" name="risiko[{{ $index }}][penyebab]"
                                    class="w-full border rounded px-3 py-2"
                                    value="{{ old("risiko.$index.penyebab", $item->penyebab) }}">
                            </div>
                            <div>
                                <label class="font-semibold">Dampak</label>
                                <input type="text" name="risiko[{{ $index }}][dampak]"
                                    class="w-full border rounded px-3 py-2"
                                    value="{{ old("risiko.$index.dampak", $item->dampak) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="font-semibold">Level Kemungkinan</label>
                                <select name="risiko[{{ $index }}][level_kemungkinan]"
                                    id="level_kemungkinan_{{ $index }}" class="w-full border rounded px-3 py-2"
                                    onchange="hitungBesaranRisiko({{ $index }})">
                                    <option value="">-- Pilih Kemungkinan --</option>
                                    <option value="1"
                                        {{ old("risiko.$index.level_kemungkinan", $item->level_kemungkinan) == '1' ? 'selected' : '' }}>
                                        1 - Hampir Tidak Terjadi</option>
                                    <option value="2"
                                        {{ old("risiko.$index.level_kemungkinan", $item->level_kemungkinan) == '2' ? 'selected' : '' }}>
                                        2 - Jarang Terjadi</option>
                                    <option value="3"
                                        {{ old("risiko.$index.level_kemungkinan", $item->level_kemungkinan) == '3' ? 'selected' : '' }}>
                                        3 - Kadang-Kadang Terjadi</option>
                                    <option value="4"
                                        {{ old("risiko.$index.level_kemungkinan", $item->level_kemungkinan) == '4' ? 'selected' : '' }}>
                                        4 - Sering Terjadi</option>
                                    <option value="5"
                                        {{ old("risiko.$index.level_kemungkinan", $item->level_kemungkinan) == '5' ? 'selected' : '' }}>
                                        5 - Hampir Pasti Terjadi</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-semibold">Level Dampak</label>
                                <select name="risiko[{{ $index }}][level_dampak]"
                                    id="level_dampak_{{ $index }}" class="w-full border rounded px-3 py-2"
                                    onchange="hitungBesaranRisiko({{ $index }})">
                                    <option value="">-- Pilih Dampak --</option>
                                    <option value="1"
                                        {{ old("risiko.$index.level_dampak", $item->level_dampak) == '1' ? 'selected' : '' }}>
                                        1 - Tidak Signifikan</option>
                                    <option value="2"
                                        {{ old("risiko.$index.level_dampak", $item->level_dampak) == '2' ? 'selected' : '' }}>
                                        2 - Kurang Signifikan</option>
                                    <option value="3"
                                        {{ old("risiko.$index.level_dampak", $item->level_dampak) == '3' ? 'selected' : '' }}>
                                        3 - Cukup Signifikan</option>
                                    <option value="4"
                                        {{ old("risiko.$index.level_dampak", $item->level_dampak) == '4' ? 'selected' : '' }}>
                                        4 - Signifikan</option>
                                    <option value="5"
                                        {{ old("risiko.$index.level_dampak", $item->level_dampak) == '5' ? 'selected' : '' }}>
                                        5 - Sangat Signifikan</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-semibold">Besaran Risiko</label>
                                <input type="text" name="risiko[{{ $index }}][besaran_risiko]"
                                    id="besaran_risiko_{{ $index }}"
                                    class="w-full border rounded px-3 py-2 bg-gray-100"
                                    value="{{ old("risiko.$index.besaran_risiko", $item->besaran_risiko) }}" readonly>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="font-semibold">Perlu Penanganan</label>
                                <select name="risiko[{{ $index }}][perlu_penanganan]"
                                    class="w-full border rounded px-3 py-2">
                                    <option value="1"
                                        {{ old("risiko.$index.perlu_penanganan", $item->perlu_penanganan) ? 'selected' : '' }}>
                                        Ya</option>
                                    <option value="0"
                                        {{ !old("risiko.$index.perlu_penanganan", $item->perlu_penanganan) ? 'selected' : '' }}>
                                        Tidak</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-semibold">Risiko Residual</label>
                                <select name="risiko[{{ $index }}][risiko_residual]"
                                    class="w-full border rounded px-3 py-2">
                                    <option value="0"
                                        {{ !old("risiko.$index.risiko_residual", $item->risiko_residual) ? 'selected' : '' }}>
                                        Tidak</option>
                                    <option value="1"
                                        {{ old("risiko.$index.risiko_residual", $item->risiko_residual) ? 'selected' : '' }}>
                                        Ya</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="font-semibold">Opsi Penanganan</label>
                                <textarea name="risiko[{{ $index }}][opsi_penanganan]" class="w-full border rounded px-3 py-2">{{ old("risiko.$index.opsi_penanganan", $item->opsi_penanganan) }}</textarea>
                            </div>
                            <div>
                                <label class="font-semibold">Rencana Aksi</label>
                                <textarea name="risiko[{{ $index }}][rencana_aksi]" class="w-full border rounded px-3 py-2">{{ old("risiko.$index.rencana_aksi", $item->rencana_aksi) }}</textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="font-semibold">Jadwal Implementasi</label>
                                <input type="text" name="risiko[{{ $index }}][jadwal_implementasi]"
                                    class="w-full border rounded px-3 py-2"
                                    value="{{ old("risiko.$index.jadwal_implementasi", $item->jadwal_implementasi) }}">
                            </div>
                            <div>
                                <label class="font-semibold">Penanggung Jawab</label>
                                <input type="text" name="risiko[{{ $index }}][penanggung_jawab]"
                                    class="w-full border rounded px-3 py-2"
                                    value="{{ old("risiko.$index.penanggung_jawab", $item->penanggung_jawab) }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tombol Tambah Risiko Baru -->
            <button type="button" onclick="addNewRisiko()"
                class="mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                + Tambah Risiko Baru
            </button>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('user.rekomendasi.aplikasi.index') }}"
                    class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        let risikoIndex = {{ count($form->risikoItems) }};

        function addNewRisiko() {
            const wrapper = document.getElementById('risiko-wrapper');
            const html = `
            <div class="risiko-item border p-4 rounded mb-6 bg-gray-50" data-original-id="new">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold">Risiko #<span class="risiko-number">${risikoIndex + 1}</span></h3>
                    <button type="button" onclick="removeRisiko(this)" class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                        Hapus Risiko
                    </button>
                </div>

                <input type="hidden" name="risiko[${risikoIndex}][id]" value="">

                <div class="mb-3">
                    <label class="font-semibold">Jenis Risiko</label>
                    <input type="text" name="risiko[${risikoIndex}][jenis_risiko]" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-3">
                    <label class="font-semibold">Uraian Risiko</label>
                    <textarea name="risiko[${risikoIndex}][uraian_risiko]" class="w-full border rounded px-3 py-2"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold">Penyebab</label>
                        <input type="text" name="risiko[${risikoIndex}][penyebab]" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Dampak</label>
                        <input type="text" name="risiko[${risikoIndex}][dampak]" class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="font-semibold">Level Kemungkinan</label>
                        <select name="risiko[${risikoIndex}][level_kemungkinan]" id="level_kemungkinan_${risikoIndex}" class="w-full border rounded px-3 py-2" onchange="hitungBesaranRisiko(${risikoIndex})">
                            <option value="">-- Pilih Kemungkinan --</option>
                            <option value="1">1 - Hampir Tidak Terjadi</option>
                            <option value="2">2 - Jarang Terjadi</option>
                            <option value="3">3 - Kadang-Kadang Terjadi</option>
                            <option value="4">4 - Sering Terjadi</option>
                            <option value="5">5 - Hampir Pasti Terjadi</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Level Dampak</label>
                        <select name="risiko[${risikoIndex}][level_dampak]" id="level_dampak_${risikoIndex}" class="w-full border rounded px-3 py-2" onchange="hitungBesaranRisiko(${risikoIndex})">
                            <option value="">-- Pilih Dampak --</option>
                            <option value="1">1 - Tidak Signifikan</option>
                            <option value="2">2 - Kurang Signifikan</option>
                            <option value="3">3 - Cukup Signifikan</option>
                            <option value="4">4 - Signifikan</option>
                            <option value="5">5 - Sangat Signifikan</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Besaran Risiko</label>
                        <input type="text" name="risiko[${risikoIndex}][besaran_risiko]" id="besaran_risiko_${risikoIndex}" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="font-semibold">Perlu Penanganan</label>
                        <select name="risiko[${risikoIndex}][perlu_penanganan]" class="w-full border rounded px-3 py-2">
                            <option value="1" selected>Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Risiko Residual</label>
                        <select name="risiko[${risikoIndex}][risiko_residual]" class="w-full border rounded px-3 py-2">
                            <option value="0" selected>Tidak</option>
                            <option value="1">Ya</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="font-semibold">Opsi Penanganan</label>
                        <textarea name="risiko[${risikoIndex}][opsi_penanganan]" class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="font-semibold">Rencana Aksi</label>
                        <textarea name="risiko[${risikoIndex}][rencana_aksi]" class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="font-semibold">Jadwal Implementasi</label>
                        <input type="text" name="risiko[${risikoIndex}][jadwal_implementasi]" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Penanggung Jawab</label>
                        <input type="text" name="risiko[${risikoIndex}][penanggung_jawab]" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
            </div>`;

            wrapper.insertAdjacentHTML('beforeend', html);
            risikoIndex++;
            updateRisikoNumbers();
        }

        function removeRisiko(button) {
            if (confirm('Apakah Anda yakin ingin menghapus risiko ini?')) {
                const risikoItem = button.closest('.risiko-item');
                const originalId = risikoItem.getAttribute('data-original-id');

                // Jika risiko sudah ada di database (bukan "new"), tambahkan input hidden untuk menandai penghapusan
                if (originalId && originalId !== 'new') {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'deleted_risiko[]';
                    hiddenInput.value = originalId;
                    document.querySelector('form').appendChild(hiddenInput);
                }

                risikoItem.remove();
                updateRisikoNumbers();
                reindexRisikoFields();
            }
        }

        function updateRisikoNumbers() {
            const risikoItems = document.querySelectorAll('.risiko-item');
            risikoItems.forEach((item, index) => {
                const numberSpan = item.querySelector('.risiko-number');
                if (numberSpan) {
                    numberSpan.textContent = index + 1;
                }
            });
        }

        function reindexRisikoFields() {
            const risikoItems = document.querySelectorAll('.risiko-item');
            risikoItems.forEach((item, newIndex) => {
                const inputs = item.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name && input.name.includes('risiko[')) {
                        const fieldName = input.name.replace(/risiko\[\d+\]/, `risiko[${newIndex}]`);
                        input.name = fieldName;
                    }
                    if (input.id && input.id.includes('_')) {
                        const parts = input.id.split('_');
                        if (parts.length > 1) {
                            parts[parts.length - 1] = newIndex;
                            input.id = parts.join('_');
                        }
                    }
                });

                const selects = item.querySelectorAll('select[onchange]');
                selects.forEach(select => {
                    select.setAttribute('onchange', `hitungBesaranRisiko(${newIndex})`);
                });
            });
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

                    // Add risk level indication
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

        // Initialize calculation for existing risks on page load
        document.addEventListener('DOMContentLoaded', function() {
            const existingRisks = document.querySelectorAll('.risiko-item');
            existingRisks.forEach((item, index) => {
                const kemungkinanSelect = item.querySelector(`#level_kemungkinan_${index}`);
                const dampakSelect = item.querySelector(`#level_dampak_${index}`);

                if (kemungkinanSelect && dampakSelect && kemungkinanSelect.value && dampakSelect.value) {
                    hitungBesaranRisiko(index);
                }
            });
        });
    </script>
@endsection
