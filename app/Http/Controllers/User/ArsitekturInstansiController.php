<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MasterArsitektur\Item;
use App\Models\MasterArsitektur\Permohonan;
use App\Services\MasterArsitektur\IsianItem;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Http\Request;

/**
 * Pembaruan Data Arsitektur oleh pengguna: melihat entri Master Data Arsitektur
 * milik instansinya dan mengusulkan penambahan/perubahan. Usulan baru berlaku
 * setelah disetujui admin (Kelola Permohonan).
 */
class ArsitekturInstansiController extends Controller
{
    public function index(Request $request)
    {
        $unit = $request->user()->unitKerja;
        $semuaKategori = $this->kategoriInstansi();

        if (! $unit) {
            return view('user.arsitektur.index', ['unit' => null, 'semuaKategori' => $semuaKategori]);
        }

        $kategori = array_key_exists($request->query('kategori'), $semuaKategori)
            ? $request->query('kategori')
            : array_key_first($semuaKategori);
        $cari = trim((string) $request->query('q'));

        $perJenis = Item::where('unit_kerja_id', $unit->id)
            ->selectRaw('jenis, count(*) as jumlah')->groupBy('jenis')->pluck('jumlah', 'jenis');
        $jumlahKategori = array_map(
            fn ($k) => (int) collect($k['jenis'])->sum(fn ($j) => $perJenis[$j] ?? 0),
            $semuaKategori
        );

        $items = Item::where('unit_kerja_id', $unit->id)
            ->whereIn('jenis', $semuaKategori[$kategori]['jenis'])
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('kode', 'like', "%{$cari}%")))
            ->orderBy('jenis')->orderBy('kode')->orderBy('nama')
            ->paginate(25)->withQueryString();

        return view('user.arsitektur.index', [
            'unit' => $unit,
            'semuaKategori' => $semuaKategori,
            'kategori' => $kategori,
            'konfig' => $semuaKategori[$kategori],
            'jumlahKategori' => $jumlahKategori,
            'cari' => $cari,
            'items' => $items,
            'sedangDiusulkan' => Permohonan::where('status', Permohonan::STATUS_MENUNGGU)
                ->whereIn('item_id', $items->pluck('id'))->pluck('id', 'item_id'),
            'jumlahMenunggu' => Permohonan::where('unit_kerja_id', $unit->id)
                ->where('status', Permohonan::STATUS_MENUNGGU)->count(),
        ]);
    }

    public function formTambah(Request $request, string $kategori)
    {
        $unit = $this->instansi($request);
        $daftarJenis = ($this->kategoriInstansi()[$kategori] ?? abort(404))['jenis'];
        $jenis = in_array($request->query('jenis'), $daftarJenis, true) ? $request->query('jenis') : $daftarJenis[0];

        return $this->form(new Item(['jenis' => $jenis]), $kategori, $unit);
    }

    public function simpanTambah(Request $request, string $kategori)
    {
        $unit = $this->instansi($request);
        $jenis = $request->input('jenis');
        abort_unless(in_array($jenis, ($this->kategoriInstansi()[$kategori] ?? abort(404))['jenis'], true), 422, 'Jenis data tidak sesuai kategori.');

        $permohonan = Permohonan::create([
            'user_id' => $request->user()->id,
            'unit_kerja_id' => $unit->id,
            'aksi' => Permohonan::AKSI_TAMBAH,
            'jenis' => $jenis,
            'data' => $this->isian($request, $jenis),
            'catatan_pemohon' => $request->input('catatan_pemohon'),
            'status' => Permohonan::STATUS_MENUNGGU,
        ]);

        return redirect()->route('user.arsitektur.permohonan.show', $permohonan)
            ->with('success', 'Usulan penambahan data terkirim dan menunggu persetujuan admin.');
    }

    public function formUbah(Request $request, Item $item)
    {
        $unit = $this->instansi($request);
        $this->pastikanMilik($item, $unit->id);

        if ($menunggu = $this->permohonanMenunggu($item)) {
            return redirect()->route('user.arsitektur.permohonan.show', $menunggu)
                ->with('error', 'Entri ini masih memiliki usulan perubahan yang menunggu persetujuan.');
        }

        return $this->form($item, Skema::kategoriDari($item->jenis), $unit);
    }

    public function simpanUbah(Request $request, Item $item)
    {
        $unit = $this->instansi($request);
        $this->pastikanMilik($item, $unit->id);

        if ($menunggu = $this->permohonanMenunggu($item)) {
            return redirect()->route('user.arsitektur.permohonan.show', $menunggu)
                ->with('error', 'Entri ini masih memiliki usulan perubahan yang menunggu persetujuan.');
        }

        $baru = $this->isian($request, $item->jenis);
        $lama = IsianItem::dariItem($item);

        $berubah = IsianItem::beda($lama, $baru);

        if (! $berubah) {
            return back()->withInput()->with('error', 'Tidak ada perubahan yang diusulkan.');
        }

        // Kolom yang tidak disentuh disimpan persis seperti nilai lamanya
        $baru = array_merge($lama, array_intersect_key($baru, array_flip($berubah)));

        $permohonan = Permohonan::create([
            'user_id' => $request->user()->id,
            'unit_kerja_id' => $unit->id,
            'item_id' => $item->id,
            'aksi' => Permohonan::AKSI_UBAH,
            'jenis' => $item->jenis,
            'data' => $baru,
            'data_lama' => $lama,
            'catatan_pemohon' => $request->input('catatan_pemohon'),
            'status' => Permohonan::STATUS_MENUNGGU,
        ]);

        return redirect()->route('user.arsitektur.permohonan.show', $permohonan)
            ->with('success', 'Usulan perubahan data terkirim dan menunggu persetujuan admin.');
    }

    /** Riwayat usulan seluruh pengguna pada instansi yang sama. */
    public function permohonan(Request $request)
    {
        $unit = $this->instansi($request);

        return view('user.arsitektur.permohonan', [
            'unit' => $unit,
            'daftar' => Permohonan::with('user')->where('unit_kerja_id', $unit->id)->latest()->paginate(20),
        ]);
    }

    public function lihatPermohonan(Request $request, Permohonan $permohonan)
    {
        abort_unless((int) $permohonan->unit_kerja_id === (int) $this->instansi($request)->id, 403);

        return view('user.arsitektur.permohonan-show', [
            'permohonan' => $permohonan->load(['user', 'unitKerja', 'pemroses']),
        ]);
    }

    public function batalkanPermohonan(Request $request, Permohonan $permohonan)
    {
        abort_unless((int) $permohonan->user_id === (int) $request->user()->id, 403);

        if (! $permohonan->menunggu()) {
            return back()->with('error', 'Usulan yang sudah diproses tidak dapat dibatalkan.');
        }

        $permohonan->delete();

        return redirect()->route('user.arsitektur.permohonan')->with('success', 'Usulan dibatalkan.');
    }

    private function form(Item $item, string $kategori, $unit)
    {
        $jenis = $item->jenis;
        $saran = [];

        // Saran isian diambil dari nilai yang sudah ada pada jenis yang sama
        foreach (['status', 'ref_1', 'ref_2', 'ref_3'] as $kolom) {
            if (isset(Skema::kolom($jenis)[$kolom])) {
                $saran[$kolom] = Item::where('jenis', $jenis)->whereNotNull($kolom)
                    ->distinct()->orderBy($kolom)->limit(500)->pluck($kolom);
            }
        }

        return view('user.arsitektur.form', [
            'item' => $item,
            'jenis' => $jenis,
            'kategori' => $kategori,
            'konfigKategori' => $this->kategoriInstansi()[$kategori],
            'kolom' => Skema::kolom($jenis),
            'saran' => $saran,
            'unit' => $unit,
        ]);
    }

    /** Validasi form lalu kembalikan nilai datar; instansi tidak dapat diubah pengguna. */
    private function isian(Request $request, string $jenis): array
    {
        [$rules, $nama] = IsianItem::aturan($jenis, denganUnitKerja: false);

        $data = $request->validate(
            $rules + ['catatan_pemohon' => ['nullable', 'string', 'max:1000']],
            [],
            $nama + ['catatan_pemohon' => 'catatan']
        );

        return IsianItem::datar($jenis, $data);
    }

    private function instansi(Request $request)
    {
        $unit = $request->user()->unitKerja;
        abort_unless($unit, 403, 'Akun Anda belum terhubung ke instansi. Lengkapi instansi pada profil terlebih dahulu.');

        return $unit;
    }

    private function pastikanMilik(Item $item, int $unitKerjaId): void
    {
        abort_unless((int) $item->unit_kerja_id === $unitKerjaId && Skema::adaJenis($item->jenis), 403);
    }

    private function permohonanMenunggu(Item $item): ?Permohonan
    {
        return Permohonan::where('item_id', $item->id)->where('status', Permohonan::STATUS_MENUNGGU)->first();
    }

    /**
     * Kategori yang dapat diusulkan pengguna: hanya jenis data yang punya kolom
     * perangkat daerah, sebab kepemilikan entri ditentukan dari kolom itu.
     */
    private function kategoriInstansi(): array
    {
        $hasil = [];

        foreach (Skema::kategori() as $kunci => $kategori) {
            $jenis = array_values(array_filter(
                $kategori['jenis'],
                fn ($j) => Skema::adaJenis($j) && isset(Skema::kolom($j)['unit_kerja'])
            ));

            if ($jenis) {
                $hasil[$kunci] = ['jenis' => $jenis] + $kategori;
            }
        }

        return $hasil;
    }
}
