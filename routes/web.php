<?php

use App\Http\Controllers\AbsensiKerjaController;
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
use App\Http\Controllers\PengajuanBarangController;
use App\Http\Controllers\PenjualanPdfController;
use App\Http\Controllers\TransaksiController;
use App\Models\Pemasok;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

Route::get('/proxy/datatables-id', function () {
    $url = 'http://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json';
    $response = Http::get($url);
    return $response->json();
});

Route::group(['prefix' => 'admin', 'middleware' => ['role:admin']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin');
    Route::group(['prefix' => 'kategori'], function () {
        Route::get('/', [KategoriController::class, 'index'])->name('kategori');
        Route::post('/store', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/{id}', [KategoriController::class, 'destroy'])->name('kategori.delete');
        Route::get('/pdf', [KategoriController::class, 'generatePdf'])->name('kategori.pdf');
        Route::get('/excel', [KategoriController::class, 'exportExcel'])->name('kategori.excel');
        Route::post('/import', [KategoriController::class, 'import'])->name('kategori.import');
    });
    Route::group(['prefix' => 'pemasok'], function () {
        Route::get('/', [PemasokController::class, 'index'])->name('admin.pemasok');
        Route::post('/store', [PemasokController::class, 'store'])->name('admin.pemasok.store');
        Route::post('/{id}', [PemasokController::class, 'edit'])->name('admin.pemasok.update');
        Route::delete('/{id}', [PemasokController::class, 'destroy'])->name('admin.pemasok.delete');
        Route::get('/pdf', [PemasokController::class, 'generatePdf'])->name('admin.pemasok.pdf');
        Route::get('/excel', [PemasokController::class, 'exportExcel'])->name('admin.pemasok.excel');
        Route::post('/import', [PemasokController::class, 'import'])->name('admin.pemasok.import');
    });
    Route::group(['prefix' => 'member'], function () {
        Route::get('/', [MemberController::class, 'index'])->name('admin.member');
        Route::post('/store', [MemberController::class, 'store'])->name('admin.member.store');
        Route::post('/{id}', [MemberController::class, 'edit'])->name('admin.member.update');
        Route::delete('/{id}', [MemberController::class, 'destroy'])->name('admin.member.delete');
        Route::get('/pdf', [MemberController::class, 'generatePdf'])->name('admin.member.pdf');
        Route::get('/excel', [MemberController::class, 'exportExcel'])->name('admin.member.excel');
        Route::post('/import', [MemberController::class, 'import'])->name('admin.member.import');
    });

    Route::group(['prefix' => 'pengajuan'], function () {
        Route::get('/', [PengajuanBarangController::class, 'index'])->name('admin.pengajuan');
        Route::get('/search-member', [PengajuanBarangController::class, 'search'])->name('admin.pengajuan.search-member');
        Route::post('/store', [PengajuanBarangController::class, 'store'])->name('admin.pengajuan.store');
        Route::post('/status/{id}', [PengajuanBarangController::class, 'edit'])->name('admin.pengajuan.edit');
        Route::post('/{id}', [PengajuanBarangController::class, 'update'])->name('admin.pengajuan.update');
        Route::delete('/{id}', [PengajuanBarangController::class, 'destroy'])->name('admin.pengajuan.destroy');
        Route::get('/pdf', [PengajuanBarangController::class, 'generatePDF'])->name('admin.pengajuan.pdf');
        Route::get('/excel', [PengajuanBarangController::class, 'exportExcel'])->name('admin.pengajuan.excel');
    });

    Route::group(['prefix' => 'barang'], function () {
        Route::get('/', [BarangController::class, 'index'])->name('admin.barang');
        Route::post('/store', [BarangController::class, 'store'])->name('admin.barang.store');
        Route::post('/{id}', [BarangController::class, 'edit'])->name('admin.barang.update');
        Route::delete('/{id}', [BarangController::class, 'destroy'])->name('admin.barang.delete');
        Route::get('/pdf', [BarangController::class, 'generatePDF'])->name('admin.barang.pdf');
        Route::get('/excel', [BarangController::class, 'exportExcel'])->name('admin.barang.excel');
        Route::post('/import', [BarangController::class, 'import'])->name('admin.barang.import');
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
    Route::group(['prefix' => 'absensi'], function () {
        Route::get('/', [AbsensiKerjaController::class, 'index'])->name('admin.absensi');
        Route::post('/store', [AbsensiKerjaController::class, 'store'])->name('absensi.store');
        Route::put('/{id}', [AbsensiKerjaController::class, 'update'])->name('admin.absensi.update');
        Route::delete('/{id}', [AbsensiKerjaController::class, 'destroy'])->name('admin.absensi.delete');
        Route::get('/pdf', [AbsensiKerjaController::class, 'generatePdf'])->name('admin.absensi.pdf');
        Route::get('/excel', [AbsensiKerjaController::class, 'exportExcel'])->name('admin.absensi.excel');
        Route::post('/import', [AbsensiKerjaController::class, 'import'])->name('absensi.import');
        Route::get('/format', [AbsensiKerjaController::class, 'format'])->name('absensi.format');
        Route::post('/update-status/{id}', [AbsensiKerjaController::class, 'updateStatus'])->name('absensi.updateStatus');
        Route::post('/selesai/{id}', [AbsensiKerjaController::class, 'selesai'])->name('absensi.selesai');


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
        Route::get('/struk/{id}', [TransaksiController::class, 'showStruk'])->name('kasir.struk');
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

