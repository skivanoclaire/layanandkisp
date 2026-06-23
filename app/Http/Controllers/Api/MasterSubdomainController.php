<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstansiSubdomainResource;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class MasterSubdomainController extends Controller
{
    /**
     * Daftar instansi (Induk & Cabang Perangkat Daerah) beserta subdomain-nya.
     * Dikonsumsi via SPLP.
     */
    public function index(Request $request)
    {
        $data = UnitKerja::active()
            ->whereIn('tipe', [UnitKerja::TIPE_INDUK, UnitKerja::TIPE_CABANG])
            ->with(['webMonitors' => fn ($q) => $q->orderBy('subdomain')])
            ->orderBy('nama')
            ->get();

        return InstansiSubdomainResource::collection($data)
            ->additional(['success' => true]);
    }
}
