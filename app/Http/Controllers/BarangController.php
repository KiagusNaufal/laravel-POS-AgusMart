<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barang = Barang::paginate(10); // Paginate the results, 10 items per page
        $kategori = Kategori::all(); // Assuming you have a Kategori model
        return view('admin.barang.index', compact('barang', 'kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'string',
            'nama_barang' => 'string',
            'harga_beli' => 'integer',
            'persentase_keuntungan' => 'numeric|between:0,99.99',
            'stok' => 'integer',
            'id_kategori' => 'required|integer',
            'gambar_barang' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imageName = time().'.'.$request->gambar_barang->extension();  
        $request->gambar_barang->move(public_path('images'), $imageName);

        $barang = Barang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'harga_beli' => $request->harga_beli,
            'persentase_keuntungan' => $request->persentase_keuntungan,
            'stok' => $request->stok,
            'id_kategori' => $request->id_kategori,
            'gambar_barang' => $imageName,
        ]);
        return redirect()->route('admin.barang', compact('barang'))->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'kode_barang' => 'required|string',
            'nama_barang' => 'required|string',
            'harga_beli' => 'required|integer',
            'persentase_keuntungan' => 'required|numeric|between:0,99.99',
            'stok' => 'required|integer',
            'id_kategori' => 'required|integer',
            'gambar_barang' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $barang = Barang::findOrFail($id);
        // Delete the old image
        if ($barang->gambar_barang && file_exists(public_path('images/'.$barang->gambar_barang))) {
            unlink(public_path('images/'.$barang->gambar_barang));
        }

        $imageName = time().'.'.$request->gambar_barang->extension();  
        $request->gambar_barang->move(public_path('images'), $imageName);

        $barang->update([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'harga_beli' => $request->harga_beli,
            'persentase_keuntungan' => $request->persentase_keuntungan,
            'stok' => $request->stok,
            'id_kategori' => $request->id_kategori,
            'gambar_barang' => $imageName,
        ]);
        return redirect()->route('admin.barang', compact('barang'))->with('success', 'Data berhasil diubah');
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();
        return redirect()->route('home')->with('success', 'Data berhasil dihapus');
    }
}
