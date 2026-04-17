<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil pengguna.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'unitKerjas' => \App\Models\UnitKerja::forLayananDigital()
                ->where('is_active', true)
                ->orderBy('nama')
                ->get(),
        ]);
    }

    /**
     * Simpan perubahan data pengguna, termasuk password (jika diisi).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nip' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],

            // Ubah password jika disediakan
            'current_password' => ['nullable', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Basic info yang selalu bisa diupdate
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // NIP dan NIK hanya bisa diupdate jika:
        // 1. User belum terverifikasi, ATAU
        // 2. User adalah Admin
        $isAdmin = $user->hasRole('Admin');
        $isVerified = $user->is_verified;

        if (!$isVerified || $isAdmin) {
            // Boleh update NIP, NIK, dan Instansi
            $user->nip = $validated['nip'] ?? null;
            $user->nik = $validated['nik'] ?? null;
            $user->unit_kerja_id = $validated['unit_kerja_id'] ?? null;
        }
        // Else: NIP, NIK, dan Instansi tidak diupdate (tetap menggunakan nilai lama)

        // Jika password diisi, update password
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profil dan/atau password berhasil diperbarui.');
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
