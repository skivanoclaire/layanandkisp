<?php
// app/Http/Controllers/User/EmailRequestController.php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmailRequest;
use App\Models\EmailRequestLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class EmailRequestController extends Controller
{
    public function index()
    {
        // daftar pengajuan milik user aktif
        $items = \App\Models\EmailRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.email.index', compact('items'));
    }

    /**
     * Formulir email pribadi (satu NIP = satu akun pribadi).
     */
    public function create()
    {
        return $this->renderCreate(EmailRequest::JENIS_PRIBADI);
    }

    /**
     * Formulir email unit kerja / unit organisasi (bidang, sekretariat).
     * Query ?tipe=unit_kerja|unit_organisasi menentukan tab yang aktif.
     * NIP boleh sama dengan permohonan lain, karena yang dicatat adalah unit tujuannya.
     */
    public function createUnit(Request $r)
    {
        $tipe = $r->query('tipe') === EmailRequest::JENIS_UNIT_ORGANISASI
            ? EmailRequest::JENIS_UNIT_ORGANISASI
            : EmailRequest::JENIS_UNIT_KERJA;

        return $this->renderCreate($tipe);
    }

    private function renderCreate(string $jenis)
    {
        // Check if user has NIP
        $user = auth()->user();
        if (empty($user->nip)) {
            return redirect()->route('user.email.index')
                ->with('error', 'NIP Anda belum terdaftar. Silakan hubungi Administrator untuk memperbarui data NIP Anda.');
        }

        // Get active unit kerja for dropdown
        $unitKerjaList = \App\Models\UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();
        $unitOrganisasiList = \App\Models\UnitOrganisasi::with('unitKerja')->active()->orderBy('nama')->get();

        return view('user.email.create', compact('jenis', 'unitKerjaList', 'unitOrganisasiList'));
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

        $jenis = (string) $r->input('jenis', EmailRequest::JENIS_PRIBADI);
        if (!in_array($jenis, EmailRequest::jenisOptions(), true)) {
            throw ValidationException::withMessages(['jenis' => 'Jenis permohonan tidak valid.']);
        }
        $isUnit = $jenis !== EmailRequest::JENIS_PRIBADI;

        // Satu NIP hanya boleh punya satu akun email PRIBADI.
        // Permohonan email unit tidak dihitung di sini, sehingga NIP yang sama tetap bisa mengajukan unit.
        if (!$isUnit) {
            $nipExists = \App\Models\EmailRequest::where('nip', $user->nip)
                ->where('jenis', EmailRequest::JENIS_PRIBADI)
                ->whereIn('status', ['proses', 'selesai'])
                ->exists();

            $nipInMasterData = \App\Models\EmailAccount::where('nip', $user->nip)
                ->where('jenis', EmailRequest::JENIS_PRIBADI)
                ->exists();

            if ($nipExists || $nipInMasterData) {
                throw ValidationException::withMessages([
                    'nip' => 'NIP Anda sudah terdaftar untuk layanan email. Satu NIP hanya dapat memiliki satu akun email.'
                ]);
            }
        }

        // Tujuan unit (divalidasi terhadap master data, bukan input teks bebas)
        $unitKerja = null;
        $unitOrganisasi = null;
        $instansi = null;
        if ($jenis === EmailRequest::JENIS_UNIT_KERJA) {
            $r->validate(['unit_kerja_id' => ['required', 'integer']], [
                'unit_kerja_id.required' => 'Silakan pilih Unit Kerja.',
            ]);
            $unitKerja = \App\Models\UnitKerja::forLayananDigital()->active()->find($r->input('unit_kerja_id'));
            if (!$unitKerja) {
                throw ValidationException::withMessages(['unit_kerja_id' => 'Unit Kerja tidak valid.']);
            }
            $instansi = $unitKerja->nama;
        } elseif ($jenis === EmailRequest::JENIS_UNIT_ORGANISASI) {
            $r->validate(['unit_organisasi_id' => ['required', 'integer']], [
                'unit_organisasi_id.required' => 'Silakan pilih Unit Organisasi.',
            ]);
            $unitOrganisasi = \App\Models\UnitOrganisasi::with('unitKerja')->active()->find($r->input('unit_organisasi_id'));
            if (!$unitOrganisasi || !$unitOrganisasi->unitKerja?->is_active) {
                throw ValidationException::withMessages(['unit_organisasi_id' => 'Unit Organisasi tidak valid.']);
            }
            $instansi = $unitOrganisasi->label;
        }

        // Satu unit hanya punya satu email resmi: tolak bila sudah ada permohonan aktif atau akun terdaftar
        if ($isUnit) {
            $column = $unitKerja ? 'unit_kerja_id' : 'unit_organisasi_id';
            $targetId = $unitKerja ? $unitKerja->id : $unitOrganisasi->id;

            $unitTaken = EmailRequest::where('jenis', $jenis)
                    ->where($column, $targetId)
                    ->whereIn('status', ['menunggu', 'proses', 'selesai'])
                    ->exists()
                || \App\Models\EmailAccount::where('jenis', $jenis)->where($column, $targetId)->exists();

            if ($unitTaken) {
                throw ValidationException::withMessages([
                    $column => 'Unit ini sudah memiliki permohonan email aktif atau email resmi terdaftar.'
                ]);
            }
        }

        // Alamat email tidak membedakan huruf besar/kecil, simpan username dalam huruf kecil
        $r->merge(['username' => strtolower(trim((string) $r->input('username')))]);

        $data = $r->validate([
            'nama'             => ['required','string','max:200'],
            // Instansi pemohon hanya wajib untuk email pribadi; untuk unit diisi dari master unit
            'instansi'         => $isUnit ? ['nullable'] : ['required','string','max:200'],
            'username'         => ['required','alpha_num','min:3','max:30', Rule::unique('email_requests','username')],
            'email_alternatif' => ['nullable','email','max:200'],
            'no_hp'            => ['required','string','max:30'],
            // Password: min 15, huruf besar, huruf kecil, angka, simbol
            'password'         => [
                'required',
                'string',
                Password::min(15)
                    ->mixedCase()      // Huruf besar dan kecil
                    ->numbers()         // Angka
                    ->symbols()         // Simbol
            ],
            'consent_true'     => ['accepted'],
        ],[
            'consent_true.accepted'    => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
            'username.unique'          => 'Username sudah digunakan.',
        ]);

        if ($isUnit) {
            $data['instansi'] = $instansi;
        }

        $req = new EmailRequest($data);
        $req->jenis        = $jenis;
        $req->unit_kerja_id      = $unitKerja?->id;
        $req->unit_organisasi_id = $unitOrganisasi?->id;
        $req->user_id      = auth()->id();
        $req->nip          = $user->nip; // Auto-filled from user, not from request input
        $req->ticket_no    = EmailRequest::nextTicket();
        $req->submitted_at = now();
        $req->status       = 'menunggu';
        $req->setPlainPassword($data['password']);
        unset($req->password);
        $req->save();

        EmailRequestLog::create([
            'email_request_id' => $req->id,
            'actor_id' => auth()->id(),
            'action'  => 'created',
            'note'    => 'Pengajuan email dibuat',
        ]);

        return redirect()->route('user.email.thanks', $req->ticket_no);
    }


    public function thanks(string $ticket)
    {
        // halaman selesai + tombol kembali ke index
        return view('user.email.thanks', ['ticket' => $ticket]);
    }

    /**
     * Display survey page for completed email request
     */
    public function survey(string $ticket)
    {
        // Find the email request by ticket number and ensure it belongs to current user
        $emailRequest = EmailRequest::where('ticket_no', $ticket)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Only allow survey for completed requests
        if ($emailRequest->status !== 'selesai') {
            abort(403, 'Survey hanya tersedia untuk permohonan yang sudah selesai.');
        }

        // URL survei dari pengaturan terpusat (token dikelola via Manajemen Survei Digital)
        $surveyUrl = \App\Services\SurveiDigitalService::urlFor('email');

        return view('user.email.survey', [
            'emailRequest' => $emailRequest,
            'surveyUrl' => $surveyUrl,
            'ticket' => $ticket
        ]);
    }

    public function edit($id)
    {
        $item = EmailRequest::where('id',$id)->where('user_id',auth()->id())->firstOrFail();
        if ($item->isUnit()) {
            abort(403, 'Permohonan email unit tidak dapat diedit. Silakan hapus lalu ajukan ulang.');
        }
        if ($item->status !== 'menunggu') {
            abort(403, 'Permohonan tidak bisa diedit karena sudah diproses.');
        }
        // Get active unit kerja for dropdown
        $unitKerjaList = \App\Models\UnitKerja::forLayananDigital()->active()->orderBy('nama')->get();
        return view('user.email.edit', compact('item', 'unitKerjaList'));
    }


    public function update(Request $r, $id)
    {
        $item = EmailRequest::where('id',$id)->where('user_id',auth()->id())->firstOrFail();
        if ($item->isUnit()) {
            throw ValidationException::withMessages(['status' => 'Permohonan email unit tidak dapat diubah. Silakan hapus lalu ajukan ulang.']);
        }
        if ($item->status !== 'menunggu') {
            throw ValidationException::withMessages(['status' => 'Permohonan tidak bisa diubah karena sudah diproses.']);
        }

        // Alamat email tidak membedakan huruf besar/kecil, simpan username dalam huruf kecil
        $r->merge(['username' => strtolower(trim((string) $r->input('username')))]);

        $data = $r->validate([
            'nama'             => ['required','string','max:200'],
            'instansi'         => ['required','string','max:200'],
            'username'         => [
                'required','alpha_num','min:3','max:30',
                Rule::unique('email_requests','username')->ignore($item->id),
            ],
            'email_alternatif' => ['nullable','email','max:200'],
            'no_hp'            => ['required','string','max:30'],
            // optional ganti password: kalau diisi, harus huruf besar+kecil+angka+simbol
            'password'         => [
                'nullable',
                'string',
                Password::min(15)
                    ->mixedCase()      // Huruf besar dan kecil
                    ->numbers()         // Angka
                    ->symbols()         // Simbol
            ],
            'consent_true'     => ['accepted'],
        ],[
            'username.unique'       => 'Username sudah digunakan.',
            'consent_true.accepted' => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
        ]);

        $item->fill($data);
        if (!empty($data['password'])) {
            $item->setPlainPassword($data['password']);
        }
        $item->save();

        return redirect()->route('user.email.index')->with('status','Permohonan berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $item = EmailRequest::where('id',$id)->where('user_id',auth()->id())->firstOrFail();
        if ($item->status !== 'menunggu') {
            abort(403, 'Permohonan tidak bisa dihapus karena sudah diproses.');
        }
        $item->delete();
        return redirect()->route('user.email.index')->with('status','Permohonan dihapus.');
    }

    /**
     * Check email availability in Master Data Email
     */
    public function checkEmailAvailability(Request $request)
    {
        $username = strtolower(trim((string) $request->input('username')));

        if (empty($username)) {
            return response()->json([
                'available' => false,
                'message' => 'Username tidak boleh kosong'
            ]);
        }

        // Construct full email
        $fullEmail = $username . '@kaltaraprov.go.id';

        // Check if email exists in email_accounts table (Master Data Email)
        $existsInMasterData = \App\Models\EmailAccount::where('email', $fullEmail)->exists();

        // Check if username already requested
        $existsInRequests = EmailRequest::where('username', $username)->exists();

        if ($existsInMasterData) {
            return response()->json([
                'available' => false,
                'message' => 'Email sudah terdaftar di sistem. Silakan gunakan username lain.'
            ]);
        }

        if ($existsInRequests) {
            return response()->json([
                'available' => false,
                'message' => 'Username sudah pernah diajukan. Silakan gunakan username lain.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Email tersedia!'
        ]);
    }

    /**
     * Check if NIP is already registered for email service
     */
    public function checkNipAvailability(Request $request)
    {
        $user = auth()->user();

        if (empty($user->nip)) {
            return response()->json([
                'available' => false,
                'message' => 'NIP Anda belum terdaftar di sistem.'
            ]);
        }

        // Check if NIP already has an approved PERSONAL email request (email unit tidak dihitung)
        $nipInRequests = EmailRequest::where('nip', $user->nip)
            ->where('jenis', EmailRequest::JENIS_PRIBADI)
            ->whereIn('status', ['proses', 'selesai'])
            ->exists();

        // Check if NIP exists in Master Data Email as a personal account
        $nipInMasterData = \App\Models\EmailAccount::where('nip', $user->nip)
            ->where('jenis', EmailRequest::JENIS_PRIBADI)
            ->exists();

        if ($nipInRequests || $nipInMasterData) {
            return response()->json([
                'available' => false,
                'message' => 'NIP Anda sudah terdaftar untuk layanan email. Satu NIP hanya dapat memiliki satu akun email.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'NIP tersedia untuk pendaftaran email.'
        ]);
    }
}
