<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin');
    Route::group(['prefix' => 'kategori'], function () {
        Route::get('/', [KategoriController::class, 'index'])->name('kategori');
        Route::post('/store', [KategoriController::class, 'store'])->name('kategori.store');
        Route::post('/{id}', [KategoriController::class, 'edit'])->name('kategori.update');
    });
});
Route::group(['prefix' => 'kasir'], function () {
    Route::get('/', [DashboardController::class, 'kasir'])->name('kasir');
});
Route::group(['prefix' => 'super'], function () {
    Route::get('/', [DashboardController::class, 'super'])->name('super');
    Route::group(['prefix' => 'kategori'], function () {
        Route::get('/', [KategoriController::class, 'index'])->name('super.kategori');
        Route::post('/store', [KategoriController::class, 'store'])->name('super.kategori.store');
        Route::post('/{id}', [KategoriController::class, 'edit'])->name('super.kategori.update');
    });
});

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

