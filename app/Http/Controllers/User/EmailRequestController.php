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

    public function create()
    {
        // Check if user has NIP
        $user = auth()->user();
        if (empty($user->nip)) {
            return redirect()->route('user.email.index')
                ->with('error', 'NIP Anda belum terdaftar. Silakan hubungi Administrator untuk memperbarui data NIP Anda.');
        }

        // Get active unit kerja for dropdown
        $unitKerjaList = \App\Models\UnitKerja::active()->orderBy('nama')->get();
        return view('user.email.create', compact('unitKerjaList'));
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

        // Check if NIP already has an approved email request or exists in email_accounts
        $nipExists = \App\Models\EmailRequest::where('nip', $user->nip)
            ->whereIn('status', ['proses', 'selesai'])
            ->exists();

        $nipInMasterData = \App\Models\EmailAccount::where('nip', $user->nip)->exists();

        if ($nipExists || $nipInMasterData) {
            throw ValidationException::withMessages([
                'nip' => 'NIP Anda sudah terdaftar untuk layanan email. Satu NIP hanya dapat memiliki satu akun email.'
            ]);
        }

        $data = $r->validate([
            'nama'             => ['required','string','max:200'],
            'instansi'         => ['required','string','max:200'],
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

        $req = new EmailRequest($data);
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


    public function edit($id)
    {
        $item = EmailRequest::where('id',$id)->where('user_id',auth()->id())->firstOrFail();
        if ($item->status !== 'menunggu') {
            abort(403, 'Permohonan tidak bisa diedit karena sudah diproses.');
        }
        // Get active unit kerja for dropdown
        $unitKerjaList = \App\Models\UnitKerja::active()->orderBy('nama')->get();
        return view('user.email.edit', compact('item', 'unitKerjaList'));
    }


    public function update(Request $r, $id)
    {
        $item = EmailRequest::where('id',$id)->where('user_id',auth()->id())->firstOrFail();
        if ($item->status !== 'menunggu') {
            throw ValidationException::withMessages(['status' => 'Permohonan tidak bisa diubah karena sudah diproses.']);
        }

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
        $username = $request->input('username');

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

        // Check if NIP already has an approved email request
        $nipInRequests = EmailRequest::where('nip', $user->nip)
            ->whereIn('status', ['proses', 'selesai'])
            ->exists();

        // Check if NIP exists in Master Data Email
        $nipInMasterData = \App\Models\EmailAccount::where('nip', $user->nip)->exists();

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
