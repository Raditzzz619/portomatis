<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\BuilderController;

Route::get('/', function () {
    return view('home');
});

Route::resource('portfolio', PortfolioController::class);
Route::resource('projects', ProjectController::class);
Route::resource('skills', SkillController::class);
Route::resource('experiences', ExperienceController::class);
Route::get('/builder', [BuilderController::class, 'index'])->name('builder');
Route::get('/builder/download', [BuilderController::class, 'download'])->name('builder.download');
Route::post('/builder/publish', [BuilderController::class, 'publish'])->middleware('throttle:10,1')->name('builder.publish');
Route::get('/builder/{step}', [BuilderController::class, 'step'])->name('builder.step');
Route::post('/builder/{step}', [BuilderController::class, 'save'])->name('builder.save');
Route::get('/p/{token}', [BuilderController::class, 'show'])->whereUuid('token')->name('builder.public');
