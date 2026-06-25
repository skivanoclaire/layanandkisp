<?php

namespace App\Http\Controllers\User\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpChangeRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpService;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * V4 — Perubahan / Perpanjangan Konfigurasi Endpoint — sisi pemohon.
 */
class ChangeRequestController extends Controller
{
    public function index()
    {
        $items = SplpChangeRequest::with('service')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.splp.change.index', compact('items'));
    }

    public function create()
    {
        return view('user.splp.change.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $user = auth()->user();
        $req = new SplpChangeRequest($data);
        $req->user_id = $user->id;
        $req->nama = $user->name;
        $req->nip = $user->nip;
        $req->email_pemohon = $user->email;
        $req->status = $isSubmit ? SplpChangeRequest::STATUS_DIAJUKAN : SplpChangeRequest::STATUS_DRAFT;
        $req->submitted_at = $isSubmit ? now() : null;
        $req->save();

        $this->handleUpload($request, $req);
        $req->save();

        SplpRequestLog::record(
            SplpChangeRequest::REQUEST_TYPE,
            $req->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit ? 'Permohonan perubahan/perpanjangan diajukan' : 'Draft permohonan dibuat'
        );

        if (!$isSubmit) {
            return redirect()->route('user.splp.change.edit', $req->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.splp.change.thanks', $req->ticket_no);
    }

    public function thanks(string $ticket)
    {
        $item = SplpChangeRequest::with('service')->where('ticket_no', $ticket)->where('user_id', auth()->id())->firstOrFail();

        return view('user.splp.change.thanks', compact('item'));
    }

    public function edit($id)
    {
        $item = SplpChangeRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diedit karena sudah diproses.');

        return view('user.splp.change.edit', array_merge(['item' => $item], $this->formData()));
    }

    public function update(Request $request, $id)
    {
        $item = SplpChangeRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $item->fill($data);
        if ($isSubmit) {
            $item->status = SplpChangeRequest::STATUS_DIAJUKAN;
            $item->submitted_at = $item->submitted_at ?: now();
        }
        $this->handleUpload($request, $item);
        $item->save();

        SplpRequestLog::record(
            SplpChangeRequest::REQUEST_TYPE,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan dilengkapi & diajukan' : 'Draft diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.splp.change.thanks', $item->ticket_no);
        }

        return redirect()->route('user.splp.change.index')->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SplpChangeRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa dihapus karena sudah diproses.');

        $item->delete();

        return redirect()->route('user.splp.change.index')->with('status', 'Permohonan dihapus.');
    }

    private function formData(): array
    {
        $services = SplpService::aktif()->with('opdPemilik')->orderBy('nama_layanan')->get();
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return compact('services', 'unitKerjaList');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'no_hp' => ['required', 'string', 'max:30'],
            'splp_service_id' => ['required', Rule::exists('splp_services', 'id')->where('status', 'aktif')],
            'kategori' => ['required', Rule::in(['perubahan', 'perpanjangan'])],
            'jenis_perubahan' => ['required', Rule::in(['minor', 'signifikan'])],
            'detail_perubahan' => ['required', 'string'],
            'perpanjangan_sampai' => ['nullable', 'required_if:kategori,perpanjangan', 'date', 'after:today'],
            'surat' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'consent_true' => ['accepted'],
        ], [
            'splp_service_id.required' => 'Layanan terdaftar wajib dipilih.',
            'perpanjangan_sampai.required_if' => 'Tanggal perpanjangan wajib diisi untuk kategori perpanjangan.',
            'consent_true.accepted' => 'Anda harus menyetujui ketentuan layanan.',
        ]);
    }

    private function handleUpload(Request $request, SplpChangeRequest $req): void
    {
        if ($request->hasFile('surat')) {
            $req->surat_path = $request->file('surat')->storeAs(
                'splp-docs',
                'SURAT_' . $req->ticket_no . '_' . time() . '.' . $request->file('surat')->extension(),
                'public'
            );
        }
    }
}
