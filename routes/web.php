<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PortfolioController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('portfolios.index') : view('landing');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/signup', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/portfolio-generator', fn () => redirect()->route('portfolios.create'))->name('portfolios.generator');
    Route::post('/welcome/create', [AuthController::class, 'startWelcomePortfolio'])->name('welcome.create');
    Route::post('/welcome/skip', [AuthController::class, 'skipWelcome'])->name('welcome.skip');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/portfolios/{portfolio}/media/{folder}/{filename}', [PortfolioController::class, 'media'])
        ->where('filename', '[^/]+')
        ->name('portfolios.media');
    Route::resource('portfolios', PortfolioController::class);
});
