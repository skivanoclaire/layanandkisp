<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Hash; // <-- letakkan DI LUAR class
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $requests = \App\Models\Request::latest()->take(10)->get();

        // Hitung total keseluruhan
        $total = \App\Models\Request::count();
        $waiting = \App\Models\Request::where('status', 'Menunggu')->count();
        $processing = \App\Models\Request::where('status', 'Dalam Proses')->count();
        $rejected = \App\Models\Request::where('status', 'Ditolak')->count();
        $finished = \App\Models\Request::where('status', 'Selesai')->count();

        // Hitung statistik kinerja bulan ini
        $summary = \App\Models\Request::selectRaw('status, COUNT(*) as total')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', compact(
            'requests',
            'total',
            'waiting',
            'processing',
            'rejected',
            'finished',
            'summary' // <-- penting untuk grafik
        ));

        return redirect()->back()->with('success', 'Data berhasil dirubah');
    }



    public function permohonan()
    {
        $requests = UserRequest::with('user')->latest()->get();
        return view('admin.permohonan', compact('requests'));
    }

    public function users()
    {
        $users = User::latest()->get();
        return view('admin.users', compact('users'));
    }

    public function updateStatus(HttpRequest $request, UserRequest $userRequest)
    {
        $request->validate([
            'status' => 'required|in:Menunggu,Dalam Proses,Ditolak,Selesai'
        ]);

        $userRequest->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function updateUser(HttpRequest $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role'     => ['required', Rule::in(['user','admin','admin-vidcon','operator-vidcon'])],
            'nik'      => ['nullable','string','max:20'],
            'phone'    => ['nullable','string','max:20'],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        // Debug: pastikan value masuk
        Log::info('ADMIN updateUser payload', ['id'=>$user->id, 'role_incoming'=>$validated['role']]);

        // Assign eksplisit (anti-tertelan)
        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];
        $user->nik   = $validated['nik']   ?? null;
        $user->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Debug: lihat hasilnya
        Log::info('ADMIN updateUser saved', ['id'=>$user->id, 'role_saved'=>$user->fresh()->role]);

        return redirect()->route('admin.users.edit', $user->id)
                    ->with('success', 'Data user berhasil diperbarui.');
    }


    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus.');
    }

public function deleteRequest(UserRequest $userRequest)
{
    $userRequest->delete();
    return back()->with('success', 'Permohonan berhasil dihapus.');
}

public function verifyUser(User $user)
{
    // opsi: cegah memverifikasi admin lain via UI, kalau mau:
    // if ($user->role === 'admin') { abort(403); }

    if (!$user->is_verified) {
        $user->forceFill([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->user()->email ?? 'admin',
        ])->save();
    }

    return back()->with('status', "User {$user->name} berhasil diverifikasi.");
}

public function unverifyUser(User $user)
{
    // opsi: cegah membatalkan verifikasi admin, kalau mau:
    // if ($user->role === 'admin') { abort(403); }

    if ($user->is_verified) {
        $user->forceFill([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
        ])->save();
    }

    return back()->with('status', "Status verifikasi untuk {$user->name} dibatalkan.");
}


}
