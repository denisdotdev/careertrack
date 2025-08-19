<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\Register;
use App\Livewire\Dashboard;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Health check route for Kubernetes probes
Route::get('/health', function () {
    return view('livewire.health-check');
});

// Authentication routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/register', Register::class)->name('register')->middleware('guest');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});