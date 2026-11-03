<?php

use App\Http\Controllers\PublicTreasuryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicTreasuryController::class, 'index'])->name('public.treasury');
Route::get('/jugador/{token}', [PublicTreasuryController::class, 'player'])->name('public.player');

// Laravel's session-auth middleware expects a route named "login" when
// a remembered session is invalidated. Point it to Filament's panel login.
Route::get('/login', fn () => redirect('/admin/login'))->name('login');
