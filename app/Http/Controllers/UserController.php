<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as UserRequest;
use App\Models\EmailRequest;
use App\Models\EmailPasswordResetRequest;
use App\Models\SubdomainRequest;
use App\Models\SubdomainIpChangeRequest;
use App\Models\RekomendasiRequest;
use App\Models\VidconRequest;
use App\Models\InternetRequest;
use App\Models\VpnRequest;
use App\Models\StarlinkRequest;
use App\Models\JipPdnsRequest;
use App\Models\VpsRequest;
use App\Models\BackupRequest;
use App\Models\CloudStorageRequest;
use App\Models\TteAssistanceRequest;
use App\Models\TteRegistrationRequest;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Mapping status untuk normalisasi
        $statusMapping = [
            'menunggu' => 'Menunggu',
            'proses' => 'Dalam Proses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        // Inisialisasi statistik
        $total = 0;
        $waiting = 0;
        $processing = 0;
        $rejected = 0;
        $finished = 0;
        $allRequests = collect();

        // 1. Permohonan Manual - Unggah Surat
        $manualRequests = $user->requests()->get();
        foreach ($manualRequests as $req) {
            $req->service_type = 'Manual';
            $req->service_name = $req->service;
            $req->normalized_status = $req->status;
        }
        $allRequests = $allRequests->merge($manualRequests);
        $total += $user->requests()->count();
        $waiting += $user->requests()->where('status', 'Menunggu')->count();
        $processing += $user->requests()->where('status', 'Dalam Proses')->count();
        $rejected += $user->requests()->where('status', 'Ditolak')->count();
        $finished += $user->requests()->where('status', 'Selesai')->count();

        // 2. Email Kaltaraprov
        $emailRequests = EmailRequest::where('user_id', $user->id)->get();
        foreach ($emailRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Email Kaltaraprov';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($emailRequests);
        $total += EmailRequest::where('user_id', $user->id)->count();
        $waiting += EmailRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += EmailRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += EmailRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += EmailRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // 3. Email Password Reset
        $emailPasswordResetRequests = EmailPasswordResetRequest::where('user_id', $user->id)->get();
        foreach ($emailPasswordResetRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Reset Password Email';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($emailPasswordResetRequests);
        $total += EmailPasswordResetRequest::where('user_id', $user->id)->count();
        $waiting += EmailPasswordResetRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += EmailPasswordResetRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += EmailPasswordResetRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += EmailPasswordResetRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // 4. Subdomain
        $subdomainRequests = SubdomainRequest::where('user_id', $user->id)->get();
        foreach ($subdomainRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Subdomain';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($subdomainRequests);
        $total += SubdomainRequest::where('user_id', $user->id)->count();
        $waiting += SubdomainRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += SubdomainRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += SubdomainRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += SubdomainRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // 5. Perubahan IP Subdomain
        $subdomainIpRequests = SubdomainIpChangeRequest::where('user_id', $user->id)->get();
        foreach ($subdomainIpRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Perubahan IP Subdomain';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($subdomainIpRequests);
        $total += SubdomainIpChangeRequest::where('user_id', $user->id)->count();
        $waiting += SubdomainIpChangeRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += SubdomainIpChangeRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += SubdomainIpChangeRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += SubdomainIpChangeRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // 6. Video Conference
        $vidconRequests = VidconRequest::where('user_id', $user->id)->get();
        foreach ($vidconRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Video Conference';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($vidconRequests);
        $total += VidconRequest::where('user_id', $user->id)->count();
        $waiting += VidconRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += VidconRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += VidconRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += VidconRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // 7. TTE - Pendampingan
        $tteAssistanceRequests = TteAssistanceRequest::where('user_id', $user->id)->get();
        foreach ($tteAssistanceRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Pendampingan TTE';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($tteAssistanceRequests);
        $total += TteAssistanceRequest::where('user_id', $user->id)->count();
        $waiting += TteAssistanceRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += TteAssistanceRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += TteAssistanceRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += TteAssistanceRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // 8. TTE - Registration
        $tteRegistrationRequests = TteRegistrationRequest::where('user_id', $user->id)->get();
        foreach ($tteRegistrationRequests as $req) {
            $req->service_type = 'Digital';
            $req->service_name = 'Pendaftaran Akun TTE';
            $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
        }
        $allRequests = $allRequests->merge($tteRegistrationRequests);
        $total += TteRegistrationRequest::where('user_id', $user->id)->count();
        $waiting += TteRegistrationRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
        $processing += TteRegistrationRequest::where('user_id', $user->id)->where('status', 'proses')->count();
        $rejected += TteRegistrationRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
        $finished += TteRegistrationRequest::where('user_id', $user->id)->where('status', 'selesai')->count();

        // Cek apakah model Internet, VPN, dan layanan pusat data lainnya ada
        if (class_exists(\App\Models\InternetRequest::class)) {
            $internetRequests = InternetRequest::where('user_id', $user->id)->get();
            foreach ($internetRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'Layanan Internet';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($internetRequests);
            $total += InternetRequest::where('user_id', $user->id)->count();
            $waiting += InternetRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += InternetRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += InternetRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += InternetRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        if (class_exists(\App\Models\VpnRequest::class)) {
            $vpnRequests = VpnRequest::where('user_id', $user->id)->get();
            foreach ($vpnRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'VPN';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($vpnRequests);
            $total += VpnRequest::where('user_id', $user->id)->count();
            $waiting += VpnRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += VpnRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += VpnRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += VpnRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        if (class_exists(\App\Models\StarlinkRequest::class)) {
            $starlinkRequests = StarlinkRequest::where('user_id', $user->id)->get();
            foreach ($starlinkRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'Starlink';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($starlinkRequests);
            $total += StarlinkRequest::where('user_id', $user->id)->count();
            $waiting += StarlinkRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += StarlinkRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += StarlinkRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += StarlinkRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        if (class_exists(\App\Models\JipPdnsRequest::class)) {
            $jipPdnsRequests = JipPdnsRequest::where('user_id', $user->id)->get();
            foreach ($jipPdnsRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'JIP/PDNS';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($jipPdnsRequests);
            $total += JipPdnsRequest::where('user_id', $user->id)->count();
            $waiting += JipPdnsRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += JipPdnsRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += JipPdnsRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += JipPdnsRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        if (class_exists(\App\Models\VpsRequest::class)) {
            $vpsRequests = VpsRequest::where('user_id', $user->id)->get();
            foreach ($vpsRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'VPS';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($vpsRequests);
            $total += VpsRequest::where('user_id', $user->id)->count();
            $waiting += VpsRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += VpsRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += VpsRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += VpsRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        if (class_exists(\App\Models\BackupRequest::class)) {
            $backupRequests = BackupRequest::where('user_id', $user->id)->get();
            foreach ($backupRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'Backup Data';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($backupRequests);
            $total += BackupRequest::where('user_id', $user->id)->count();
            $waiting += BackupRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += BackupRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += BackupRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += BackupRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        if (class_exists(\App\Models\CloudStorageRequest::class)) {
            $cloudStorageRequests = CloudStorageRequest::where('user_id', $user->id)->get();
            foreach ($cloudStorageRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'Cloud Storage';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($cloudStorageRequests);
            $total += CloudStorageRequest::where('user_id', $user->id)->count();
            $waiting += CloudStorageRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += CloudStorageRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += CloudStorageRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += CloudStorageRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        // Cek Rekomendasi jika ada
        if (class_exists(\App\Models\RekomendasiRequest::class)) {
            $rekomendasiRequests = RekomendasiRequest::where('user_id', $user->id)->get();
            foreach ($rekomendasiRequests as $req) {
                $req->service_type = 'Digital';
                $req->service_name = 'Rekomendasi';
                $req->normalized_status = $statusMapping[$req->status] ?? $req->status;
            }
            $allRequests = $allRequests->merge($rekomendasiRequests);
            $total += RekomendasiRequest::where('user_id', $user->id)->count();
            $waiting += RekomendasiRequest::where('user_id', $user->id)->where('status', 'menunggu')->count();
            $processing += RekomendasiRequest::where('user_id', $user->id)->where('status', 'proses')->count();
            $rejected += RekomendasiRequest::where('user_id', $user->id)->where('status', 'ditolak')->count();
            $finished += RekomendasiRequest::where('user_id', $user->id)->where('status', 'selesai')->count();
        }

        // Urutkan semua permohonan berdasarkan tanggal terbaru dan ambil 10 teratas
        $requests = $allRequests->sortByDesc('created_at')->take(10);

        // Statistik bulanan untuk chart
        $summary = [
            'Menunggu' => $waiting,
            'Dalam Proses' => $processing,
            'Ditolak' => $rejected,
            'Selesai' => $finished,
        ];

        return view('user.dashboard', compact(
            'requests',
            'total',
            'waiting',
            'processing',
            'rejected',
            'finished',
            'summary'
        ));
    }
}
