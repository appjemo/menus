<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Player de la pantalla (lo abre el Raspberry en la TV)
// Rate limit generoso: cada pantalla refresca ~1/min + en eventos; permite varias por local.
Route::middleware('throttle:120,1')->group(function () {
    Route::get('/play/{token}', [PlayerController::class, 'show'])->name('player.show');
    Route::get('/play/{token}/menu', [PlayerController::class, 'menu'])->name('player.menu');
});
