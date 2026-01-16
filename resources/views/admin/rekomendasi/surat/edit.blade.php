@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Surat Rekomendasi</h1>
                <p class="text-gray-600 mt-1">{{ $surat->proposal->nama_aplikasi }}</p>
            </div>
            <a href="{{ route('admin.rekomendasi.surat.show', $surat->id) }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                Kembali
            </a>
        </div>

        <!-- Proposal Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-900 mb-2">Informasi Usulan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-blue-600">Nomor Tiket:</span>
                    <span class="text-blue-900 font-medium ml-2">{{ $surat->proposal->ticket_number }}</span>
                </div>
                <div>
                    <span class="text-blue-600">Pemohon:</span>
                    <span class="text-blue-900 font-medium ml-2">{{ $surat->proposal->user->name }}</span>
                </div>
                <div>
                    <span class="text-blue-600">Unit Kerja:</span>
                    <span class="text-blue-900 font-medium ml-2">{{ $surat->proposal->pemilikProsesBisnis?->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-blue-600">Nomor Surat:</span>
                    <span class="text-blue-900 font-medium ml-2">{{ $surat->nomor_surat_draft }}</span>
                </div>
            </div>
        </div>

        <!-- Letter Form -->
        <form method="POST" action="{{ route('admin.rekomendasi.surat.update', $surat->id) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
                <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">Informasi Surat</h2>

                <!-- Nomor Surat Draft -->
                <div>
                    <label for="nomor_surat_draft" class="block text-sm font-medium text-gray-700 mb-1">
                        Nomor Surat (Draft) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nomor_surat_draft" name="nomor_surat_draft"
                        value="{{ old('nomor_surat_draft', $surat->nomor_surat_draft) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    <p class="text-xs text-gray-500 mt-1">Nomor ini akan digunakan sebagai draft. Nomor final akan diinput saat penandatanganan.</p>
                    @error('nomor_surat_draft')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Surat -->
                <div>
                    <label for="tanggal_surat" class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Surat <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal_surat" name="tanggal_surat"
                        value="{{ old('tanggal_surat', $surat->tanggal_surat->format('Y-m-d')) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    @error('tanggal_surat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Perihal -->
                <div>
                    <label for="perihal" class="block text-sm font-medium text-gray-700 mb-1">
                        Perihal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="perihal" name="perihal"
                        value="{{ old('perihal', $surat->perihal) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    @error('perihal')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tujuan Surat -->
                <div>
                    <label for="tujuan_surat" class="block text-sm font-medium text-gray-700 mb-1">
                        Tujuan Surat <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="tujuan_surat" name="tujuan_surat"
                        value="{{ old('tujuan_surat', $surat->tujuan_surat) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    @error('tujuan_surat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Referensi Hukum -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Referensi Hukum (Opsional)
                    </label>
                    <div id="referensi-container" class="space-y-2">
                        @if(old('referensi_hukum', $surat->referensi_hukum ?? []))
                            @foreach(old('referensi_hukum', $surat->referensi_hukum ?? []) as $index => $ref)
                                <div class="referensi-item flex gap-2">
                                    <input type="text" name="referensi_hukum[]"
                                        value="{{ $ref }}"
                                        placeholder="Contoh: UU No. 11 Tahun 2008 tentang ITE"
                                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <button type="button" class="remove-referensi px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        Hapus
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="referensi-item flex gap-2">
                                <input type="text" name="referensi_hukum[]"
                                    placeholder="Contoh: UU No. 11 Tahun 2008 tentang ITE"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <button type="button" class="remove-referensi px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Hapus
                                </button>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-referensi" class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                        + Tambah Referensi
                    </button>
                </div>

                <!-- Isi Surat -->
                <div>
                    <label for="isi_surat" class="block text-sm font-medium text-gray-700 mb-1">
                        Isi Surat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="isi_surat" name="isi_surat" rows="20"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                        required>{{ old('isi_surat', $surat->isi_surat) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Tulis isi surat lengkap. Anda dapat menggunakan format paragraf biasa.</p>
                    @error('isi_surat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Suggested Content Template -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <button type="button" onclick="useTemplate()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Gunakan Template Surat
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Klik untuk mengisi isi surat dengan template yang telah disesuaikan dengan data usulan.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.rekomendasi.surat.show', $surat->id) }}"
                        class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Add referensi hukum
document.getElementById('add-referensi').addEventListener('click', function() {
    const container = document.getElementById('referensi-container');
    const newItem = `
        <div class="referensi-item flex gap-2">
            <input type="text" name="referensi_hukum[]"
                placeholder="Contoh: UU No. 11 Tahun 2008 tentang ITE"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="button" class="remove-referensi px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Hapus
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItem);
});

// Remove referensi hukum
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-referensi')) {
        if (document.querySelectorAll('.referensi-item').length > 1) {
            e.target.closest('.referensi-item').remove();
        } else {
            alert('Minimal harus ada satu field referensi');
        }
    }
});

// Use template
function useTemplate() {
    const proposal = @json($surat->proposal);

    const template = `Kepada Yth.
Menteri Komunikasi dan Informatika Republik Indonesia
Di Jakarta

Dengan hormat,

Berdasarkan hasil verifikasi dan evaluasi terhadap usulan pengembangan aplikasi yang diajukan oleh ${proposal.pemilik_proses_bisnis.nama}, dengan ini kami sampaikan rekomendasi untuk pengembangan aplikasi sebagai berikut:

1. INFORMASI APLIKASI
   Nama Aplikasi: ${proposal.nama_aplikasi}
   Jenis Layanan: ${proposal.jenis_layanan}
   Target Pengguna: ${proposal.target_pengguna}
   Estimasi Pengguna: ${proposal.estimasi_pengguna.toLocaleString('id-ID')} pengguna

2. TUJUAN DAN MANFAAT
   Tujuan:
   ${proposal.tujuan}

   Manfaat:
   ${proposal.manfaat}

3. PERENCANAAN TEKNIS
   Platform: ${proposal.platform.join(', ')}
   ${proposal.teknologi_diusulkan ? 'Teknologi: ' + proposal.teknologi_diusulkan : ''}
   Estimasi Waktu: ${proposal.estimasi_waktu_pengembangan} bulan
   Estimasi Biaya: Rp ${proposal.estimasi_biaya.toLocaleString('id-ID')}
   Sumber Pendanaan: ${proposal.sumber_pendanaan.toUpperCase()}

4. REKOMENDASI
   Berdasarkan hasil verifikasi dan kelengkapan dokumen yang telah disampaikan, kami merekomendasikan agar usulan pengembangan aplikasi ${proposal.nama_aplikasi} dapat disetujui untuk dilanjutkan ke tahap pengembangan.

   Aplikasi ini dinilai memiliki urgensi ${proposal.prioritas.replace('_', ' ')} dan akan memberikan dampak positif bagi pelayanan publik dan efisiensi administrasi pemerintahan.

5. TINDAK LANJUT
   Kami mohon arahan dan persetujuan dari Kementerian untuk dapat melanjutkan proses pengembangan aplikasi sesuai dengan ketentuan dan regulasi yang berlaku.

Demikian surat rekomendasi ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.

Hormat kami,`;

    document.getElementById('isi_surat').value = template;
}
</script>
@endpush
@endsection
