<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuilderController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::post('/activity/heartbeat', [AuthController::class, 'heartbeat'])->middleware('throttle:10,1')->name('activity.heartbeat');
    Route::middleware('activity')->group(function () {
        Route::get('/builder', [BuilderController::class, 'index'])->name('builder');
        Route::post('/builder/new', [BuilderController::class, 'startNew'])->name('builder.new');
        Route::get('/builder/resume/{portfolio}', [BuilderController::class, 'resume'])->name('builder.resume');
        Route::get('/builder/download', [BuilderController::class, 'download'])->name('builder.download');
        Route::get('/builder/pdf', [BuilderController::class, 'pdf'])->name('builder.pdf');
        Route::get('/builder/download/pdf', [BuilderController::class, 'downloadPdf'])->name('builder.download.pdf');
        Route::post('/builder/publish', [BuilderController::class, 'publish'])->middleware('throttle:10,1')->name('builder.publish');
        Route::get('/builder/{step}', [BuilderController::class, 'step'])->name('builder.step');
        Route::post('/builder/{step}', [BuilderController::class, 'save'])->name('builder.save');
    });
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/users/{user}', [AdminController::class, 'show'])->name('admin.users.show');
    Route::resource('portfolio', PortfolioController::class);
});

Route::get('/p/{token}', [BuilderController::class, 'show'])->whereUuid('token')->name('builder.public');
