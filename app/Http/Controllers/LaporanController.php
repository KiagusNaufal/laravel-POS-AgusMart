<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Kategori;
use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan barang dengan filter dan pagination.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function barang(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $searchTerm = $request->input('search_term');
        $perPage = $request->input('per_page', 10); // Jumlah data per halaman
    
        // Query untuk mengambil data barang yang terjual
        $query = DetailPenjualan::select('id_barang', DB::raw('SUM(jumlah) as total_terjual'), DB::raw('SUM(detail_penjualan.harga_jual * jumlah) as total_penjualan'))
            ->join('penjualan', 'detail_penjualan.id_penjualan', '=', 'penjualan.id')
            ->join('barang', 'detail_penjualan.id_barang', '=', 'barang.id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('penjualan.tanggal_faktur', [$startDate, $endDate]);
            })
            ->when($searchTerm, function ($query) use ($searchTerm) {
                return $query->where(function ($query) use ($searchTerm) {
                    $query->where('barang.nama_barang', 'like', '%' . $searchTerm . '%')
                          ->orWhere('barang.kode_barang', 'like', '%' . $searchTerm . '%');
                });
            })
            ->groupBy('id_barang')
            ->with('barang')
            ->get();
    
        // Mapping data untuk menambahkan perhitungan keuntungan
        $mappedData = $query->map(function ($item) {
            return [
                'nama_barang' => $item->barang->nama_barang,
                'total_terjual' => $item->total_terjual,
                'total_penjualan' => $item->total_penjualan,
                'kode_barang' => $item->barang->kode_barang,
                'keuntungan' => $item->total_penjualan - ($item->total_terjual * $item->barang->harga_beli),
            ];
        });

        // Menghitung total keuntungan
        $totalKeuntungan = $mappedData->sum('keuntungan');
    
        // Manual pagination menggunakan LengthAwarePaginator
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $mappedData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedBarang = new LengthAwarePaginator($currentItems, $mappedData->count(), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    
        // Mengambil data kategori untuk digunakan dalam view
        $kategori = Kategori::all();
    
        // Mengambil data user yang sedang login
        $user = Auth::user();
        if ($user->role == 'admin') {
            // Menampilkan laporan barang untuk admin
            return view('admin.laporan.barang', compact('paginatedBarang', 'kategori', 'totalKeuntungan'));
        } elseif ($user->role == 'super') {
            // Menampilkan laporan barang untuk supervisor
            return view('supervisor.laporan.barang', compact('paginatedBarang', 'kategori', 'totalKeuntungan'));
        } else {
            return redirect('/');
        }
    }

    /**
     * Menampilkan laporan penjualan dengan filter dan pagination.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function penjualan(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->input('per_page', 5); // Default 5 data per halaman
    
        // Query untuk mengambil data penjualan
        $penjualan = Penjualan::with('user', 'detail_penjualan.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('no_faktur', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_faktur', [$startDate, $endDate]);
            })
            ->paginate($perPage);
    
        // Mengambil data user yang sedang login
        $user = Auth::user();
        if ($user->role == 'admin') {
            // Menampilkan laporan penjualan untuk admin
            return view('admin.laporan.penjualan', compact('penjualan'));
        } elseif ($user->role == 'super') {
            // Menampilkan laporan penjualan untuk supervisor
            return view('supervisor.laporan.penjualan', compact('penjualan'));
        } else {
            return redirect('/');
        }
    }

    /**
     * Menampilkan laporan pembelian dengan filter dan pagination.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function pembelian(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->input('per_page', 5); // Default 5 data per halaman
    
        // Query untuk mengambil data pembelian
        $pembelian = Pembelian::with('user', 'detail_pembelian.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('kode_masuk', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_masuk', [$startDate, $endDate]);
            })
            ->paginate($perPage);
    
        // Mengambil data user yang sedang login
        $user = Auth::user();
        if ($user->role == 'admin') {
            // Menampilkan laporan pembelian untuk admin
            return view('admin.laporan.pembelian', compact('pembelian'));
        } elseif ($user->role == 'super') {
            // Menampilkan laporan pembelian untuk supervisor
            return view('supervisor.laporan.pembelian', compact('pembelian'));
        } else {
            return redirect('/');
        }
    }

}
