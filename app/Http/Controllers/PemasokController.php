<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use App\Http\Requests\StorePemasokRequest;
use App\Http\Requests\UpdatePemasokRequest;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pemasok = Pemasok::all();
        return view('admin.pemasok.index', compact('pemasok'));
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
            'nama_pemasok' => 'required|string',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'email' => 'required|email',
        ]);

        $pemasok = Pemasok::create([
            'nama_pemasok' => $request->nama_pemasok,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.pemasok', compact('pemasok'))->with('success', 'Pemasok berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     */
    public function show(Pemasok $pemasok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'nama_pemasok' => 'required|string',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'email' => 'required|email',
        ]);

        $pemasok = Pemasok::findOrFail($id);
        $pemasok->update([
            'nama_pemasok' => $request->nama_pemasok,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'email' => $request->email,
        ]);
        return redirect()->route('admin.pemasok')->with('success', 'Pemasok berhasil diubah');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePemasokRequest $request, Pemasok $pemasok)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pemasok = Pemasok::findOrFail($id);
        $pemasok->delete();
        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil dihapus');
    }
}
