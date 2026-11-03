<?php

use App\Http\Controllers\PublicTreasuryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicTreasuryController::class, 'index'])->name('public.treasury');
Route::get('/jugador/{token}', [PublicTreasuryController::class, 'player'])->name('public.player');
Route::post('/jugador/{token}/avisos-pago', [PublicTreasuryController::class, 'storePaymentNotice'])
    ->middleware('throttle:5,1')
    ->name('public.player.payment-notices.store');

Route::get('/admin/avisos-pago/{submission}/comprobante', [PublicTreasuryController::class, 'downloadSubmissionAttachment'])
    ->middleware('auth')
    ->name('admin.payment-submissions.attachment');

Route::get('/login', fn () => redirect('/admin/login'))->name('login');
