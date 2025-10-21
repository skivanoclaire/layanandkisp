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
        return view('user.email.create'); // form
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama'             => ['required','string','max:200'],
            // NIP tepat 18 digit (pakai regex supaya leading zero aman)
            'nip'              => ['nullable','regex:/^\d{18}$/'],
            'instansi'         => ['required','string','max:200'],
            'username'         => ['required','alpha_num','min:3','max:30', Rule::unique('email_requests','username')],
            'email_alternatif' => ['nullable','email','max:200'],
            'no_hp'            => ['required','string','max:30'],
            // Password: min 8, huruf, angka, simbol
            'password'         => [
                'required',
                'string',
                Password::min(8)->letters()->numbers()->symbols()
            ],
            'consent_true'     => ['accepted'],
        ],[
            'nip.regex'                => 'NIP harus berisi tepat 18 digit angka.',
            'consent_true.accepted'    => 'Anda harus menyetujui pernyataan dan persyaratan layanan.',
            'username.unique'          => 'Username sudah digunakan.',
        ]);

        $req = new EmailRequest($data);
        $req->user_id      = auth()->id();
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
        return view('user.email.edit', compact('item'));
    }


    public function update(Request $r, $id)
    {
        $item = EmailRequest::where('id',$id)->where('user_id',auth()->id())->firstOrFail();
        if ($item->status !== 'menunggu') {
            throw ValidationException::withMessages(['status' => 'Permohonan tidak bisa diubah karena sudah diproses.']);
        }

        $data = $r->validate([
            'nama'             => ['required','string','max:200'],
            'nip'              => ['nullable','regex:/^\d{18}$/'],
            'instansi'         => ['required','string','max:200'],
            'username'         => [
                'required','alpha_num','min:3','max:30',
                Rule::unique('email_requests','username')->ignore($item->id),
            ],
            'email_alternatif' => ['nullable','email','max:200'],
            'no_hp'            => ['required','string','max:30'],
            // optional ganti password: kalau diisi, harus huruf+angka+simbol
            'password'         => [
                'nullable',
                'string',
                Password::min(8)->letters()->numbers()->symbols()
            ],
            'consent_true'     => ['accepted'],
        ],[
            'nip.regex'             => 'NIP harus berisi tepat 18 digit angka.',
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
}
