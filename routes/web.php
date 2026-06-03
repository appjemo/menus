<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Player de la pantalla (lo abre el Raspberry en la TV)
Route::get('/play/{token}', [PlayerController::class, 'show'])->name('player.show');
Route::get('/play/{token}/menu', [PlayerController::class, 'menu'])->name('player.menu');
