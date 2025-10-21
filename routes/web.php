<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\DigitalFormController;
use App\Http\Controllers\RekomendasiAplikasiController;
use App\Http\Controllers\User\EmailRequestController;
use App\Http\Controllers\Admin\EmailRequestAdminController;
use App\Http\Controllers\Operator\TikBorrowingController as OpBorrow;
use App\Http\Controllers\Admin\TikBorrowingAdminController as AdminBorrow;
use App\Http\Controllers\Admin\SimpegCheckController;


// Halaman Publik Layanan
Route::get('/', function () {
    return view('welcome');
});

Route::get('/services', function () {
    return view('services');
});

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

// User biasa (dashboard & profil)
Route::middleware(['auth', 'role:user'])->group(function () {
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

    Route::get('/permohonan', [PermohonanController::class, 'permohonan'])->name('user.permohonan');
    Route::post('/submit', [PermohonanController::class, 'submit'])->name('user.submit');
    Route::delete('/request/{id}', [PermohonanController::class, 'delete'])->name('user.delete');

    Route::prefix('/digital/rekomendasi/aplikasi')->group(function () {
        Route::get('/', [RekomendasiAplikasiController::class, 'index'])->name('user.rekomendasi.aplikasi.index');
        Route::get('/create', [RekomendasiAplikasiController::class, 'create'])->name('user.rekomendasi.aplikasi.create');
        Route::post('/store', [RekomendasiAplikasiController::class, 'store'])->name('user.rekomendasi.aplikasi.store');
        Route::get('/{id}', [RekomendasiAplikasiController::class, 'show'])->name('user.rekomendasi.aplikasi.show');
        Route::get('/{id}/edit', [RekomendasiAplikasiController::class, 'edit'])->name('user.rekomendasi.aplikasi.edit');
        Route::put('/{id}', [RekomendasiAplikasiController::class, 'update'])->name('user.rekomendasi.aplikasi.update');
        Route::delete('/{id}', [RekomendasiAplikasiController::class, 'destroy'])->name('user.rekomendasi.aplikasi.destroy');
    });
});

// Untuk admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/permohonan', [AdminController::class, 'permohonan'])->name('permohonan');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/update-status/{userRequest}', [AdminController::class, 'updateStatus'])->name('update-status');
    Route::delete('/requests/{userRequest}', [AdminController::class, 'deleteRequest'])->name('delete-request');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::get('/monitorweb', [MonitorController::class, 'monitorweb'])->name('monitorweb');
    Route::delete('/monitorweb/{id}', [MonitorController::class, 'monitorwebDelete'])->name('monitorweb.delete');
    Route::get('/monitorweb/{id}/edit', [MonitorController::class, 'monitorwebEdit'])->name('monitorweb.edit');
    Route::post('/monitorweb/{id}/update', [MonitorController::class, 'monitorwebUpdate'])->name('monitorweb.update');
    Route::get('/monitorweb/create', [MonitorController::class, 'monitorwebCreate'])->name('monitorweb.create');
    Route::post('/monitorweb/store', [MonitorController::class, 'monitorwebStore'])->name('monitorweb.store');

    Route::post('/users/{user}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
    Route::post('/users/{user}/unverify', [AdminController::class, 'unverifyUser'])->name('users.unverify');  

    Route::get('/simpeg-check', [SimpegCheckController::class, 'index'])->name('simpeg.index');
    Route::post('/simpeg-check', [SimpegCheckController::class, 'check'])->name('simpeg.check');

});

// Form Permohonan Email Digital level Verified User
Route::middleware(['auth','verified.user'])
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
    });

// Form Permohonan Email Digital level Admin
Route::middleware(['auth','role:admin'])->prefix('admin/digital/email')->name('admin.email.')->group(function () {
    Route::get('/',           [EmailRequestAdminController::class, 'index'])->name('index');
    Route::get('/{id}',       [EmailRequestAdminController::class, 'show'])->name('show');
    Route::post('/{id}/status', [EmailRequestAdminController::class, 'updateStatus'])->name('status');
    Route::get('/export/csv', [EmailRequestAdminController::class, 'exportCsv'])->name('export'); // filter opsional: ?status=selesai
});

// Aset TIK - admin + admin-vidcon
Route::middleware(['auth','role:admin,admin-vidcon'])
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

// ===== Admin + Admin Vidcon: Pelacakan & Detail Borrowing =====
Route::middleware(['auth','role:admin,admin-vidcon'])
    ->prefix('admin/aset-tik/borrowings')->name('admin.tik.borrow.')
    ->group(function () {
        Route::get('/',            \App\Http\Controllers\Admin\TikBorrowingAdminController::class.'@index')->name('index');
        Route::get('/{borrowing}', \App\Http\Controllers\Admin\TikBorrowingAdminController::class.'@show')->name('show');
    });


// ===== Operator Vidcon: Peminjaman Aset =====
Route::middleware(['auth','role:operator-vidcon'])
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
    });

// ===== Operator Vidcon: Jadwal Vidcon (embed Looker Studio) =====
Route::middleware(['auth','role:operator-vidcon,admin,admin-vidcon'])
    ->prefix('op/tik')->name('op.tik.')
    ->group(function () {
        // Halaman jadwal (tanpa controller—langsung return view)
        Route::get('/schedule', function () {
            return view('operator.tik.schedule');   // resources/views/operator/tik/schedule.blade.php
        })->name('schedule.index');
        // Halaman statistik (tanpa controller—langsung return view)
        Route::get('/statistic', function () {
            return view('operator.tik.statistic');   // resources/views/operator/tik/statistic.blade.php
        })->name('statistic.index');
    });