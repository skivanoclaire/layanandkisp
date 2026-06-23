<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstansiResource;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class MasterInstansiController extends Controller
{
    /**
     * Daftar instansi bertipe Induk & Cabang Perangkat Daerah.
     * Dikonsumsi via SPLP.
     */
    public function index(Request $request)
    {
        $data = UnitKerja::active()
            ->whereIn('tipe', [UnitKerja::TIPE_INDUK, UnitKerja::TIPE_CABANG])
            ->orderBy('nama')
            ->get();

        return InstansiResource::collection($data)
            ->additional(['success' => true]);
    }
}
