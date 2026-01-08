<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleAsetTikHardware;
use App\Models\GoogleAsetTikSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleAsetTikController extends Controller
{
    /**
     * Dashboard dengan statistik
     */
    public function dashboard()
    {
        // Total assets
        $totalHardware = GoogleAsetTikHardware::count();
        $totalSoftware = GoogleAsetTikSoftware::count();

        // Total nilai
        $totalNilaiHardware = GoogleAsetTikHardware::sum('nilai_perolehan');
        $totalNilaiSoftware = GoogleAsetTikSoftware::sum('harga');

        // By kondisi (hardware)
        $byKondisi = GoogleAsetTikHardware::select('keadaan_barang', DB::raw('count(*) as total'))
            ->groupBy('keadaan_barang')
            ->get();

        // By tahun
        $byTahun = GoogleAsetTikHardware::select('tahun', DB::raw('count(*) as total'), DB::raw('sum(nilai_perolehan) as nilai'))
            ->whereNotNull('tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'desc')
            ->take(10)
            ->get();

        // By OPD
        $byOpd = GoogleAsetTikHardware::select('nama_opd', DB::raw('count(*) as total'))
            ->groupBy('nama_opd')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // Top 10 aset bernilai tinggi
        $topAssets = GoogleAsetTikHardware::orderBy('nilai_perolehan', 'desc')
            ->take(10)
            ->get();

        return view('admin.google-aset-tik.dashboard', compact(
            'totalHardware',
            'totalSoftware',
            'totalNilaiHardware',
            'totalNilaiSoftware',
            'byKondisi',
            'byTahun',
            'byOpd',
            'topAssets'
        ));
    }

    /**
     * List hardware dengan filter dan search
     */
    public function hardware(Request $request)
    {
        $query = GoogleAsetTikHardware::query();

        // Filter OPD
        if ($request->filled('opd')) {
            $query->forOpd($request->opd);
        }

        // Filter kondisi
        if ($request->filled('kondisi')) {
            $query->kondisi($request->kondisi);
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->tahun($request->tahun);
        }

        // Filter jenis
        if ($request->filled('jenis')) {
            $query->jenis($request->jenis);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_aset', 'like', "%{$search}%")
                  ->orWhere('merk_type', 'like', "%{$search}%")
                  ->orWhere('kode_gab_barang', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $hardware = $query->paginate(25)->withQueryString();

        // Data untuk filter
        $opdList = GoogleAsetTikHardware::select('nama_opd')->distinct()->pluck('nama_opd');
        $kondisiList = GoogleAsetTikHardware::select('keadaan_barang')->distinct()->pluck('keadaan_barang');
        $tahunList = GoogleAsetTikHardware::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $jenisList = GoogleAsetTikHardware::select('jenis_aset_tik')->distinct()->pluck('jenis_aset_tik');

        return view('admin.google-aset-tik.hardware.index', compact(
            'hardware',
            'opdList',
            'kondisiList',
            'tahunList',
            'jenisList'
        ));
    }

    /**
     * Detail hardware
     */
    public function showHardware($id)
    {
        $hardware = GoogleAsetTikHardware::findOrFail($id);

        return view('admin.google-aset-tik.hardware.show', compact('hardware'));
    }

    /**
     * List software dengan filter
     */
    public function software(Request $request)
    {
        $query = GoogleAsetTikSoftware::query();

        // Filter OPD
        if ($request->filled('opd')) {
            $query->forOpd($request->opd);
        }

        // Filter status aktif
        if ($request->filled('is_aktif')) {
            $query->where('is_aktif', $request->is_aktif);
        }

        // Filter jenis
        if ($request->filled('jenis')) {
            $query->jenis($request->jenis);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        $software = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        // Data untuk filter
        $opdList = GoogleAsetTikSoftware::select('nama_opd')->distinct()->pluck('nama_opd');
        $jenisList = GoogleAsetTikSoftware::select('jenis_perangkat_lunak')->distinct()->pluck('jenis_perangkat_lunak');

        return view('admin.google-aset-tik.software.index', compact(
            'software',
            'opdList',
            'jenisList'
        ));
    }

    /**
     * Detail software
     */
    public function showSoftware($id)
    {
        $software = GoogleAsetTikSoftware::findOrFail($id);

        return view('admin.google-aset-tik.software.show', compact('software'));
    }
}
