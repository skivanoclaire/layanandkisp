@extends('layouts.authenticated')

@section('title', '- Edit Rekomendasi Aplikasi')
@section('header-title', 'Edit Rekomendasi Aplikasi')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Edit Usulan Rekomendasi Aplikasi</h1>

        {{-- Revision Alert Banner --}}
        @if ($form->status == 'perlu_revisi' && $form->revision_notes)
            <div class="mb-6 p-4 bg-orange-100 border-l-4 border-orange-500 rounded shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-semibold text-orange-800">Revisi Diperlukan</h3>
                        <p class="mt-1 text-sm text-orange-700">Silakan perbaiki usulan Anda sesuai catatan berikut,
                            kemudian submit ulang.</p>
                        <div class="mt-2 p-3 bg-white border border-orange-300 rounded">
                            <p class="text-sm font-semibold text-gray-700">Catatan dari Admin:</p>
                            <p class="mt-1 text-gray-800">{{ $form->revision_notes }}</p>
                        </div>
                        <p class="mt-3 text-sm text-orange-600 font-medium">⚠ Setelah Anda submit, status akan berubah
                            menjadi "Diajukan" dan akan ditinjau kembali oleh Admin.</p>
                    </div>
                </div>
            </div>
        @endif

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
                <label class="font-semibold">Dasar Hukum <span class="text-red-500">*</span></label>
                <textarea name="dasar_hukum" id="editor_dasar_hukum" class="w-full border rounded px-3 py-2 editor-field" rows="3">{{ old('dasar_hukum', $form->dasar_hukum) }}</textarea>
                <div id="wordcount_dasar_hukum" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Permasalahan Kebutuhan <span class="text-red-500">*</span></label>
                <textarea name="permasalahan_kebutuhan" id="editor_permasalahan_kebutuhan" class="w-full border rounded px-3 py-2 editor-field">{{ old('permasalahan_kebutuhan', $form->permasalahan_kebutuhan) }}</textarea>
                <div id="wordcount_permasalahan_kebutuhan" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
            </div>

            {{-- Pihak Terkait - Structured Fields --}}
            <div class="mb-4">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Pihak Terkait</h3>

                {{-- Pemilik Utama Proses Bisnis --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Pemilik Utama Proses Bisnis / Instansi Utama Pengusul</label>
                    <select name="pemilik_proses_bisnis_id" class="w-full border rounded px-3 py-2">
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($unitKerjas as $unit)
                            <option value="{{ $unit->id }}" {{ old('pemilik_proses_bisnis_id', $form->pemilik_proses_bisnis_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->nama }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Unit kerja/instansi yang menjadi pemilik utama dari aplikasi ini</p>
                </div>

                {{-- Stakeholder Internal Lainnya --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Stakeholder Internal Lainnya</label>
                    <select name="stakeholder_internal[]" class="w-full border rounded px-3 py-2" multiple size="5">
                        @foreach($unitKerjas as $unit)
                            <option value="{{ $unit->id }}"
                                {{ is_array(old('stakeholder_internal', $form->stakeholder_internal)) && in_array($unit->id, old('stakeholder_internal', $form->stakeholder_internal ?? [])) ? 'selected' : '' }}>
                                {{ $unit->nama }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Unit kerja internal lainnya yang terlibat. Tekan <kbd class="px-1 py-0.5 bg-gray-200 rounded">Ctrl</kbd> (Windows) atau <kbd class="px-1 py-0.5 bg-gray-200 rounded">Cmd</kbd> (Mac) untuk memilih lebih dari satu</p>
                </div>

                {{-- Stakeholder Eksternal --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Stakeholder Eksternal <span class="text-red-500">*</span></label>
                    <textarea name="stakeholder_eksternal" id="editor_stakeholder_eksternal" class="w-full border rounded px-3 py-2 editor-field" rows="3" placeholder="Contoh: Masyarakat umum, Vendor IT (PT ABC), Kementerian XYZ">{{ old('stakeholder_eksternal', $form->stakeholder_eksternal) }}</textarea>
                    <div id="wordcount_stakeholder_eksternal" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                    <p class="text-xs text-gray-500 mt-1 italic">💡 Pihak eksternal yang terlibat (masyarakat, vendor, instansi lain di luar organisasi). Tulis manual dengan pemisah koma</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Maksud & Tujuan <span class="text-red-500">*</span></label>
                <textarea name="maksud_tujuan" id="editor_maksud_tujuan" class="w-full border rounded px-3 py-2 editor-field">{{ old('maksud_tujuan', $form->maksud_tujuan) }}</textarea>
                <div id="wordcount_maksud_tujuan" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Ruang Lingkup <span class="text-red-500">*</span></label>
                <textarea name="ruang_lingkup" id="editor_ruang_lingkup" class="w-full border rounded px-3 py-2 editor-field">{{ old('ruang_lingkup', $form->ruang_lingkup) }}</textarea>
                <div id="wordcount_ruang_lingkup" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Analisis Biaya Manfaat <span class="text-red-500">*</span></label>
                <textarea name="analisis_biaya_manfaat" id="editor_analisis_biaya_manfaat" class="w-full border rounded px-3 py-2 editor-field">{{ old('analisis_biaya_manfaat', $form->analisis_biaya_manfaat) }}</textarea>
                <div id="wordcount_analisis_biaya_manfaat" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
            </div>

            <div class="mb-4">
                <label class="font-semibold">Analisis Risiko <span class="text-red-500">*</span></label>
                <textarea name="analisis_risiko" id="editor_analisis_risiko" class="w-full border rounded px-3 py-2 editor-field">{{ old('analisis_risiko', $form->analisis_risiko) }}</textarea>
                <div id="wordcount_analisis_risiko" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
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
                <div class="mb-4">
                    <label class="font-semibold">{{ $label }} <span class="text-red-500">*</span></label>
                    <textarea name="{{ $field }}" id="editor_{{ $field }}" class="w-full border rounded px-3 py-2 editor-field">{{ old($field, $form->$field) }}</textarea>
                    <div id="wordcount_{{ $field }}" class="text-xs text-gray-600 mt-1">Jumlah kata: 0 / 2000</div>
                </div>
            @endforeach

            <!-- Manajemen Risiko -->
            <hr class="my-8">
            <h2 class="text-lg font-bold mb-4">Edit Manajemen Risiko</h2>

            {{-- Petunjuk Pengisian Manajemen Risiko --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">📋 Petunjuk Pengisian Manajemen Risiko</h3>
                        <div class="text-sm text-blue-700 space-y-2">
                            <p>Sebelum mengisi form manajemen risiko, silakan pelajari pedoman berikut:</p>
                            <div class="bg-white p-3 rounded border border-blue-200 space-y-3">
                                <div>
                                    <p class="font-semibold mb-2">📖 Pedoman Manajemen Risiko Provinsi Kalimantan Utara</p>
                                    <a href="https://drive.google.com/file/d/1xQXin3YnIOiQybDU8O90MobXx6pb-uEf/view?usp=sharing"
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Buka Pedoman Manajemen Risiko
                                    </a>
                                </div>
                                <div class="border-t border-blue-100 pt-3">
                                    <p class="font-semibold mb-2">🌐 Microsite Manajemen Risiko SPBE Kaltara</p>
                                    <a href="https://s.id/manrisk-spbe-kaltara"
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                        Kunjungi Microsite Manajemen Risiko
                                    </a>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="font-semibold mb-1">💡 Catatan Penting:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Silakan merujuk pada pedoman di atas untuk memahami kategori dan jenis risiko</li>
                                    <li>Anda dapat mengidentifikasi risiko yang sudah tercantum dalam pedoman</li>
                                    <li><strong>Anda juga dapat menambahkan risiko baru</strong> yang belum ada dalam pedoman jika relevan dengan aplikasi Anda</li>
                                    <li>Pastikan setiap risiko memiliki penilaian level kemungkinan dan dampak yang akurat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                            <label class="font-semibold">Jenis Risiko SPBE</label>
                            <select name="risiko[{{ $index }}][jenis_risiko]" class="w-full border rounded px-3 py-2" required>
                                <option value="">-- Pilih Jenis Risiko SPBE --</option>
                                <option value="Risiko SPBE Positif" {{ old("risiko.$index.jenis_risiko", $item->jenis_risiko) == 'Risiko SPBE Positif' ? 'selected' : '' }}>Risiko SPBE Positif</option>
                                <option value="Risiko SPBE Negatif" {{ old("risiko.$index.jenis_risiko", $item->jenis_risiko) == 'Risiko SPBE Negatif' ? 'selected' : '' }}>Risiko SPBE Negatif</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Risiko SPBE Positif: peluang yang menguntungkan | Risiko SPBE Negatif: ancaman/kerugian</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-semibold">Kategori Risiko SPBE</label>
                            <select name="risiko[{{ $index }}][kategori_risiko_spbe]" class="w-full border rounded px-3 py-2">
                                <option value="">-- Pilih Kategori Risiko SPBE --</option>
                                <option value="Rencana Induk SPBE Nasional" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Rencana Induk SPBE Nasional' ? 'selected' : '' }}>1. Rencana Induk SPBE Nasional</option>
                                <option value="Arsitektur SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Arsitektur SPBE' ? 'selected' : '' }}>2. Arsitektur SPBE</option>
                                <option value="Peta Rencana SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Peta Rencana SPBE' ? 'selected' : '' }}>3. Peta Rencana SPBE</option>
                                <option value="Proses Bisnis" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Proses Bisnis' ? 'selected' : '' }}>4. Proses Bisnis</option>
                                <option value="Rencana dan Anggaran" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Rencana dan Anggaran' ? 'selected' : '' }}>5. Rencana dan Anggaran</option>
                                <option value="Inovasi" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Inovasi' ? 'selected' : '' }}>6. Inovasi</option>
                                <option value="Kepatuhan terhadap Peraturan" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Kepatuhan terhadap Peraturan' ? 'selected' : '' }}>7. Kepatuhan terhadap Peraturan</option>
                                <option value="Pengadaan Barang dan Jasa" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Pengadaan Barang dan Jasa' ? 'selected' : '' }}>8. Pengadaan Barang dan Jasa</option>
                                <option value="Proyek Pembangunan/Pengembangan Sistem" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Proyek Pembangunan/Pengembangan Sistem' ? 'selected' : '' }}>9. Proyek Pembangunan/Pengembangan Sistem</option>
                                <option value="Data dan Informasi" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Data dan Informasi' ? 'selected' : '' }}>10. Data dan Informasi</option>
                                <option value="Infrastruktur SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Infrastruktur SPBE' ? 'selected' : '' }}>11. Infrastruktur SPBE</option>
                                <option value="Aplikasi SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Aplikasi SPBE' ? 'selected' : '' }}>12. Aplikasi SPBE</option>
                                <option value="Keamanan SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Keamanan SPBE' ? 'selected' : '' }}>13. Keamanan SPBE</option>
                                <option value="Layanan SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Layanan SPBE' ? 'selected' : '' }}>14. Layanan SPBE</option>
                                <option value="Sumber Daya Manusia SPBE" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Sumber Daya Manusia SPBE' ? 'selected' : '' }}>15. Sumber Daya Manusia SPBE</option>
                                <option value="Bencana Alam" {{ old("risiko.$index.kategori_risiko_spbe", $item->kategori_risiko_spbe) == 'Bencana Alam' ? 'selected' : '' }}>16. Bencana Alam</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih kategori sesuai Tabel 3.6 Pedoman Manajemen Risiko SPBE</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-semibold">Area Dampak Risiko SPBE</label>
                            <select name="risiko[{{ $index }}][area_dampak_risiko_spbe]" class="w-full border rounded px-3 py-2">
                                <option value="">-- Pilih Area Dampak --</option>
                                <option value="Finansial" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Finansial' ? 'selected' : '' }}>Finansial - Aspek keuangan</option>
                                <option value="Reputasi" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Reputasi' ? 'selected' : '' }}>Reputasi - Tingkat kepercayaan pemangku kepentingan</option>
                                <option value="Kinerja" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Kinerja' ? 'selected' : '' }}>Kinerja - Pencapaian sasaran SPBE</option>
                                <option value="Layanan Organisasi" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Layanan Organisasi' ? 'selected' : '' }}>Layanan Organisasi - Pemenuhan kebutuhan/jasa kepada pemangku kepentingan</option>
                                <option value="Operasional dan Aset TIK" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Operasional dan Aset TIK' ? 'selected' : '' }}>Operasional dan Aset TIK - Kegiatan operasional TIK dan pengelolaan aset TIK</option>
                                <option value="Hukum dan Regulasi" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Hukum dan Regulasi' ? 'selected' : '' }}>Hukum dan Regulasi - Peraturan perundang-undangan dan kebijakan</option>
                                <option value="Sumber Daya Manusia" {{ old("risiko.$index.area_dampak_risiko_spbe", $item->area_dampak_risiko_spbe) == 'Sumber Daya Manusia' ? 'selected' : '' }}>Sumber Daya Manusia - Fisik dan mental pegawai</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih area yang terkena dampak dari risiko SPBE</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-semibold">Uraian Kejadian</label>
                            <textarea name="risiko[{{ $index }}][uraian_risiko]" class="w-full border rounded px-3 py-2">{{ old("risiko.$index.uraian_risiko", $item->uraian_risiko) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Deskripsi kejadian/peristiwa yang menimbulkan risiko SPBE (dari riwayat atau prediksi masa depan)</p>
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

    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

    <script>
        // Store editor instances
        const editorInstances = {};

        // Initialize CKEditor for all editor fields with word limit
        document.addEventListener('DOMContentLoaded', function() {
            const MAX_WORDS = 2000;
            const editorFields = document.querySelectorAll('.editor-field');

            editorFields.forEach(function(textarea) {
                const editorId = textarea.id;
                const wordCountId = 'wordcount_' + editorId.replace('editor_', '');

                ClassicEditor
                    .create(textarea, {
                        toolbar: {
                            items: [
                                'heading', '|',
                                'bold', 'italic', 'underline', '|',
                                'bulletedList', 'numberedList', '|',
                                'outdent', 'indent', '|',
                                'undo', 'redo'
                            ]
                        },
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                            ]
                        }
                    })
                    .then(editor => {
                        // Store editor instance
                        editorInstances[editorId] = editor;

                        // Function to count words
                        function countWords(text) {
                            const strippedText = text.replace(/<[^>]*>/g, '').trim();
                            if (!strippedText) return 0;
                            return strippedText.split(/\s+/).length;
                        }

                        // Update word count display
                        function updateWordCount() {
                            const data = editor.getData();
                            const wordCount = countWords(data);
                            const wordCountElement = document.getElementById(wordCountId);

                            if (wordCountElement) {
                                wordCountElement.textContent = `Jumlah kata: ${wordCount} / ${MAX_WORDS}`;

                                if (wordCount > MAX_WORDS) {
                                    wordCountElement.style.color = '#dc2626';
                                    wordCountElement.textContent += ' - Maksimal 2000 kata!';
                                } else if (wordCount > MAX_WORDS * 0.9) {
                                    wordCountElement.style.color = '#f59e0b';
                                } else {
                                    wordCountElement.style.color = '#6b7280';
                                }
                            }
                        }

                        // Listen to changes
                        editor.model.document.on('change:data', () => {
                            updateWordCount();
                            const data = editor.getData();
                            const wordCount = countWords(data);
                            if (wordCount <= MAX_WORDS) {
                                editor.disableReadOnlyMode('word-limit');
                            }
                        });

                        // Initial count
                        updateWordCount();
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });
            });

            // Sync CKEditor data to textarea before form submission
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Check if at least one risk is added
                    const risikoItems = document.querySelectorAll('.risiko-item').length;
                    if (risikoItems === 0) {
                        e.preventDefault();
                        alert('Anda harus menambahkan minimal 1 risiko!');
                        return false;
                    }

                    // Update all textareas with CKEditor data
                    for (const [editorId, editor] of Object.entries(editorInstances)) {
                        const textarea = document.getElementById(editorId);
                        if (textarea && editor) {
                            textarea.value = editor.getData();
                        }
                    }

                    // Validate required CKEditor fields
                    const requiredEditorFields = [
                        { id: 'editor_dasar_hukum', name: 'Dasar Hukum' },
                        { id: 'editor_permasalahan_kebutuhan', name: 'Permasalahan Kebutuhan' },
                        { id: 'editor_stakeholder_eksternal', name: 'Stakeholder Eksternal' },
                        { id: 'editor_maksud_tujuan', name: 'Maksud & Tujuan' },
                        { id: 'editor_ruang_lingkup', name: 'Ruang Lingkup' },
                        { id: 'editor_analisis_biaya_manfaat', name: 'Analisis Biaya Manfaat' },
                        { id: 'editor_analisis_risiko', name: 'Analisis Risiko' },
                        { id: 'editor_perencanaan_ruang_lingkup', name: 'Perencanaan Ruang Lingkup' },
                        { id: 'editor_perencanaan_proses_bisnis', name: 'Perencanaan Proses Bisnis' },
                        { id: 'editor_kerangka_kerja', name: 'Kerangka Kerja' },
                        { id: 'editor_pelaksana_pembangunan', name: 'Pelaksana Pembangunan' },
                        { id: 'editor_peran_tanggung_jawab', name: 'Peran & Tanggung Jawab' },
                        { id: 'editor_jadwal_pelaksanaan', name: 'Jadwal Pelaksanaan' },
                        { id: 'editor_rencana_aksi', name: 'Rencana Aksi' },
                        { id: 'editor_keamanan_informasi', name: 'Keamanan Informasi' },
                        { id: 'editor_sumber_daya', name: 'Sumber Daya' },
                        { id: 'editor_indikator_keberhasilan', name: 'Indikator Keberhasilan' },
                        { id: 'editor_alih_pengetahuan', name: 'Alih Pengetahuan' },
                        { id: 'editor_pemantauan_pelaporan', name: 'Pemantauan & Pelaporan' }
                    ];

                    for (const field of requiredEditorFields) {
                        const editor = editorInstances[field.id];
                        if (editor) {
                            const content = editor.getData().replace(/<[^>]*>/g, '').trim();
                            if (!content) {
                                e.preventDefault();
                                alert(`Field "${field.name}" wajib diisi!`);
                                editor.focus();
                                return false;
                            }
                        }
                    }

                    return true;
                });
            }
        });
    </script>
@endsection
