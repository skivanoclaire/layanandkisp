<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SurveiKepuasanLayanan;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveiKepuasanController extends Controller
{
    /**
     * Display a listing of user's surveys
     */
    public function index()
    {
        $surveys = SurveiKepuasanLayanan::with('webMonitor')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('user.survei-kepuasan.index', compact('surveys'));
    }

    /**
     * Show the form for creating a new survey
     */
    public function create()
    {
        // Get ALL subdomains from Master Data Subdomain (open survey - anyone can rate any subdomain)
        $webMonitors = WebMonitor::whereNotNull('subdomain')
            ->whereDoesntHave('surveiKepuasan', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('subdomain')
            ->get();

        if ($webMonitors->isEmpty()) {
            return redirect()->route('survei-kepuasan.index')
                ->with('warning', 'Tidak ada subdomain yang tersedia untuk disurvei atau Anda sudah mengisi survei untuk semua subdomain.');
        }

        return view('user.survei-kepuasan.create', compact('webMonitors'));
    }

    /**
     * Store a newly created survey in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'web_monitor_id' => ['required', 'exists:web_monitors,id'],
            'rating_kecepatan' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_kemudahan' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_kualitas' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_responsif' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_keamanan' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_keseluruhan' => ['required', 'integer', 'min:1', 'max:5'],
            'kelebihan' => ['nullable', 'string', 'max:1000'],
            'kekurangan' => ['nullable', 'string', 'max:1000'],
            'saran' => ['nullable', 'string', 'max:1000'],
        ]);

        // Verify subdomain exists
        $webMonitor = WebMonitor::where('id', $validated['web_monitor_id'])
            ->firstOrFail();

        // Check if survey already exists for this user and subdomain
        $existing = SurveiKepuasanLayanan::where('user_id', Auth::id())
            ->where('web_monitor_id', $validated['web_monitor_id'])
            ->first();

        if ($existing) {
            return redirect()->route('survei-kepuasan.index')
                ->with('warning', 'Anda sudah mengisi survei untuk subdomain ini.');
        }

        // Create survey
        SurveiKepuasanLayanan::create([
            'user_id' => Auth::id(),
            'web_monitor_id' => $validated['web_monitor_id'],
            'rating_kecepatan' => $validated['rating_kecepatan'],
            'rating_kemudahan' => $validated['rating_kemudahan'],
            'rating_kualitas' => $validated['rating_kualitas'],
            'rating_responsif' => $validated['rating_responsif'],
            'rating_keamanan' => $validated['rating_keamanan'],
            'rating_keseluruhan' => $validated['rating_keseluruhan'],
            'kelebihan' => $validated['kelebihan'] ?? null,
            'kekurangan' => $validated['kekurangan'] ?? null,
            'saran' => $validated['saran'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('survei-kepuasan.index')
            ->with('success', 'Terima kasih! Survei kepuasan Anda telah berhasil disimpan.');
    }

    /**
     * Display the specified survey
     */
    public function show($id)
    {
        $survey = SurveiKepuasanLayanan::with('webMonitor')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('user.survei-kepuasan.show', compact('survey'));
    }
}
