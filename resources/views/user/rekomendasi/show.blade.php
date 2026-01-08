@extends('layouts.authenticated')

@section('title', '- Detail Rekomendasi Aplikasi')
@section('header-title', 'Detail Rekomendasi Aplikasi')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Detail Rekomendasi Aplikasi</h1>

        {{-- Alert for Revision Request --}}
        @if ($form->status == 'perlu_revisi' && $form->revision_notes)
            <div class="mb-6 p-4 bg-orange-50 border-l-4 border-orange-500 rounded shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-semibold text-orange-800">Usulan Perlu Direvisi</h3>
                        <p class="mt-1 text-sm text-orange-700">Admin meminta Anda untuk merevisi usulan aplikasi ini.
                        </p>
                        <div class="mt-3 p-3 bg-white border border-orange-200 rounded">
                            <p class="text-sm font-semibold text-gray-700">Catatan dari Admin:</p>
                            <p class="mt-2 text-gray-800">{{ $form->revision_notes }}</p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('user.rekomendasi.aplikasi.edit', $form->id) }}"
                                class="inline-flex items-center px-6 py-3 text-orange-700 font-bold rounded-lg hover:text-orange-800 hover:underline transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                ✏️ Edit dan Kirim Ulang Usulan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Alert for Admin Feedback (Rejected) --}}
        @if ($form->status == 'ditolak' && $form->admin_feedback)
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-red-800">Usulan Ditolak</h3>
                        <div class="mt-2 p-3 bg-white border border-red-200 rounded">
                            <p class="text-sm font-semibold text-gray-700">Alasan Penolakan:</p>
                            <p class="mt-2 text-gray-800">{{ $form->admin_feedback }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Alert for Approval --}}
        @if ($form->status == 'disetujui')
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-semibold text-green-800">Usulan Disetujui</h3>
                        <p class="mt-1 text-sm text-green-700">Selamat! Usulan aplikasi Anda telah disetujui.</p>
                        @if ($form->admin_feedback)
                            <div class="mt-2 p-3 bg-white border border-green-200 rounded">
                                <p class="text-sm font-semibold text-gray-700">Catatan dari Admin:</p>
                                <p class="mt-2 text-gray-800">{{ $form->admin_feedback }}</p>
                            </div>
                        @endif
                        <div class="mt-4">
                            <a href="{{ route('user.rekomendasi.aplikasi.download-pdf', $form->id) }}"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-lg transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Surat Rekomendasi (PDF)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-4">
            <strong>Judul Aplikasi:</strong> {{ $form->judul_aplikasi }}
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div><strong>Target Waktu:</strong> {{ $form->target_waktu }}</div>
            <div><strong>Sasaran Pengguna:</strong> {{ $form->sasaran_pengguna }}</div>
            <div><strong>Lokasi Implementasi:</strong> {{ $form->lokasi_implementasi }}</div>
            <div><strong>Status:</strong> {{ strtoupper($form->status) }}</div>
        </div>

        <div class="mb-4"><strong>Dasar Hukum:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->dasar_hukum, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Permasalahan Kebutuhan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->permasalahan_kebutuhan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

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
        <div class="mb-4"><strong>Stakeholder Eksternal:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->stakeholder_eksternal, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

        <div class="mb-4"><strong>Maksud & Tujuan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->maksud_tujuan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Ruang Lingkup:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->ruang_lingkup, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

        <div class="mb-4"><strong>Analisis Biaya Manfaat:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->analisis_biaya_manfaat, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Analisis Risiko:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->analisis_risiko, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

        <hr class="my-6">

        <h2 class="text-lg font-bold mb-3">Dokumen Perencanaan</h2>
        <div class="mb-4"><strong>Perencanaan Ruang Lingkup:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->perencanaan_ruang_lingkup, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Perencanaan Proses Bisnis:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->perencanaan_proses_bisnis, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Kerangka Kerja:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->kerangka_kerja, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Pelaksana Pembangunan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->pelaksana_pembangunan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Peran & Tanggung Jawab:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->peran_tanggung_jawab, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Jadwal Pelaksanaan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->jadwal_pelaksanaan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Rencana Aksi:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->rencana_aksi, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Keamanan Informasi:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->keamanan_informasi, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Sumber Daya:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->sumber_daya, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Indikator Keberhasilan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->indikator_keberhasilan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Alih Pengetahuan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->alih_pengetahuan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>
        <div class="mb-4"><strong>Pemantauan & Pelaporan:</strong><div class="prose max-w-none mt-1">{!! strip_tags($form->pemantauan_pelaporan, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6>') !!}</div></div>

        <hr class="my-6">

        <h2 class="text-lg font-bold mb-2">Manajemen Risiko</h2>
        @forelse ($form->risikoItems as $item)
            <div class="border rounded bg-gray-50 p-4 mb-4 space-y-1">
                <div><strong>Jenis Risiko:</strong> {{ $item->jenis_risiko }}</div>
                <div><strong>Uraian Risiko:</strong><br>{!! nl2br(e($item->uraian_risiko)) !!}</div>
                <div><strong>Penyebab:</strong> {{ $item->penyebab }}</div>
                <div><strong>Dampak:</strong> {{ $item->dampak }}</div>
                <div><strong>Level Kemungkinan:</strong> {{ $item->level_kemungkinan }}</div>
                <div><strong>Level Dampak:</strong> {{ $item->level_dampak }}</div>
                <div><strong>Besaran Risiko:</strong> {{ $item->besaran_risiko }}</div>
                <div><strong>Perlu Penanganan:</strong> {{ $item->perlu_penanganan ? 'Ya' : 'Tidak' }}</div>

                <hr class="my-2">

                <div><strong>Opsi Penanganan:</strong><br>{!! nl2br(e($item->opsi_penanganan)) !!}</div>
                <div><strong>Rencana Aksi:</strong><br>{!! nl2br(e($item->rencana_aksi)) !!}</div>
                <div><strong>Jadwal Implementasi:</strong> {{ $item->jadwal_implementasi }}</div>
                <div><strong>Penanggung Jawab:</strong> {{ $item->penanggung_jawab }}</div>
                <div><strong>Risiko Residual:</strong> {{ $item->risiko_residual ? 'Ya' : 'Tidak' }}</div>
            </div>
        @empty
            <p class="text-gray-500">Tidak ada risiko tercatat.</p>
        @endforelse

    </div>
@endsection
