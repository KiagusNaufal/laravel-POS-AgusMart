<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Member;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{

    public function index(Request $request)
    {
        $query = $request->input('query');
        if ($query) {
            $barang = Barang::where('kode_barang', 'LIKE', "%{$query}%")
                            ->orWhere('nama_barang', 'LIKE', "%{$query}%")
                            ->get();
        } else {
            // Jika tidak ada query, kembalikan array kosong
            $barang = [];
        }
        
        return view('admin.penjualan.index', compact('barang'));
    }
    

    public function store(Request $request)
    {
        $request->validate([
            
        ]);
    }
}
