<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function barang(Request $request)
    {
        $query = Barang::query();
    
        if ($request->filled('filter')) {
            $query->where('nama_barang', 'like', '%' . $request->filter . '%')
                  ->orWhere('kode_barang', 'like', '%' . $request->filter . '%');
        }

        if ($request->filled('ditarik')) {
            $query->where('ditarik', $request->ditarik);
        }
    
        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }
    
        $barang = $query->paginate(5)->appends([
            'filter' => $request->filter,
            'kategori' => $request->kategori,
            'ditarik' => $request->ditarik,
        ]);
        

    $kategori = Kategori::all();
    
        return view('admin.laporan.barang', compact('barang', 'kategori'));
    }
    
}
