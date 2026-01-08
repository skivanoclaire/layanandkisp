@extends('layouts.authenticated')

@section('title', '- Detail Rekomendasi Aplikasi')
@section('header-title', 'Detail Rekomendasi Aplikasi')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Detail Rekomendasi Aplikasi</h1>
            <a href="{{ route('admin.rekomendasi.index') }}" class="text-blue-600 hover:underline">
                &larr; Kembali ke Daftar
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Info Pengusul --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-lg font-bold mb-3">Informasi Pengusul</h2>
            <div class="grid grid-cols-2 gap-4">
                <div><strong>Nama:</strong> {{ $form->user->name ?? '-' }}</div>
                <div><strong>NIP:</strong> {{ $form->user->nip ?? '-' }}</div>
                <div><strong>Email:</strong> {{ $form->user->email ?? '-' }}</div>
                <div><strong>Tanggal Pengajuan:</strong> {{ $form->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        {{-- Status & Actions --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <strong>Status:</strong>
                    @php
                        $statusClass = match($form->status) {
                            'diajukan' => 'bg-yellow-100 text-yellow-800',
                            'diproses' => 'bg-blue-100 text-blue-800',
                            'perlu_revisi' => 'bg-orange-100 text-orange-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusClass }}">
                        {{ strtoupper($form->status) }}
                    </span>
                </div>

                @if (in_array($form->status, ['diajukan', 'diproses']))
                    <div class="flex gap-2">
                        @if ($form->status == 'diajukan')
                            <form action="{{ route('admin.rekomendasi.change-status', $form->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="new_status" value="diproses">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Proses
                                </button>
                            </form>
                        @endif

                        <button onclick="document.getElementById('approveModal').classList.remove('hidden')"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Setujui
                        </button>

                        <button onclick="document.getElementById('revisionModal').classList.remove('hidden')"
                            class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                            Minta Revisi
                        </button>

                        <button onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Tolak
                        </button>
                    </div>
                @elseif ($form->status == 'disetujui')
                    <div class="flex gap-2">
                        <a href="{{ route('admin.rekomendasi.pdf', $form->id) }}"
                            class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                            Cetak Surat Rekomendasi
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Detail Aplikasi --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-lg font-bold mb-3">Detail Aplikasi</h2>

            <div class="mb-4"><strong>Judul Aplikasi:</strong> {{ $form->judul_aplikasi }}</div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div><strong>Target Waktu:</strong> {{ $form->target_waktu }}</div>
                <div><strong>Sasaran Pengguna:</strong> {{ $form->sasaran_pengguna }}</div>
                <div><strong>Lokasi Implementasi:</strong> {{ $form->lokasi_implementasi }}</div>
            </div>

            <div class="mb-4"><strong>Dasar Hukum:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->dasar_hukum, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Permasalahan Kebutuhan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->permasalahan_kebutuhan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

            {{-- Pihak Terkait --}}
            <div class="mb-4">
                <strong>Pemilik Utama Proses Bisnis:</strong>
                {{ $form->pemilikProsesBisnis ? $form->pemilikProsesBisnis->nama : '-' }}
            </div>
            @if($form->stakeholder_internal && is_array($form->stakeholder_internal))
            <div class="mb-4">
                <strong>Stakeholder Internal:</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach(\App\Models\UnitKerja::whereIn('id', $form->stakeholder_internal)->get() as $unit)
                        <li>{{ $unit->nama }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="mb-4"><strong>Stakeholder Eksternal:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->stakeholder_eksternal, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

            <div class="mb-4"><strong>Maksud & Tujuan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->maksud_tujuan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Ruang Lingkup:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->ruang_lingkup, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Analisis Biaya Manfaat:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->analisis_biaya_manfaat, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Analisis Risiko:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->analisis_risiko, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        </div>

        {{-- Dokumen Perencanaan --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-lg font-bold mb-3">Dokumen Perencanaan</h2>
            <div class="mb-4"><strong>Perencanaan Ruang Lingkup:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->perencanaan_ruang_lingkup, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Perencanaan Proses Bisnis:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->perencanaan_proses_bisnis, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Kerangka Kerja:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->kerangka_kerja, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Pelaksana Pembangunan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->pelaksana_pembangunan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Peran & Tanggung Jawab:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->peran_tanggung_jawab, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Jadwal Pelaksanaan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->jadwal_pelaksanaan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Rencana Aksi:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->rencana_aksi, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Keamanan Informasi:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->keamanan_informasi, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Sumber Daya:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->sumber_daya, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Indikator Keberhasilan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->indikator_keberhasilan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Alih Pengetahuan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->alih_pengetahuan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
            <div class="mb-4"><strong>Pemantauan & Pelaporan:</strong><div class="prose max-w-none mt-1 bg-gray-50 p-3 rounded">{!! strip_tags($form->pemantauan_pelaporan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        </div>

        {{-- Manajemen Risiko --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-lg font-bold mb-3">Manajemen Risiko</h2>
            @forelse ($form->risikoItems as $item)
                <div class="border rounded bg-gray-50 p-4 mb-4 space-y-1">
                    <div><strong>Jenis Risiko:</strong> {{ $item->jenis_risiko }}</div>
                    <div><strong>Kategori:</strong> {{ $item->kategori_risiko_spbe ?? '-' }}</div>
                    <div><strong>Area Dampak:</strong> {{ $item->area_dampak_risiko_spbe ?? '-' }}</div>
                    <div><strong>Uraian Risiko:</strong> {{ $item->uraian_risiko ?? '-' }}</div>
                    <div><strong>Penyebab:</strong> {{ $item->penyebab ?? '-' }}</div>
                    <div><strong>Dampak:</strong> {{ $item->dampak ?? '-' }}</div>
                    <div class="grid grid-cols-3 gap-2">
                        <div><strong>Kemungkinan:</strong> {{ $item->level_kemungkinan ?? '-' }}</div>
                        <div><strong>Level Dampak:</strong> {{ $item->level_dampak ?? '-' }}</div>
                        <div><strong>Besaran:</strong> {{ $item->besaran_risiko ?? '-' }}</div>
                    </div>
                    <div><strong>Perlu Penanganan:</strong> {{ $item->perlu_penanganan ? 'Ya' : 'Tidak' }}</div>
                    <div><strong>Opsi Penanganan:</strong> {{ $item->opsi_penanganan ?? '-' }}</div>
                    <div><strong>Rencana Aksi:</strong> {{ $item->rencana_aksi ?? '-' }}</div>
                    <div><strong>Jadwal:</strong> {{ $item->jadwal_implementasi ?? '-' }}</div>
                    <div><strong>PIC:</strong> {{ $item->penanggung_jawab ?? '-' }}</div>
                </div>
            @empty
                <p class="text-gray-500">Tidak ada risiko tercatat.</p>
            @endforelse
        </div>
    </div>

    {{-- Approve Modal --}}
    <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Setujui Usulan</h3>
            <form action="{{ route('admin.rekomendasi.approve', $form->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Catatan (Opsional)</label>
                    <textarea name="admin_feedback" rows="3" class="w-full border rounded p-2"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('approveModal').classList.add('hidden')"
                        class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Revision Modal --}}
    <div id="revisionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Minta Revisi</h3>
            <form action="{{ route('admin.rekomendasi.request-revision', $form->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Catatan Revisi <span class="text-red-500">*</span></label>
                    <textarea name="revision_notes" rows="3" class="w-full border rounded p-2" required></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('revisionModal').classList.add('hidden')"
                        class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
                    <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Kirim Permintaan Revisi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Tolak Usulan</h3>
            <form action="{{ route('admin.rekomendasi.reject', $form->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block font-semibold mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="admin_feedback" rows="3" class="w-full border rounded p-2" required></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                        class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Tolak</button>
                </div>
            </form>
        </div>
    </div>
@endsection
