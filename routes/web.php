<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangPdfController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\PembelianPdfController;
use App\Http\Controllers\PenjualanPdfController;
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
        Route::delete('/{id}', [PemasokController::class, 'destroy'])->name('admin.pemasok.delete');
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
        Route::get('search-member', [MemberController::class, 'search'])->name('admin.penjualan.search-member');
        Route::get('/struk/{id}', [TransaksiController::class, 'showStruk'])->name('struk');
    });

    Route::group(['prefix' => 'pembelian'], function () {
        Route::get('/', [TransaksiController::class, 'pembelian'])->name('admin.pembelian');
        Route::post('/store', [TransaksiController::class, 'storePembelian'])->name('admin.pembelian.store');
        Route::get('/search-barang', [TransaksiController::class, 'searchPembelian'])->name('admin.pembelian.search');
        Route::get('/search-pemasok', [TransaksiController::class, 'searchVendor'])->name('admin.pembelian.search-pemasok');
        Route::get('/create', [TransaksiController::class, 'createPembelian'])->name('admin.pembelian.create');
    });

    Route::group(['prefix' => 'laporan'], function () {
        Route::get('/barang', [LaporanController::class, 'barang'])->name('admin.laporan.barang');
        Route::get('/barang/pdf', [BarangPdfController::class, 'generatePdf'])->name('admin.laporan.barang.pdf');
        Route::get('/barang/excel', [BarangPdfController::class, 'exportExcel'])->name('admin.laporan.barang.excel');
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('admin.laporan.penjualan');
        Route::get('/penjualan/pdf', [PenjualanPdfController::class, 'generatePdf'])->name('admin.laporan.penjualan.pdf');
        Route::get('/penjualan/excel', [PenjualanPdfController::class, 'exportExcel'])->name('admin.laporan.penjualan.excel');
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('admin.laporan.pembelian');
        Route::get('/pembelian/pdf', [PembelianPdfController::class, 'generatePdf'])->name('admin.laporan.pembelian.pdf');
        Route::get('/pembelian/excel', [PembelianPdfController::class, 'exportExcel'])->name('admin.laporan.pembelian.excel');
    });
});
Route::group(['prefix' => 'kasir', 'middleware' => ['role:kasir']], function () {
    Route::get('/', [DashboardController::class, 'kasir'])->name('kasir');

    Route::group(['prefix' => 'penjualan'], function () {
        Route::get('/', [TransaksiController::class, 'index'])->name('kasir.penjualan');
        Route::get('/search', [TransaksiController::class, 'search'])->name('kasir.penjualan.search');
        Route::post('/store', [TransaksiController::class, 'store'])->name('kasir.penjualan.store');
        Route::get('search-member', [MemberController::class, 'search'])->name('kasir.penjualan.search-member');
        Route::get('/struk/{id}', [TransaksiController::class, 'showStruk'])->name('struk');
    });

    Route::group(['prefix' => 'member'], function () {
        Route::get('/', [MemberController::class, 'index'])->name('kasir.member');
        Route::post('/store', [MemberController::class, 'store'])->name('kasir.member.store');
        Route::post('/{id}', [MemberController::class, 'edit'])->name('kasir.member.update');
    });
});
Route::group(['prefix' => 'super', 'middleware' => ['role:super']], function () {
    Route::get('/', [DashboardController::class, 'super'])->name('super');
    Route::group(['prefix' => 'laporan'], function () {
        Route::get('/barang', [LaporanController::class, 'barang'])->name('super.laporan.barang');
        Route::get('/barang/pdf', [BarangPdfController::class, 'generatePdf'])->name('super.laporan.barang.pdf');
        Route::get('/barang/excel', [BarangPdfController::class, 'exportExcel'])->name('super.laporan.barang.excel');
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('super.laporan.penjualan');
        Route::get('/penjualan/pdf', [PenjualanPdfController::class, 'generatePdf'])->name('super.laporan.penjualan.pdf');
        Route::get('/penjualan/excel', [PenjualanPdfController::class, 'exportExcel'])->name('super.laporan.penjualan.excel');
        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('super.laporan.pembelian');
        Route::get('/pembelian/pdf', [PembelianPdfController::class, 'generatePdf'])->name('super.laporan.pembelian.pdf');
        Route::get('/pembelian/excel', [PembelianPdfController::class, 'exportExcel'])->name('super.laporan.pembelian.excel');
    });
});

Route::get('/', [LoginController::class, 'index'])->name('auth');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

