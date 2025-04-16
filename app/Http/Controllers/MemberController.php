<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Member;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Imports\MemberImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    /**
     * Menampilkan daftar member.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil semua data member
        $members = Member::all();

        // Mendapatkan data user yang sedang login
        $user = Auth::user();

        // Menentukan tampilan berdasarkan role user
        if ($user->role == 'admin') {
            return view('admin.member.index', compact('members'));
        } elseif ($user->role == 'kasir') {
            return view('kasir.member.index', compact('members'));
        } else {
            return redirect('/');  // Jika bukan admin atau kasir, arahkan ke halaman utama
        }
    }

    /**
     * Mencari member berdasarkan query.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Mengambil query pencarian dari input user
        $query = $request->input('query');

        // Mencari member berdasarkan nomor telepon, kode pelanggan, atau nama pelanggan
        $results = Member::where('no_telp', 'like', "%$query%")
                         ->orWhere('kode_pelanggan', 'like', "%$query%")
                         ->orWhere('nama_pelanggan', 'like', "%$query%")
                         ->get();

        // Mengembalikan hasil pencarian dalam format JSON
        return response()->json($results);
    }

    /**
     * Menyimpan member baru ke dalam database.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validasi input yang diterima dari user
            $request->validate([
                'nama_pelanggan' => 'required|string|max:255',
                'email' => 'required|string|max:255',
                'no_telp' => 'required|string|max:15',
            ]);

            // Membuat kode pelanggan secara acak
            $kode_pelanggan = random_int(100000, 999999);

            // Membuat member baru dengan data yang diterima
            $member = Member::create([
                'nama_pelanggan' => $request->nama_pelanggan,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'kode_pelanggan' => $kode_pelanggan,
            ]);

            // Mendapatkan data user yang sedang login
            $user = Auth::user();

            LogHelper::save('create_member', 'Menambahkan member baru: ' . $member->nama_pelanggan . ' dengan kode pelanggan: ' . $member->kode_pelanggan . ' oleh User: ' . $user->name);

            // Mengarahkan berdasarkan role user setelah berhasil menyimpan member
            if ($user->role == 'admin') {
                return redirect()->route('admin.member')->with('success', 'Selamat datang, Admin');
            } elseif ($user->role == 'kasir') {
                return redirect()->route('kasir.member')->with('success', 'Selamat datang, Kasir');
            } else {
                return redirect('/');
            }
        } catch (\Exception $e) {
            // Menangani error dan mengembalikan pesan error
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Menampilkan detail member tertentu.
     * 
     * @param Member $member
     * @return void
     */
    public function show(Member $member)
    {
        // Tidak ada implementasi untuk menampilkan detail member
    }

    /**
     * Menampilkan form untuk mengedit data member tertentu.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit(Request $request)
    {
        // Validasi input yang diterima dari user
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'no_telp' => 'required|string|max:15',
        ]);

        try {
            
            $member = Member::find($request->id);
            
            // Memperbarui data member dengan input yang diterima
            $member->update([
                'nama_pelanggan' => $request->nama_pelanggan,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
            ]);
        
            // Mendapatkan data user yang sedang login
            $user = Auth::user();
            LogHelper::save('update_member', 'Memperbarui member: ' . $member->nama_pelanggan . ' dengan kode pelanggan: ' . $member->kode_pelanggan . ' oleh User: ' . $user->name);
        
            // Mengarahkan berdasarkan role user setelah berhasil memperbarui member
            if ($user->role == 'admin') {
                return redirect()->route('admin.member')->with('success', 'Selamat datang, Admin');
            } elseif ($user->role == 'kasir') {
                return redirect()->route('kasir.member')->with('success', 'Selamat datang, Kasir');
            } else {
                return redirect('/');
            }
        }catch (\Exception $e) {
            // Menangani error dan mengembalikan pesan error
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
}

public function getDataMember()
{
    $member = Member::all();
    return $member;
}

public function generatePdf()
{
    $member = $this->getDataMember();
    $pdf = Pdf::loadView('admin.laporan.pdf.member', compact('member'));
    return $pdf->download('member.pdf');
}

public function exportExcel(Request $request)
{
    $member = $this->getDataMember($request);

    $response = new StreamedResponse(function() use ($member) {
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('laporan_member.xlsx');

        // Style untuk header
        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(10)
            ->setBackgroundColor('8fd3fe')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setShouldWrapText(true)
            ->build();

        // Header
        $headerRow = WriterEntityFactory::createRowFromArray([
            'No', 'Kode Member', 'Nama Member', 'No Telepon', 'Email',
        ], $headerStyle);
        $writer->addRow($headerRow);

        // Style untuk data
        $dataStyle = (new StyleBuilder())
            ->setCellAlignment(CellAlignment::CENTER)
            ->setFontSize(10)
            ->build();

        // Data
        foreach ($member as $index => $item) {
            $dataRow = WriterEntityFactory::createRowFromArray([
                $index + 1,
                $item->kode_pelanggan,
                $item->nama_pelanggan,
                $item->no_telp,
                $item->email,
            ], $dataStyle);
            
            $writer->addRow($dataRow);
        }

        $writer->close();
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="laporan_member.xlsx"');
    
    return $response;
}

public function import(Request $request)
{
    // Validasi file yang diunggah
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Menentukan tipe file yang diterima
    ]);

    try {
        // Mengimpor file yang diunggah
        $file = $request->file('file');
        Excel::import(new MemberImport, $file);

        // Mengembalikan response jika berhasil
        return redirect()->route('kategori')->with('success', 'Data Pemasok berhasil diimpor!');
    } catch (\Exception $e) {
        // Menangani error jika terjadi kesalahan dalam proses import
        Log::error('Error importing Kategori: ' . $e->getMessage());
        return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
    }
}


    /**
     * Menghapus member dari database.
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Mencari member berdasarkan ID dan menghapusnya
        $member = Member::findOrFail($id);
        $member->delete();

        // Mendapatkan data user yang sedang login
        $user = Auth::user();
        LogHelper::save('delete_member', 'Menghapus member: ' . $member->nama_pelanggan . ' dengan kode pelanggan: ' . $member->kode_pelanggan . ' oleh User: ' . $user->name);

        // Mengarahkan berdasarkan role user setelah menghapus member
        if ($user->role == 'admin') {
            return redirect()->route('admin.member')->with('success', 'Selamat datang, Admin');
        } elseif ($user->role == 'kasir') {
            return redirect()->route('kasir.member')->with('success', 'Selamat datang, Kasir');
        } else {
            return redirect('/');
        }
    }
}
