<?php

// app/Http/Controllers/Admin/SimpegCheckController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SimpegCheck;
use App\Services\SimpegClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SimpegCheckController extends Controller
{
    public function index()
    {
        $logs = SimpegCheck::with(['user','createdBy'])
            ->latest()->take(10)->get();

        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('nama')->get();

        return view('admin.simpeg.index', [
            'logs'   => $logs,
            'result' => null,
            'layout' => 'layouts.app',
            'unitKerjas' => $unitKerjas,
        ]);
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

        // === Jika tidak ditemukan → alert kuning & stop ===
        if (empty($api['valid'])) {
            return back()
                ->withInput()
                ->with('flash_warning', 'NIK tersebut tidak ditemukan pada SIMPEG.');
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

        // Karena NIK di-encrypt, kita perlu cari user dengan cara decrypt setiap NIK
        $user = User::whereNotNull('nik')->get()->first(function($u) use ($nik) {
            return $u->nik === $nik;
        });

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
        ]);
    }

    public function saveToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nik' => ['required', 'string'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['in:nik,nip,name,phone,email,jabatan,unit_kerja'],
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

        // Update hanya field yang dipilih
        foreach ($validated['fields'] as $field) {
            switch ($field) {
                case 'nik':
                    if (!empty($validated['nik'])) {
                        $user->nik = $validated['nik'];
                        $updatedFields[] = 'NIK';
                    }
                    break;
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
                    if (!empty($validated['email_simpeg'])) {
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
        return back()->with('flash_success', "Data berhasil disimpan ke user {$user->name}: {$fieldsList}");
    }
}
