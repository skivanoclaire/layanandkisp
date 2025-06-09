<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Hash; // <-- letakkan DI LUAR class

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
            'nik' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

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
}
