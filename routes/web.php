<?php

use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;

Route::resource('barang', BarangController::class);
Route::get(
    '/',
    function () {
        return view('home');
    }
);