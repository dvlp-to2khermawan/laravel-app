<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 1. Main '/' route
Route::get('/', function () {
    return view('welcome');
});

// 2. Authenticated user group
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard for regular users (role: 'user')
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('role:user')->name('dashboard');

    // Profile routes from Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin dashboard route group (role: 'admin')
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });

    // Superadmin dashboard route group (role: 'super_admin')
    Route::prefix('superadmin')->name('superadmin.')->middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('superadmin.dashboard');
        })->name('dashboard');
    });
});

// 3. Authentication Routes (ensure this is at the bottom)
require __DIR__.'/auth.php';
