<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Database;
use App\Models\Framework;
use App\Models\ProgrammingLanguage;
use App\Models\ServerLocation;
use App\Models\SubdomainDataUpdateRequest;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubdomainDataUpdateController extends Controller
{
    /**
     * Daftar permohonan pembaruan data milik pengguna.
     */
    public function index()
    {
        $requests = SubdomainDataUpdateRequest::with(['webMonitor', 'processedBy'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.subdomain.data-update.index', compact('requests'));
    }

    /**
     * Form pengajuan. Tanpa ?web_monitor_id menampilkan pemilih subdomain,
     * dengan ?web_monitor_id menampilkan form ter-prefill.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $subdomains = $this->ownedSubdomains($user);

        if ($subdomains->isEmpty()) {
            return redirect()->route('user.subdomain.data-update.index')
                ->with('error', 'Belum ada subdomain milik unit kerja Anda yang dapat diperbarui. Pastikan akun Anda terhubung ke unit kerja yang benar.');
        }

        $webMonitor = null;
        if ($request->filled('web_monitor_id')) {
            $webMonitor = $subdomains->firstWhere('id', (int) $request->web_monitor_id);
            if (!$webMonitor) {
                return redirect()->route('user.subdomain.data-update.create')
                    ->with('error', 'Subdomain yang dipilih tidak valid atau bukan milik unit kerja Anda.');
            }

            // Cegah permohonan ganda yang masih berjalan.
            if ($this->hasActiveRequest($webMonitor->id)) {
                return redirect()->route('user.subdomain.data-update.index')
                    ->with('error', 'Sudah ada permohonan pembaruan data yang sedang diproses untuk subdomain ini.');
            }
        }

        return view('user.subdomain.data-update.create', array_merge(
            compact('subdomains', 'webMonitor'),
            $this->lookups()
        ));
    }

    /**
     * Simpan permohonan baru (status pending).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate(array_merge(
            ['web_monitor_id' => 'required|exists:web_monitors,id'],
            $this->fieldRules(),
            [
                'reason' => 'nullable|string|max:1000',
                'proposed_decommission' => 'nullable|in:0,1',
            ],
        ));

        $webMonitor = $this->ownedSubdomains($user)->firstWhere('id', (int) $request->web_monitor_id);
        if (!$webMonitor) {
            return back()->withInput()->with('error', 'Subdomain yang dipilih tidak valid atau bukan milik unit kerja Anda.');
        }

        if ($this->hasActiveRequest($webMonitor->id)) {
            return redirect()->route('user.subdomain.data-update.index')
                ->with('error', 'Sudah ada permohonan pembaruan data yang sedang diproses untuk subdomain ini.');
        }

        $proposed = $request->only(SubdomainDataUpdateRequest::EDITABLE_FIELDS);
        $original = collect(SubdomainDataUpdateRequest::EDITABLE_FIELDS)
            ->mapWithKeys(fn ($f) => [$f => $webMonitor->{$f}])
            ->all();

        $dataRequest = SubdomainDataUpdateRequest::create([
            'user_id' => Auth::id(),
            'web_monitor_id' => $webMonitor->id,
            'proposed_data' => $proposed,
            'proposed_decommission' => $request->filled('proposed_decommission') ? (int) $request->proposed_decommission : null,
            'original_data' => $original,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('user.subdomain.data-update.show', $dataRequest->id)
            ->with('success', 'Permohonan pembaruan data berhasil diajukan dengan nomor tiket: ' . $dataRequest->ticket_number);
    }

    /**
     * Detail permohonan milik pengguna.
     */
    public function show($id)
    {
        $dataRequest = SubdomainDataUpdateRequest::with(['webMonitor', 'processedBy'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.subdomain.data-update.show', array_merge(
            ['request' => $dataRequest],
            $this->lookups()
        ));
    }

    /**
     * Form edit — hanya bila status pending/revisi.
     */
    public function edit($id)
    {
        $dataRequest = SubdomainDataUpdateRequest::with('webMonitor')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$dataRequest->isEditable()) {
            return redirect()->route('user.subdomain.data-update.show', $dataRequest->id)
                ->with('error', 'Permohonan ini sudah diproses dan tidak dapat diubah lagi.');
        }

        return view('user.subdomain.data-update.edit', array_merge(
            ['request' => $dataRequest],
            $this->lookups()
        ));
    }

    /**
     * Simpan perubahan & ajukan ulang (status kembali pending).
     */
    public function update(Request $request, $id)
    {
        $dataRequest = SubdomainDataUpdateRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$dataRequest->isEditable()) {
            return redirect()->route('user.subdomain.data-update.show', $dataRequest->id)
                ->with('error', 'Permohonan ini sudah diproses dan tidak dapat diubah lagi.');
        }

        $request->validate(array_merge(
            $this->fieldRules(),
            [
                'reason' => 'nullable|string|max:1000',
                'proposed_decommission' => 'nullable|in:0,1',
            ],
        ));

        $dataRequest->update([
            'proposed_data' => $request->only(SubdomainDataUpdateRequest::EDITABLE_FIELDS),
            'proposed_decommission' => $request->filled('proposed_decommission') ? (int) $request->proposed_decommission : null,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('user.subdomain.data-update.show', $dataRequest->id)
            ->with('success', 'Permohonan pembaruan data berhasil diperbarui dan diajukan ulang.');
    }

    /**
     * Unduh berita acara milik permohonan sendiri.
     */
    public function downloadBeritaAcara($id)
    {
        $dataRequest = SubdomainDataUpdateRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$dataRequest->file_berita_acara) {
            return back()->with('error', 'Berita acara belum tersedia.');
        }

        $filePath = storage_path('app/public/' . $dataRequest->file_berita_acara);

        if (!file_exists($filePath)) {
            return back()->with('error', 'Berkas berita acara tidak ditemukan.');
        }

        $extension = pathinfo($dataRequest->file_berita_acara, PATHINFO_EXTENSION);
        $filename = 'Berita_Acara_' . $dataRequest->ticket_number . '.' . $extension;

        return response()->download($filePath, $filename);
    }

    /**
     * Subdomain milik unit kerja pengguna (semua status — termasuk non-aktif,
     * agar datanya tetap bisa diperbarui sebagai bahan pertimbangan).
     */
    private function ownedSubdomains($user)
    {
        if (!$user || !$user->unit_kerja_id) {
            return collect();
        }

        return WebMonitor::where('instansi_id', $user->unit_kerja_id)
            ->whereNotNull('subdomain')
            ->where('subdomain', '!=', '')
            ->orderBy('subdomain')
            ->get();
    }

    private function hasActiveRequest(int $webMonitorId): bool
    {
        return SubdomainDataUpdateRequest::where('web_monitor_id', $webMonitorId)
            ->whereIn('status', ['pending', 'revisi'])
            ->exists();
    }

    /**
     * Aturan validasi 18 field (selaras WebMonitorController@update).
     */
    private function fieldRules(): array
    {
        return [
            'nama_aplikasi' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latar_belakang' => 'nullable|string',
            'manfaat_aplikasi' => 'nullable|string',
            'tahun_pembuatan' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'developer' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'programming_language_id' => 'nullable|exists:programming_languages,id',
            'programming_language_version' => 'nullable|string|max:50',
            'framework_id' => 'nullable|exists:frameworks,id',
            'framework_version' => 'nullable|string|max:50',
            'database_id' => 'nullable|exists:databases,id',
            'database_version' => 'nullable|string|max:50',
            'frontend_tech' => 'nullable|string|max:200',
            'server_ownership' => 'nullable|in:Provinsi Kaltara,Pihak Ketiga',
            'server_owner_name' => 'nullable|string|max:200',
            'server_location_id' => 'nullable|exists:server_locations,id',
        ];
    }

    /**
     * Lookup dropdown (selaras WebMonitorController@edit).
     */
    private function lookups(): array
    {
        return [
            'programmingLanguages' => ProgrammingLanguage::orderBy('name')->get(),
            'frameworks' => Framework::orderBy('name')->get()->unique('name')->values(),
            'databases' => Database::orderBy('name')->get(),
            'serverLocations' => ServerLocation::orderBy('name')->get(),
        ];
    }
}
