<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\DetailPenjualan;
use App\Models\Member;
use App\Models\Pemasok;
use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Cast\String_;

class TransaksiController extends Controller
{

    public function index()
    {
        $user = FacadesAuth::user();
        if ($user->role == 'admin') {
            return view('admin.penjualan.index');
        } elseif ($user->role == 'kasir') {
            return view('kasir.penjualan.index');
        } else {
            return redirect('/');
        }
    }

    public function search(Request $request)
    {
        $query = trim($request->input('query'));

        if ($query === '') {
            return response()->json([]); // Return empty array if query is empty
        }

        $results = Barang::where(function ($q) use ($query) {
            $q->where('nama_barang', 'like', "%$query%")
                ->orWhere('kode_barang', 'like', "%$query%");
        })
            ->get();

        return response()->json($results);
    }
    

    public function store(Request $request)
    {
        $user = FacadesAuth::user();
    
        // Cek apakah user memiliki role yang diperbolehkan
        if (!in_array($user->role, ['admin', 'kasir'])) {
            return redirect('/')->withErrors(['error' => 'Anda tidak memiliki izin untuk melakukan transaksi']);
        }
    
        $request->validate([
            'id_barang' => 'required|array',
            'id_barang.*' => 'required',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|integer|min:1',
            'harga_jual' => 'required|array',
            'harga_jual.*' => 'required|numeric|min:0',
        ]);
    
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
    
                // Update stok barang
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->stok -= $request->jumlah[$index];
                    $barang->save();
                }
            }
    
            $cash = $request->cash ?? 0;
            $kembalian = $cash - $penjualan->total;
    
            if ($user->role == 'admin') {
                return redirect()->route('struk', [
                    'id' => $penjualan->id,
                    'cash' => $cash,
                    'kembalian' => $kembalian
                ])->with('success', 'Transaksi berhasil disimpan.');
            } elseif ($user->role == 'kasir') {
                return redirect()->route('kasir.struk', [
                    'id' => $penjualan->id,
                    'cash' => $cash,
                    'kembalian' => $kembalian
                ])->with('success', 'Transaksi berhasil disimpan.');
            } else {
                return redirect('/');
            }
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan transaksi: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan transaksi']);
        }
    }
    
    

    public function showStruk($id)
    {
        $penjualan = Penjualan::with(['detail_penjualan' => function ($query) use ($id) {
            $query->with(['barang' => function ($query) {
                $query->whereNotNull('nama_barang');
            }])->where('id_penjualan', $id);
        }])->findOrFail($id);

        $user = FacadesAuth::user();
        if ($user->role == 'admin') {
            return view('admin.penjualan.struk', compact('penjualan'))->with('success', 'Selamat datang, Admin');
        } elseif ($user->role == 'kasir') {
            return view('kasir.penjualan.struk', compact('penjualan'))->with('success', 'Selamat datang, Kasir');
        } else {
            return redirect('/');
        }
    }



    public function pembelian()
    {
        $pembelian = Pembelian::with('user', 'detail_pembelian.barang')->get();

        return view('admin.pembelian.index', compact('pembelian'));
    }

    public function createPembelian()
    {
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

    public function storePembelian(Request $request)
    {
        $request->validate([
            'id_pemasok' => 'required|exists:pemasok,id',
            'id_barang' => 'required|array',
            'id_barang.*' => 'required',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|integer|min:1',
            'harga_beli' => 'required|array',
            'harga_beli.*' => 'required|numeric|min:0',
        ]);

        $user = FacadesAuth::user();
        $userId = $user->id;
        Log::info("User ID {$userId} Melakuakn Transaksi Pembelian");

        Pembelian::create([
            'kode_masuk' => 'M' . strtoupper(Str::random(8)),
            'tanggal_masuk' => now(),
            'id_pemasok' => $request->id_pemasok,
            'user_id' => $userId,
        ]);

        $pembelian = Pembelian::latest('id')->first();
        try {
            foreach ($request->id_barang as $index => $id_barang) {
                $sub_total = $request->harga_beli[$index] * $request->jumlah[$index];
                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id,
                    'id_barang' => $id_barang,
                    'harga_beli' => $request->harga_beli[$index],
                    'jumlah' => $request->jumlah[$index],
                    'sub_total' => $sub_total,
                ]);

                // Update stock in Barang table
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->stok += $request->jumlah[$index];
                    $barang->save();
                }

                // Update purchase price in Barang table
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->harga_beli = $request->harga_beli[$index];
                    $barang->save();
                }
            }

            return redirect()->route('admin.pembelian')->with('success', 'Pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan pembelian: ' . $e->getMessage()]);
        }
    }
}
