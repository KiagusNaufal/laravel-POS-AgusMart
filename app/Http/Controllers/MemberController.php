<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $members = Member::all();
        return view('admin.member.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Member::where('no_telp', 'like', "%$query%")
                         ->orWhere('kode_pelanggan', 'like', "%$query%")
                         ->orWhere('nama_pelanggan', 'like', "%$query%")
                         ->get();
        return response()->json($results);
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'no_telp' => 'required|string|max:15',
        ]);

        $kode_pelanggan = random_int(100000, 999999);

        $member = Member::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'kode_pelanggan' => $kode_pelanggan,
        ]);

        return redirect()->route('admin.member', compact('member'))->with('success', 'Member berhasil ditambahkan dengan kode pelanggan: ' . $kode_pelanggan);
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'no_telp' => 'required|string|max:15',
        ]);

        $member = Member::find($request->id);
        
        $member->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
        ]);

        return redirect()->route('admin.member')->with('success', 'Member berhasil diubah');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();
        return redirect()->route('member.index')->with('success', 'Member berhasil dihapus');
    }
}
