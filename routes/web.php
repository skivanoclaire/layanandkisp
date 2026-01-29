<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\WebMonitorController;
use App\Http\Controllers\DigitalFormController;
use App\Http\Controllers\RekomendasiAplikasiController;
use App\Http\Controllers\User\EmailRequestController;
use App\Http\Controllers\Admin\EmailRequestAdminController;
use App\Http\Controllers\User\RekomendasiUsulanController;
use App\Http\Controllers\User\RekomendasiFasePengembanganController;
use App\Http\Controllers\User\RekomendasiEvaluasiController;
use App\Http\Controllers\Admin\RekomendasiVerifikasiController;
use App\Http\Controllers\Admin\RekomendasiSuratController;
use App\Http\Controllers\Admin\RekomendasiMonitoringController;
use App\Http\Controllers\Operator\TikBorrowingController as OpBorrow;
use App\Http\Controllers\Admin\TikBorrowingAdminController as AdminBorrow;
use App\Http\Controllers\Admin\SimpegCheckController;
use App\Http\Controllers\Admin\UnitKerjaController;
use App\Http\Controllers\VerificationController;


// Halaman Publik Layanan
Route::get('/', function () {
    return view('welcome');
});

Route::get('/services', function () {
    return view('services');
});

// Public Document Verification
Route::get('/verify/{code}', [VerificationController::class, 'verify'])->name('verify.document');

// File Download Route (untuk handle storage file access)
Route::get('/download/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan: ' . $fullPath);
    }

    return response()->file($fullPath);
})->where('path', '.*')->name('file.download')->middleware('auth');

Route::get('/request', function () {
    return view('request');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/syarat-email', function () {
    return view('syaratemail');
})->name('syarat.email');


// Breeze Auth
require __DIR__ . '/auth.php';

// User biasa (dashboard & profile) - bisa diakses semua authenticated users dengan permission
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Hanya untuk user TER-VERIFIKASI
Route::middleware(['auth', 'verified.user'])->group(function () {
    Route::get('/digital', [DigitalFormController::class, 'index'])->name('user.digital');
    Route::get('/digital/rekomendasi', function () {
        return view('user.digital-rekomendasi');
    })->name('user.digital.rekomendasi');

    Route::middleware(['permission:user.permohonan'])->group(function () {
        Route::get('/unggahmanual', [PermohonanController::class, 'dashboard'])->name('user.permohonan');
        Route::post('/unggahmanual/submit', [PermohonanController::class, 'submit'])->name('user.submit');
        Route::delete('/unggahmanual/request/{id}', [PermohonanController::class, 'delete'])->name('user.delete');
    });

    Route::prefix('/digital/rekomendasi/aplikasi')->group(function () {
        Route::get('/', [RekomendasiAplikasiController::class, 'index'])->name('user.rekomendasi.aplikasi.index');
        Route::get('/create', [RekomendasiAplikasiController::class, 'create'])->name('user.rekomendasi.aplikasi.create');
        Route::post('/store', [RekomendasiAplikasiController::class, 'store'])->name('user.rekomendasi.aplikasi.store');
        Route::get('/{id}', [RekomendasiAplikasiController::class, 'show'])->name('user.rekomendasi.aplikasi.show');
        Route::get('/{id}/edit', [RekomendasiAplikasiController::class, 'edit'])->name('user.rekomendasi.aplikasi.edit');
        Route::put('/{id}', [RekomendasiAplikasiController::class, 'update'])->name('user.rekomendasi.aplikasi.update');
        Route::delete('/{id}', [RekomendasiAplikasiController::class, 'destroy'])->name('user.rekomendasi.aplikasi.destroy');
        Route::get('/{id}/download-pdf', [RekomendasiAplikasiController::class, 'downloadPDF'])->name('user.rekomendasi.aplikasi.download-pdf');
    });

    // FASE 1: Usulan Pertimbangan - V2
    Route::middleware(['permission:user.rekomendasi.usulan.create'])
        ->prefix('digital/rekomendasi/usulan')
        ->name('user.rekomendasi.usulan.')
        ->group(function () {
            Route::get('/', [RekomendasiUsulanController::class, 'index'])->name('index');
            Route::get('/create', [RekomendasiUsulanController::class, 'create'])->name('create');
            Route::post('/', [RekomendasiUsulanController::class, 'store'])->name('store');
            Route::get('/{id}', [RekomendasiUsulanController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RekomendasiUsulanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RekomendasiUsulanController::class, 'update'])->name('update');
            Route::delete('/{id}', [RekomendasiUsulanController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/submit', [RekomendasiUsulanController::class, 'submit'])->name('submit');

            // Document management
            Route::post('/{id}/dokumen', [RekomendasiUsulanController::class, 'uploadDokumen'])->name('dokumen.upload');
            Route::get('/{id}/dokumen/{dokumenId}', [RekomendasiUsulanController::class, 'downloadDokumen'])->name('dokumen.download');

            // Download surat persetujuan/respons Kementerian
            Route::get('/{id}/download-surat-kementerian', [RekomendasiUsulanController::class, 'downloadSuratKementerian'])->name('download-surat-kementerian');
        });

    // FASE PENGEMBANGAN: Simple Document Upload (3 Phases)
    Route::middleware(['permission:user.fase-pengembangan'])
        ->prefix('fase-pengembangan')
        ->name('fase-pengembangan.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\User\FasePengembanganController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\User\FasePengembanganController::class, 'show'])->name('show');
            Route::post('/{id}/upload', [\App\Http\Controllers\User\FasePengembanganController::class, 'uploadDokumen'])->name('upload');
            Route::get('/{id}/dokumen/{dokumenId}', [\App\Http\Controllers\User\FasePengembanganController::class, 'downloadDokumen'])->name('dokumen.download');
            Route::delete('/{id}/dokumen/{dokumenId}', [\App\Http\Controllers\User\FasePengembanganController::class, 'deleteDokumen'])->name('dokumen.delete');
        });

    // Rekomendasi V2 - Development Phase Tracking (User)
    Route::middleware(['permission:user.rekomendasi.fase.update'])
        ->prefix('rekomendasi/fase-pengembangan')
        ->name('rekomendasi.fase.')
        ->group(function () {
            Route::get('/{rekomendasiId}', [RekomendasiFasePengembanganController::class, 'index'])->name('index');
            Route::get('/{rekomendasiId}/fase/{faseId}', [RekomendasiFasePengembanganController::class, 'show'])->name('show');
            Route::put('/{rekomendasiId}/fase/{faseId}/progress', [RekomendasiFasePengembanganController::class, 'updateProgress'])->name('progress');
            Route::post('/{rekomendasiId}/fase/{faseId}/dokumen', [RekomendasiFasePengembanganController::class, 'uploadDokumen'])->name('dokumen.upload');
            Route::delete('/{rekomendasiId}/fase/{faseId}/dokumen/{dokumenId}', [RekomendasiFasePengembanganController::class, 'deleteDokumen'])->name('dokumen.delete');

            Route::post('/{rekomendasiId}/fase/{faseId}/milestone', [RekomendasiFasePengembanganController::class, 'createMilestone'])->name('milestone.create');
            Route::put('/{rekomendasiId}/fase/{faseId}/milestone/{milestoneId}', [RekomendasiFasePengembanganController::class, 'updateMilestone'])->name('milestone.update');
            Route::delete('/{rekomendasiId}/fase/{faseId}/milestone/{milestoneId}', [RekomendasiFasePengembanganController::class, 'deleteMilestone'])->name('milestone.delete');

            Route::post('/{rekomendasiId}/fase/{faseId}/complete', [RekomendasiFasePengembanganController::class, 'markPhaseComplete'])->name('complete');

            Route::get('/{rekomendasiId}/team', [RekomendasiFasePengembanganController::class, 'manageTeam'])->name('team');
            Route::post('/{rekomendasiId}/team', [RekomendasiFasePengembanganController::class, 'addTeamMember'])->name('team.add');
            Route::delete('/{rekomendasiId}/team/{teamId}', [RekomendasiFasePengembanganController::class, 'deleteTeamMember'])->name('team.delete');
        });

    // Rekomendasi V2 - Evaluation (User)
    Route::middleware(['permission:user.rekomendasi.evaluasi.create'])
        ->prefix('rekomendasi/evaluasi')
        ->name('rekomendasi.evaluasi.')
        ->group(function () {
            Route::get('/{rekomendasiId}', [RekomendasiEvaluasiController::class, 'index'])->name('index');
            Route::get('/{rekomendasiId}/create', [RekomendasiEvaluasiController::class, 'create'])->name('create');
            Route::post('/{rekomendasiId}', [RekomendasiEvaluasiController::class, 'store'])->name('store');
            Route::get('/{rekomendasiId}/{evaluasiId}', [RekomendasiEvaluasiController::class, 'show'])->name('show');
            Route::get('/{rekomendasiId}/{evaluasiId}/edit', [RekomendasiEvaluasiController::class, 'edit'])->name('edit');
            Route::put('/{rekomendasiId}/{evaluasiId}', [RekomendasiEvaluasiController::class, 'update'])->name('update');
            Route::delete('/{rekomendasiId}/{evaluasiId}', [RekomendasiEvaluasiController::class, 'destroy'])->name('destroy');
        });
});

// Untuk admin
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/chart-data', [AdminController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/permohonan', [AdminController::class, 'permohonan'])->name('permohonan');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/update-status/{userRequest}', [AdminController::class, 'updateStatus'])->name('update-status');
    Route::delete('/requests/{userRequest}', [AdminController::class, 'deleteRequest'])->name('delete-request');
    // Web Monitor with Cloudflare Integration
    Route::prefix('web-monitor')->name('web-monitor.')->group(function () {
        // STATIC ROUTES FIRST (no parameters)
        Route::get('/', [WebMonitorController::class, 'index'])->name('index');
        Route::get('/create', [WebMonitorController::class, 'create'])->name('create');
        Route::post('/', [WebMonitorController::class, 'store'])->name('store');

        // Additional Cloudflare actions
        Route::post('/sync-cloudflare', [WebMonitorController::class, 'syncWithCloudflare'])->name('sync-cloudflare');
        Route::post('/check-all-status', [WebMonitorController::class, 'checkAllStatus'])->name('check-all-status');

        // Check IP Publik routes (MOVED UP before parameterized routes)
        Route::get('/check-ip-publik', [WebMonitorController::class, 'checkIpPublik'])->name('check-ip-publik');
        Route::get('/check-ip-availability', [WebMonitorController::class, 'checkIpAvailability'])->name('check-ip-availability');

        // PARAMETERIZED ROUTES LAST (with {webMonitor} parameter)
        Route::get('/{webMonitor}', [WebMonitorController::class, 'show'])->name('show');
        Route::get('/{webMonitor}/edit', [WebMonitorController::class, 'edit'])->name('edit');
        Route::put('/{webMonitor}', [WebMonitorController::class, 'update'])->name('update');
        Route::delete('/{webMonitor}', [WebMonitorController::class, 'destroy'])->name('destroy');
        Route::post('/{webMonitor}/check-status', [WebMonitorController::class, 'checkStatus'])->name('check-status');

        // TTE PDF Routes
        Route::get('/{webMonitor}/generate-tte-pdf', [WebMonitorController::class, 'generateTtePdf'])->name('generate-tte-pdf');
        Route::get('/{webMonitor}/download-tte-pdf', [WebMonitorController::class, 'downloadTtePdf'])->name('download-tte-pdf');

        // IP Terpakai CRUD (alias untuk web-monitor CRUD dengan konteks IP)
        Route::prefix('ip-terpakai')->name('ip-terpakai.')->group(function () {
            Route::get('/create', [WebMonitorController::class, 'create'])->name('create');
            Route::get('/{webMonitor}/edit', [WebMonitorController::class, 'edit'])->name('edit');
            Route::delete('/{webMonitor}', [WebMonitorController::class, 'destroy'])->name('destroy');
        });
    });

    Route::post('/users/{user}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
    Route::post('/users/{user}/unverify', [AdminController::class, 'unverifyUser'])->name('users.unverify');

    // Rekomendasi V2 - Verification
    Route::middleware(['permission:admin.rekomendasi.verifikasi.view'])
        ->prefix('rekomendasi/verifikasi')
        ->name('rekomendasi.verifikasi.')
        ->group(function () {
            Route::get('/', [RekomendasiVerifikasiController::class, 'index'])->name('index');
            Route::get('/{id}', [RekomendasiVerifikasiController::class, 'show'])->name('show');
            Route::post('/{id}/start', [RekomendasiVerifikasiController::class, 'startVerification'])->name('start');
            Route::get('/{id}/verify', [RekomendasiVerifikasiController::class, 'verify'])->name('verify');
            Route::put('/{id}/checklist', [RekomendasiVerifikasiController::class, 'updateChecklist'])->name('checklist.update');
            Route::post('/{id}/approve', [RekomendasiVerifikasiController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [RekomendasiVerifikasiController::class, 'reject'])->name('reject');
            Route::post('/{id}/revision', [RekomendasiVerifikasiController::class, 'requestRevision'])->name('revision');
            Route::get('/{id}/dokumen/{dokumenId}', [RekomendasiVerifikasiController::class, 'downloadDokumen'])->name('dokumen.download');

            // Update status Kementerian
            Route::post('/{id}/ministry-status', [RekomendasiVerifikasiController::class, 'updateMinistryStatus'])->name('ministry-status');

            // Export PDF
            Route::get('/{id}/export-pdf', [RekomendasiVerifikasiController::class, 'exportPdf'])->name('export-pdf');
        });

    // Rekomendasi V2 - Fase Pengembangan (Admin Monitoring)
    Route::middleware(['permission:admin.fase-pengembangan.view'])
        ->prefix('rekomendasi/fase-pengembangan')
        ->name('fase-pengembangan.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FasePengembanganAdminController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\FasePengembanganAdminController::class, 'show'])->name('show');
            Route::get('/{id}/dokumen/{dokumenId}', [\App\Http\Controllers\Admin\FasePengembanganAdminController::class, 'downloadDokumen'])->name('dokumen.download');
            Route::delete('/{id}/dokumen/{dokumenId}', [\App\Http\Controllers\Admin\FasePengembanganAdminController::class, 'deleteDokumen'])->name('dokumen.delete');
            Route::post('/{id}/note', [\App\Http\Controllers\Admin\FasePengembanganAdminController::class, 'addNote'])->name('note.add');
        });

    // Rekomendasi V2 - Monitoring & Dashboard
    Route::middleware(['permission:admin.rekomendasi.monitoring.view'])
        ->prefix('rekomendasi/monitoring')
        ->name('rekomendasi.monitoring.')
        ->group(function () {
            Route::get('/', [RekomendasiMonitoringController::class, 'dashboard'])->name('dashboard');
            Route::get('/fase/{fase}', [RekomendasiMonitoringController::class, 'byPhase'])->name('by-phase');
            Route::get('/status/{status}', [RekomendasiMonitoringController::class, 'byStatus'])->name('by-status');
            Route::get('/history/{id}', [RekomendasiMonitoringController::class, 'history'])->name('history');
            Route::get('/export/excel', [RekomendasiMonitoringController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [RekomendasiMonitoringController::class, 'exportPDF'])->name('export.pdf');
            Route::get('/chart-data', [RekomendasiMonitoringController::class, 'getChartData'])->name('chart-data');
        });

    // Rekomendasi V2 - Letter Management (DISABLED - fitur dipindahkan ke Verifikasi)
    // Route::middleware(['permission:admin.rekomendasi.surat.manage'])
    //     ->prefix('rekomendasi/surat')
    //     ->name('rekomendasi.surat.')
    //     ->group(function () {
    //         Route::get('/', [RekomendasiSuratController::class, 'index'])->name('index');
    //         Route::get('/proposal/{proposalId}/create', [RekomendasiSuratController::class, 'create'])->name('create');
    //         Route::post('/proposal/{proposalId}', [RekomendasiSuratController::class, 'store'])->name('store');
    //         Route::get('/{id}', [RekomendasiSuratController::class, 'show'])->name('show');
    //         Route::get('/{id}/edit', [RekomendasiSuratController::class, 'edit'])->name('edit');
    //         Route::put('/{id}', [RekomendasiSuratController::class, 'update'])->name('update');
    //         Route::post('/{id}/sign', [RekomendasiSuratController::class, 'sign'])->name('sign');
    //         Route::post('/{id}/delivery', [RekomendasiSuratController::class, 'recordDelivery'])->name('delivery');
    //         Route::post('/{id}/ministry-status', [RekomendasiSuratController::class, 'updateMinistryStatus'])->name('ministry-status');
    //         Route::get('/{id}/download', [RekomendasiSuratController::class, 'downloadSigned'])->name('download');
    //     });

    Route::get('/simpeg-check', [SimpegCheckController::class, 'index'])->name('simpeg.index');
    Route::post('/simpeg-check', [SimpegCheckController::class, 'check'])->name('simpeg.check');
    Route::post('/simpeg-check/save-to-user', [SimpegCheckController::class, 'saveToUser'])->name('simpeg.saveToUser');

    // Rekomendasi Aplikasi (Admin melihat semua data)
    Route::prefix('/rekomendasi')->name('rekomendasi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'reject'])->name('reject');
        Route::post('/{id}/request-revision', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'requestRevision'])->name('request-revision');
        Route::post('/{id}/change-status', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'changeStatus'])->name('change-status');
        Route::get('/{id}/pdf', [\App\Http\Controllers\Admin\RekomendasiAplikasiController::class, 'generatePDF'])->name('pdf');
    });

    // Master Data Unit Kerja
    Route::resource('unit-kerja', UnitKerjaController::class);

    // Role Management (CRUD)
    Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');

    // Role Permissions Management
    Route::get('/role-permissions', [AdminController::class, 'rolePermissions'])->name('role-permissions');
    Route::post('/role-permissions', [AdminController::class, 'updateRolePermissions'])->name('role-permissions.update');

    // Master Data Email Accounts
    Route::get('/email-accounts', [\App\Http\Controllers\Admin\EmailAccountController::class, 'index'])->name('email-accounts.index');
    Route::post('/email-accounts/sync', [\App\Http\Controllers\Admin\EmailAccountController::class, 'sync'])->name('email-accounts.sync');
    Route::post('/email-accounts/test-connection', [\App\Http\Controllers\Admin\EmailAccountController::class, 'testConnection'])->name('email-accounts.test-connection');
    Route::get('/email-accounts/import-nip', [\App\Http\Controllers\Admin\EmailAccountController::class, 'showImportNip'])->name('email-accounts.import-nip.show');
    Route::post('/email-accounts/import-nip', [\App\Http\Controllers\Admin\EmailAccountController::class, 'importNip'])->name('email-accounts.import-nip');
    Route::get('/email-accounts/{emailAccount}', [\App\Http\Controllers\Admin\EmailAccountController::class, 'show'])->name('email-accounts.show');
    Route::put('/email-accounts/{emailAccount}/update-nip', [\App\Http\Controllers\Admin\EmailAccountController::class, 'updateNip'])->name('email-accounts.update-nip');
    Route::put('/email-accounts/{emailAccount}/update-requester-info', [\App\Http\Controllers\Admin\EmailAccountController::class, 'updateRequesterInfo'])->name('email-accounts.update-requester-info');
    Route::delete('/email-accounts/{emailAccount}', [\App\Http\Controllers\Admin\EmailAccountController::class, 'destroy'])->name('email-accounts.destroy');
    Route::delete('/email-accounts-destroy-all', [\App\Http\Controllers\Admin\EmailAccountController::class, 'destroyAll'])->name('email-accounts.destroy-all');

});

// Kelola Data Vidcon - Accessible by Admin and Operator-Vidcon
Route::middleware(['auth', 'role:Admin,Operator-Vidcon'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('vidcon-data', \App\Http\Controllers\Admin\VidconDataController::class)
            ->parameters(['vidcon-data' => 'vidconData']);
        Route::get('/vidcon-data-export-excel', [\App\Http\Controllers\Admin\VidconDataController::class, 'exportExcel'])->name('vidcon-data.export-excel');
        Route::get('/vidcon-data-export-pdf', [\App\Http\Controllers\Admin\VidconDataController::class, 'exportPdf'])->name('vidcon-data.export-pdf');
        Route::post('/vidcon-data-check-zoom-conflict', [\App\Http\Controllers\Admin\VidconDataController::class, 'checkZoomConflict'])->name('vidcon-data.check-zoom-conflict');

        // Kelola Data Operator
        Route::get('/operators', [\App\Http\Controllers\Admin\OperatorManagementController::class, 'index'])->name('operators.index');
        Route::get('/operators/create', [\App\Http\Controllers\Admin\OperatorManagementController::class, 'create'])->name('operators.create');
        Route::post('/operators', [\App\Http\Controllers\Admin\OperatorManagementController::class, 'store'])->name('operators.store');
        Route::delete('/operators/{user}', [\App\Http\Controllers\Admin\OperatorManagementController::class, 'destroy'])->name('operators.destroy');
    });

// Form Permohonan Email Digital level Verified User
Route::middleware(['auth','verified.user','permission:user.email.index,user.email.create'])
    ->prefix('digital/email')
    ->name('user.email.')
    ->group(function () {
        Route::get('/',           [EmailRequestController::class, 'index'])->name('index');   // daftar
        Route::get('/create',     [EmailRequestController::class, 'create'])->name('create'); // form
        Route::post('/submit',    [EmailRequestController::class, 'store'])->name('store');   // submit
        Route::get('/thanks/{ticket}', [EmailRequestController::class, 'thanks'])->name('thanks');
        Route::get('/{id}/edit',    [EmailRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}',         [EmailRequestController::class, 'update'])->name('update');
        Route::delete('/{id}',      [EmailRequestController::class, 'destroy'])->name('destroy');

        // Check email availability
        Route::post('/check-email', [EmailRequestController::class, 'checkEmailAvailability'])->name('check-email');

        // Check NIP availability
        Route::post('/check-nip', [EmailRequestController::class, 'checkNipAvailability'])->name('check-nip');
    });

// Form Permohonan Email Digital level Admin
Route::middleware(['auth','role:Admin'])->prefix('admin/digital/email')->name('admin.email.')->group(function () {
    Route::get('/',           [EmailRequestAdminController::class, 'index'])->name('index');
    Route::get('/export-excel', [EmailRequestAdminController::class, 'exportExcel'])->name('export-excel');
    Route::get('/export-pdf', [EmailRequestAdminController::class, 'exportPdf'])->name('export-pdf');
    Route::get('/export/csv', [EmailRequestAdminController::class, 'exportCsv'])->name('export'); // filter opsional: ?status=selesai
    Route::get('/{id}',       [EmailRequestAdminController::class, 'show'])->name('show');
    Route::post('/{id}/status', [EmailRequestAdminController::class, 'updateStatus'])->name('status');
    Route::post('/{id}/update-password', [EmailRequestAdminController::class, 'updatePassword'])->name('update-password');
});

// Form Permohonan Subdomain level Verified User
Route::middleware(['auth','verified.user','permission:user.subdomain.index,user.subdomain.create'])
    ->prefix('digital/subdomain')
    ->name('user.subdomain.')
    ->group(function () {
        Route::get('/',           [\App\Http\Controllers\User\SubdomainRequestController::class, 'index'])->name('index');
        Route::get('/create',     [\App\Http\Controllers\User\SubdomainRequestController::class, 'create'])->name('create');
        Route::post('/submit',    [\App\Http\Controllers\User\SubdomainRequestController::class, 'store'])->name('store');
        Route::get('/thanks/{ticket}', [\App\Http\Controllers\User\SubdomainRequestController::class, 'thanks'])->name('thanks');
        Route::get('/{id}/edit',  [\App\Http\Controllers\User\SubdomainRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [\App\Http\Controllers\User\SubdomainRequestController::class, 'update'])->name('update');
        Route::delete('/{id}',    [\App\Http\Controllers\User\SubdomainRequestController::class, 'destroy'])->name('destroy');

        // Check subdomain availability
        Route::post('/check-subdomain', [\App\Http\Controllers\User\SubdomainRequestController::class, 'checkSubdomainAvailability'])->name('check-subdomain');
        // Get frameworks by programming language
        Route::get('/frameworks', [\App\Http\Controllers\User\SubdomainRequestController::class, 'getFrameworks'])->name('get-frameworks');

        // Subdomain IP Change Routes
        Route::prefix('ip-change')->name('ip-change.')->group(function () {
            Route::get('/', [\App\Http\Controllers\User\SubdomainIpChangeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\User\SubdomainIpChangeController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\User\SubdomainIpChangeController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\User\SubdomainIpChangeController::class, 'show'])->name('show');
        });

        // Subdomain Name Change Routes
        Route::prefix('name-change')->name('name-change.')->group(function () {
            Route::get('/', [\App\Http\Controllers\User\SubdomainNameChangeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\User\SubdomainNameChangeController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\User\SubdomainNameChangeController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\User\SubdomainNameChangeController::class, 'show'])->name('show');
            Route::post('/check-availability', [\App\Http\Controllers\User\SubdomainNameChangeController::class, 'checkAvailability'])->name('check-availability');
        });
    });

// PSE Update Data - User Routes
Route::middleware(['auth','verified.user','permission:Akses Update Data PSE'])
    ->prefix('digital/pse-update')
    ->name('user.pse-update.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\PseUpdateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\PseUpdateController::class, 'create'])->name('create');
        Route::get('/create/subdomain/{webMonitorId}', [\App\Http\Controllers\User\PseUpdateController::class, 'createForm'])->name('create-form');
        Route::post('/store', [\App\Http\Controllers\User\PseUpdateController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\User\PseUpdateController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\User\PseUpdateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\User\PseUpdateController::class, 'update'])->name('update');
        Route::post('/{id}/submit', [\App\Http\Controllers\User\PseUpdateController::class, 'submit'])->name('submit');
        Route::delete('/{id}', [\App\Http\Controllers\User\PseUpdateController::class, 'destroy'])->name('destroy');
    });

// Form Permohonan Subdomain level Admin
Route::middleware(['auth','role:Admin'])->prefix('admin/digital/subdomain')->name('admin.subdomain.')->group(function () {
    Route::get('/',           [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'index'])->name('index');
    Route::get('/export-excel', [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'exportExcel'])->name('export-excel');
    Route::get('/export-pdf', [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'exportPdf'])->name('export-pdf');
    Route::get('/export/csv', [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'exportCsv'])->name('export'); // filter opsional: ?status=selesai

    // Subdomain IP Change - Admin Routes (harus sebelum /{id} agar tidak tertangkap)
    Route::prefix('ip-change')->name('ip-change.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SubdomainIpChangeAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\SubdomainIpChangeAdminController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\SubdomainIpChangeAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\SubdomainIpChangeAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/complete', [\App\Http\Controllers\Admin\SubdomainIpChangeAdminController::class, 'complete'])->name('complete');
    });

    // Subdomain Name Change - Admin Routes
    Route::prefix('name-change')->name('name-change.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SubdomainNameChangeAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\SubdomainNameChangeAdminController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\SubdomainNameChangeAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\SubdomainNameChangeAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/complete', [\App\Http\Controllers\Admin\SubdomainNameChangeAdminController::class, 'complete'])->name('complete');
    });

    // Route dengan parameter dinamis harus di akhir
    Route::get('/{id}',       [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'show'])->name('show');
    Route::post('/{id}/status', [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'updateStatus'])->name('status');
    Route::patch('/{id}/update-ip', [\App\Http\Controllers\Admin\SubdomainRequestAdminController::class, 'updateIpAddress'])->name('update-ip');
});

// PSE Update Data - Admin Routes
Route::middleware(['auth','role:Admin','permission:Kelola Permohonan PSE'])
    ->prefix('admin/digital/pse-update')
    ->name('admin.pse-update.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PseUpdateAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\PseUpdateAdminController::class, 'show'])->name('show');
        Route::post('/{id}/update-status', [\App\Http\Controllers\Admin\PseUpdateAdminController::class, 'updateStatus'])->name('update-status');
    });

// Unified Subdomain Management - Admin Routes
Route::middleware(['auth','role:Admin'])->prefix('admin/unified-subdomain')->name('admin.unified-subdomain.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\UnifiedSubdomainController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Admin\UnifiedSubdomainController::class, 'show'])->name('show');
    Route::post('/{id}/approve', [\App\Http\Controllers\Admin\UnifiedSubdomainController::class, 'approve'])->name('approve');
    Route::post('/{id}/check-status', [\App\Http\Controllers\Admin\UnifiedSubdomainController::class, 'checkStatus'])->name('check-status');
    Route::get('/export/all', [\App\Http\Controllers\Admin\UnifiedSubdomainController::class, 'exportAll'])->name('export-all');
});


// Email Password Reset - User Routes
Route::middleware(['auth', 'verified.user'])
    ->prefix('email-password-reset')
    ->name('user.email-password-reset.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\EmailPasswordResetController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\EmailPasswordResetController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\User\EmailPasswordResetController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\User\EmailPasswordResetController::class, 'show'])->name('show');
    });

// Email Password Reset - Admin Routes
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin/email-password-reset')
    ->name('admin.email-password-reset.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\EmailPasswordResetController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\EmailPasswordResetController::class, 'show'])->name('show');
        Route::post('/{id}/process', [\App\Http\Controllers\Admin\EmailPasswordResetController::class, 'process'])->name('process');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\EmailPasswordResetController::class, 'reject'])->name('reject');
    });

// Video Conference Request - User Routes
Route::middleware(['auth', 'verified.user', 'permission:Akses Video Conference'])
    ->prefix('digital/vidcon')
    ->name('user.vidcon.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\VidconRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\VidconRequestController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\User\VidconRequestController::class, 'store'])->name('store');
        Route::get('/thanks/{vidconRequest}', [\App\Http\Controllers\User\VidconRequestController::class, 'thanks'])->name('thanks');
        Route::get('/{vidconRequest}/edit', [\App\Http\Controllers\User\VidconRequestController::class, 'edit'])->name('edit');
        Route::put('/{vidconRequest}', [\App\Http\Controllers\User\VidconRequestController::class, 'update'])->name('update');
        Route::delete('/{vidconRequest}', [\App\Http\Controllers\User\VidconRequestController::class, 'destroy'])->name('destroy');
    });

// Video Conference Request - Admin Routes
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin/digital/vidcon')
    ->name('admin.vidcon.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'index'])->name('index');
        Route::get('/export-excel', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/process', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'setProcess'])->name('process');
        Route::post('/{id}/update-info', [\App\Http\Controllers\Admin\VidconRequestAdminController::class, 'updateInfo'])->name('update-info');
    });

// ===== SURVEI KEPUASAN LAYANAN =====

// Survei Kepuasan Layanan - User Routes
Route::middleware(['auth', 'verified.user', 'permission:Akses Survei Kepuasan'])
    ->prefix('digital/survei-kepuasan')
    ->name('survei-kepuasan.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\SurveiKepuasanController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\SurveiKepuasanController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\User\SurveiKepuasanController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\User\SurveiKepuasanController::class, 'show'])->name('show');
    });

// Survei Kepuasan Layanan - Admin Routes
Route::middleware(['auth', 'role:Admin', 'permission:Kelola Survei Kepuasan'])
    ->prefix('admin/survei-kepuasan')
    ->name('admin.survei-kepuasan.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SurveiKepuasanAdminController::class, 'index'])->name('index');
        Route::get('/statistics', [\App\Http\Controllers\Admin\SurveiKepuasanAdminController::class, 'statistics'])->name('statistics');
        Route::get('/export-pdf', [\App\Http\Controllers\Admin\SurveiKepuasanAdminController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [\App\Http\Controllers\Admin\SurveiKepuasanAdminController::class, 'exportExcel'])->name('export-excel');
        Route::get('/{id}', [\App\Http\Controllers\Admin\SurveiKepuasanAdminController::class, 'show'])->name('show');
    });

// ===== INTERNET SERVICES =====

// Laporan Gangguan Internet - User Routes
Route::middleware(['auth', 'verified.user', 'permission:Akses Lapor Gangguan Internet'])
    ->prefix('digital/internet/laporan-gangguan')
    ->name('user.internet.laporan-gangguan.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\LaporanGangguanController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\LaporanGangguanController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\User\LaporanGangguanController::class, 'store'])->name('store');
        Route::get('/{laporanGangguan}', [\App\Http\Controllers\User\LaporanGangguanController::class, 'show'])->name('show');
    });

// Starlink Jelajah - User Routes
Route::middleware(['auth', 'verified.user', 'permission:Akses Starlink Jelajah'])
    ->prefix('digital/internet/starlink')
    ->name('user.internet.starlink.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\StarlinkController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\StarlinkController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\User\StarlinkController::class, 'store'])->name('store');
        Route::get('/{starlinkRequest}', [\App\Http\Controllers\User\StarlinkController::class, 'show'])->name('show');
    });

// Laporan Gangguan Internet - Admin Routes
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin/digital/internet/laporan-gangguan')
    ->name('admin.internet.laporan-gangguan.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LaporanGangguanAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\LaporanGangguanAdminController::class, 'show'])->name('show');
        Route::post('/{id}/process', [\App\Http\Controllers\Admin\LaporanGangguanAdminController::class, 'setProcess'])->name('process');
        Route::post('/{id}/complete', [\App\Http\Controllers\Admin\LaporanGangguanAdminController::class, 'complete'])->name('complete');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\LaporanGangguanAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/update-notes', [\App\Http\Controllers\Admin\LaporanGangguanAdminController::class, 'updateNotes'])->name('update-notes');
    });

// Starlink Jelajah - Admin Routes
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin/digital/internet/starlink')
    ->name('admin.internet.starlink.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'show'])->name('show');
        Route::post('/{id}/process', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'setProcess'])->name('process');
        Route::post('/{id}/complete', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'complete'])->name('complete');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'reject'])->name('reject');
        Route::post('/{id}/update-notes', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'updateNotes'])->name('update-notes');
        Route::post('/toggle-service', [\App\Http\Controllers\Admin\StarlinkAdminController::class, 'toggleService'])->name('toggle-service');
    });

// VPN Registration - User
Route::middleware(['auth','verified.user','permission:Akses Pendaftaran VPN'])
    ->prefix('digital/vpn/registration')
    ->name('user.vpn.registration.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\VpnRegistrationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\VpnRegistrationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\VpnRegistrationController::class, 'store'])->name('store');
        Route::get('/{vpnRegistration}', [\App\Http\Controllers\User\VpnRegistrationController::class, 'show'])->name('show');
    });

// VPN Reset - User
Route::middleware(['auth','verified.user','permission:Akses Reset Akun VPN'])
    ->prefix('digital/vpn/reset')
    ->name('user.vpn.reset.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\VpnResetController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\VpnResetController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\VpnResetController::class, 'store'])->name('store');
        Route::get('/{vpnReset}', [\App\Http\Controllers\User\VpnResetController::class, 'show'])->name('show');
    });

// JIP PDNS - User
Route::middleware(['auth','verified.user','permission:Akses JIP PDNS'])
    ->prefix('digital/vpn/jip-pdns')
    ->name('user.vpn.jip-pdns.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\JipPdnsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\JipPdnsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\JipPdnsController::class, 'store'])->name('store');
        Route::get('/{jipPdnsRequest}', [\App\Http\Controllers\User\JipPdnsController::class, 'show'])->name('show');
    });

// VPN Registration - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/vpn/registration')
    ->name('admin.vpn.registration.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VpnRegistrationController::class, 'index'])->name('index');
        Route::get('/{vpnRegistration}', [\App\Http\Controllers\Admin\VpnRegistrationController::class, 'show'])->name('show');
        Route::post('/{vpnRegistration}/process', [\App\Http\Controllers\Admin\VpnRegistrationController::class, 'process'])->name('process');
        Route::post('/{vpnRegistration}/complete', [\App\Http\Controllers\Admin\VpnRegistrationController::class, 'complete'])->name('complete');
        Route::post('/{vpnRegistration}/reject', [\App\Http\Controllers\Admin\VpnRegistrationController::class, 'reject'])->name('reject');
        Route::post('/{vpnRegistration}/update-notes', [\App\Http\Controllers\Admin\VpnRegistrationController::class, 'updateNotes'])->name('update-notes');
    });

// VPN Reset - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/vpn/reset')
    ->name('admin.vpn.reset.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VpnResetController::class, 'index'])->name('index');
        Route::get('/{vpnReset}', [\App\Http\Controllers\Admin\VpnResetController::class, 'show'])->name('show');
        Route::post('/{vpnReset}/process', [\App\Http\Controllers\Admin\VpnResetController::class, 'process'])->name('process');
        Route::post('/{vpnReset}/complete', [\App\Http\Controllers\Admin\VpnResetController::class, 'complete'])->name('complete');
        Route::post('/{vpnReset}/reject', [\App\Http\Controllers\Admin\VpnResetController::class, 'reject'])->name('reject');
        Route::post('/{vpnReset}/update-notes', [\App\Http\Controllers\Admin\VpnResetController::class, 'updateNotes'])->name('update-notes');
    });

// JIP PDNS - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/vpn/jip-pdns')
    ->name('admin.vpn.jip-pdns.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\JipPdnsController::class, 'index'])->name('index');
        Route::get('/{jipPdnsRequest}', [\App\Http\Controllers\Admin\JipPdnsController::class, 'show'])->name('show');
        Route::post('/{jipPdnsRequest}/process', [\App\Http\Controllers\Admin\JipPdnsController::class, 'process'])->name('process');
        Route::post('/{jipPdnsRequest}/complete', [\App\Http\Controllers\Admin\JipPdnsController::class, 'complete'])->name('complete');
        Route::post('/{jipPdnsRequest}/reject', [\App\Http\Controllers\Admin\JipPdnsController::class, 'reject'])->name('reject');
        Route::post('/{jipPdnsRequest}/update-notes', [\App\Http\Controllers\Admin\JipPdnsController::class, 'updateNotes'])->name('update-notes');
    });

// Visitation/Colocation - User
Route::middleware(['auth','verified.user','permission:Akses Kunjungan/Colocation Data Center'])
    ->prefix('digital/datacenter/visitation')
    ->name('user.datacenter.visitation.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\VisitationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\VisitationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\VisitationController::class, 'store'])->name('store');
        Route::get('/{visitation}', [\App\Http\Controllers\User\VisitationController::class, 'show'])->name('show');
    });

// VPS - User
Route::middleware(['auth','verified.user','permission:Akses VPS/VM'])
    ->prefix('digital/datacenter/vps')
    ->name('user.datacenter.vps.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\VpsRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\VpsRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\VpsRequestController::class, 'store'])->name('store');
        Route::get('/{vpsRequest}', [\App\Http\Controllers\User\VpsRequestController::class, 'show'])->name('show');
    });

// Backup - User
Route::middleware(['auth','verified.user','permission:Akses Backup'])
    ->prefix('digital/datacenter/backup')
    ->name('user.datacenter.backup.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\BackupRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\BackupRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\BackupRequestController::class, 'store'])->name('store');
        Route::get('/{backupRequest}', [\App\Http\Controllers\User\BackupRequestController::class, 'show'])->name('show');
    });

// Cloud Storage - User
Route::middleware(['auth','verified.user','permission:Akses Cloud Storage'])
    ->prefix('digital/datacenter/cloud-storage')
    ->name('user.datacenter.cloud-storage.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\CloudStorageRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\CloudStorageRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\CloudStorageRequestController::class, 'store'])->name('store');
        Route::get('/{cloudStorageRequest}', [\App\Http\Controllers\User\CloudStorageRequestController::class, 'show'])->name('show');
    });

// Visitation/Colocation - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/datacenter/visitation')
    ->name('admin.datacenter.visitation.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VisitationController::class, 'index'])->name('index');
        Route::get('/{visitation}', [\App\Http\Controllers\Admin\VisitationController::class, 'show'])->name('show');
        Route::post('/{visitation}/approve', [\App\Http\Controllers\Admin\VisitationController::class, 'approve'])->name('approve');
        Route::post('/{visitation}/reject', [\App\Http\Controllers\Admin\VisitationController::class, 'reject'])->name('reject');
        Route::post('/{visitation}/complete', [\App\Http\Controllers\Admin\VisitationController::class, 'complete'])->name('complete');
        Route::post('/{visitation}/update-notes', [\App\Http\Controllers\Admin\VisitationController::class, 'updateNotes'])->name('update-notes');
    });

// VPS - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/datacenter/vps')
    ->name('admin.datacenter.vps.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VpsRequestController::class, 'index'])->name('index');
        Route::get('/{vpsRequest}', [\App\Http\Controllers\Admin\VpsRequestController::class, 'show'])->name('show');
        Route::post('/{vpsRequest}/process', [\App\Http\Controllers\Admin\VpsRequestController::class, 'process'])->name('process');
        Route::post('/{vpsRequest}/complete', [\App\Http\Controllers\Admin\VpsRequestController::class, 'complete'])->name('complete');
        Route::post('/{vpsRequest}/reject', [\App\Http\Controllers\Admin\VpsRequestController::class, 'reject'])->name('reject');
        Route::post('/{vpsRequest}/update-notes', [\App\Http\Controllers\Admin\VpsRequestController::class, 'updateNotes'])->name('update-notes');
        // API routes for IP Public
        Route::get('/api/available-ips', [\App\Http\Controllers\Admin\VpsRequestController::class, 'getAvailableIPs'])->name('api.available-ips');
        Route::get('/api/autopick-ip', [\App\Http\Controllers\Admin\VpsRequestController::class, 'autopickIP'])->name('api.autopick-ip');
    });

// Backup - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/datacenter/backup')
    ->name('admin.datacenter.backup.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BackupRequestController::class, 'index'])->name('index');
        Route::get('/{backupRequest}', [\App\Http\Controllers\Admin\BackupRequestController::class, 'show'])->name('show');
        Route::post('/{backupRequest}/process', [\App\Http\Controllers\Admin\BackupRequestController::class, 'process'])->name('process');
        Route::post('/{backupRequest}/complete', [\App\Http\Controllers\Admin\BackupRequestController::class, 'complete'])->name('complete');
        Route::post('/{backupRequest}/reject', [\App\Http\Controllers\Admin\BackupRequestController::class, 'reject'])->name('reject');
        Route::post('/{backupRequest}/update-notes', [\App\Http\Controllers\Admin\BackupRequestController::class, 'updateNotes'])->name('update-notes');
    });

// Cloud Storage - Admin
Route::middleware(['auth','role:Admin'])
    ->prefix('admin/digital/datacenter/cloud-storage')
    ->name('admin.datacenter.cloud-storage.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CloudStorageRequestController::class, 'index'])->name('index');
        Route::get('/{cloudStorageRequest}', [\App\Http\Controllers\Admin\CloudStorageRequestController::class, 'show'])->name('show');
        Route::post('/{cloudStorageRequest}/process', [\App\Http\Controllers\Admin\CloudStorageRequestController::class, 'process'])->name('process');
        Route::post('/{cloudStorageRequest}/complete', [\App\Http\Controllers\Admin\CloudStorageRequestController::class, 'complete'])->name('complete');
        Route::post('/{cloudStorageRequest}/reject', [\App\Http\Controllers\Admin\CloudStorageRequestController::class, 'reject'])->name('reject');
        Route::post('/{cloudStorageRequest}/update-notes', [\App\Http\Controllers\Admin\CloudStorageRequestController::class, 'updateNotes'])->name('update-notes');
    });

// Konsultasi SPBE Berbasis AI - User only (no admin needed)
Route::middleware(['auth','verified.user','permission:Akses Konsultasi SPBE AI'])
    ->prefix('digital/konsultasi-spbe-ai')
    ->name('user.konsultasi-spbe-ai.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\KonsultasiSpbeAiController::class, 'index'])->name('index');
    });

// TTE - Pendampingan Aktivasi dan Penggunaan TTE (User)
Route::middleware(['auth','verified.user','permission:Akses Bantuan TTE'])
    ->prefix('digital/tte/assistance')
    ->name('user.tte.assistance.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\TteAssistanceController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\TteAssistanceController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\TteAssistanceController::class, 'store'])->name('store');
        Route::get('/{tteAssistance}', [\App\Http\Controllers\User\TteAssistanceController::class, 'show'])->name('show');
    });

// TTE - Pendaftaran Akun Baru TTE (User)
Route::middleware(['auth','verified.user','permission:Akses Registrasi TTE'])
    ->prefix('digital/tte/registration')
    ->name('user.tte.registration.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\TteRegistrationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\TteRegistrationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\TteRegistrationController::class, 'store'])->name('store');
        Route::get('/{tteRegistration}', [\App\Http\Controllers\User\TteRegistrationController::class, 'show'])->name('show');
    });

// TTE - Reset Passphrase TTE (User)
Route::middleware(['auth','verified.user','permission:Akses Reset Passphrase TTE'])
    ->prefix('digital/tte/passphrase-reset')
    ->name('user.tte.passphrase-reset.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\TtePassphraseResetController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\TtePassphraseResetController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\TtePassphraseResetController::class, 'store'])->name('store');
        Route::get('/{ttePassphraseReset}', [\App\Http\Controllers\User\TtePassphraseResetController::class, 'show'])->name('show');
    });

// TTE - Pembaruan Sertifikat TTE (User)
Route::middleware(['auth','verified.user','permission:Akses Pembaruan Sertifikat TTE'])
    ->prefix('digital/tte/certificate-update')
    ->name('user.tte.certificate-update.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\User\TteCertificateUpdateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\TteCertificateUpdateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\TteCertificateUpdateController::class, 'store'])->name('store');
        Route::get('/{tteCertificateUpdate}', [\App\Http\Controllers\User\TteCertificateUpdateController::class, 'show'])->name('show');
    });

// TTE - Pendampingan Aktivasi dan Penggunaan TTE (Admin)
Route::middleware(['auth','role:Admin,Operator-Sandi'])
    ->prefix('admin/tte/assistance')
    ->name('admin.tte.assistance.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TteAssistanceController::class, 'index'])->name('index');
        Route::get('/{tteAssistance}', [\App\Http\Controllers\Admin\TteAssistanceController::class, 'show'])->name('show');
        Route::patch('/{tteAssistance}/update-status', [\App\Http\Controllers\Admin\TteAssistanceController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{tteAssistance}', [\App\Http\Controllers\Admin\TteAssistanceController::class, 'destroy'])->name('destroy');
    });

// TTE - Pendaftaran Akun TTE (Admin)
Route::middleware(['auth','role:Admin,Operator-Sandi'])
    ->prefix('admin/tte/registration')
    ->name('admin.tte.registration.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TteRegistrationController::class, 'index'])->name('index');
        Route::get('/export-excel', [\App\Http\Controllers\Admin\TteRegistrationController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [\App\Http\Controllers\Admin\TteRegistrationController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{tteRegistration}', [\App\Http\Controllers\Admin\TteRegistrationController::class, 'show'])->name('show');
        Route::patch('/{tteRegistration}/update-status', [\App\Http\Controllers\Admin\TteRegistrationController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{tteRegistration}', [\App\Http\Controllers\Admin\TteRegistrationController::class, 'destroy'])->name('destroy');
    });

// TTE - Reset Passphrase TTE (Admin)
Route::middleware(['auth','role:Admin,Operator-Sandi'])
    ->prefix('admin/tte/passphrase-reset')
    ->name('admin.tte.passphrase-reset.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TtePassphraseResetController::class, 'index'])->name('index');
        Route::get('/export-excel', [\App\Http\Controllers\Admin\TtePassphraseResetController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [\App\Http\Controllers\Admin\TtePassphraseResetController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{ttePassphraseReset}', [\App\Http\Controllers\Admin\TtePassphraseResetController::class, 'show'])->name('show');
        Route::patch('/{ttePassphraseReset}/update-status', [\App\Http\Controllers\Admin\TtePassphraseResetController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{ttePassphraseReset}', [\App\Http\Controllers\Admin\TtePassphraseResetController::class, 'destroy'])->name('destroy');
    });

// TTE - Pembaruan Sertifikat TTE (Admin)
Route::middleware(['auth','role:Admin,Operator-Sandi'])
    ->prefix('admin/tte/certificate-update')
    ->name('admin.tte.certificate-update.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TteCertificateUpdateController::class, 'index'])->name('index');
        Route::get('/export-excel', [\App\Http\Controllers\Admin\TteCertificateUpdateController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [\App\Http\Controllers\Admin\TteCertificateUpdateController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{tteCertificateUpdate}', [\App\Http\Controllers\Admin\TteCertificateUpdateController::class, 'show'])->name('show');
        Route::patch('/{tteCertificateUpdate}/update-status', [\App\Http\Controllers\Admin\TteCertificateUpdateController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{tteCertificateUpdate}', [\App\Http\Controllers\Admin\TteCertificateUpdateController::class, 'destroy'])->name('destroy');
    });

// Aset TIK - admin + admin-vidcon
Route::middleware(['auth','role:Admin,Operator-Vidcon'])
    ->prefix('admin/aset-tik')->name('admin.tik.')
    ->group(function () {
        // Assets
        Route::get('/',              \App\Http\Controllers\Admin\TikAssetController::class.'@index')->name('assets.index');
        Route::get('/create',        \App\Http\Controllers\Admin\TikAssetController::class.'@create')->name('assets.create');
        Route::post('/',             \App\Http\Controllers\Admin\TikAssetController::class.'@store')->name('assets.store');
        Route::get('/{asset}/edit',  \App\Http\Controllers\Admin\TikAssetController::class.'@edit')->name('assets.edit');
        Route::put('/{asset}',       \App\Http\Controllers\Admin\TikAssetController::class.'@update')->name('assets.update');
        Route::delete('/{asset}',    \App\Http\Controllers\Admin\TikAssetController::class.'@destroy')->name('assets.destroy');

        // Categories
        Route::get('/kategori',                \App\Http\Controllers\Admin\TikCategoryController::class.'@index')->name('categories.index');
        Route::post('/kategori',               \App\Http\Controllers\Admin\TikCategoryController::class.'@store')->name('categories.store');
        Route::get('/kategori/{category}/edit',\App\Http\Controllers\Admin\TikCategoryController::class.'@edit')->name('categories.edit');
        Route::put('/kategori/{category}',     \App\Http\Controllers\Admin\TikCategoryController::class.'@update')->name('categories.update');
        Route::delete('/kategori/{category}',  \App\Http\Controllers\Admin\TikCategoryController::class.'@destroy')->name('categories.destroy');
    });

// ===== Google Aset TIK Routes (Admin Only) =====
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin/aset-tik/google-sheets')
    ->name('admin.google-aset-tik.')
    ->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\GoogleAsetTikController::class, 'dashboard'])->name('dashboard');

        // Hardware
        Route::get('/hardware', [\App\Http\Controllers\Admin\GoogleAsetTikController::class, 'hardware'])->name('hardware.index');
        Route::get('/hardware/{id}', [\App\Http\Controllers\Admin\GoogleAsetTikController::class, 'showHardware'])->name('hardware.show');

        // Software
        Route::get('/software', [\App\Http\Controllers\Admin\GoogleAsetTikController::class, 'software'])->name('software.index');
        Route::get('/software/{id}', [\App\Http\Controllers\Admin\GoogleAsetTikController::class, 'showSoftware'])->name('software.show');
    });

// Sync Management Routes (Admin Only)
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin/aset-tik/google-sheets/sync')
    ->name('admin.google-aset-tik.sync.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GoogleAsetTikSyncController::class, 'index'])->name('index');
        Route::post('/import', [\App\Http\Controllers\Admin\GoogleAsetTikSyncController::class, 'import'])->name('import');
        Route::post('/export', [\App\Http\Controllers\Admin\GoogleAsetTikSyncController::class, 'export'])->name('export');
        Route::get('/preview/{type}', [\App\Http\Controllers\Admin\GoogleAsetTikSyncController::class, 'preview'])->name('preview');
        Route::get('/logs', [\App\Http\Controllers\Admin\GoogleAsetTikSyncController::class, 'logs'])->name('logs');
        Route::get('/test-connection', [\App\Http\Controllers\Admin\GoogleAsetTikSyncController::class, 'testConnection'])->name('test-connection');
    });

// ===== Admin + Admin Vidcon: Pelacakan & Detail Borrowing =====
Route::middleware(['auth','role:Admin,Operator-Vidcon'])
    ->prefix('admin/aset-tik/borrowings')->name('admin.tik.borrow.')
    ->group(function () {
        Route::get('/',            \App\Http\Controllers\Admin\TikBorrowingAdminController::class.'@index')->name('index');
        Route::get('/{borrowing}', \App\Http\Controllers\Admin\TikBorrowingAdminController::class.'@show')->name('show');
        Route::post('/{borrowing}/force-close', \App\Http\Controllers\Admin\TikBorrowingAdminController::class.'@forceClose')->name('forceClose');
    });


// ===== Operator Vidcon: Peminjaman Aset (bisa diakses Admin & Operator-Vidcon) =====
Route::middleware(['auth','role:Admin,Operator-Vidcon'])
    ->prefix('op/tik/borrow')->name('op.tik.borrow.')
    ->group(function () {
        Route::get('/',              [OpBorrow::class, 'index'])->name('index');
        Route::get('/create',        [OpBorrow::class, 'create'])->name('create');
        Route::post('/',             [OpBorrow::class, 'store'])->name('store');
        Route::get('/{borrowing}/edit',     [OpBorrow::class, 'edit'])->name('edit');        // <--- BARU
        Route::put('/{borrowing}',          [OpBorrow::class, 'update'])->name('update');    // <--- BARU

        Route::get('/{borrowing}',          [OpBorrow::class, 'show'])->name('show');
        Route::get('/{borrowing}/return',   [OpBorrow::class, 'returnForm'])->name('return.form');
        Route::post('/{borrowing}/return',  [OpBorrow::class, 'doReturn'])->name('return.do');

                // API endpoint for barcode scanner lookup
        Route::get('/api/lookup-by-code',   [OpBorrow::class, 'lookupAssetByCode'])->name('lookup.code');
    });

// ===== Operator Vidcon: Jadwal Vidcon (menggunakan data dari vidcon_data table) =====
Route::middleware(['auth','role:Operator-Vidcon,Admin'])
    ->prefix('op/tik')->name('op.tik.')
    ->group(function () {
        // Halaman jadwal menggunakan data dari vidcon_data table
        Route::get('/schedule', [\App\Http\Controllers\Operator\TikScheduleController::class, 'schedule'])->name('schedule.index');
        // Detail jadwal vidcon
        Route::get('/schedule/{vidconData}', [\App\Http\Controllers\Operator\TikScheduleController::class, 'show'])->name('schedule.show');
        // Halaman statistik menggunakan data dari vidcon_data table
        Route::get('/statistic', [\App\Http\Controllers\Operator\TikScheduleController::class, 'statistic'])->name('statistic.index');
    });

// ===== Operator Vidcon: Pelaporan & Dokumentasi =====
Route::middleware(['auth','role:Operator-Vidcon,Admin'])
    ->prefix('operator/vidcon')->name('operator.vidcon.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Operator\OperatorVidconController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Operator\OperatorVidconController::class, 'show'])->name('show');
        Route::post('/{id}/documentation', [\App\Http\Controllers\Operator\OperatorVidconController::class, 'storeDocumentation'])->name('documentation.store');
        Route::delete('/documentation/{id}', [\App\Http\Controllers\Operator\OperatorVidconController::class, 'deleteDocumentation'])->name('documentation.delete');
    });
