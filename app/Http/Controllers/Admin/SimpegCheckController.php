<?php

// app/Http/Controllers/Admin/SimpegCheckController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SimpegCheck;
use App\Services\SimpegClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SimpegCheckController extends Controller
{
    public function index(Request $request)
    {
        $logs = SimpegCheck::with(['user','createdBy'])
            ->latest()->take(10)->get();

        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('nama')->get();

        return view('admin.simpeg.index', [
            'logs'   => $logs,
            'result' => null,
            'layout' => 'layouts.app',
            'unitKerjas' => $unitKerjas,
            'prefilledNik' => $request->query('nik'),
            'targetUserId' => $request->query('target_user_id'),
            'returnUrl'    => $this->safeReturnUrl($request->query('return_url')),
        ]);
    }

    /**
     * Whitelist: hanya return URL yang se-origin dengan app (anti open redirect).
     */
    protected function safeReturnUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        return str_starts_with($url, url('/')) ? $url : null;
    }

    public function check(Request $request, SimpegClient $client)
    {
        // Validasi & normalisasi
        $validated = $request->validate([
            'nik' => ['required','regex:/^\d{16}$/'],
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.regex'    => 'NIK harus 16 digit angka',
        ]);

        $nik = preg_replace('/\D+/', '', (string)$validated['nik']);

        try {
            $api = $client->byNik($nik);
        } catch (\App\Exceptions\SimpegException $e) {
            Log::error('SIMPEG check failed', [
                'nik'    => $nik,
                'status' => $e->status,
                'payload'=> $e->payload,
                'msg'    => $e->getMessage(),
            ]);

            $msg = match ($e->status) {
                401 => 'API key SIMPEG tidak valid.',
                403 => 'IP server ini belum diizinkan (forbidden).',
                404 => 'NIK tidak ditemukan pada SIMPEG.',
                429 => 'Terlalu banyak permintaan (rate limit). Coba lagi sebentar.',
                502,503,504 => 'Layanan SIMPEG sedang tidak stabil. Coba lagi beberapa saat.',
                default => 'Layanan SIMPEG sedang bermasalah. Coba lagi beberapa saat lagi.',
            };

            return back()->withInput()->with('flash_error', $msg);
        } catch (Throwable $e) {
            Log::error('SIMPEG unknown error', ['nik'=>$nik, 'e'=>$e->getMessage()]);
            return back()->withInput()->with('flash_error', 'NIK Tidak ditemukan/Gangguan tidak terduga ke SIMPEG');
        }

        // === Jika tidak ditemukan / tidak valid → alert kuning & stop ===
        if (empty($api['valid'])) {
            $warning = ($api['reason'] ?? null) === 'invalid_nik'
                ? 'NIK tidak valid — NIK harus 16 digit angka.'
                : 'NIK tersebut tidak ditemukan pada SIMPEG.';

            return back()
                ->withInput()
                ->with('flash_warning', $warning);
        }

        // === Mapping hasil bila ditemukan ===
        $data = is_array($api['data'] ?? null) ? $api['data'] : [];

        // Log all available fields from SIMPEG for debugging
        Log::info('SIMPEG data fields', [
            'nik' => $nik,
            'available_fields' => array_keys($data)
        ]);

        $nip     = $data['nip']      ?? null;
        $nama    = $data['nama']     ?? null;
        $tel     = $data['telepon']  ?? null; // sesuaikan key API
        $eml     = $data['email']    ?? null;
        $jabatan = $data['jabatan']  ?? null;
        $golongan = $data['golongan'] ?? null;

        // Try different possible field names for instansi/unit kerja
        $instansi = $data['instansi']
                    ?? $data['unit_kerja']
                    ?? $data['unitKerja']
                    ?? $data['unit_kerja_nama']
                    ?? $data['nama_unit_kerja']
                    ?? $data['organisasi']
                    ?? $data['skpd']
                    ?? null;

        // Try to match instansi with UnitKerja
        $matchedUnitKerja = null;
        if (!empty($instansi)) {
            // Try exact match first
            $matchedUnitKerja = \App\Models\UnitKerja::where('nama', $instansi)->first();

            // If no exact match, try case-insensitive and trimmed match
            if (!$matchedUnitKerja) {
                $matchedUnitKerja = \App\Models\UnitKerja::whereRaw('LOWER(TRIM(nama)) = ?', [
                    mb_strtolower(trim($instansi))
                ])->first();
            }

            // If still no match, try partial match (LIKE)
            if (!$matchedUnitKerja) {
                $matchedUnitKerja = \App\Models\UnitKerja::where('nama', 'like', '%' . $instansi . '%')->first();
            }
        }

        // Kalau datang dari halaman edit-user (target_user_id dikirim di request),
        // langsung pakai user itu — lebih cepat, skip iterasi decrypt NIK semua user.
        if ($request->filled('target_user_id')) {
            $user = User::find($request->integer('target_user_id'));
        } else {
            // Lookup via nik_hash (deterministic SHA-256). Fallback ke iterasi
            // decrypt untuk row legacy yang belum di-backfill nik_hash-nya.
            $nikHash = User::hashNik($nik);
            $user = User::where('nik_hash', $nikHash)->first();
            if (!$user) {
                $user = User::whereNotNull('nik')->whereNull('nik_hash')->get()
                    ->first(fn($u) => $u->nik === $nik);
            }
        }

        $nameMatch  = $user && $nama
            ? (mb_strtoupper(trim($user->name)) === mb_strtoupper(trim($nama)))
            : false;

        $phoneMatch = $user && $tel
            ? (preg_replace('/\D+/', '', (string)$user->phone) === preg_replace('/\D+/', '', (string)$tel))
            : false;

        $emailMatch = $user && $eml
            ? (mb_strtolower(trim($user->email)) === mb_strtolower(trim($eml)))
            : false;

        // Simpan log (jangan sampai gagal total kalau error)
        try {
            SimpegCheck::create([
                'nik'              => $nik,
                'user_id'          => optional($user)->id,
                'is_nik_valid'     => true,
                'nip'              => $nip,
                'name_from_simpeg' => $nama,
                'name_match'       => $nameMatch,
                'phone_match'      => $phoneMatch,
                'email_match'      => $emailMatch,
                'raw_response'     => $api,
                'created_by'       => $request->user()->id,
                'ip'               => $request->ip(),
                'user_agent'       => (string)$request->header('User-Agent'),
            ]);
        } catch (Throwable $e) {
            Log::warning('SIMPEG check log failed', ['nik' => $nik, 'e' => $e->getMessage()]);
        }

        $logs = SimpegCheck::with(['user','createdBy'])->latest()->take(10)->get();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('nama')->get();

        return view('admin.simpeg.index', [
            'result' => [
                'is_valid'  => true,
                'nip'       => $nip,
                'nama'      => $nama,
                'telepon'   => $tel,
                'email'     => $eml,
                'jabatan'   => $jabatan,
                'golongan'  => $golongan,
                'instansi'  => $instansi,
                'matched_unit_kerja' => $matchedUnitKerja,
                'user'      => $user,
                'name_ok'   => $nameMatch,
                'phone_ok'  => $phoneMatch,
                'email_ok'  => $emailMatch,
            ],
            'logs'      => $logs,
            'layout'    => 'layouts.app',
            'input_nik' => $nik,
            'unitKerjas' => $unitKerjas,
            'prefilledNik' => $nik,
            'targetUserId' => $request->input('target_user_id'),
            'returnUrl'    => $this->safeReturnUrl($request->input('return_url')),
        ]);
    }

    public function saveToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nik' => ['required', 'string'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['in:nip,name,phone,email,jabatan,unit_kerja'],
            'nip' => ['nullable', 'string', 'max:20'],
            'nama' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'email_simpeg' => ['nullable', 'email', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'instansi_simpeg' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $updatedFields = [];

        // NIK ditangani server-side (bukan via checkbox) supaya tidak bisa
        // dipakai untuk overwrite NIK user lain & menciptakan duplikat:
        //   - Jika user belum punya NIK → auto-set ke NIK SIMPEG
        //   - Jika NIK user sama dengan SIMPEG → no-op
        //   - Jika NIK user beda → tolak (admin harus perbaiki via Edit User)
        $simpegHash   = User::hashNik($validated['nik']);
        $existingHash = User::hashNik($user->nik);

        if ($existingHash && $existingHash !== $simpegHash) {
            return back()->with('flash_error',
                'NIK user yang dipilih (' . $user->nik . ') berbeda dengan NIK SIMPEG (' . $validated['nik'] . '). '
                . 'Perbaiki NIK user terlebih dahulu via halaman Edit User.');
        }

        if (!$existingHash) {
            // User belum punya NIK. Pastikan NIK SIMPEG belum dimiliki user lain.
            $conflict = User::where('nik_hash', $simpegHash)
                ->where('id', '!=', $user->id)
                ->exists();
            if ($conflict) {
                return back()->with('flash_error',
                    'NIK ' . $validated['nik'] . ' sudah terdaftar pada user lain. '
                    . 'Tidak bisa menyimpan ke user ini untuk mencegah duplikasi NIK.');
            }
            $user->nik = $validated['nik'];
            $updatedFields[] = 'NIK';
        }

        // Update hanya field yang dipilih
        foreach ($validated['fields'] as $field) {
            switch ($field) {
                case 'nip':
                    if (!empty($validated['nip'])) {
                        $user->nip = $validated['nip'];
                        $updatedFields[] = 'NIP';
                    }
                    break;
                case 'name':
                    if (!empty($validated['nama'])) {
                        $user->name = $validated['nama'];
                        $updatedFields[] = 'Nama';
                    }
                    break;
                case 'phone':
                    if (!empty($validated['telepon'])) {
                        $user->phone = $validated['telepon'];
                        $updatedFields[] = 'Nomor HP';
                    }
                    break;
                case 'email':
                    if (!empty($validated['email_simpeg'])
                        && strcasecmp($user->email, $validated['email_simpeg']) !== 0) {
                        // Cek konflik unique constraint sebelum save (UNIQUE pada users.email).
                        $emailConflict = User::where('email', $validated['email_simpeg'])
                            ->where('id', '!=', $user->id)
                            ->exists();
                        if ($emailConflict) {
                            return back()->with('flash_error',
                                'Email ' . $validated['email_simpeg'] . ' sudah dipakai user lain. '
                                . 'Kemungkinan ada user duplikat — periksa lewat /admin/users.');
                        }
                        $user->email = $validated['email_simpeg'];
                        $updatedFields[] = 'Email';
                    }
                    break;
                case 'jabatan':
                    if (!empty($validated['jabatan'])) {
                        // Prepare jabatan data
                        $jabatanData = ['nama_jabatan' => $validated['jabatan']];

                        // Add unit_kerja_id to jabatan if available
                        if (!empty($validated['unit_kerja_id'])) {
                            $jabatanData['unit_kerja_id'] = $validated['unit_kerja_id'];
                        }

                        // Add legacy instansi if available
                        if (!empty($validated['instansi_simpeg'])) {
                            $jabatanData['unit_kerja_legacy'] = $validated['instansi_simpeg'];
                        }

                        // Update or create jabatan record in separate table
                        $user->jabatan()->updateOrCreate(
                            ['user_id' => $user->id],
                            $jabatanData
                        );
                        $updatedFields[] = 'Jabatan';
                    }
                    break;
                case 'unit_kerja':
                    if (!empty($validated['unit_kerja_id'])) {
                        $user->unit_kerja_id = $validated['unit_kerja_id'];
                        $updatedFields[] = 'Unit Kerja';
                    }
                    break;
            }
        }

        $user->save();

        // Log aktivitas
        try {
            Log::info('SIMPEG data saved to user', [
                'user_id' => $user->id,
                'nik' => $validated['nik'],
                'updated_fields' => $updatedFields,
                'admin' => $request->user()->email,
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to log SIMPEG save activity', ['e' => $e->getMessage()]);
        }

        $fieldsList = implode(', ', $updatedFields);
        $message = "Data berhasil disimpan ke user {$user->name}: {$fieldsList}";

        // Kalau datang dari halaman lain (mis. edit-user), kembalikan ke sana.
        $returnUrl = $this->safeReturnUrl($request->input('return_url'));
        if ($returnUrl) {
            return redirect($returnUrl)->with('success', $message);
        }

        return back()->with('flash_success', $message);
    }

    /**
     * AJAX endpoint dari modal "Cek Data" di /admin/users.
     * Pakai NIK milik user yang dipilih, ambil data SIMPEG, balikan JSON.
     */
    public function apiCheckUser(Request $request, User $user, SimpegClient $client): JsonResponse
    {
        $user->load('jabatan.unitKerja', 'unitKerja');

        if (empty($user->nik)) {
            return response()->json([
                'success' => false,
                'message' => 'User ini belum punya NIK. Isi NIK dulu lewat Edit User.',
            ], 422);
        }

        $nik = preg_replace('/\D+/', '', (string) $user->nik);

        try {
            $api = $client->byNik($nik);
        } catch (\App\Exceptions\SimpegException $e) {
            Log::warning('SIMPEG check (admin modal) failed', [
                'admin_id' => $request->user()->id, 'target_user_id' => $user->id,
                'status' => $e->status, 'msg' => $e->getMessage(),
            ]);
            $msg = match ($e->status) {
                404 => 'NIK user tidak ditemukan di SIMPEG.',
                429 => 'Terlalu banyak permintaan ke SIMPEG. Coba lagi sebentar.',
                401, 403 => 'Layanan SIMPEG menolak akses saat ini.',
                default => 'Layanan SIMPEG sedang bermasalah. Coba lagi nanti.',
            };
            return response()->json(['success' => false, 'message' => $msg], 502);
        } catch (Throwable $e) {
            Log::error('SIMPEG check (admin modal) unexpected error', [
                'target_user_id' => $user->id, 'e' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Gangguan tidak terduga ke SIMPEG.'], 502);
        }

        if (empty($api['valid'])) {
            Log::warning('SIMPEG byNik returned not-found for stored user NIK', [
                'target_user_id' => $user->id,
                'nik_len_raw'    => mb_strlen((string) $user->nik),
                'nik_digits_len' => strlen($nik),
                'nik_digits'     => $nik,
                'api_response'   => $api,
            ]);

            if (($api['reason'] ?? null) === 'invalid_nik') {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK tidak valid — NIK harus 16 digit angka. Periksa & perbaiki NIK user lewat Edit User.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'NIK tidak ditemukan di SIMPEG. SIMPEG hanya menyimpan data ASN/pegawai terdaftar — verifikasi ke admin SIMPEG / Kepegawaian apakah NIK ini seharusnya ada di sana.',
            ], 404);
        }

        $data = is_array($api['data'] ?? null) ? $api['data'] : [];

        $instansiText = $data['instansi'] ?? $data['unit_kerja'] ?? $data['unitKerja']
            ?? $data['nama_unit_kerja'] ?? $data['organisasi'] ?? $data['skpd'] ?? null;

        $jabatanText = $data['jabatan'] ?? $data['nama_jabatan'] ?? $data['jabatan_nama']
            ?? $data['posisi'] ?? $data['position'] ?? null;

        // Log field names when jabatan/instansi is missing — helps diagnose SIMPEG field name changes.
        if (!$jabatanText || !$instansiText) {
            Log::info('SIMPEG: jabatan/instansi null for user', [
                'user_id'     => $user->id,
                'nik'         => $nik,
                'data_keys'   => array_keys($data),
                'jabatan_raw' => $data['jabatan'] ?? null,
                'instansi_raw'=> $data['instansi'] ?? null,
            ]);
        }

        $matchedUnitKerja = null;
        if ($instansiText) {
            $matchedUnitKerja = \App\Models\UnitKerja::where('nama', $instansiText)->first()
                ?? \App\Models\UnitKerja::whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(trim($instansiText))])->first()
                ?? \App\Models\UnitKerja::where('nama', 'like', '%' . $instansiText . '%')->first();
        }

        // Catat log seperti flow check() biasa supaya history admin tetap konsisten.
        try {
            $nameMatch  = $data['nama']    ? (mb_strtoupper(trim($user->name)) === mb_strtoupper(trim($data['nama']))) : false;
            $phoneMatch = $data['telepon'] ? (preg_replace('/\D+/', '', (string)$user->phone) === preg_replace('/\D+/', '', (string)$data['telepon'])) : false;
            $emailMatch = $data['email']   ? (mb_strtolower(trim($user->email)) === mb_strtolower(trim($data['email']))) : false;
            SimpegCheck::create([
                'nik'              => $nik,
                'user_id'          => $user->id,
                'is_nik_valid'     => true,
                'nip'              => $data['nip'] ?? null,
                'name_from_simpeg' => $data['nama'] ?? null,
                'name_match'       => $nameMatch,
                'phone_match'      => $phoneMatch,
                'email_match'      => $emailMatch,
                'raw_response'     => $api,
                'created_by'       => $request->user()->id,
                'ip'               => $request->ip(),
                'user_agent'       => (string) $request->header('User-Agent'),
            ]);
        } catch (Throwable $e) {
            Log::warning('SIMPEG check log failed (admin modal)', ['e' => $e->getMessage()]);
        }

        // SIMPEG kadang kirim beberapa email sekaligus: "a@x.com & b@y.com".
        // Ambil alamat pertama yang valid supaya tidak gagal validasi di apply.
        $rawEmail = $data['email'] ?? null;
        $cleanEmail = null;
        if ($rawEmail) {
            foreach (preg_split('/[\s,;&]+/', (string) $rawEmail) as $part) {
                $part = trim($part);
                if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
                    $cleanEmail = $part;
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'simpeg' => [
                'nip'      => $data['nip'] ?? null,
                'nama'     => $data['nama'] ?? null,
                'telepon'  => $data['telepon'] ?? null,
                'email'    => $cleanEmail,
                'jabatan'  => $jabatanText,
                'instansi' => $instansiText,
                'matched_unit_kerja_id'   => $matchedUnitKerja->id ?? null,
                'matched_unit_kerja_nama' => $matchedUnitKerja->nama ?? null,
            ],
            'current' => [
                'nip'             => $user->nip,
                'name'            => $user->name,
                'phone'           => $user->phone,
                'email'           => $user->email,
                'jabatan'         => $user->jabatan->nama_jabatan ?? null,
                'unit_kerja_nama' => $user->unitKerja->nama ?? null,
                'nik'             => $user->nik,
            ],
        ]);
    }

    /**
     * AJAX endpoint untuk apply field yang admin centang dari modal.
     * Reuse aturan keamanan saveToUser() (NIK conflict, email unique).
     */
    public function apiApplyUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'fields'   => ['required', 'array', 'min:1'],
            'fields.*' => ['in:nip,name,phone,email,jabatan,unit_kerja'],
            'nip'      => ['nullable', 'string', 'max:20'],
            'name'     => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'email'    => ['nullable', 'string', 'max:255'],
            'jabatan'  => ['nullable', 'string', 'max:255'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
        ]);

        $updated = [];

        foreach ($validated['fields'] as $field) {
            switch ($field) {
                case 'nip':
                    if (!empty($validated['nip'])) {
                        $user->nip = $validated['nip'];
                        $updated[] = 'NIP';
                    }
                    break;
                case 'name':
                    if (!empty($validated['name'])) {
                        $user->name = $validated['name'];
                        $updated[] = 'Nama';
                    }
                    break;
                case 'phone':
                    if (!empty($validated['phone'])) {
                        $user->phone = $validated['phone'];
                        $updated[] = 'Nomor HP';
                    }
                    break;
                case 'email':
                    if (!empty($validated['email'])
                        && strcasecmp($user->email, $validated['email']) !== 0) {
                        if (!filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Email dari SIMPEG bukan alamat yang valid: ' . $validated['email'],
                            ], 422);
                        }
                        // UNIQUE constraint pada users.email — tolak konflik supaya tidak 500.
                        $emailConflict = User::where('email', $validated['email'])
                            ->where('id', '!=', $user->id)
                            ->exists();
                        if ($emailConflict) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Email ' . $validated['email'] . ' sudah dipakai user lain. '
                                    . 'Kemungkinan ada user duplikat — periksa lewat /admin/users.',
                            ], 409);
                        }
                        $user->email = $validated['email'];
                        $updated[] = 'Email';
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
                case 'unit_kerja':
                    if (!empty($validated['unit_kerja_id'])) {
                        $user->unit_kerja_id = $validated['unit_kerja_id'];
                        $updated[] = 'Unit Kerja';
                    }
                    break;
            }
        }

        try {
            $user->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Catch-all kalau ada UNIQUE lain yang belum kita pre-check.
            Log::warning('apiApplyUser unique violation', ['e' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal simpan: ada nilai yang bentrok dengan user lain (kemungkinan email/NIK duplikat).',
            ], 409);
        }

        Log::info('SIMPEG data saved to user (admin modal)', [
            'admin'   => $request->user()->email,
            'user_id' => $user->id,
            'updated' => $updated,
        ]);

        $message = count($updated) > 0
            ? "Data {$user->name} berhasil disinkron: " . implode(', ', $updated) . '.'
            : 'Tidak ada field yang diupdate.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'updated' => $updated,
        ]);
    }
}
