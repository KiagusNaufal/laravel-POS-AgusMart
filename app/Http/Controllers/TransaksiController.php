<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Member;
use App\Models\Pemasok;
use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{

    public function index()
    {
        return view('admin.penjualan.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Barang::where('nama_barang', 'like', "%$query%")
                         ->orWhere('kode_barang', 'like', "%$query%")
                         ->get();
        return response()->json($results);
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'id_member' => 'exists:member,id',
            'id_barang' => 'required|array',
            'id_barang.*' => 'required',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|integer|min:1',
            'harga_jual' => 'required|array',
            'harga_jual.*' => 'required|numeric|min:0',
        ]);
        
        $user = FacadesAuth::user();
        $userId = $user->id;

        $total = 0;

        foreach ($request->id_barang as $index => $id_barang) {
            $sub_total = $request->harga_jual[$index] * $request->jumlah[$index];
            $total += $sub_total;
        }

        Penjualan::create([
            'no_faktur' => 'F' . date('YmdHis'),
            'tanggal_faktur' => now(),
            'total' => $total,
            'id_member' => $request->id_member,
            'user_id' => $userId,
        ]);

        $penjualan = Penjualan::latest('id')->first();
        try {
            foreach ($request->id_barang as $index => $id_barang) {
                $sub_total = $request->harga_jual[$index] * $request->jumlah[$index];
                DetailPenjualan::create([
                    'id_penjualan' => $penjualan->id,
                    'id_barang' => $id_barang,
                    'harga_jual' => $request->harga_jual[$index],
                    'jumlah' => $request->jumlah[$index],
                    'sub_total' => $sub_total,
                ]);

                // Update stock in Barang table
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->stok -= $request->jumlah[$index];
                    $barang->save();
                }
            }

            return redirect()->route('admin.penjualan')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage()]);
        }
    }


    public function pembelian() {
        $pembelian = Pembelian::with('detail_pembelian.barang')->get();
        return view('admin.pembelian.index', compact('pembelian'));
    }

    public function createPembelian() {
        return view('admin.pembelian.create');
    }
    public function searchPembelian(Request $request)
    {
 $query = trim($request->input('q'));

    Log::info("Query Barang: " . ($query ?: 'NULL')); // Debugging log

    if ($query === '') {
        return response()->json([]); // Jangan kembalikan data jika query kosong
    }
        $items = Barang::where('nama_barang', 'LIKE', "%{$query}%")
                     ->orWhere('kode_barang', 'LIKE', "%{$query}%")
                     ->get();
    
        return response()->json($items);
    }
    
    
    public function searchVendor(Request $request)
    {
        $query = trim($request->input('q'));
        $results = Pemasok::where('nama_pemasok', 'like', "%{$query}%")
                         ->get();
        return response()->json($results);
    }

}
