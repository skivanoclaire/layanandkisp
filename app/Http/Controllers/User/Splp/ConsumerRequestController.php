<?php

namespace App\Http\Controllers\User\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpConsumerRequest;
use App\Models\SplpRequestLog;
use App\Models\SplpService;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * V2 — Pendaftaran Akses Konsumen Layanan (Service Consumer) — sisi pemohon.
 */
class ConsumerRequestController extends Controller
{
    public function index()
    {
        $items = SplpConsumerRequest::with('service')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.splp.consumer.index', compact('items'));
    }

    public function create()
    {
        return view('user.splp.consumer.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $user = auth()->user();
        $req = new SplpConsumerRequest($data);
        $req->user_id = $user->id;
        $req->nama = $user->name;
        $req->nip = $user->nip;
        $req->email_pemohon = $user->email;
        $req->instansi_konsumen_id = $data['instansi_konsumen_id'] ?? $user->unit_kerja_id;
        $req->status = $isSubmit ? SplpConsumerRequest::STATUS_DIAJUKAN : SplpConsumerRequest::STATUS_DRAFT;
        $req->submitted_at = $isSubmit ? now() : null;
        $req->save();

        $this->handleUploads($request, $req);
        $req->save();

        SplpRequestLog::record(
            SplpConsumerRequest::REQUEST_TYPE,
            $req->id,
            $isSubmit ? 'created_submitted' : 'created_draft',
            $isSubmit ? 'Permohonan akses konsumen diajukan' : 'Draft permohonan dibuat'
        );

        if (!$isSubmit) {
            return redirect()->route('user.splp.consumer.edit', $req->id)
                ->with('status', 'Draft disimpan. Anda dapat melengkapi & mengajukan kapan saja.');
        }

        return redirect()->route('user.splp.consumer.thanks', $req->ticket_no);
    }

    public function thanks(string $ticket)
    {
        $item = SplpConsumerRequest::with('service')
            ->where('ticket_no', $ticket)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('user.splp.consumer.thanks', compact('item'));
    }

    public function edit($id)
    {
        $item = SplpConsumerRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diedit karena sudah diproses.');

        return view('user.splp.consumer.edit', array_merge(['item' => $item], $this->formData()));
    }

    public function update(Request $request, $id)
    {
        $item = SplpConsumerRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa diubah karena sudah diproses.');

        $data = $this->validateData($request);
        $isSubmit = $request->input('action') === 'submit';

        $item->fill($data);
        $item->instansi_konsumen_id = $data['instansi_konsumen_id'] ?? auth()->user()->unit_kerja_id;
        if ($isSubmit) {
            $item->status = SplpConsumerRequest::STATUS_DIAJUKAN;
            $item->submitted_at = $item->submitted_at ?: now();
        }
        $this->handleUploads($request, $item);
        $item->save();

        SplpRequestLog::record(
            SplpConsumerRequest::REQUEST_TYPE,
            $item->id,
            $isSubmit ? 'updated_submitted' : 'updated_draft',
            $isSubmit ? 'Permohonan dilengkapi & diajukan' : 'Draft diperbarui'
        );

        if ($isSubmit) {
            return redirect()->route('user.splp.consumer.thanks', $item->ticket_no);
        }

        return redirect()->route('user.splp.consumer.index')->with('status', 'Draft berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SplpConsumerRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        abort_unless($item->isEditableByOwner(), 403, 'Permohonan tidak bisa dihapus karena sudah diproses.');

        $item->delete();

        return redirect()->route('user.splp.consumer.index')->with('status', 'Permohonan dihapus.');
    }

    /**
     * Data untuk form: katalog layanan aktif (produksi) + daftar instansi.
     */
    private function formData(): array
    {
        $services = SplpService::selectableForConsumer()
            ->with('opdPemilik')
            ->orderBy('nama_layanan')
            ->get();

        $unitKerjaList = UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();

        return compact('services', 'unitKerjaList');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'no_hp' => ['required', 'string', 'max:30'],
            'splp_service_id' => ['required', Rule::exists('splp_services', 'id')->where(function ($q) {
                $q->where('status', 'aktif')->where('environment', 'produksi');
            })],
            'instansi_konsumen_id' => ['nullable', 'exists:unit_kerjas,id'],
            'is_eksternal' => ['nullable', 'boolean'],
            'ip_domain' => ['required', 'string', 'max:1000'],
            'estimasi_volume' => ['nullable', 'string', 'max:100'],
            'volume_satuan' => ['nullable', Rule::in(['per_hari', 'per_bulan'])],
            'credential_pref' => ['required', Rule::in(['mengikuti_layanan', 'apikey', 'oauth2'])],
            'tujuan_penggunaan' => ['required', 'string'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'pks' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'hasil_uji' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
            'consent_true' => ['accepted'],
        ], [
            'splp_service_id.required' => 'Layanan tujuan wajib dipilih.',
            'splp_service_id.exists' => 'Layanan tujuan tidak valid atau tidak aktif.',
            'ip_domain.required' => 'IP/Domain sumber akses wajib diisi.',
            'tujuan_penggunaan.required' => 'Tujuan penggunaan wajib diisi.',
            'consent_true.accepted' => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
        ]);
    }

    private function handleUploads(Request $request, SplpConsumerRequest $req): void
    {
        $map = [
            'surat_permohonan' => 'surat_permohonan_path',
            'pks' => 'pks_path',
            'hasil_uji' => 'hasil_uji_path',
        ];

        foreach ($map as $input => $column) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $req->{$column} = $file->storeAs(
                    'splp-docs',
                    strtoupper($input) . '_' . $req->ticket_no . '_' . time() . '.' . $file->extension(),
                    'public'
                );
            }
        }
    }
}
