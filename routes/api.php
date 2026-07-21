<?php

use App\Http\Controllers\Api\MasterInstansiController;
use App\Http\Controllers\Api\MasterSubdomainController;
use App\Http\Controllers\Api\SlaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (untuk SPLP - Sistem Penghubung Layanan Pemerintah)
|--------------------------------------------------------------------------
| Diamankan dua lapis: IP whitelist + API key.
| Prefix path: /api/v1/...
*/

Route::prefix('v1')
    ->middleware(['api.whitelist', 'api.key'])
    ->group(function () {
        Route::get('/master/instansi', [MasterInstansiController::class, 'index'])
            ->name('api.master.instansi');

        Route::get('/master/subdomain', [MasterSubdomainController::class, 'index'])
            ->name('api.master.subdomain');

        // Capaian SLA layanan digital (agregat, tanpa data permohonan individual/PII)
        Route::get('/sla/summary', [SlaController::class, 'summary'])
            ->name('api.sla.summary');

        Route::get('/sla/layanan/{serviceKey}', [SlaController::class, 'show'])
            ->name('api.sla.show');
    });
