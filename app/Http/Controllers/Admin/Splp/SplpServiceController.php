<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpService;
use App\Models\SplpServiceLog;
use App\Models\UnitKerja;
use App\Services\SplpWso2Importer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SplpServiceController extends Controller
{
    /**
     * Impor ekspor API dari SPLP (WSO2) lalu prefill form tambah layanan untuk ditinjau admin.
     */
    public function import(Request $request, SplpWso2Importer $importer)
    {
        $request->validate([
            'splp_export' => ['required', 'file', 'mimes:zip,yaml,yml,txt', 'max:20480'],
        ], [
            'splp_export.required' => 'Pilih file ekspor SPLP (.zip atau api.yaml).',
            'splp_export.mimes' => 'File harus berupa .zip atau .yaml.',
        ]);

        try {
            $file = $request->file('splp_export');
            $result = $importer->import($file->getRealPath(), $file->getClientOriginalName());
        } catch (\Throwable $e) {
            return back()->with('import_error', 'Gagal mengimpor: ' . $e->getMessage());
        }

        $notice = "Data dari ekspor SPLP berhasil dibaca ({$result['info']['nama']} {$result['info']['versi']}, "
            . "{$result['info']['jumlah_endpoint']} endpoint). Tinjau & lengkapi OPD pemilik, lalu simpan.";

        return redirect()->route('admin.splp.services.create')
            ->withInput($result['prefill'])
            ->with('import_notice', $notice)
            ->with('import_warnings', $result['warnings']);
    }

    public function index(Request $request)
    {
        $query = SplpService::with('opdPemilik')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('environment')) {
            $query->where('environment', $request->environment);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_layanan', 'like', "%{$q}%")
                    ->orWhere('kode_layanan', 'like', "%{$q}%");
            });
        }

        $services = $query->paginate(25)->withQueryString();

        return view('admin.splp.services.index', compact('services'));
    }

    public function create()
    {
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();

        return view('admin.splp.services.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['kode_layanan'] = $data['kode_layanan'] ?: SplpService::nextKode();

        $service = SplpService::create($data);

        SplpServiceLog::record([
            'splp_service_id' => $service->id,
            'action' => 'service_created',
            'config_baru' => $service->only(array_keys($data)),
            'keterangan' => 'Layanan SPLP dibuat manual via Master Data',
        ]);

        return redirect()->route('admin.splp.services.index')
            ->with('success', "Layanan {$service->nama_layanan} berhasil ditambahkan.");
    }

    public function show(SplpService $service)
    {
        $service->load(['opdPemilik', 'consumers.instansi', 'logs.actor']);

        return view('admin.splp.services.show', compact('service'));
    }

    public function edit(SplpService $service)
    {
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();

        return view('admin.splp.services.edit', compact('service', 'unitKerjaList'));
    }

    public function update(Request $request, SplpService $service)
    {
        $data = $this->validateData($request, $service->id);

        $old = $service->only(array_keys($data));
        $service->update($data);

        SplpServiceLog::record([
            'splp_service_id' => $service->id,
            'action' => 'service_updated',
            'config_lama' => $old,
            'config_baru' => $service->only(array_keys($data)),
            'keterangan' => 'Konfigurasi layanan diperbarui via Master Data',
        ]);

        return redirect()->route('admin.splp.services.index')
            ->with('success', "Layanan {$service->nama_layanan} berhasil diperbarui.");
    }

    public function destroy(SplpService $service)
    {
        $nama = $service->nama_layanan;
        $service->delete();

        return redirect()->route('admin.splp.services.index')
            ->with('success', "Layanan {$nama} berhasil dihapus.");
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'kode_layanan' => [
                'nullable', 'string', 'max:50',
                Rule::unique('splp_services', 'kode_layanan')->ignore($ignoreId),
            ],
            'nama_layanan' => ['required', 'string', 'max:200'],
            'opd_pemilik_id' => ['nullable', 'exists:unit_kerjas,id'],
            'deskripsi' => ['nullable', 'string'],
            'backend_url' => ['nullable', 'string', 'max:500'],
            'route_path' => ['nullable', 'string', 'max:255'],
            'environment' => ['required', Rule::in(SplpService::ENVIRONMENTS)],
            'auth_type' => ['required', Rule::in(SplpService::AUTH_TYPES)],
            'klasifikasi_data' => ['required', Rule::in(SplpService::KLASIFIKASI)],
            'gateway_service_id' => ['nullable', 'string', 'max:100'],
            'gateway_route_id' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(SplpService::STATUSES)],
            'tgl_aktif' => ['nullable', 'date'],
        ]);
    }
}
