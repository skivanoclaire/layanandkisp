<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MonitorController;

// Halaman Publik Layanan - Penambahan terbaru
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

// Untuk user biasa
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::post('/submit', [UserController::class, 'submit'])->name('user.submit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Untuk admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/permohonan', [AdminController::class, 'permohonan'])->name('permohonan');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/update-status/{userRequest}', [AdminController::class, 'updateStatus'])->name('update-status');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::get('/monitorweb', [MonitorController::class, 'monitorweb'])->name('monitorweb');
    Route::delete('/monitorweb/{id}', [MonitorController::class, 'monitorwebDelete'])->name('monitorweb.delete');
    Route::get('/monitorweb/{id}/edit', [MonitorController::class, 'monitorwebEdit'])->name('monitorweb.edit');
    Route::post('/monitorweb/{id}/update', [MonitorController::class, 'monitorwebUpdate'])->name('monitorweb.update');
    Route::get('/monitorweb/create', [MonitorController::class, 'monitorwebCreate'])->name('monitorweb.create');
    Route::post('/monitorweb/store', [MonitorController::class, 'monitorwebStore'])->name('monitorweb.store');



});


