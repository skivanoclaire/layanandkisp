<?php

namespace App\Http\Controllers\User\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenAccountReactivation;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenRequestLog;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\Dtsen\ProfilPemohon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Tahap 1 - Pembuatan Akun Layanan DTSEN (sisi OPD pemohon).
 */
class AccountRequestController extends Controller
{
    public function index()
    {
        $items = DtsenAccountRequest::with('unitKerja')
            ->where('user_id', auth()->id())
            ->withCount('members')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.dtsen.akun.index', compact('items'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.dtsen.akun.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $item = DB::transaction(function () use ($request, $data, $isSubmit) {
            $item = new DtsenAccountRequest($data);
            $item->user_id = auth()->id();
            $item->status = $isSubmit ? DtsenAccountRequest::STATUS_DIAJUKAN : DtsenAccountRequest::STATUS_DRAFT;
            $item->submitted_at = $isSubmit ? now() : null;
            $item->save();

            $this->handleSuratUpload($request, $item);
            $item->save();

            $this->syncMembers($item, $request->input('members', []));

            return $item;
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_AKUN,
            $item->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit ? 'Permohonan akun layanan DTSEN diajukan' : 'Draft permohonan akun dibuat'
        );

        if (! $isSubmit) {
            return redirect()->route('user.dtsen.akun.edit', $item->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.dtsen.akun.show', $item->id)
            ->with('status', "Permohonan akun {$item->ticket_no} berhasil diajukan. Verifikasi ditargetkan selesai dalam 1 hari kerja.");
    }

    public function show($id)
    {
        $item = DtsenAccountRequest::with(['unitKerja', 'members', 'verifier', 'reactivations.decidedBy'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_AKUN, $item->id);

        return view('user.dtsen.akun.show', compact('item', 'logs'));
    }

    public function edit($id)
    {
        $item = DtsenAccountRequest::with('members')
            ->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan akun tidak bisa diubah karena sudah diproses.');

        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.dtsen.akun.edit', compact('item', 'unitKerjaList'));
    }

    public function update(Request $request, $id)
    {
        $item = DtsenAccountRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan akun tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        DB::transaction(function () use ($request, $item, $data, $isSubmit) {
            $item->fill($data);
            if ($isSubmit) {
                $item->status = DtsenAccountRequest::STATUS_DIAJUKAN;
                $item->submitted_at = $item->submitted_at ?: now();
                // Isian perbaikan sebelumnya tidak lagi relevan setelah diajukan ulang.
                $item->catatan_perbaikan = null;
            }
            $this->handleSuratUpload($request, $item);
            $item->save();

            $this->syncMembers($item, $request->input('members', []));
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_AKUN,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan akun dilengkapi & diajukan' : 'Draft permohonan akun diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.dtsen.akun.show', $item->id)
                ->with('status', 'Permohonan akun berhasil diajukan.');
        }

        return redirect()->route('user.dtsen.akun.index')->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = DtsenAccountRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless(
            $item->status === DtsenAccountRequest::STATUS_DRAFT,
            403,
            'Hanya draft yang dapat dihapus.'
        );

        $item->delete();

        return redirect()->route('user.dtsen.akun.index')->with('status', 'Draft permohonan akun dihapus.');
    }

    /**
     * Form aktivasi ulang akun yang dinonaktifkan karena tidak digunakan 30 hari.
     */
    public function requestReactivation(Request $request, $id)
    {
        $item = DtsenAccountRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_if($item->is_active, 400, 'Akun masih aktif, tidak perlu diaktifkan ulang.');
        abort_unless($item->status === DtsenAccountRequest::STATUS_DISETUJUI, 403, 'Akun belum pernah disetujui.');

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ]);

        $pending = $item->reactivations()
            ->where('status', DtsenAccountReactivation::STATUS_DIAJUKAN)
            ->exists();

        if ($pending) {
            return back()->withErrors(['alasan' => 'Masih ada permintaan aktivasi ulang yang menunggu keputusan.']);
        }

        $item->reactivations()->create([
            'user_id' => auth()->id(),
            'alasan' => $data['alasan'],
            'status' => DtsenAccountReactivation::STATUS_DIAJUKAN,
        ]);

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_AKUN,
            $item->id,
            'reaktivasi_diajukan',
            'Permintaan aktivasi ulang akun diajukan'
        );

        return back()->with('status', 'Permintaan aktivasi ulang dikirim ke Admin DKISP.');
    }

    // ------------------------------------------------------------ Helper

    /**
     * Timpa isian yang datanya sudah ada di profil pengguna.
     *
     * Kolom terkunci di form hanya berupa `readonly`, jadi penegakan sebenarnya
     * dilakukan di sini — nilai profil selalu menang atas apa pun yang dikirim
     * browser. Kolom yang profilnya masih kosong dibiarkan apa adanya.
     */
    private function terapkanProfil(Request $request): void
    {
        $profil = ProfilPemohon::untuk($request->user());

        $request->merge($profil->untukRequest(['unit_kerja_id' => 'unit_kerja_id']));

        // Narahubung mengikuti profil bila pemohon menyatakan dirinya sendiri
        // sebagai narahubung teknis.
        if ($request->boolean('narahubung_sama_profil')) {
            $request->merge($profil->untukRequest([
                'narahubung_nama' => 'nama',
                'narahubung_kontak' => 'telepon',
                'narahubung_email' => 'email',
            ]));
        }

        // Personel pertama selalu pengaju sendiri.
        $members = array_values((array) $request->input('members', []));
        if ($members !== []) {
            $members[0] = array_merge((array) $members[0], $profil->untukRequest([
                'nama' => 'nama',
                'nip' => 'nip',
                'jabatan' => 'jabatan',
                'no_hp' => 'telepon',
                'email' => 'email',
            ]));

            if (filled($profil->namaUnitKerja())) {
                $members[0]['unit_kerja'] = $profil->namaUnitKerja();
            }

            $request->merge(['members' => $members]);
        }
    }

    private function validateData(Request $request): array
    {
        $this->terapkanProfil($request);

        $validated = $request->validate([
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'nomor_surat' => ['required', 'string', 'max:150'],
            'sifat_surat' => ['required', Rule::in(array_keys(DtsenAccountRequest::sifatSuratLabels()))],
            'jumlah_lampiran' => ['nullable', 'string', 'max:50'],
            'tanggal_surat' => ['required', 'date'],
            'surat' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'narahubung_nama' => ['required', 'string', 'max:150'],
            'narahubung_kontak' => ['required', 'string', 'max:30'],
            'narahubung_email' => ['required', 'email', 'max:200'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.nama' => ['required', 'string', 'max:150'],
            'members.*.nip' => ['nullable', 'string', 'max:30'],
            'members.*.jabatan' => ['nullable', 'string', 'max:150'],
            'members.*.unit_kerja' => ['nullable', 'string', 'max:200'],
            'members.*.no_hp' => ['nullable', 'string', 'max:30'],
            'members.*.email' => ['required', 'email', 'max:200'],
            'consent_true' => ['accepted'],
        ], [
            'members.required' => 'Minimal satu calon pengguna akun harus diisi.',
            'members.*.nama.required' => 'Nama calon pengguna akun wajib diisi.',
            'members.*.email.required' => 'Surel calon pengguna akun wajib diisi.',
            'surat.mimes' => 'Surat permohonan harus berkas PDF (mendukung dokumen bertanda tangan elektronik).',
            'consent_true.accepted' => 'Anda harus menyatakan kebenaran data yang diisi.',
        ]);

        // `members`, `surat`, dan `consent_true` ditangani terpisah dari kolom tabel.
        return collect($validated)
            ->except(['members', 'surat'])
            ->put('consent_true', true)
            ->all();
    }

    private function handleSuratUpload(Request $request, DtsenAccountRequest $item): void
    {
        if ($request->hasFile('surat')) {
            $item->surat_path = $request->file('surat')->storeAs(
                'dtsen-docs/akun',
                'SURAT_' . $item->ticket_no . '_' . time() . '.' . $request->file('surat')->extension(),
                'public'
            );
        }
    }

    /**
     * Tulis ulang daftar personel: baris yang dikirim menggantikan seluruh isi lama.
     */
    private function syncMembers(DtsenAccountRequest $item, array $members): void
    {
        $item->members()->delete();

        foreach ($members as $member) {
            if (empty($member['nama']) || empty($member['email'])) {
                continue;
            }

            $item->members()->create([
                'nama' => $member['nama'],
                'nip' => $member['nip'] ?? null,
                'jabatan' => $member['jabatan'] ?? null,
                'unit_kerja' => $member['unit_kerja'] ?? null,
                'no_hp' => $member['no_hp'] ?? null,
                'email' => $member['email'],
                'user_id' => $this->matchPortalUser($member),
            ]);
        }
    }

    /**
     * Tautkan personel ke akun portal yang sudah ada berdasarkan surel atau NIP,
     * supaya yang bersangkutan bisa langsung memakai akun DTSEN milik OPD-nya.
     */
    private function matchPortalUser(array $member): ?int
    {
        return User::query()
            ->where(function ($query) use ($member) {
                $query->where('email', $member['email']);

                if (! empty($member['nip'])) {
                    $query->orWhere('nip', $member['nip']);
                }
            })
            ->value('id');
    }
}
