<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data transaksi per bulan
        $transaksiPerBulan = Penjualan::selectRaw('MONTH(tanggal_faktur) as bulan, SUM(total) as total')
            ->whereYear('tanggal_faktur', Carbon::now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');

        // Total barang terjual tahunan
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');

        // Total pendapatan bulanan
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $currentMonth)
            ->sum('total');

        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('jumlah');

        $labels = [];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date('M', mktime(0, 0, 0, $i, 1));
            $total = $transaksiPerBulan->firstWhere('bulan', $i)->total ?? 0;
            $data[] = $total;
        }

        return view('admin.dashboard.index', compact('labels', 'data', 'pendapatanTahunan', 'totalBarangTerjualTahun', 'pendapatanBulanan', 'totalBarangTerjualBulan'));
    }

    public function kasir()
    {
        // Ambil data transaksi per bulan
        $transaksiPerBulan = Penjualan::selectRaw('MONTH(tanggal_faktur) as bulan, SUM(total) as total')
            ->whereYear('tanggal_faktur', Carbon::now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');

        // Total barang terjual tahunan
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');

        // Total pendapatan bulanan
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $currentMonth)
            ->sum('total');

        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('jumlah');

        $labels = [];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date('M', mktime(0, 0, 0, $i, 1));
            $total = $transaksiPerBulan->firstWhere('bulan', $i)->total ?? 0;
            $data[] = $total;
        }

        return view('kasir.dashboard.index', compact('labels', 'data', 'pendapatanTahunan', 'totalBarangTerjualTahun', 'pendapatanBulanan', 'totalBarangTerjualBulan'));
    }

    public function super()
    {
        // Ambil data transaksi per bulan
        $transaksiPerBulan = Penjualan::selectRaw('MONTH(tanggal_faktur) as bulan, SUM(total) as total')
            ->whereYear('tanggal_faktur', Carbon::now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');

        // Total barang terjual tahunan
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');

        // Total pendapatan bulanan
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $currentMonth)
            ->sum('total');

        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('jumlah');

        $labels = [];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date('M', mktime(0, 0, 0, $i, 1));
            $total = $transaksiPerBulan->firstWhere('bulan', $i)->total ?? 0;
            $data[] = $total;
        }

        return view('supervisor.dashboard.index', compact('labels', 'data', 'pendapatanTahunan', 'totalBarangTerjualTahun', 'pendapatanBulanan', 'totalBarangTerjualBulan'));
    }


}
