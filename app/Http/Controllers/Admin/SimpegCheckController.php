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

        return view('admin.simpeg.index', [
            'logs'   => $logs,
            'result' => null,
            'layout' => 'layouts.app',
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
        $nip  = $data['nip']     ?? null;
        $nama = $data['nama']    ?? null;
        $tel  = $data['telepon'] ?? null; // sesuaikan key API
        $eml  = $data['email']   ?? null;

        $user = User::where('nik', $nik)->first();

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

        return view('admin.simpeg.index', [
            'result' => [
                'is_valid' => true,
                'nip'      => $nip,
                'nama'     => $nama,
                'user'     => $user,
                'name_ok'  => $nameMatch,
                'phone_ok' => $phoneMatch,
                'email_ok' => $emailMatch,
            ],
            'logs'      => $logs,
            'layout'    => 'layouts.app',
            'input_nik' => $nik,
        ]);
    }
}
