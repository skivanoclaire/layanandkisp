<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SubdomainRequest;
use App\Models\SubdomainRequestLog;
use App\Models\UnitKerja;
use App\Models\ProgrammingLanguage;
use App\Models\Framework;
use App\Models\Database;
use App\Models\ServerLocation;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubdomainRequestController extends Controller
{
    public function index()
    {
        // daftar pengajuan milik user aktif
        $items = SubdomainRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.subdomain.index', compact('items'));
    }

    public function create()
    {
        // Check if user has NIP
        $user = auth()->user();
        if (empty($user->nip)) {
            return redirect()->route('user.subdomain.index')
                ->with('error', 'NIP Anda belum terdaftar. Silakan hubungi Administrator untuk memperbarui data NIP Anda.');
        }

        // Get master data for dropdowns
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();
        $programmingLanguages = ProgrammingLanguage::orderBy('name')->get();
        $databases = Database::orderBy('name')->get();
        $serverLocations = ServerLocation::orderBy('name')->get();

        return view('user.subdomain.create', compact(
            'unitKerjaList',
            'programmingLanguages',
            'databases',
            'serverLocations'
        ));
    }

    public function store(Request $r)
    {
        // Check if user has NIP
        $user = auth()->user();
        if (empty($user->nip)) {
            throw ValidationException::withMessages([
                'nip' => 'NIP Anda belum terdaftar. Silakan hubungi Administrator.'
            ]);
        }

        $data = $r->validate([
            // Informasi Pemohon
            'nama'                       => ['required', 'string', 'max:200'],
            'unit_kerja_id'              => ['required', 'exists:unit_kerjas,id'],
            'email_pemohon'              => ['required', 'email', 'max:200'],
            'no_hp'                      => ['required', 'string', 'max:30'],

            // Subdomain Details
            'subdomain_requested'        => [
                'required',
                'alpha_dash',
                'max:63',
                Rule::unique('subdomain_requests', 'subdomain_requested'),
            ],
            'server_location'            => ['required', Rule::in(['dkisp', 'external'])],
            'ip_address'                 => ['required_if:server_location,external', 'nullable', 'ip'],
            'ip_address_auto'            => ['nullable', 'string'],
            'jenis_website'              => ['required', 'string', 'max:100'],
            'description'                => ['nullable', 'string'],

            // Informasi Aplikasi
            'nama_aplikasi'              => ['required', 'string', 'max:200'],
            'latar_belakang'             => ['required', 'string'],
            'manfaat_aplikasi'           => ['required', 'string'],
            'tahun_pembuatan'            => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'developer'                  => ['required', 'string', 'max:200'],
            'contact_person'             => ['required', 'string', 'max:200'],
            'contact_phone'              => ['required', 'string', 'max:30'],

            // Tech Stack
            'programming_language_id'    => ['required'],
            'other_programming_language' => ['required_if:programming_language_id,other', 'nullable', 'string', 'max:100'],
            'programming_language_version' => ['nullable', 'string', 'max:50'],
            'framework_id'               => ['nullable'],
            'other_framework'            => ['nullable', 'string', 'max:100'],
            'framework_version'          => ['nullable', 'string', 'max:50'],
            'database_id'                => ['required', 'exists:databases,id'],
            'database_version'           => ['nullable', 'string', 'max:50'],
            'frontend_tech'              => ['nullable', 'string', 'max:200'],

            // Backup & Maintenance
            'backup_frequency'           => ['required', Rule::in(['Realtime', 'Harian', 'Mingguan', 'Bulanan'])],
            'backup_retention'           => ['required', Rule::in(['7 hari', '14 hari', '30 hari', '60 hari', '90 hari', '180 hari', '365 hari'])],
            'has_bcp'                    => ['nullable', Rule::in(['Ya', 'Dalam Proses', 'Belum'])],
            'has_drp'                    => ['nullable', Rule::in(['Ya', 'Dalam Proses', 'Belum'])],
            'rto'                        => ['nullable', Rule::in(['< 1 jam', '1-4 jam', '4-24 jam', '1-3 hari', '> 3 hari'])],
            'maintenance_schedule'       => ['nullable', 'string'],
            'has_https'                  => ['nullable', 'boolean'],

            // Cloudflare
            'needs_ssl'                  => ['nullable', 'boolean'],
            'needs_proxy'                => ['nullable', 'boolean'],

            // Electronic System Category
            'esc_answers'                => ['required', 'array', 'size:10'],
            'esc_answers.1_1'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_2'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_3'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_4'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_5'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_6'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_7'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_8'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_9'            => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_answers.1_10'           => ['required', Rule::in(['A', 'B', 'C'])],
            'esc_document'               => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],

            // Data Classification
            'dc_data_name'               => ['required', 'string', 'max:255'],
            'dc_data_attributes'         => ['required', 'string'],
            'dc_confidentiality'         => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_integrity'               => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],
            'dc_availability'            => ['required', Rule::in(['Rendah', 'Sedang', 'Tinggi'])],

            // Consent
            'consent_true'               => ['accepted'],
        ], [
            'unit_kerja_id.required'           => 'Instansi wajib diisi.',
            'subdomain_requested.unique'       => 'Subdomain sudah pernah diajukan atau sudah terdaftar.',
            'subdomain_requested.alpha_dash'   => 'Subdomain hanya boleh berisi huruf, angka, dan tanda hubung.',
            'ip_address.ip'                    => 'Format IP Address tidak valid.',
            'other_programming_language.required_if' => 'Nama bahasa pemrograman wajib diisi jika memilih "Lainnya".',
            'backup_frequency.required'        => 'Frekuensi Backup wajib dipilih.',
            'backup_retention.required'        => 'Retensi Backup wajib dipilih.',
            'esc_answers.required'             => 'Kuesioner Kategori Sistem Elektronik wajib diisi.',
            'esc_answers.*.required'           => 'Semua pertanyaan kuesioner wajib dijawab.',
            'esc_answers.*.in'                 => 'Jawaban harus berupa A, B, atau C.',
            'esc_document.mimes'               => 'Dokumen pendukung harus berformat PDF, DOC, DOCX, XLS, atau XLSX.',
            'esc_document.max'                 => 'Ukuran dokumen pendukung maksimal 10MB.',
            'dc_data_name.required'            => 'Nama Data wajib diisi.',
            'dc_data_attributes.required'      => 'Atribut Data wajib diisi.',
            'dc_confidentiality.required'      => 'Tingkat Kerahasiaan wajib dipilih.',
            'dc_integrity.required'            => 'Tingkat Integritas wajib dipilih.',
            'dc_availability.required'         => 'Tingkat Ketersediaan wajib dipilih.',
            'consent_true.accepted'            => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
        ]);

        // Auto-assign IP if server location is DKISP
        if ($data['server_location'] === 'dkisp') {
            $autoIp = $this->getNextAvailableIP();
            if (!$autoIp) {
                throw ValidationException::withMessages([
                    'server_location' => 'Tidak ada IP tersedia di pool DKISP. Silakan gunakan server eksternal atau hubungi administrator.'
                ]);
            }
            $data['ip_address'] = $autoIp;
        }

        // Create subdomain request
        $req = new SubdomainRequest($data);
        $req->user_id      = auth()->id();
        $req->nama         = $user->name;
        $req->nip          = $user->nip; // Auto-filled from user, not from request input
        $req->email_pemohon = $user->email;
        $req->no_hp        = $user->phone ?? $data['no_hp'];
        $req->ticket_no    = SubdomainRequest::nextTicket();
        $req->submitted_at = now();
        $req->status       = 'menunggu';
        $req->save();

        // Handle ESC document upload
        if ($r->hasFile('esc_document')) {
            $file = $r->file('esc_document');
            $filename = 'ESC_' . $req->ticket_no . '_' . time() . '.' . $file->extension();
            $path = $file->storeAs('electronic-category-docs', $filename, 'public');
            $req->esc_document_path = $path;
        }

        // Set ESC data and calculate score
        $req->esc_answers = $data['esc_answers'];
        $req->updateEscScoreAndCategory();

        // Set DC data and calculate score
        $req->dc_data_name = $data['dc_data_name'];
        $req->dc_data_attributes = $data['dc_data_attributes'];
        $req->dc_confidentiality = $data['dc_confidentiality'];
        $req->dc_integrity = $data['dc_integrity'];
        $req->dc_availability = $data['dc_availability'];
        $req->updateDcScore();

        // Create activity log
        SubdomainRequestLog::create([
            'subdomain_request_id' => $req->id,
            'actor_id'             => auth()->id(),
            'action'               => 'created',
            'note'                 => 'Pengajuan subdomain dibuat',
        ]);

        return redirect()->route('user.subdomain.thanks', $req->ticket_no);
    }

    public function thanks(string $ticket)
    {
        return view('user.subdomain.thanks', ['ticket' => $ticket]);
    }

    public function edit($id)
    {
        $item = SubdomainRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($item->status !== 'menunggu') {
            abort(403, 'Permohonan tidak bisa diedit karena sudah diproses.');
        }

        // Get master data for dropdowns
        $unitKerjaList = UnitKerja::active()->orderBy('nama')->get();
        $programmingLanguages = ProgrammingLanguage::orderBy('name')->get();
        $frameworks = Framework::orderBy('name')->get();
        $databases = Database::orderBy('name')->get();
        $serverLocations = ServerLocation::orderBy('name')->get();

        return view('user.subdomain.edit', compact(
            'item',
            'unitKerjaList',
            'programmingLanguages',
            'frameworks',
            'databases',
            'serverLocations'
        ));
    }

    public function update(Request $r, $id)
    {
        $item = SubdomainRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($item->status !== 'menunggu') {
            throw ValidationException::withMessages([
                'status' => 'Permohonan tidak bisa diubah karena sudah diproses.'
            ]);
        }

        $data = $r->validate([
            // Informasi Pemohon
            'nama'                       => ['required', 'string', 'max:200'],
            'unit_kerja_id'              => ['required', 'exists:unit_kerjas,id'],
            'email_pemohon'              => ['required', 'email', 'max:200'],
            'no_hp'                      => ['required', 'string', 'max:30'],

            // Subdomain Details
            'subdomain_requested'        => [
                'required',
                'alpha_dash',
                'max:63',
                Rule::unique('subdomain_requests', 'subdomain_requested')->ignore($item->id),
            ],
            'ip_address'                 => ['required', 'ip'],
            'jenis_website'              => ['required', 'string', 'max:100'],
            'description'                => ['nullable', 'string'],

            // Informasi Aplikasi
            'nama_aplikasi'              => ['required', 'string', 'max:200'],
            'latar_belakang'             => ['required', 'string'],
            'manfaat_aplikasi'           => ['required', 'string'],
            'tahun_pembuatan'            => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'developer'                  => ['required', 'string', 'max:200'],
            'contact_person'             => ['required', 'string', 'max:200'],
            'contact_phone'              => ['required', 'string', 'max:30'],

            // Tech Stack
            'programming_language_id'    => ['required'],
            'other_programming_language' => ['required_if:programming_language_id,other', 'nullable', 'string', 'max:100'],
            'programming_language_version' => ['nullable', 'string', 'max:50'],
            'framework_id'               => ['nullable'],
            'other_framework'            => ['nullable', 'string', 'max:100'],
            'framework_version'          => ['nullable', 'string', 'max:50'],
            'database_id'                => ['required', 'exists:databases,id'],
            'database_version'           => ['nullable', 'string', 'max:50'],
            'frontend_tech'              => ['nullable', 'string', 'max:200'],

            // Backup & Maintenance
            'backup_frequency'           => ['required', Rule::in(['Realtime', 'Harian', 'Mingguan', 'Bulanan'])],
            'backup_retention'           => ['required', Rule::in(['7 hari', '14 hari', '30 hari', '60 hari', '90 hari', '180 hari', '365 hari'])],
            'has_bcp'                    => ['nullable', Rule::in(['Ya', 'Dalam Proses', 'Belum'])],
            'has_drp'                    => ['nullable', Rule::in(['Ya', 'Dalam Proses', 'Belum'])],
            'rto'                        => ['nullable', Rule::in(['< 1 jam', '1-4 jam', '4-24 jam', '1-3 hari', '> 3 hari'])],
            'maintenance_schedule'       => ['nullable', 'string'],
            'has_https'                  => ['nullable', 'boolean'],

            // Consent
            'consent_true'               => ['accepted'],
        ], [
            'subdomain_requested.unique'       => 'Subdomain sudah pernah diajukan atau sudah terdaftar.',
            'subdomain_requested.alpha_dash'   => 'Subdomain hanya boleh berisi huruf, angka, dan tanda hubung.',
            'ip_address.ip'                    => 'Format IP Address tidak valid.',
            'other_programming_language.required_if' => 'Nama bahasa pemrograman wajib diisi jika memilih "Lainnya".',
            'backup_frequency.required'        => 'Frekuensi Backup wajib dipilih.',
            'backup_retention.required'        => 'Retensi Backup wajib dipilih.',
            'consent_true.accepted'            => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
        ]);

        $item->fill($data);
        $item->save();

        return redirect()->route('user.subdomain.index')
            ->with('status', 'Permohonan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = SubdomainRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($item->status !== 'menunggu') {
            abort(403, 'Permohonan tidak bisa dihapus karena sudah diproses.');
        }

        $item->delete();

        return redirect()->route('user.subdomain.index')
            ->with('status', 'Permohonan dihapus.');
    }

    /**
     * Check subdomain availability (AJAX endpoint)
     */
    public function checkSubdomainAvailability(Request $request)
    {
        $subdomain = $request->input('subdomain');

        if (empty($subdomain)) {
            return response()->json([
                'available' => false,
                'message' => 'Subdomain tidak boleh kosong'
            ]);
        }

        // Validate alpha_dash format
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $subdomain)) {
            return response()->json([
                'available' => false,
                'message' => 'Subdomain hanya boleh berisi huruf, angka, dan tanda hubung.'
            ]);
        }

        // Check if subdomain exists in web_monitors table
        $existsInWebMonitors = WebMonitor::where('subdomain', $subdomain)->exists();

        // Check if subdomain already requested
        $existsInRequests = SubdomainRequest::where('subdomain_requested', $subdomain)->exists();

        if ($existsInWebMonitors) {
            return response()->json([
                'available' => false,
                'message' => 'Subdomain sudah terdaftar di sistem. Silakan gunakan subdomain lain.'
            ]);
        }

        if ($existsInRequests) {
            return response()->json([
                'available' => false,
                'message' => 'Subdomain sudah pernah diajukan. Silakan gunakan subdomain lain.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Subdomain tersedia!'
        ]);
    }

    /**
     * Get frameworks by programming language (AJAX endpoint)
     */
    public function getFrameworks(Request $request)
    {
        $programmingLanguageId = $request->input('programming_language_id');

        if (empty($programmingLanguageId)) {
            return response()->json([]);
        }

        $frameworks = Framework::where('programming_language_id', $programmingLanguageId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($frameworks);
    }

    /**
     * Get next available IP from DKISP pool (103.156.110.0/24)
     * Starting from highest (254) going down to lowest (1)
     */
    private function getNextAvailableIP()
    {
        $baseIp = '103.156.110.';
        $startRange = 254; // Start from highest
        $endRange = 1;     // Go down to lowest

        // Get all used IPs from web_monitors and subdomain_requests
        $usedFromMonitor = WebMonitor::whereNotNull('ip_address')
            ->where('ip_address', 'like', $baseIp . '%')
            ->pluck('ip_address')
            ->toArray();

        $usedFromRequests = SubdomainRequest::whereNotNull('ip_address')
            ->where('ip_address', 'like', $baseIp . '%')
            ->whereIn('status', ['menunggu', 'disetujui']) // Only count pending and approved
            ->pluck('ip_address')
            ->toArray();

        $allUsedIps = array_merge($usedFromMonitor, $usedFromRequests);
        $allUsedIps = array_unique($allUsedIps);

        // Find first available IP starting from 254 going down
        for ($i = $startRange; $i >= $endRange; $i--) {
            $testIp = $baseIp . $i;
            if (!in_array($testIp, $allUsedIps)) {
                return $testIp;
            }
        }

        return null; // No available IP
    }
}
