<?php

namespace App\Http\Controllers\User\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpProviderRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpService;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * V1 — Pendaftaran Endpoint Penyedia Layanan (Service Provider) — sisi pemohon.
 */
class ProviderRequestController extends Controller
{
    public function index()
    {
        $items = SplpProviderRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.splp.provider.index', compact('items'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.splp.provider.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $user = auth()->user();
        $req = new SplpProviderRequest($data);
        $req->user_id = $user->id;
        $req->nama = $user->name;
        $req->nip = $user->nip;
        $req->email_pemohon = $user->email;
        $req->status = $isSubmit ? SplpProviderRequest::STATUS_DIAJUKAN : SplpProviderRequest::STATUS_DRAFT;
        $req->submitted_at = $isSubmit ? now() : null;
        $req->save();

        $this->handleUploads($request, $req);
        $req->save();

        SplpRequestLog::record(
            SplpProviderRequest::REQUEST_TYPE,
            $req->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit ? 'Permohonan pendaftaran endpoint penyedia diajukan' : 'Draft permohonan dibuat'
        );

        if (!$isSubmit) {
            return redirect()->route('user.splp.provider.edit', $req->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.splp.provider.thanks', $req->ticket_no);
    }

    public function thanks(string $ticket)
    {
        $item = SplpProviderRequest::where('ticket_no', $ticket)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('user.splp.provider.thanks', compact('item'));
    }

    public function edit($id)
    {
        $item = SplpProviderRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diedit karena sudah diproses.');

        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return view('user.splp.provider.edit', compact('item', 'unitKerjaList'));
    }

    public function update(Request $request, $id)
    {
        $item = SplpProviderRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $item->fill($data);
        if ($isSubmit) {
            $item->status = SplpProviderRequest::STATUS_DIAJUKAN;
            $item->submitted_at = $item->submitted_at ?: now();
        }
        $this->handleUploads($request, $item);
        $item->save();

        SplpRequestLog::record(
            SplpProviderRequest::REQUEST_TYPE,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan dilengkapi & diajukan' : 'Draft diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.splp.provider.thanks', $item->ticket_no);
        }

        return redirect()->route('user.splp.provider.index')
            ->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SplpProviderRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa dihapus karena sudah diproses.');

        $item->delete();

        return redirect()->route('user.splp.provider.index')->with('status', 'Permohonan dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_layanan' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'backend_url' => ['required', 'url', 'max:500'],
            'route_path' => ['nullable', 'string', 'max:255'],
            'auth_type' => ['required', Rule::in(SplpService::AUTH_TYPES)],
            'klasifikasi_data' => ['required', Rule::in(SplpService::KLASIFIKASI)],
            'dc_confidentiality' => ['nullable', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_integrity' => ['nullable', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_availability' => ['nullable', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'openapi_doc' => ['nullable', 'file', 'mimes:pdf,doc,docx,json,yaml,yml,zip', 'max:10240'],
            'consent_true' => ['accepted'],
        ], [
            'backend_url.url' => 'URL backend tidak valid (sertakan http:// atau https://).',
            'consent_true.accepted' => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
        ]);
    }

    private function handleUploads(Request $request, SplpProviderRequest $req): void
    {
        if ($request->hasFile('surat_permohonan')) {
            $req->surat_permohonan_path = $request->file('surat_permohonan')
                ->storeAs('splp-docs', 'SURAT_' . $req->ticket_no . '_' . time() . '.' . $request->file('surat_permohonan')->extension(), 'public');
        }
        if ($request->hasFile('openapi_doc')) {
            $req->openapi_doc_path = $request->file('openapi_doc')
                ->storeAs('splp-docs', 'OPENAPI_' . $req->ticket_no . '_' . time() . '.' . $request->file('openapi_doc')->extension(), 'public');
        }
    }
}
