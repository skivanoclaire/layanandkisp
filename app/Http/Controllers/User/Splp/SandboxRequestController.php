<?php

namespace App\Http\Controllers\User\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpRequestLog;
use App\Models\SplpSandboxRequest;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

/**
 * V3 — Permohonan Uji Coba (Sandbox) — sisi pemohon.
 */
class SandboxRequestController extends Controller
{
    public function index()
    {
        $items = SplpSandboxRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.splp.sandbox.index', compact('items'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.splp.sandbox.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $user = auth()->user();
        $req = new SplpSandboxRequest($data);
        $req->user_id = $user->id;
        $req->nama = $user->name;
        $req->nip = $user->nip;
        $req->email_pemohon = $user->email;
        $req->status = $isSubmit ? SplpSandboxRequest::STATUS_DIAJUKAN : SplpSandboxRequest::STATUS_DRAFT;
        $req->submitted_at = $isSubmit ? now() : null;
        $req->save();

        if ($request->hasFile('spesifikasi_file')) {
            $req->spesifikasi_file_path = $request->file('spesifikasi_file')->storeAs(
                'splp-docs',
                'SPEC_' . $req->ticket_no . '_' . time() . '.' . $request->file('spesifikasi_file')->extension(),
                'public'
            );
            $req->save();
        }

        SplpRequestLog::record(
            SplpSandboxRequest::REQUEST_TYPE,
            $req->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit ? 'Permohonan uji coba sandbox diajukan' : 'Draft permohonan dibuat'
        );

        if (!$isSubmit) {
            return redirect()->route('user.splp.sandbox.edit', $req->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.splp.sandbox.thanks', $req->ticket_no);
    }

    public function thanks(string $ticket)
    {
        $item = SplpSandboxRequest::where('ticket_no', $ticket)->where('user_id', auth()->id())->firstOrFail();

        return view('user.splp.sandbox.thanks', compact('item'));
    }

    public function edit($id)
    {
        $item = SplpSandboxRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diedit karena sudah diproses.');

        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.splp.sandbox.edit', compact('item', 'unitKerjaList'));
    }

    public function update(Request $request, $id)
    {
        $item = SplpSandboxRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $item->fill($data);
        if ($isSubmit) {
            $item->status = SplpSandboxRequest::STATUS_DIAJUKAN;
            $item->submitted_at = $item->submitted_at ?: now();
        }
        if ($request->hasFile('spesifikasi_file')) {
            $item->spesifikasi_file_path = $request->file('spesifikasi_file')->storeAs(
                'splp-docs',
                'SPEC_' . $item->ticket_no . '_' . time() . '.' . $request->file('spesifikasi_file')->extension(),
                'public'
            );
        }
        $item->save();

        SplpRequestLog::record(
            SplpSandboxRequest::REQUEST_TYPE,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan dilengkapi & diajukan' : 'Draft diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.splp.sandbox.thanks', $item->ticket_no);
        }

        return redirect()->route('user.splp.sandbox.index')->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SplpSandboxRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa dihapus karena sudah diproses.');

        $item->delete();

        return redirect()->route('user.splp.sandbox.index')->with('status', 'Permohonan dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_layanan' => ['required', 'string', 'max:200'],
            'spesifikasi_draft' => ['required', 'string'],
            'masa_uji_hari' => ['required', 'integer', 'min:1', 'max:90'],
            'spesifikasi_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,json,yaml,yml,zip', 'max:10240'],
            'consent_true' => ['accepted'],
        ], [
            'masa_uji_hari.max' => 'Masa uji maksimal 90 hari.',
            'consent_true.accepted' => 'Anda harus menyetujui ketentuan uji coba sandbox.',
        ]);
    }
}
