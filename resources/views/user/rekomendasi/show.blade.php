@extends('layouts.user')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Detail Rekomendasi Aplikasi</h1>

        <div class="mb-4">
            <strong>Judul Aplikasi:</strong> {{ $form->judul_aplikasi }}
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div><strong>Target Waktu:</strong> {{ $form->target_waktu }}</div>
            <div><strong>Sasaran Pengguna:</strong> {{ $form->sasaran_pengguna }}</div>
            <div><strong>Lokasi Implementasi:</strong> {{ $form->lokasi_implementasi }}</div>
            <div><strong>Status:</strong> {{ strtoupper($form->status) }}</div>
        </div>

        <div class="mb-4"><strong>Dasar Hukum:</strong><br>{!! nl2br(e($form->dasar_hukum)) !!}</div>
        <div class="mb-4"><strong>Permasalahan Kebutuhan:</strong><br>{!! nl2br(e($form->permasalahan_kebutuhan)) !!}</div>
        <div class="mb-4"><strong>Pihak Terkait:</strong><br>{!! nl2br(e($form->pihak_terkait)) !!}</div>
        <div class="mb-4"><strong>Maksud & Tujuan:</strong><br>{!! nl2br(e($form->maksud_tujuan)) !!}</div>
        <div class="mb-4"><strong>Ruang Lingkup:</strong><br>{!! nl2br(e($form->ruang_lingkup)) !!}</div>

        <div class="mb-4"><strong>Analisis Biaya Manfaat:</strong><br>{!! nl2br(e($form->analisis_biaya_manfaat)) !!}</div>
        <div class="mb-4"><strong>Analisis Risiko:</strong><br>{!! nl2br(e($form->analisis_risiko)) !!}</div>

        <hr class="my-6">

        <h2 class="text-lg font-bold mb-3">Dokumen Perencanaan</h2>
        <div class="mb-4"><strong>Ruang Lingkup:</strong><br>{!! nl2br(e($form->perencanaan_ruang_lingkup)) !!}</div>
        <div class="mb-4"><strong>Proses Bisnis:</strong><br>{!! nl2br(e($form->perencanaan_proses_bisnis)) !!}</div>
        <div class="mb-4"><strong>Kerangka Kerja:</strong> {{ $form->kerangka_kerja }}</div>
        <div class="mb-4"><strong>Pelaksana:</strong> {{ $form->pelaksana_pembangunan }}</div>
        <div class="mb-4"><strong>Peran & Tanggung Jawab:</strong><br>{!! nl2br(e($form->peran_tanggung_jawab)) !!}</div>
        <div class="mb-4"><strong>Jadwal Pelaksanaan:</strong> {{ $form->jadwal_pelaksanaan }}</div>
        <div class="mb-4"><strong>Rencana Aksi:</strong><br>{!! nl2br(e($form->rencana_aksi)) !!}</div>
        <div class="mb-4"><strong>Keamanan Informasi:</strong><br>{!! nl2br(e($form->keamanan_informasi)) !!}</div>
        <div class="mb-4"><strong>Sumber Daya:</strong><br>{!! nl2br(e($form->sumber_daya)) !!}</div>
        <div class="mb-4"><strong>Indikator Keberhasilan:</strong><br>{!! nl2br(e($form->indikator_keberhasilan)) !!}</div>
        <div class="mb-4"><strong>Alih Pengetahuan:</strong><br>{!! nl2br(e($form->alih_pengetahuan)) !!}</div>
        <div class="mb-4"><strong>Pemantauan & Pelaporan:</strong><br>{!! nl2br(e($form->pemantauan_pelaporan)) !!}</div>

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
