<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard for the admin role with sales data.
     */
    public function index(Request $request)
    {
        // Get the current year and selected month (default to current month)
        $currentYear = Carbon::now()->year;
        $selectedMonth = $request->input('bulan', Carbon::now()->month); // Default to current month
    
        // Retrieve daily transaction totals for the selected month
        $transaksiPerHari = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, SUM(total) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        // Retrieve daily count of transactions for the selected month
        $totalTransaksi = Penjualan::selectRaw('DAY(tanggal_faktur) as hari, COUNT(*) as total')
            ->whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->groupBy('hari')
            ->orderBy('hari')
            ->get();
    
        // Calculate the total revenue for the current year
        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');
    
        // Calculate the total number of items sold for the current year
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');
    
        // Calculate the total revenue for the selected month
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->sum('total');
    
        // Calculate the total number of items sold for the selected month
        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $selectedMonth)
            ->sum('jumlah');
    
        // Prepare labels (days of the month) and data arrays for chart
        $jumlahHari = Carbon::create($currentYear, $selectedMonth)->daysInMonth;
        $labels = [];
        $data = [];
        $dataTransaksi = [];
    
        // Loop through each day of the month to populate data for the chart
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $labels[] = $i; // Day of the month
    
            // Get total revenue for the current day, default to 0 if no data
            $totalPendapatan = $transaksiPerHari->firstWhere('hari', $i)->total ?? 0;
            $data[] = $totalPendapatan;
    
            // Get the total number of transactions for the current day, default to 0 if no data
            $totalTransaksiHari = $totalTransaksi->firstWhere('hari', $i)->total ?? 0;
            $dataTransaksi[] = $totalTransaksiHari;
        }
    
        // Prepare a list of months for the dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Return the data to the admin dashboard view
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
    
    /**
     * Display the dashboard for the cashier role with sales data.
     */
    public function kasir(Request $request)
    {
        // Use the same logic as the admin dashboard, but for cashier
        return $this->getDashboardData($request, 'kasir.dashboard.index');
    }
    
    /**
     * Display the dashboard for the supervisor role with sales data.
     */
    public function super(Request $request)
    {
        // Use the same logic as the admin dashboard, but for supervisor
        return $this->getDashboardData($request, 'supervisor.dashboard.index');
    }

    /**
     * Common function to retrieve dashboard data for various roles.
     */
    private function getDashboardData(Request $request, $view)
    {
        $currentYear = Carbon::now()->year;
        $selectedMonth = $request->input('bulan', Carbon::now()->month); // Default to current month
    
        // Retrieve daily transaction totals and transaction counts for the selected month
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
    
        // Calculate total revenue for the current year
        $pendapatanTahunan = Penjualan::whereYear('tanggal_faktur', $currentYear)->sum('total');
    
        // Calculate total number of items sold for the current year
        $totalBarangTerjualTahun = DetailPenjualan::whereYear('created_at', $currentYear)->sum('jumlah');
    
        // Calculate total revenue for the selected month
        $pendapatanBulanan = Penjualan::whereYear('tanggal_faktur', $currentYear)
            ->whereMonth('tanggal_faktur', $selectedMonth)
            ->sum('total');
    
        // Calculate total number of items sold for the selected month
        $totalBarangTerjualBulan = DetailPenjualan::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $selectedMonth)
            ->sum('jumlah');
    
        // Prepare labels (days of the month) and data arrays for chart
        $jumlahHari = Carbon::create($currentYear, $selectedMonth)->daysInMonth;
        $labels = [];
        $data = [];
        $dataTransaksi = [];
    
        // Loop through each day of the month to populate data for the chart
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $labels[] = $i; // Day of the month
    
            // Get total revenue for the current day, default to 0 if no data
            $totalPendapatan = $transaksiPerHari->firstWhere('hari', $i)->total ?? 0;
            $data[] = $totalPendapatan;
    
            // Get the total number of transactions for the current day, default to 0 if no data
            $totalTransaksiHari = $totalTransaksi->firstWhere('hari', $i)->total ?? 0;
            $dataTransaksi[] = $totalTransaksiHari;
        }
    
        // Prepare a list of months for the dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Return the data to the specified view
        return view($view, compact(
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
