<?php

namespace App\Http\Controllers;

use App\Services\SimpegClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil pengguna.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('jabatan.unitKerja');

        return view('profile.edit', [
            'user' => $user,
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
     * Lookup data SIMPEG berdasarkan NIK user saat ini. Dipanggil via AJAX
     * dari halaman /profile (tombol "Sinkron Data via SIMPEG").
     * Mengembalikan JSON berisi data SIMPEG + nilai profil sekarang +
     * daftar field yang boleh diupdate (tergantung status verifikasi user).
     */
    public function simpegCheck(Request $request, SimpegClient $client): JsonResponse
    {
        $user = $request->user()->load('jabatan.unitKerja', 'unitKerja');

        if (empty($user->nik)) {
            return response()->json([
                'success' => false,
                'message' => 'NIK Anda belum diisi di profil. Isi NIK dulu, simpan, lalu coba sinkron lagi.',
            ], 422);
        }

        $nik = preg_replace('/\D+/', '', (string) $user->nik);

        try {
            $api = $client->byNik($nik);
        } catch (\App\Exceptions\SimpegException $e) {
            Log::warning('SIMPEG check (user self-sync) failed', [
                'user_id' => $user->id, 'status' => $e->status, 'msg' => $e->getMessage(),
            ]);
            $msg = match ($e->status) {
                404 => 'NIK Anda tidak ditemukan di SIMPEG. Hubungi admin kepegawaian.',
                429 => 'Terlalu banyak permintaan. Coba lagi beberapa saat.',
                401, 403 => 'Layanan SIMPEG menolak akses saat ini.',
                default => 'Layanan SIMPEG sedang bermasalah. Coba lagi nanti.',
            };
            return response()->json(['success' => false, 'message' => $msg], 502);
        } catch (\Throwable $e) {
            Log::error('SIMPEG check unexpected error', ['user_id' => $user->id, 'e' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gangguan tidak terduga ke SIMPEG.'], 502);
        }

        if (empty($api['valid'])) {
            return response()->json([
                'success' => false,
                'message' => 'NIK Anda tidak ditemukan di SIMPEG.',
            ], 404);
        }

        $data = is_array($api['data'] ?? null) ? $api['data'] : [];

        // Coba cocokkan instansi SIMPEG dengan Master UnitKerja lokal
        $instansiText = $data['instansi'] ?? $data['unit_kerja'] ?? $data['unitKerja']
            ?? $data['nama_unit_kerja'] ?? $data['organisasi'] ?? $data['skpd'] ?? null;
        $matchedUnitKerja = null;
        if ($instansiText) {
            $matchedUnitKerja = \App\Models\UnitKerja::where('nama', $instansiText)->first()
                ?? \App\Models\UnitKerja::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(trim($instansiText))])->first()
                ?? \App\Models\UnitKerja::where('nama', 'like', '%' . $instansiText . '%')->first();
        }

        // Field mana yang boleh user update sendiri?
        // - name, email, phone, jabatan: selalu bisa
        // - nip, nik, unit_kerja_id: hanya jika user belum terverifikasi ATAU admin
        //   (konsisten dengan lock di method update())
        $isAdmin = $user->hasRole('Admin');
        $isVerified = (bool) $user->is_verified;
        $canEditLocked = !$isVerified || $isAdmin;

        $editable = [
            'name'          => true,
            'email'         => true,
            'phone'         => true,
            'jabatan'       => true,
            'nip'           => $canEditLocked,
            'nik'           => $canEditLocked,
            'unit_kerja_id' => $canEditLocked,
        ];

        return response()->json([
            'success' => true,
            'editable' => $editable,
            'simpeg' => [
                'nip'      => $data['nip'] ?? null,
                'nama'     => $data['nama'] ?? null,
                'telepon'  => $data['telepon'] ?? null,
                'email'    => $data['email'] ?? null,
                'jabatan'  => $data['jabatan'] ?? null,
                'instansi' => $instansiText,
                'matched_unit_kerja_id'   => $matchedUnitKerja->id ?? null,
                'matched_unit_kerja_nama' => $matchedUnitKerja->nama ?? null,
            ],
            'current' => [
                'nip'   => $user->nip,
                'name'  => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'jabatan' => $user->jabatan->nama_jabatan ?? null,
                'unit_kerja_nama' => $user->unitKerja->nama ?? null,
            ],
        ]);
    }

    /**
     * Terapkan field yang user pilih dari hasil SIMPEG ke data profilnya sendiri.
     * Server-side WAJIB enforce aturan lock (NIP/NIK/UnitKerja terkunci setelah verifikasi).
     */
    public function simpegApply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fields'   => ['required', 'array', 'min:1'],
            'fields.*' => ['in:name,email,phone,jabatan,nip,nik,unit_kerja'],
            'nip'      => ['nullable', 'string', 'max:20'],
            'name'     => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:255'],
            'jabatan'  => ['nullable', 'string', 'max:255'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
        ]);

        $user = $request->user();
        $isAdmin = $user->hasRole('Admin');
        $isVerified = (bool) $user->is_verified;
        $canEditLocked = !$isVerified || $isAdmin;

        $updated = [];
        $skipped = [];

        foreach ($validated['fields'] as $field) {
            // Server-side enforce lock
            $isLocked = in_array($field, ['nip', 'nik', 'unit_kerja'], true);
            if ($isLocked && !$canEditLocked) {
                $skipped[] = $field;
                continue;
            }

            switch ($field) {
                case 'name':
                    if (!empty($validated['name'])) {
                        $user->name = $validated['name'];
                        $updated[] = 'Nama';
                    }
                    break;
                case 'email':
                    if (!empty($validated['email'])) {
                        $user->email = $validated['email'];
                        $updated[] = 'Email';
                    }
                    break;
                case 'phone':
                    if (!empty($validated['phone'])) {
                        $user->phone = $validated['phone'];
                        $updated[] = 'Nomor HP';
                    }
                    break;
                case 'nip':
                    if (!empty($validated['nip'])) {
                        $user->nip = $validated['nip'];
                        $updated[] = 'NIP';
                    }
                    break;
                case 'unit_kerja':
                    if (!empty($validated['unit_kerja_id'])) {
                        $user->unit_kerja_id = $validated['unit_kerja_id'];
                        $updated[] = 'Unit Kerja';
                    }
                    break;
                case 'jabatan':
                    if (!empty($validated['jabatan'])) {
                        $jabatanData = ['nama_jabatan' => $validated['jabatan']];
                        if (!empty($validated['unit_kerja_id'])) {
                            $jabatanData['unit_kerja_id'] = $validated['unit_kerja_id'];
                        }
                        if (!empty($validated['instansi'])) {
                            $jabatanData['unit_kerja_legacy'] = $validated['instansi'];
                        }
                        $user->jabatan()->updateOrCreate(
                            ['user_id' => $user->id],
                            $jabatanData
                        );
                        $updated[] = 'Jabatan';
                    }
                    break;
            }
        }

        $user->save();

        Log::info('Profile self-sync from SIMPEG', [
            'user_id' => $user->id,
            'updated' => $updated,
            'skipped_locked' => $skipped,
        ]);

        $message = count($updated) > 0
            ? 'Data profil berhasil disinkron: ' . implode(', ', $updated) . '.'
            : 'Tidak ada field yang diupdate.';

        if (count($skipped) > 0) {
            $message .= ' Beberapa field terkunci (akun sudah terverifikasi): ' . implode(', ', $skipped) . '. Hubungi admin untuk mengubahnya.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'updated' => $updated,
        ]);
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
