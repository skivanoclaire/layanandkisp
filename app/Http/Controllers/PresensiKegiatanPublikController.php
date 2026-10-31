<?php

namespace App\Http\Controllers;

use App\Models\PresensiKegiatan;
use App\Models\PresensiKegiatanPeserta;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Halaman publik konfirmasi kehadiran & presensi kegiatan.
 * Peserta boleh mengisi manual atau masuk dengan akun E-Layanan agar data diri terisi otomatis.
 */
class PresensiKegiatanPublikController extends Controller
{
    public const SESSION_REDIRECT = 'presensi_kegiatan_redirect';

    /** Unit kerja yang muncul di pilihan instansi; instansi pusat/lainnya diisi manual. */
    public const TIPE_PEMPROV = [UnitKerja::TIPE_INDUK, UnitKerja::TIPE_CABANG, UnitKerja::TIPE_SEKOLAH];

    public function show(PresensiKegiatan $kegiatan)
    {
        return view('public.presensi-kegiatan.show', compact('kegiatan'));
    }

    public function konfirmasi(Request $request, PresensiKegiatan $kegiatan)
    {
        return $this->form($request, $kegiatan, 'konfirmasi');
    }

    public function presensi(Request $request, PresensiKegiatan $kegiatan)
    {
        return $this->form($request, $kegiatan, 'presensi');
    }

    public function simpanKonfirmasi(Request $request, PresensiKegiatan $kegiatan)
    {
        return $this->simpan($request, $kegiatan, 'konfirmasi');
    }

    public function simpanPresensi(Request $request, PresensiKegiatan $kegiatan)
    {
        return $this->simpan($request, $kegiatan, 'presensi');
    }

    /** Simpan tujuan kembali, lalu arahkan ke halaman login (email/password maupun SSO). */
    public function masuk(Request $request, PresensiKegiatan $kegiatan, string $mode)
    {
        abort_unless(in_array($mode, ['konfirmasi', 'presensi'], true), 404);

        $tujuan = route('kegiatan.' . $mode, $kegiatan);
        if ($request->user()) {
            return redirect($tujuan);
        }

        $request->session()->put(self::SESSION_REDIRECT, $tujuan);

        return redirect()->route('login');
    }

    public function selesai(PresensiKegiatan $kegiatan)
    {
        abort_unless(session()->has('presensi_selesai'), 404);

        return view('public.presensi-kegiatan.selesai', [
            'kegiatan' => $kegiatan,
            'hasil' => session('presensi_selesai'),
        ]);
    }

    private function form(Request $request, PresensiKegiatan $kegiatan, string $mode)
    {
        $dibuka = $mode === 'konfirmasi' ? $kegiatan->konfirmasi_aktif : $kegiatan->presensi_aktif;
        $user = $request->user();

        return view('public.presensi-kegiatan.form', [
            'kegiatan' => $kegiatan,
            'mode' => $mode,
            'dibuka' => $dibuka,
            'akun' => $user ? $this->profilAkun($user) : null,
            'sudah' => $user ? $this->cariPeserta($kegiatan, ['user_id' => $user->id]) : null,
            'unitKerjas' => UnitKerja::whereIn('tipe', self::TIPE_PEMPROV)->orderBy('nama')
                ->get(['id', 'nama', 'tipe'])->groupBy('tipe')
                ->sortBy(fn ($_, $tipe) => array_search($tipe, self::TIPE_PEMPROV)),
        ]);
    }

    private function simpan(Request $request, PresensiKegiatan $kegiatan, string $mode)
    {
        $dibuka = $mode === 'konfirmasi' ? $kegiatan->konfirmasi_aktif : $kegiatan->presensi_aktif;
        if (!$dibuka) {
            return redirect()->route('kegiatan.' . $mode, $kegiatan)
                ->with('error', $mode === 'konfirmasi' ? 'Konfirmasi kehadiran sudah ditutup.' : 'Presensi belum dibuka atau sudah ditutup.');
        }

        // Honeypot: bot mengisi semua kolom, manusia tidak melihat kolom ini.
        if ($request->filled('website')) {
            return redirect()->route('kegiatan.show', $kegiatan);
        }

        $user = $request->user();
        $data = $this->validasi($request, $kegiatan, $mode, $user);

        $peserta = $this->cariPeserta($kegiatan, $data);

        if ($mode === 'presensi' && $peserta?->hadir_at) {
            return redirect()->route('kegiatan.selesai', $kegiatan)->with('presensi_selesai', [
                'mode' => 'presensi',
                'nama' => $peserta->nama,
                'waktu' => $peserta->hadir_at->format('d/m/Y H:i'),
                'sudah' => true,
            ]);
        }

        if ($mode === 'konfirmasi') {
            $this->cekKuota($kegiatan, $data, $peserta);
        }

        $peserta ??= new PresensiKegiatanPeserta(['presensi_kegiatan_id' => $kegiatan->id]);
        $peserta->fill([
            'user_id' => $user?->id ?? $peserta->user_id,
            'nama' => $data['nama'],
            'nip' => $data['nip'],
            'jabatan' => $data['jabatan'],
            'unit_kerja_id' => $data['unit_kerja_id'],
            'instansi' => $data['instansi'],
            'no_hp' => $data['no_hp'],
            'email' => $data['email'],
            'sumber' => $user ? 'akun' : ($peserta->exists ? $peserta->sumber : 'manual'),
            'ip_address' => $request->ip(),
        ]);

        if ($mode === 'konfirmasi') {
            $peserta->konfirmasi = $data['konfirmasi'];
            $peserta->konfirmasi_at = now();
            $peserta->catatan = $data['catatan'];
        } else {
            $peserta->hadir_at = now();
            $peserta->tanda_tangan = $data['tanda_tangan'];
            // Hadir di lokasi berarti sudah pasti hadir walau belum/tidak mengisi konfirmasi.
            $peserta->konfirmasi ??= 'hadir';
        }

        $peserta->save();

        // Tautan rapat online langsung dikirim ke WA bila panitia sudah mengisinya.
        $waTerkirim = false;
        if ($mode === 'konfirmasi' && $peserta->konfirmasi === 'online' && $kegiatan->tautan_online && !$peserta->wa_online_terkirim_at) {
            $waTerkirim = $peserta->setRelation('kegiatan', $kegiatan)->kirimTautanOnline();
        }

        return redirect()->route('kegiatan.selesai', $kegiatan)->with('presensi_selesai', [
            'mode' => $mode,
            'nama' => $peserta->nama,
            'konfirmasi' => $peserta->konfirmasi,
            'no_hp' => $peserta->no_hp,
            'wa_terkirim' => $waTerkirim || $peserta->wa_online_terkirim_at,
            'waktu' => now()->format('d/m/Y H:i'),
            'sudah' => false,
        ]);
    }

    private function validasi(Request $request, PresensiKegiatan $kegiatan, string $mode, ?User $user): array
    {
        $request->merge([
            'nip' => preg_replace('/\s+/', '', (string) $request->input('nip')) ?: null,
            'no_hp' => PresensiKegiatanPeserta::normalisasiHp($request->input('no_hp')) ?: null,
        ]);

        $rules = [
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|digits_between:8,20',
            'jabatan' => 'nullable|string|max:255',
            'instansi' => 'required|string|max:255',
            'no_hp' => 'required|digits_between:9,15',
            'email' => 'nullable|email|max:255',
        ];

        if ($mode === 'konfirmasi') {
            $rules['konfirmasi'] = 'required|in:' . implode(',', array_keys(PresensiKegiatanPeserta::KONFIRMASI));
            $rules['catatan'] = 'nullable|string|max:500';
        } else {
            $rules['tanda_tangan'] = [
                $kegiatan->wajib_tanda_tangan ? 'required' : 'nullable',
                'string',
                'max:400000',
                'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/',
            ];
        }

        // Instansi dipilih dari daftar unit kerja Pemprov, atau "Lainnya" lalu diketik manual.
        if ($request->has('pilihan_instansi')) {
            $pilihan = $request->input('pilihan_instansi');
            $request->merge(['instansi' => $pilihan === 'lainnya'
                ? trim((string) $request->input('instansi_lainnya')) ?: null
                : UnitKerja::whereIn('tipe', self::TIPE_PEMPROV)->whereKey((int) $pilihan)->value('nama')]);
        }

        // Data dari akun diambil langsung dari profil, bukan dari isian form.
        if ($user) {
            $akun = $this->profilAkun($user);
            $request->merge(array_filter([
                'nama' => $akun['nama'],
                'nip' => $akun['nip'],
                'instansi' => $akun['instansi'],
            ]));
        }

        $data = $request->validate($rules, [
            'tanda_tangan.required' => 'Tanda tangan wajib dibubuhkan.',
            'tanda_tangan.regex' => 'Format tanda tangan tidak valid, silakan ulangi.',
            'no_hp.digits_between' => 'Nomor HP harus 9–15 digit angka.',
            'nip.digits_between' => 'NIP hanya berisi angka (8–20 digit).',
            'instansi.required' => 'Pilih instansi dari daftar, atau pilih "Lainnya" lalu tuliskan nama instansi.',
        ], [
            'no_hp' => 'nomor HP',
            'nip' => 'NIP',
        ]);

        $data['nama'] = trim($data['nama']);
        $data['instansi'] = trim($data['instansi']);
        $data['unit_kerja_id'] = ($user && $user->unit_kerja_id)
            ? $user->unit_kerja_id
            : UnitKerja::where('nama', $data['instansi'])->value('id');
        $data['user_id'] = $user?->id;

        return $data + ['jabatan' => null, 'email' => null, 'catatan' => null, 'tanda_tangan' => null];
    }

    /** Satu orang = satu baris per kegiatan; dicocokkan lewat akun, NIP, lalu nomor HP. */
    private function cariPeserta(PresensiKegiatan $kegiatan, array $data): ?PresensiKegiatanPeserta
    {
        $query = fn () => PresensiKegiatanPeserta::where('presensi_kegiatan_id', $kegiatan->id);

        if (!empty($data['user_id']) && $p = $query()->where('user_id', $data['user_id'])->first()) {
            return $p;
        }
        if (!empty($data['nip']) && $p = $query()->where('nip', $data['nip'])->first()) {
            return $p;
        }
        if (!empty($data['no_hp'])) {
            return $query()->where('no_hp', $data['no_hp'])->first();
        }

        return null;
    }

    private function cekKuota(PresensiKegiatan $kegiatan, array $data, ?PresensiKegiatanPeserta $peserta): void
    {
        if (!$kegiatan->kuota_per_instansi) {
            return;
        }

        $terisi = PresensiKegiatanPeserta::where('presensi_kegiatan_id', $kegiatan->id)
            ->whereNotNull('konfirmasi')
            ->when($peserta, fn ($q) => $q->whereKeyNot($peserta->id))
            ->where(fn ($q) => $data['unit_kerja_id']
                ? $q->where('unit_kerja_id', $data['unit_kerja_id'])
                : $q->where('instansi', $data['instansi']))
            ->count();

        if ($terisi >= $kegiatan->kuota_per_instansi) {
            throw ValidationException::withMessages([
                'instansi' => "Kuota peserta untuk instansi ini sudah terpenuhi ({$kegiatan->kuota_per_instansi} orang). "
                    . 'Hubungi panitia bila perlu penggantian peserta.',
            ]);
        }
    }

    private function profilAkun(User $user): array
    {
        $user->loadMissing(['unitKerja', 'jabatan']);

        return [
            'nama' => $user->name,
            'nip' => $user->nip ? preg_replace('/\s+/', '', $user->nip) : null,
            'jabatan' => $user->jabatan?->nama_jabatan,
            'instansi' => $user->unitKerja?->nama,
            'no_hp' => $user->phone ? PresensiKegiatanPeserta::normalisasiHp($user->phone) : null,
            'email' => $user->email,
        ];
    }
}
