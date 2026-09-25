<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\ExperienceController;

Route::get('/', function () {
    return view('home');
});

Route::resource('portfolio', PortfolioController::class);
Route::resource('projects', ProjectController::class);
Route::resource('skills', SkillController::class);
Route::resource('experiences', ExperienceController::class);