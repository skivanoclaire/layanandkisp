<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as UserRequest;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil 10 permohonan terbaru milik user
        $requests = $user->requests()->latest()->take(10)->get();

        // Hitung total keseluruhan milik user
        $total = $user->requests()->count();
        $waiting = $user->requests()->where('status', 'Menunggu')->count();
        $processing = $user->requests()->where('status', 'Dalam Proses')->count();
        $rejected = $user->requests()->where('status', 'Ditolak')->count();
        $finished = $user->requests()->where('status', 'Selesai')->count();

        // Statistik bulanan pribadi
        $summary = $user->requests()
            ->selectRaw('status, COUNT(*) as total')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('status')
            ->pluck('total', 'status');

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
