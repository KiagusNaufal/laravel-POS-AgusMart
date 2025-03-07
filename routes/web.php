<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\TransaksiController;
use App\Models\Pemasok;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['role:admin']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin');
    Route::group(['prefix' => 'kategori'], function () {
        Route::get('/', [KategoriController::class, 'index'])->name('kategori');
        Route::post('/store', [KategoriController::class, 'store'])->name('kategori.store');
        Route::post('/{id}', [KategoriController::class, 'edit'])->name('kategori.update');
    });
    Route::group(['prefix' => 'pemasok'], function () {
        Route::get('/', [PemasokController::class, 'index'])->name('admin.pemasok');
        Route::post('/store', [PemasokController::class, 'store'])->name('admin.pemasok.store');
        Route::post('/{id}', [PemasokController::class, 'edit'])->name('admin.pemasok.update');
    });
    Route::group(['prefix' => 'member'], function () {
        Route::get('/', [MemberController::class, 'index'])->name('admin.member');
        Route::post('/store', [MemberController::class, 'store'])->name('admin.member.store');
        Route::post('/{id}', [MemberController::class, 'edit'])->name('admin.member.update');
    });

    Route::group(['prefix' => 'barang'], function () {
        Route::get('/', [BarangController::class, 'index'])->name('admin.barang');
        Route::post('/store', [BarangController::class, 'store'])->name('admin.barang.store');
        Route::post('/{id}', [BarangController::class, 'edit'])->name('admin.barang.update');
    });

    Route::group(['prefix' => 'penjualan'], function () {
        Route::get('/', [TransaksiController::class, 'index'])->name('admin.penjualan');
        Route::get('/search', [TransaksiController::class, 'search'])->name('admin.penjualan.search');
        Route::post('/store', [TransaksiController::class, 'store'])->name('admin.penjualan.store');
    });

    Route::group(['prefix' => 'pembelian'], function () {
        Route::get('/', [TransaksiController::class, 'pembelian'])->name('admin.pembelian');
        Route::get('/search-barang', [TransaksiController::class, 'searchPembelian'])->name('admin.pembelian.search');
        Route::get('/search-pemasok', [TransaksiController::class, 'searchVendor'])->name('admin.pembelian.search-pemasok');
        Route::get('/create', [TransaksiController::class, 'createPembelian'])->name('admin.pembelian.create');
    });
});
Route::group(['prefix' => 'kasir', 'middleware' => ['role:kasir']], function () {
    Route::get('/', [DashboardController::class, 'kasir'])->name('kasir');
});
Route::group(['prefix' => 'super', 'middleware' => ['role:super']], function () {
    Route::get('/', [DashboardController::class, 'super'])->name('super');
    Route::group(['prefix' => 'kategori'], function () {
        Route::get('/', [KategoriController::class, 'index'])->name('super.kategori');
        Route::post('/store', [KategoriController::class, 'store'])->name('super.kategori.store');
        Route::post('/{id}', [KategoriController::class, 'edit'])->name('super.kategori.update');
    });
});

Route::get('/', [LoginController::class, 'index'])->name('auth');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

