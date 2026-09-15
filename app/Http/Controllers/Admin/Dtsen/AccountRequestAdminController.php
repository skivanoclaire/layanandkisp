<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenAccountReactivation;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form 1.2 - Verifikasi akun layanan DTSEN oleh Admin DKISP (SLA 1 hari kerja).
 */
class AccountRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DtsenAccountRequest::with(['user', 'unitKerja'])
            ->withCount('members')
            ->where('status', '!=', DtsenAccountRequest::STATUS_DRAFT)
            ->orderByDesc('submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_no', 'like', "%{$q}%")
                    ->orWhere('nomor_surat', 'like', "%{$q}%")
                    ->orWhere('narahubung_nama', 'like', "%{$q}%");
            });
        }

        $items = $query->paginate(25)->withQueryString();

        // Permintaan aktivasi ulang yang belum diputuskan (Bab III huruf C).
        $pendingReactivations = DtsenAccountReactivation::with(['accountRequest.unitKerja', 'user'])
            ->where('status', DtsenAccountReactivation::STATUS_DIAJUKAN)
            ->orderBy('created_at')
            ->get();

        return view('admin.dtsen.akun.index', compact('items', 'pendingReactivations'));
    }

    public function show($id)
    {
        $item = DtsenAccountRequest::with(['user', 'unitKerja', 'members', 'verifier', 'reactivations.user', 'reactivations.decidedBy'])
            ->findOrFail($id);

        $logs = DtsenRequestLog::forRequest(DtsenRequestLog::TYPE_AKUN, $item->id);

        return view('admin.dtsen.akun.show', compact('item', 'logs'));
    }

    /**
     * Hasil verifikasi: disetujui (akun langsung aktif) atau dikembalikan untuk perbaikan.
     */
    public function verify(Request $request, $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                DtsenAccountRequest::STATUS_DISETUJUI,
                DtsenAccountRequest::STATUS_DIKEMBALIKAN,
                DtsenAccountRequest::STATUS_DITOLAK,
            ])],
            'catatan_perbaikan' => [
                'required_if:status,' . DtsenAccountRequest::STATUS_DIKEMBALIKAN,
                'required_if:status,' . DtsenAccountRequest::STATUS_DITOLAK,
                'nullable', 'string', 'max:2000',
            ],
            'check_surat_lengkap' => ['nullable', 'boolean'],
            'check_ttd_kepala_opd' => ['nullable', 'boolean'],
            'check_data_personel' => ['nullable', 'boolean'],
        ], [
            'catatan_perbaikan.required_if' => 'Catatan perbaikan wajib diisi bila permohonan dikembalikan atau ditolak.',
        ]);

        $item = DtsenAccountRequest::findOrFail($id);
        $old = $item->status;

        DB::transaction(function () use ($request, $item, $data) {
            $item->check_surat_lengkap = $request->boolean('check_surat_lengkap');
            $item->check_ttd_kepala_opd = $request->boolean('check_ttd_kepala_opd');
            $item->check_data_personel = $request->boolean('check_data_personel');
            $item->catatan_perbaikan = $data['catatan_perbaikan'] ?? null;
            $item->status = $data['status'];
            $item->verified_at = now();
            $item->verified_by = auth()->id();

            if ($data['status'] === DtsenAccountRequest::STATUS_DISETUJUI) {
                $item->is_active = true;
                $item->activated_at = $item->activated_at ?: now();
                $item->last_used_at = now();
                $item->deactivated_at = null;
                $item->deactivation_warned_at = null;
            } else {
                $item->is_active = false;
            }

            $item->save();
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_AKUN,
            $item->id,
            "status:{$old}->{$item->status}",
            $item->catatan_perbaikan
        );

        return back()->with('status', "Akun {$item->ticket_no} kini berstatus {$item->status_label}.");
    }

    /** Nonaktifkan / aktifkan kembali akun secara manual. */
    public function toggleActive(Request $request, $id)
    {
        $item = DtsenAccountRequest::findOrFail($id);

        abort_unless(
            $item->status === DtsenAccountRequest::STATUS_DISETUJUI,
            403,
            'Hanya akun yang sudah disetujui yang dapat diaktifkan/dinonaktifkan.'
        );

        $request->validate(['alasan' => ['nullable', 'string', 'max:500']]);

        $item->is_active = ! $item->is_active;
        if ($item->is_active) {
            $item->activated_at = $item->activated_at ?: now();
            $item->last_used_at = now();
            $item->deactivated_at = null;
            $item->deactivation_warned_at = null;
        } else {
            $item->deactivated_at = now();
        }
        $item->save();

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_AKUN,
            $item->id,
            $item->is_active ? 'akun_diaktifkan' : 'akun_dinonaktifkan',
            $request->input('alasan')
        );

        return back()->with('status', 'Status keaktifan akun diperbarui.');
    }

    /** Keputusan atas permintaan aktivasi ulang akun. */
    public function decideReactivation(Request $request, $reactivationId)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                DtsenAccountReactivation::STATUS_DISETUJUI,
                DtsenAccountReactivation::STATUS_DITOLAK,
            ])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $reactivation = DtsenAccountReactivation::with('accountRequest')->findOrFail($reactivationId);

        abort_unless(
            $reactivation->status === DtsenAccountReactivation::STATUS_DIAJUKAN,
            400,
            'Permintaan aktivasi ulang ini sudah diputuskan.'
        );

        DB::transaction(function () use ($reactivation, $data) {
            $reactivation->status = $data['status'];
            $reactivation->catatan = $data['catatan'] ?? null;
            $reactivation->decided_by = auth()->id();
            $reactivation->decided_at = now();
            $reactivation->save();

            if ($data['status'] === DtsenAccountReactivation::STATUS_DISETUJUI) {
                $account = $reactivation->accountRequest;
                $account->is_active = true;
                $account->last_used_at = now();
                $account->deactivated_at = null;
                $account->deactivation_warned_at = null;
                $account->save();
            }
        });

        DtsenRequestLog::record(
            DtsenRequestLog::TYPE_AKUN,
            $reactivation->dtsen_account_request_id,
            'reaktivasi_' . $reactivation->status,
            $reactivation->catatan
        );

        return back()->with('status', 'Permintaan aktivasi ulang telah diputuskan.');
    }
}
