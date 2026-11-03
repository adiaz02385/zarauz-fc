<?php

use App\Http\Controllers\PublicTreasuryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicTreasuryController::class, 'index'])->name('public.treasury');
Route::get('/jugador/{token}', [PublicTreasuryController::class, 'player'])->name('public.player');
