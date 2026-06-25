<?php

namespace App\Http\Controllers\User\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpConsumer;
use App\Models\SplpDeactivationRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpService;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * V5 — Penonaktifan / Pencabutan Endpoint — sisi pemohon.
 */
class DeactivationRequestController extends Controller
{
    public function index()
    {
        $items = SplpDeactivationRequest::with(['service', 'consumer'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.splp.deactivation.index', compact('items'));
    }

    public function create()
    {
        return view('user.splp.deactivation.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $this->normalizeTarget($data);

        $user = auth()->user();
        $req = new SplpDeactivationRequest($data);
        $req->user_id = $user->id;
        $req->nama = $user->name;
        $req->nip = $user->nip;
        $req->email_pemohon = $user->email;
        $req->status = $isSubmit ? SplpDeactivationRequest::STATUS_DIAJUKAN : SplpDeactivationRequest::STATUS_DRAFT;
        $req->submitted_at = $isSubmit ? now() : null;
        $req->save();

        $this->handleUpload($request, $req);
        $req->save();

        SplpRequestLog::record(
            SplpDeactivationRequest::REQUEST_TYPE,
            $req->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit ? 'Permohonan penonaktifan/pencabutan diajukan' : 'Draft permohonan dibuat'
        );

        if (!$isSubmit) {
            return redirect()->route('user.splp.deactivation.edit', $req->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.splp.deactivation.thanks', $req->ticket_no);
    }

    public function thanks(string $ticket)
    {
        $item = SplpDeactivationRequest::with(['service', 'consumer'])
            ->where('ticket_no', $ticket)->where('user_id', auth()->id())->firstOrFail();

        return view('user.splp.deactivation.thanks', compact('item'));
    }

    public function edit($id)
    {
        $item = SplpDeactivationRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diedit karena sudah diproses.');

        return view('user.splp.deactivation.edit', array_merge(['item' => $item], $this->formData()));
    }

    public function update(Request $request, $id)
    {
        $item = SplpDeactivationRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';
        $this->normalizeTarget($data);

        $item->fill($data);
        if ($isSubmit) {
            $item->status = SplpDeactivationRequest::STATUS_DIAJUKAN;
            $item->submitted_at = $item->submitted_at ?: now();
        }
        $this->handleUpload($request, $item);
        $item->save();

        SplpRequestLog::record(
            SplpDeactivationRequest::REQUEST_TYPE,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan dilengkapi & diajukan' : 'Draft diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.splp.deactivation.thanks', $item->ticket_no);
        }

        return redirect()->route('user.splp.deactivation.index')->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SplpDeactivationRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa dihapus karena sudah diproses.');

        $item->delete();

        return redirect()->route('user.splp.deactivation.index')->with('status', 'Permohonan dihapus.');
    }

    private function formData(): array
    {
        $services = SplpService::whereIn('status', ['aktif', 'nonaktif'])->with('opdPemilik')->orderBy('nama_layanan')->get();
        $consumers = SplpConsumer::whereIn('status', ['aktif', 'nonaktif'])->with('service')->orderBy('nama_konsumen')->get();
        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return compact('services', 'consumers', 'unitKerjaList');
    }

    /**
     * Kosongkan FK yang tidak relevan dengan target_type terpilih.
     */
    private function normalizeTarget(array &$data): void
    {
        if (($data['target_type'] ?? 'service') === 'service') {
            $data['splp_consumer_id'] = null;
        } else {
            $data['splp_service_id'] = null;
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'no_hp' => ['required', 'string', 'max:30'],
            'target_type' => ['required', Rule::in(['service', 'consumer'])],
            'splp_service_id' => ['nullable', 'required_if:target_type,service', 'exists:splp_services,id'],
            'splp_consumer_id' => ['nullable', 'required_if:target_type,consumer', 'exists:splp_consumers,id'],
            'jenis_tindakan' => ['required', Rule::in(['nonaktif', 'cabut'])],
            'alasan' => ['required', 'string'],
            'is_darurat' => ['nullable', 'boolean'],
            'surat' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'consent_true' => ['accepted'],
        ], [
            'splp_service_id.required_if' => 'Layanan target wajib dipilih.',
            'splp_consumer_id.required_if' => 'Konsumen target wajib dipilih.',
            'alasan.required' => 'Alasan penonaktifan/pencabutan wajib diisi.',
            'consent_true.accepted' => 'Anda harus menyetujui ketentuan layanan.',
        ]);
    }

    private function handleUpload(Request $request, SplpDeactivationRequest $req): void
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
