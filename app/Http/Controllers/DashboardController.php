<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $selectedMonth = $request->input('bulan', Carbon::now()->month); // Default ke bulan ini
    
        // Ambil data transaksi per hari dalam bulan yang dipilih
        $transaksiPerHari = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, SUM(total) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        $totalTransaksi = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, COUNT(*) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');
    
        // Total barang terjual tahunan
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');
    
        // Total pendapatan bulanan
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->sum('total');
    
        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $selectedMonth)
            ->sum('jumlah');
    
        // Buat array dengan jumlah hari dalam bulan yang dipilih
        $jumlahHari = Carbon::create($currentYear, $selectedMonth)->daysInMonth;
        $labels = [];
        $data = [];
        $dataTransaksi = [];
    
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $labels[] = $i; // Label tanggal 1-31
    
            $totalPendapatan = $transaksiPerHari->firstWhere('hari', $i)->total ?? 0;
            $data[] = $totalPendapatan;
    
            $totalTransaksiHari = $totalTransaksi->firstWhere('hari', $i)->total ?? 0;
            $dataTransaksi[] = $totalTransaksiHari;
        }
    
        // List bulan untuk dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.dashboard.index', compact(
            'labels', 
            'data', 
            'dataTransaksi', 
            'pendapatanTahunan', 
            'totalBarangTerjualTahun', 
            'pendapatanBulanan', 
            'totalBarangTerjualBulan', 
            'selectedMonth',
            'bulanList'
        ));
    }
    
    

    public function kasir(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $selectedMonth = $request->input('bulan', Carbon::now()->month); // Default ke bulan ini
    
        // Ambil data transaksi per hari dalam bulan yang dipilih
        $transaksiPerHari = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, SUM(total) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        $totalTransaksi = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, COUNT(*) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');
    
        // Total barang terjual tahunan
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');
    
        // Total pendapatan bulanan
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->sum('total');
    
        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $selectedMonth)
            ->sum('jumlah');
    
        // Buat array dengan jumlah hari dalam bulan yang dipilih
        $jumlahHari = Carbon::create($currentYear, $selectedMonth)->daysInMonth;
        $labels = [];
        $data = [];
        $dataTransaksi = [];
    
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $labels[] = $i; // Label tanggal 1-31
    
            $totalPendapatan = $transaksiPerHari->firstWhere('hari', $i)->total ?? 0;
            $data[] = $totalPendapatan;
    
            $totalTransaksiHari = $totalTransaksi->firstWhere('hari', $i)->total ?? 0;
            $dataTransaksi[] = $totalTransaksiHari;
        }
    
        // List bulan untuk dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('kasir.dashboard.index', compact(
            'labels', 
            'data', 
            'dataTransaksi', 
            'pendapatanTahunan', 
            'totalBarangTerjualTahun', 
            'pendapatanBulanan', 
            'totalBarangTerjualBulan', 
            'selectedMonth',
            'bulanList'
        ));
    }
    public function super(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $selectedMonth = $request->input('bulan', Carbon::now()->month); // Default ke bulan ini
    
        // Ambil data transaksi per hari dalam bulan yang dipilih
        $transaksiPerHari = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, SUM(total) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        $totalTransaksi = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, COUNT(*) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');
    
        // Total barang terjual tahunan
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');
    
        // Total pendapatan bulanan
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->sum('total');
    
        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $selectedMonth)
            ->sum('jumlah');
    
        // Buat array dengan jumlah hari dalam bulan yang dipilih
        $jumlahHari = Carbon::create($currentYear, $selectedMonth)->daysInMonth;
        $labels = [];
        $data = [];
        $dataTransaksi = [];
    
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $labels[] = $i; // Label tanggal 1-31
    
            $totalPendapatan = $transaksiPerHari->firstWhere('hari', $i)->total ?? 0;
            $data[] = $totalPendapatan;
    
            $totalTransaksiHari = $totalTransaksi->firstWhere('hari', $i)->total ?? 0;
            $dataTransaksi[] = $totalTransaksiHari;
        }
    
        // List bulan untuk dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('supervisor.dashboard.index', compact(
            'labels', 
            'data', 
            'dataTransaksi', 
            'pendapatanTahunan', 
            'totalBarangTerjualTahun', 
            'pendapatanBulanan', 
            'totalBarangTerjualBulan', 
            'selectedMonth',
            'bulanList'
        ));
    }
}