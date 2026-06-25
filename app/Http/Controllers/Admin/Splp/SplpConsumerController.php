<?php

namespace App\Http\Controllers\Admin\Splp;

use App\Http\Controllers\Controller;
use App\Models\SplpConsumer;
use App\Models\SplpService;
use App\Models\SplpServiceLog;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SplpConsumerController extends Controller
{
    public function index(Request $request)
    {
        $query = SplpConsumer::with(['service', 'instansi'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('splp_service_id')) {
            $query->where('splp_service_id', $request->splp_service_id);
        }
        if ($request->filled('q')) {
            $query->where('nama_konsumen', 'like', '%' . $request->q . '%');
        }

        $consumers = $query->paginate(25)->withQueryString();
        $services = SplpService::orderBy('nama_layanan')->get(['id', 'nama_layanan']);

        return view('admin.splp.consumers.index', compact('consumers', 'services'));
    }

    public function create()
    {
        $services = SplpService::orderBy('nama_layanan')->get();
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();

        return view('admin.splp.consumers.create', compact('services', 'unitKerjaList'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $consumer = SplpConsumer::create($data);

        SplpServiceLog::record([
            'splp_service_id' => $consumer->splp_service_id,
            'splp_consumer_id' => $consumer->id,
            'action' => 'consumer_created',
            'config_baru' => $consumer->only(array_keys($data)),
            'keterangan' => "Konsumen {$consumer->nama_konsumen} dibuat via Master Data",
        ]);

        return redirect()->route('admin.splp.consumers.index')
            ->with('success', "Konsumen {$consumer->nama_konsumen} berhasil ditambahkan.");
    }

    public function edit(SplpConsumer $consumer)
    {
        $services = SplpService::orderBy('nama_layanan')->get();
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();

        return view('admin.splp.consumers.edit', compact('consumer', 'services', 'unitKerjaList'));
    }

    public function update(Request $request, SplpConsumer $consumer)
    {
        $data = $this->validateData($request);

        $old = $consumer->only(array_keys($data));
        $consumer->update($data);

        SplpServiceLog::record([
            'splp_service_id' => $consumer->splp_service_id,
            'splp_consumer_id' => $consumer->id,
            'action' => 'consumer_updated',
            'config_lama' => $old,
            'config_baru' => $consumer->only(array_keys($data)),
            'keterangan' => "Konsumen {$consumer->nama_konsumen} diperbarui via Master Data",
        ]);

        return redirect()->route('admin.splp.consumers.index')
            ->with('success', "Konsumen {$consumer->nama_konsumen} berhasil diperbarui.");
    }

    public function destroy(SplpConsumer $consumer)
    {
        $nama = $consumer->nama_konsumen;
        $consumer->delete();

        return redirect()->route('admin.splp.consumers.index')
            ->with('success', "Konsumen {$nama} berhasil dihapus.");
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'splp_service_id' => ['required', 'exists:splp_services,id'],
            'instansi_id' => ['nullable', 'exists:unit_kerjas,id'],
            'nama_konsumen' => ['required', 'string', 'max:200'],
            'credential_type' => ['required', Rule::in(SplpConsumer::CREDENTIAL_TYPES)],
            'credential_ref' => ['nullable', 'string', 'max:200'],
            'acl' => ['nullable', 'string', 'max:200'],
            'rate_limit' => ['nullable', 'string', 'max:100'],
            'ip_whitelist' => ['nullable', 'string'],
            'environment' => ['required', Rule::in(SplpService::ENVIRONMENTS)],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(SplpConsumer::STATUSES)],
        ]);
    }
}
