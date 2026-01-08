<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SubdomainIpChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubdomainIpChangeController extends Controller
{
    /**
     * Display list of IP change requests
     */
    public function index()
    {
        $requests = SubdomainIpChangeRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.subdomain.ip-change.index', compact('requests'));
    }

    /**
     * Show form to create new IP change request
     */
    public function create()
    {
        // Check if user has pending request
        $hasPending = SubdomainIpChangeRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()->route('user.subdomain.ip-change.index')
                ->with('error', 'Anda masih memiliki permohonan yang sedang diproses. Mohon tunggu hingga permohonan sebelumnya selesai.');
        }

        return view('user.subdomain.ip-change.create');
    }

    /**
     * Store new IP change request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subdomain_name' => 'required|string|max:255',
            'old_ip_address' => 'required|ip',
            'new_ip_address' => 'required|ip|different:old_ip_address',
            'reason' => 'required|string|min:10',
        ], [
            'subdomain_name.required' => 'Nama subdomain harus diisi',
            'old_ip_address.required' => 'IP address lama harus diisi',
            'old_ip_address.ip' => 'Format IP address lama tidak valid',
            'new_ip_address.required' => 'IP address baru harus diisi',
            'new_ip_address.ip' => 'Format IP address baru tidak valid',
            'new_ip_address.different' => 'IP address baru harus berbeda dengan IP address lama',
            'reason.required' => 'Alasan perubahan harus diisi',
            'reason.min' => 'Alasan perubahan minimal 10 karakter',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate unique ticket number
        do {
            $ticketNumber = SubdomainIpChangeRequest::generateTicketNumber();
        } while (SubdomainIpChangeRequest::where('ticket_number', $ticketNumber)->exists());

        $ipChangeRequest = SubdomainIpChangeRequest::create([
            'user_id' => Auth::id(),
            'ticket_number' => $ticketNumber,
            'subdomain_name' => $request->subdomain_name,
            'old_ip_address' => $request->old_ip_address,
            'new_ip_address' => $request->new_ip_address,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('user.subdomain.ip-change.show', $ipChangeRequest->id)
            ->with('success', 'Permohonan perubahan IP berhasil diajukan dengan nomor tiket: ' . $ticketNumber);
    }

    /**
     * Show detail of IP change request
     */
    public function show($id)
    {
        $request = SubdomainIpChangeRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.subdomain.ip-change.show', compact('request'));
    }
}
