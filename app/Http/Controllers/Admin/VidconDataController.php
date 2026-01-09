<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VidconData;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class VidconDataController extends Controller
{
    public function index(Request $request)
    {
        $query = VidconData::with(['operators', 'unitKerja']);

        // Filter berdasarkan unit kerja
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }

        // Filter berdasarkan platform
        if ($request->filled('platform')) {
            $query->where('platform', 'like', '%' . $request->platform . '%');
        }

        $vidconData = $query->orderBy('tanggal_mulai', 'desc')->paginate(20);

        // Get all unit kerjas for filter dropdown
        $unitKerjas = \App\Models\UnitKerja::orderBy('nama')->get();

        return view('admin.vidcon-data.index', compact('vidconData', 'unitKerjas'));
    }

    public function create()
    {
        // Get all operators (users with Operator-Vidcon role)
        $operators = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'Operator-Vidcon');
        })->orderBy('name')->get();

        // Get all unit kerjas
        $unitKerjas = \App\Models\UnitKerja::orderBy('nama')->get();

        // Get AI recommendation for operators
        $recommendedOperatorIds = VidconData::recommendOperators($operators, 1);

        return view('admin.vidcon-data.create', compact('operators', 'unitKerjas', 'recommendedOperatorIds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Pemohon Info
            'nama_pemohon' => 'required|string|max:255',
            'nip_pemohon' => 'required|string|max:255',
            'email_pemohon' => 'required|email|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'no_hp' => 'required|string|max:20',
            'nomor_surat' => 'nullable|string|max:255',

            // Kegiatan Info
            'judul_kegiatan' => 'required|string|max:500',
            'deskripsi_kegiatan' => 'nullable|string',
            'lokasi' => 'nullable|string|max:255',
            'jumlah_peserta' => 'nullable|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'platform' => 'required|string|max:255',
            'platform_lainnya' => 'nullable|string|max:255',
            'keperluan_khusus' => 'nullable|string',

            // Meeting Info
            'link_meeting' => 'nullable|url|max:500',
            'meeting_id' => 'nullable|string|max:200',
            'meeting_password' => 'nullable|string|max:200',
            'informasi_tambahan' => 'nullable|string',

            // Legacy fields
            'operator' => 'nullable|string|max:255',
            'dokumentasi' => 'nullable|string|max:255',
            'akun_zoom' => 'nullable|string|in:001,002,003,004',
            'informasi_pimpinan' => 'nullable|string',
            'keterangan' => 'nullable|string',

            // Operators
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
        ]);

        // Handle platform_lainnya
        if ($request->platform === 'Lainnya' && $request->platform_lainnya) {
            $validated['platform'] = $request->platform_lainnya;
        }

        // Auto-fill nama_instansi from unit_kerja
        $unitKerja = \App\Models\UnitKerja::find($request->unit_kerja_id);
        $validated['nama_instansi'] = $unitKerja ? $unitKerja->nama : null;

        // Create VidconData
        $vidconData = VidconData::create($validated);

        // Attach operators if provided
        if ($request->has('operators') && is_array($request->operators)) {
            $vidconData->operators()->sync($request->operators);
            // Update legacy operator field
            $operatorNames = \App\Models\User::whereIn('id', $request->operators)->pluck('name')->join(', ');
            $vidconData->update(['operator' => $operatorNames]);
        }

        return redirect()->route('admin.vidcon-data.index')
            ->with('success', 'Data vidcon berhasil ditambahkan.');
    }

    public function show(VidconData $vidconData)
    {
        // Eager load relationships
        $vidconData->load(['unitKerja', 'operators', 'documentations.uploader']);

        return view('admin.vidcon-data.show', compact('vidconData'));
    }

    public function edit(VidconData $vidconData)
    {
        // Get all operators (users with Operator-Vidcon role)
        $operators = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'Operator-Vidcon');
        })->orderBy('name')->get();

        // Get all unit kerjas
        $unitKerjas = \App\Models\UnitKerja::orderBy('nama')->get();

        // Load relationships
        $vidconData->load('operators', 'unitKerja');

        // Get AI recommendation for operators
        $recommendedOperatorIds = VidconData::recommendOperators($operators, 1);

        return view('admin.vidcon-data.edit', compact('vidconData', 'operators', 'unitKerjas', 'recommendedOperatorIds'));
    }

    public function update(Request $request, VidconData $vidconData)
    {
        $validated = $request->validate([
            // Pemohon Info
            'nama_pemohon' => 'required|string|max:255',
            'nip_pemohon' => 'required|string|max:255',
            'email_pemohon' => 'required|email|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'no_hp' => 'required|string|max:20',
            'nomor_surat' => 'nullable|string|max:255',

            // Kegiatan Info
            'judul_kegiatan' => 'required|string|max:500',
            'deskripsi_kegiatan' => 'nullable|string',
            'lokasi' => 'nullable|string|max:255',
            'jumlah_peserta' => 'nullable|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'platform' => 'required|string|max:255',
            'platform_lainnya' => 'nullable|string|max:255',
            'keperluan_khusus' => 'nullable|string',

            // Meeting Info
            'link_meeting' => 'nullable|url|max:500',
            'meeting_id' => 'nullable|string|max:200',
            'meeting_password' => 'nullable|string|max:200',
            'informasi_tambahan' => 'nullable|string',

            // Legacy fields
            'operator' => 'nullable|string|max:255',
            'dokumentasi' => 'nullable|string|max:255',
            'akun_zoom' => 'nullable|string|in:001,002,003,004',
            'informasi_pimpinan' => 'nullable|string',
            'keterangan' => 'nullable|string',

            // Operators
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
        ]);

        // Handle platform_lainnya
        if ($request->platform === 'Lainnya' && $request->platform_lainnya) {
            $validated['platform'] = $request->platform_lainnya;
        }

        // Auto-fill nama_instansi from unit_kerja
        $unitKerja = \App\Models\UnitKerja::find($request->unit_kerja_id);
        $validated['nama_instansi'] = $unitKerja ? $unitKerja->nama : null;

        // Update VidconData
        $vidconData->update($validated);

        // Sync operators
        if ($request->has('operators') && is_array($request->operators) && count($request->operators) > 0) {
            $vidconData->operators()->sync($request->operators);
            // Update legacy operator field
            $operatorNames = \App\Models\User::whereIn('id', $request->operators)->pluck('name')->join(', ');
            $vidconData->update(['operator' => $operatorNames]);
        } else {
            $vidconData->operators()->detach();
            $vidconData->update(['operator' => null]);
        }

        return redirect()->route('admin.vidcon-data.index')
            ->with('success', 'Data vidcon berhasil diupdate.');
    }

    public function destroy(VidconData $vidconData)
    {
        $vidconData->delete();

        return redirect()->route('admin.vidcon-data.index')
            ->with('success', 'Data vidcon berhasil dihapus.');
    }

    /**
     * Check if selected Zoom account has scheduling conflict
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkZoomConflict(Request $request)
    {
        $request->validate([
            'akun_zoom' => 'required|string|in:001,002,003,004',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'exclude_id' => 'nullable|integer', // For edit mode
        ]);

        $akunZoom = $request->akun_zoom;
        $tanggalMulai = $request->tanggal_mulai;
        $tanggalSelesai = $request->tanggal_selesai;
        $jamMulai = $request->jam_mulai;
        $jamSelesai = $request->jam_selesai;
        $excludeId = $request->exclude_id;

        // Query to find conflicting bookings
        $query = VidconData::where('akun_zoom', $akunZoom)
            ->where(function ($q) use ($tanggalMulai, $tanggalSelesai) {
                // Check if dates overlap
                $q->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                  ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                  ->orWhere(function ($q2) use ($tanggalMulai, $tanggalSelesai) {
                      // Check if requested period is within existing period
                      $q2->where('tanggal_mulai', '<=', $tanggalMulai)
                         ->where('tanggal_selesai', '>=', $tanggalSelesai);
                  });
            });

        // Exclude current record in edit mode
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $potentialConflicts = $query->get();

        // Check time overlap for matching dates
        $conflicts = [];
        foreach ($potentialConflicts as $booking) {
            // Check if times overlap
            $existingStart = strtotime($booking->jam_mulai);
            $existingEnd = strtotime($booking->jam_selesai);
            $requestStart = strtotime($jamMulai);
            $requestEnd = strtotime($jamSelesai);

            // Time overlap logic
            $hasTimeOverlap = (
                ($requestStart >= $existingStart && $requestStart < $existingEnd) ||
                ($requestEnd > $existingStart && $requestEnd <= $existingEnd) ||
                ($requestStart <= $existingStart && $requestEnd >= $existingEnd)
            );

            if ($hasTimeOverlap) {
                $conflicts[] = [
                    'id' => $booking->id,
                    'akun_zoom' => $booking->akun_zoom,
                    'judul_kegiatan' => $booking->judul_kegiatan,
                    'tanggal_mulai' => $booking->tanggal_mulai->format('d/m/Y'),
                    'tanggal_selesai' => $booking->tanggal_selesai->format('d/m/Y'),
                    'jam_mulai' => $booking->jam_mulai->format('H:i'),
                    'jam_selesai' => $booking->jam_selesai->format('H:i'),
                ];
            }
        }

        return response()->json([
            'has_conflict' => count($conflicts) > 0,
            'conflicts' => $conflicts,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = VidconData::with('unitKerja');

        // Apply filters
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('platform')) {
            $query->where('platform', 'like', '%' . $request->platform . '%');
        }

        $data = $query->orderBy('tanggal_mulai', 'desc')->get();

        $filename = 'data-vidcon-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'No', 'Instansi', 'Nomor Surat', 'Judul Kegiatan', 'Lokasi',
                'Tanggal Mulai', 'Tanggal Selesai', 'Jam Mulai', 'Jam Selesai',
                'Platform', 'Operator', 'Dokumentasi', 'Akun Zoom',
                'Informasi Pimpinan', 'Keterangan'
            ]);

            // Data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->no,
                    $row->unitKerja->nama ?? '-',
                    $row->nomor_surat,
                    $row->judul_kegiatan,
                    $row->lokasi,
                    $row->tanggal_mulai ? $row->tanggal_mulai->format('d/m/Y') : '',
                    $row->tanggal_selesai ? $row->tanggal_selesai->format('d/m/Y') : '',
                    $row->jam_mulai ? $row->jam_mulai->format('H:i') : '',
                    $row->jam_selesai ? $row->jam_selesai->format('H:i') : '',
                    $row->platform,
                    $row->operator,
                    $row->dokumentasi,
                    $row->akun_zoom,
                    $row->informasi_pimpinan,
                    $row->keterangan,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = VidconData::with('unitKerja');

        // Apply same filters as index
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('platform')) {
            $query->where('platform', 'like', '%' . $request->platform . '%');
        }

        $vidconData = $query->orderBy('tanggal_mulai', 'desc')->get();

        // Configure Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        // Instantiate Dompdf
        $dompdf = new Dompdf($options);

        // Render view to HTML
        $html = view('admin.vidcon-data.pdf', compact('vidconData'))->render();

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render PDF (first pass to get total pages)
        $dompdf->render();

        // Output PDF for download
        return $dompdf->stream('data-vidcon-' . date('Y-m-d') . '.pdf', [
            'Attachment' => true
        ]);
    }
}
