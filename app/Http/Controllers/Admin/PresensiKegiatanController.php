<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PresensiKegiatanExport;
use App\Http\Controllers\Controller;
use App\Models\PresensiKegiatan;
use App\Models\PresensiKegiatanPeserta;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Master Data > Presensi Kegiatan: kelola kegiatan, buka/tutup halaman publik,
 * dasbor peserta, cetak QR Code lokasi, dan rekap daftar hadir untuk SPJ.
 */
class PresensiKegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = PresensiKegiatan::withCount([
            'peserta as konfirmasi_count' => fn ($q) => $q->whereNotNull('konfirmasi'),
            'peserta as hadir_count' => fn ($q) => $q->whereNotNull('hadir_at'),
        ])->orderByDesc('tanggal_mulai')->paginate(15);

        return view('admin.presensi-kegiatan.index', compact('kegiatans'));
    }

    public function create()
    {
        return view('admin.presensi-kegiatan.form', ['kegiatan' => new PresensiKegiatan([
            'wajib_tanda_tangan' => true,
            'penyelenggara' => 'Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara',
        ])]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['slug'] = $data['slug'] ?: $this->slugUnik($data['judul']);
        $data['created_by'] = $request->user()->id;

        $kegiatan = PresensiKegiatan::create($data);

        return redirect()->route('admin.presensi-kegiatan.show', $kegiatan)
            ->with('success', 'Kegiatan berhasil dibuat. Aktifkan konfirmasi/presensi bila sudah siap dibuka.');
    }

    public function edit(PresensiKegiatan $kegiatan)
    {
        return view('admin.presensi-kegiatan.form', compact('kegiatan'));
    }

    public function update(Request $request, PresensiKegiatan $kegiatan)
    {
        $data = $this->validasi($request, $kegiatan);
        $data['slug'] = $data['slug'] ?: $kegiatan->slug;

        $kegiatan->update($data);

        return redirect()->route('admin.presensi-kegiatan.show', $kegiatan)
            ->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    public function destroy(PresensiKegiatan $kegiatan)
    {
        $kegiatan->delete();

        return redirect()->route('admin.presensi-kegiatan.index')
            ->with('success', 'Kegiatan beserta seluruh data pesertanya telah dihapus.');
    }

    public function toggle(Request $request, PresensiKegiatan $kegiatan)
    {
        $kolom = $request->validate(['kolom' => 'required|in:konfirmasi_aktif,presensi_aktif'])['kolom'];

        $kegiatan->update([$kolom => !$kegiatan->{$kolom}]);

        $label = $kolom === 'konfirmasi_aktif' ? 'Konfirmasi kehadiran' : 'Presensi';

        return back()->with('success', $label . ($kegiatan->{$kolom} ? ' dibuka untuk publik.' : ' ditutup.'));
    }

    public function show(Request $request, PresensiKegiatan $kegiatan)
    {
        $semua = $kegiatan->peserta()->get();

        $statistik = [
            'konfirmasi_hadir' => $semua->where('konfirmasi', 'hadir')->count(),
            'konfirmasi_online' => $semua->where('konfirmasi', 'online')->count(),
            'hadir' => $semua->whereNotNull('hadir_at')->count(),
            'belum_datang' => $semua->whereNotNull('konfirmasi')->whereNull('hadir_at')->count(),
            'instansi' => $semua->whereNotNull('hadir_at')->pluck('instansi')->unique()->count(),
            'online_belum_wa' => $semua->where('konfirmasi', 'online')->whereNull('wa_online_terkirim_at')->count(),
        ];

        // Perangkat daerah yang belum mengirim satu pun peserta (acuan daftar undangan).
        $sudahUnit = $semua->pluck('unit_kerja_id')->filter()->unique();
        $belumKonfirmasi = UnitKerja::whereIn('tipe', [UnitKerja::TIPE_INDUK, UnitKerja::TIPE_CABANG])
            ->whereNotIn('id', $sudahUnit)
            ->orderBy('nama')
            ->pluck('nama');

        $filter = $request->input('filter');
        $cari = trim((string) $request->input('cari'));
        $peserta = $kegiatan->peserta()
            ->when($filter === 'hadir', fn ($q) => $q->whereNotNull('hadir_at'))
            ->when($filter === 'konfirmasi_hadir', fn ($q) => $q->where('konfirmasi', 'hadir'))
            ->when($filter === 'belum_datang', fn ($q) => $q->whereNotNull('konfirmasi')->whereNull('hadir_at'))
            ->when($filter === 'online', fn ($q) => $q->where('konfirmasi', 'online'))
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', "%{$cari}%")
                ->orWhere('nip', 'like', "%{$cari}%")
                ->orWhere('instansi', 'like', "%{$cari}%")))
            ->orderBy('instansi')->orderBy('nama')
            ->get();

        $unitKerjas = UnitKerja::orderBy('nama')->pluck('nama');

        return view('admin.presensi-kegiatan.show', compact(
            'kegiatan', 'statistik', 'belumKonfirmasi', 'peserta', 'filter', 'cari', 'unitKerjas'
        ));
    }

    /** Panitia menambahkan peserta yang tidak sempat mengisi sendiri (langsung tercatat hadir). */
    public function storePeserta(Request $request, PresensiKegiatan $kegiatan)
    {
        $request->merge([
            'nip' => preg_replace('/\s+/', '', (string) $request->input('nip')) ?: null,
            'no_hp' => PresensiKegiatanPeserta::normalisasiHp($request->input('no_hp')) ?: null,
        ]);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|digits_between:8,20',
            'jabatan' => 'nullable|string|max:255',
            'instansi' => 'required|string|max:255',
            'no_hp' => 'required|digits_between:9,15',
            'hadir' => 'nullable|boolean',
        ]);

        $kegiatan->peserta()->create([
            'nama' => trim($data['nama']),
            'nip' => $data['nip'],
            'jabatan' => $data['jabatan'] ?? null,
            'instansi' => trim($data['instansi']),
            'unit_kerja_id' => UnitKerja::where('nama', trim($data['instansi']))->value('id'),
            'no_hp' => $data['no_hp'],
            'konfirmasi' => 'hadir',
            'konfirmasi_at' => now(),
            'hadir_at' => $request->boolean('hadir') ? now() : null,
            'sumber' => 'admin',
        ]);

        return back()->with('success', 'Peserta ditambahkan.');
    }

    public function tandaiHadir(PresensiKegiatan $kegiatan, PresensiKegiatanPeserta $peserta)
    {
        abort_unless($peserta->presensi_kegiatan_id === $kegiatan->id, 404);

        $peserta->update(['hadir_at' => $peserta->hadir_at ? null : now()]);

        return back()->with('success', $peserta->hadir_at
            ? "{$peserta->nama} ditandai hadir."
            : "Status hadir {$peserta->nama} dibatalkan.");
    }

    /** Kirim tautan rapat online via WA ke peserta hadir online (yang belum menerima, atau semua bila ulang=1). */
    public function kirimTautanOnline(Request $request, PresensiKegiatan $kegiatan)
    {
        if (!$kegiatan->tautan_online) {
            return back()->with('error', 'Isi dulu tautan rapat online pada menu Ubah kegiatan.');
        }

        $penerima = $kegiatan->peserta()->where('konfirmasi', 'online')
            ->when(!$request->boolean('ulang'), fn ($q) => $q->whereNull('wa_online_terkirim_at'))
            ->get();

        if ($penerima->isEmpty()) {
            return back()->with('success', 'Tidak ada peserta online yang perlu dikirimi tautan.');
        }

        // Dikirim satu per satu (tanpa antrean), beri waktu cukup untuk puluhan peserta.
        set_time_limit(300);
        $gagal = $penerima->reject(fn ($p) => $p->setRelation('kegiatan', $kegiatan)->kirimTautanOnline());

        $pesan = 'Tautan rapat online terkirim ke ' . ($penerima->count() - $gagal->count()) . ' peserta.';
        if ($gagal->isNotEmpty()) {
            return back()->with('error', $pesan . ' Gagal ke: ' . $gagal->pluck('nama')->join(', ')
                . '. Periksa nomor HP atau token Fonnte Aptika.');
        }

        return back()->with('success', $pesan);
    }

    public function kirimTautanPeserta(PresensiKegiatan $kegiatan, PresensiKegiatanPeserta $peserta)
    {
        abort_unless($peserta->presensi_kegiatan_id === $kegiatan->id, 404);

        if (!$kegiatan->tautan_online) {
            return back()->with('error', 'Isi dulu tautan rapat online pada menu Ubah kegiatan.');
        }

        return $peserta->setRelation('kegiatan', $kegiatan)->kirimTautanOnline()
            ? back()->with('success', "Tautan rapat online terkirim ke WA {$peserta->nama}.")
            : back()->with('error', "Gagal mengirim WA ke {$peserta->nama}. Periksa nomor HP atau token Fonnte Aptika.");
    }

    public function destroyPeserta(PresensiKegiatan $kegiatan, PresensiKegiatanPeserta $peserta)
    {
        abort_unless($peserta->presensi_kegiatan_id === $kegiatan->id, 404);

        $peserta->delete();

        return back()->with('success', 'Data peserta dihapus.');
    }

    public function tandaTangan(PresensiKegiatan $kegiatan, PresensiKegiatanPeserta $peserta)
    {
        abort_unless($peserta->presensi_kegiatan_id === $kegiatan->id && $peserta->tanda_tangan, 404);

        $png = base64_decode(Str::after($peserta->tanda_tangan, 'base64,'));

        return response($png, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=3600']);
    }

    /** Lembar QR Code untuk dicetak & dipasang di lokasi acara. */
    public function qr(Request $request, PresensiKegiatan $kegiatan)
    {
        $mode = $request->input('mode') === 'konfirmasi' ? 'konfirmasi' : 'presensi';
        $url = $mode === 'konfirmasi' ? $kegiatan->urlKonfirmasi() : $kegiatan->urlPresensi();
        $svg = (string) QrCode::format('svg')->errorCorrection('H')->margin(1)->size(600)->generate($url);
        $svg = preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);

        return view('admin.presensi-kegiatan.qr', compact('kegiatan', 'mode', 'url', 'svg'));
    }

    /** Daftar hadir (PDF) untuk lampiran SPJ. */
    public function rekap(Request $request, PresensiKegiatan $kegiatan)
    {
        $jenis = $request->input('jenis') === 'konfirmasi' ? 'konfirmasi' : 'hadir';

        $peserta = $kegiatan->peserta()
            ->when($jenis === 'hadir', fn ($q) => $q->whereNotNull('hadir_at')->orderBy('hadir_at'))
            ->when($jenis === 'konfirmasi', fn ($q) => $q->whereNotNull('konfirmasi')->orderBy('instansi')->orderBy('nama'))
            ->get()
            ->makeVisible('tanda_tangan');

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(view('admin.presensi-kegiatan.rekap-pdf', [
            'kegiatan' => $kegiatan,
            'peserta' => $peserta,
            'jenis' => $jenis,
            'logo' => $this->logoBase64(),
        ])->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nama = ($jenis === 'hadir' ? 'Daftar-Hadir-' : 'Rekap-Konfirmasi-') . $kegiatan->slug . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nama . '"',
        ]);
    }

    public function ekspor(PresensiKegiatan $kegiatan)
    {
        return Excel::download(new PresensiKegiatanExport($kegiatan), 'Peserta-' . $kegiatan->slug . '.xlsx');
    }

    private function validasi(Request $request, ?PresensiKegiatan $kegiatan = null): array
    {
        $request->merge(['slug' => Str::slug((string) $request->input('slug')) ?: null]);

        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:80', Rule::unique('presensi_kegiatans', 'slug')->ignore($kegiatan?->id)],
            'deskripsi' => 'nullable|string|max:2000',
            'nomor_surat' => 'nullable|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'waktu' => 'nullable|string|max:100',
            'tempat' => 'required|string|max:255',
            'penyelenggara' => 'nullable|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'tautan_online' => 'nullable|url|max:500',
            'keterangan_online' => 'nullable|string|max:1000',
            'kuota_per_instansi' => 'nullable|integer|min:1|max:1000',
            'pj_jabatan' => 'nullable|string|max:255',
            'pj_nama' => 'nullable|string|max:255',
            'pj_nip' => 'nullable|string|max:30',
        ], [], ['slug' => 'alamat tautan']);

        $data['wajib_tanda_tangan'] = $request->boolean('wajib_tanda_tangan');
        $data['konfirmasi_aktif'] = $request->boolean('konfirmasi_aktif');
        $data['presensi_aktif'] = $request->boolean('presensi_aktif');

        return $data;
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::limit(Str::slug($judul), 60, '') ?: 'kegiatan';
        $slug = $dasar;
        $i = 2;
        while (PresensiKegiatan::where('slug', $slug)->exists()) {
            $slug = $dasar . '-' . $i++;
        }

        return $slug;
    }

    private function logoBase64(): ?string
    {
        $path = public_path('logokaltarafix.png');

        return is_file($path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($path)) : null;
    }
}
