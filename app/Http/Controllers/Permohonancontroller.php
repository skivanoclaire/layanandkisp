<?php
// UserController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\UserRequest; // Pastikan menggunakan UserRequest
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function dashboard()
    {
        $requests = Auth::user()->requests()->latest()->get();
        return view('user.permohonan', compact('requests'));
    }
    
    public function submit(HttpRequest $request)
    {
        $data = $request->validate([
            'service' => 'required',
            'file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:512000',
        ]);
        
        // Generate ticket number terlebih dahulu
        $ticketNumber = UserRequest::generateTicketNumber();
        
        // Inisialisasi file path sebagai null
        $filePath = null;
        
        // Cek apakah ada file yang diupload
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $originalExtension = $request->file('file')->getClientOriginalExtension();
            // Gunakan ticket number sebagai nama file
            $filename = $ticketNumber . '.' . $originalExtension;
            $filePath = $request->file('file')->storeAs('surat', $filename, 'public');
        }
        
        // Simpan data permohonan dengan ticket number yang sudah digenerate
        UserRequest::create([
            'user_id' => Auth::id(),
            'service' => $data['service'],
            'file' => $filePath,
            'ticket_number' => $ticketNumber, // Set manual ticket number
        ]);
        
        return redirect()->route('user.permohonan')->with('success', 'Permohonan berhasil diajukan.');
    }

    public function delete($id)
    {
        $request = UserRequest::where('id', $id)
                            ->where('user_id', Auth::id()) // Pastikan user hanya bisa hapus request miliknya
                            ->firstOrFail();
        
        // Hapus file jika ada
        if ($request->file && Storage::disk('public')->exists($request->file)) {
            Storage::disk('public')->delete($request->file);
        }
        
        // Hapus record dari database
        $request->delete();
        
        return redirect()->route('user.permohonan')->with('success', 'Permohonan berhasil dihapus.');
    }
}