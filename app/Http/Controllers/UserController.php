<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Request as UserRequest; // Pastikan model ini mengarah ke tabel 'requests'
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function dashboard()
    {
        $requests = Auth::user()->requests()->latest()->get();
        return view('user.dashboard', compact('requests'));
    }
    
    public function submit(HttpRequest $request)
    {
        $data = $request->validate([
            'service' => 'required',
            'file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:512000',
        ]);
        
        // Inisialisasi file path sebagai null
        $filePath = null;
        
        // Cek apakah ada file yang diupload
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            // Gunakan Storage facade untuk konsistensi
            $filePath = $request->file('file')->storeAs('surat', $filename, 'public');
        }
        
        // Sesuaikan field status dengan nilai enum yang ada di database
        UserRequest::create([
            'user_id' => Auth::id(),
            'service' => $data['service'],
            'file' => $filePath,
            'status' => 'Menunggu', // Sesuaikan dengan nilai enum di database
        ]);
        
        return redirect()->route('user.dashboard')->with('success', 'Permohonan berhasil diajukan.');
    }
}