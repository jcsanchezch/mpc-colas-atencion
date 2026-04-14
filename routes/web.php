<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PanelController;

// Tickets
Route::get('/ticket', [TicketController::class, 'index'])->name('ticket.index');
Route::post('/ticket', [TicketController::class, 'store'])->name('ticket.store');

// Panel de Llamadas
Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use App\Http\Controllers\ClerkController;

Route::middleware(['auth', 'verified', 'role:ventanillero|admin'])->group(function () {
    Route::get('/dashboard', [ClerkController::class, 'index'])->name('dashboard');
    Route::post('/clerk/call', [ClerkController::class, 'callNext'])->name('clerk.callNext');
    Route::post('/clerk/{ticket}/recall', [ClerkController::class, 'reCall'])->name('clerk.reCall');
    Route::post('/clerk/{ticket}/serve', [ClerkController::class, 'serve'])->name('clerk.serve');
    Route::post('/clerk/{ticket}/complete', [ClerkController::class, 'complete'])->name('clerk.complete');
    Route::post('/clerk/{ticket}/abandon', [ClerkController::class, 'abandon'])->name('clerk.abandon');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/tramites', [ProfileController::class, 'updateTramites'])->name('profile.tramites');
});

use App\Http\Controllers\UserController;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('ventanillas', \App\Http\Controllers\VentanillaController::class)->except(['show']);
    Route::resource('tramites', \App\Http\Controllers\TramiteController::class)->except(['show']);
});

require __DIR__ . '/auth.php';
