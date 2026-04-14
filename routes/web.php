<?php

use App\Http\Controllers\Atencion\TicketController as AtencionTicketController;
use App\Http\Controllers\Atencion\DiaController;
use App\Http\Controllers\Atencion\UserController;
use App\Http\Controllers\Paneles\PanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tickets\TicketController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Tickets
Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

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

Route::middleware(['auth', 'verified', 'role:Ventanilla|Admin'])->group(function () {
    Route::get('/dashboard', [AtencionTicketController::class, 'index'])->name('dashboard');
    Route::post('/tickets/call', [AtencionTicketController::class, 'callNext'])->name('tickets.call');
    Route::post('/tickets/{ticket}/recall', [AtencionTicketController::class, 'reCall'])->name('tickets.recall');
    Route::post('/tickets/{ticket}/serve', [AtencionTicketController::class, 'serve'])->name('tickets.serve');
    Route::post('/tickets/{ticket}/complete', [AtencionTicketController::class, 'complete'])->name('tickets.complete');
    Route::post('/tickets/{ticket}/abandon', [AtencionTicketController::class, 'abandon'])->name('tickets.abandon');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/tramites', [ProfileController::class, 'updateTramites'])->name('profile.tramites');
});

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('dias', DiaController::class)->except(['show']);
    Route::resource('ventanillas', \App\Http\Controllers\Atencion\VentanillaController::class)->except(['show']);
    Route::resource('tramites', \App\Http\Controllers\Atencion\TramiteController::class)->except(['show']);
});

require __DIR__ . '/auth.php';
