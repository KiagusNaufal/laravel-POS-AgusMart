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
    public function barang(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $searchTerm = $request->input('search_term');
        $perPage = $request->input('per_page', 10);
    
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
    
        // Mapping data
        $mappedData = $query->map(function ($item) {
            return [
                'nama_barang' => $item->barang->nama_barang,
                'total_terjual' => $item->total_terjual,
                'total_penjualan' => $item->total_penjualan,
                'kode_barang' => $item->barang->kode_barang,
                'keuntungan' => $item->total_penjualan - ($item->total_terjual * $item->barang->harga_beli),
            ];
        });

        // Calculate total profit
        $totalKeuntungan = $mappedData->sum('keuntungan');
    
        // Manual pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $mappedData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedBarang = new LengthAwarePaginator($currentItems, $mappedData->count(), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    
        $kategori = Kategori::all();
    
        $user = Auth::user();
        if ($user->role == 'admin') {
            return view('admin.laporan.barang', compact('paginatedBarang', 'kategori', 'totalKeuntungan'));
        } elseif ($user->role == 'super') {
            return view('supervisor.laporan.barang', compact('paginatedBarang', 'kategori', 'totalKeuntungan'));
        } else {
            return redirect('/');
        }
    }

    public function penjualan(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->input('per_page', 5); // Default 10 data per halaman
    
        $penjualan = Penjualan::with('user', 'detail_penjualan.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('no_faktur', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_faktur', [$startDate, $endDate]);
            })
            ->paginate($perPage);
    
            $user = Auth::user();
            if ($user->role == 'admin') {
                return view('admin.laporan.penjualan', compact('penjualan'));
            } elseif ($user->role == 'super') {
                return view('supervisor.laporan.penjualan', compact('penjualan'));
            } else {
                return redirect('/');
            }
    }

    public function pembelian(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->input('per_page', 5); // Default 10 data per halaman
    
        $pembelian = Pembelian::with('user', 'detail_pembelian.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('kode_masuk', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_masuk', [$startDate, $endDate]);
            })
            ->paginate($perPage);
    
            $user = Auth::user();
            if ($user->role == 'admin') {
                return view('admin.laporan.pembelian', compact('pembelian'));
            } elseif ($user->role == 'super') {
                return view('supervisor.laporan.pembelian', compact('pembelian'));
            } else {
                return redirect('/');
            }
    }

}
