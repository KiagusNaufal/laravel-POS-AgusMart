<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Pemasok;
use App\Http\Requests\StorePemasokRequest;
use App\Http\Requests\UpdatePemasokRequest;
use App\Imports\PemasokImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemasokController extends Controller
{
    /**
     * Menampilkan daftar pemasok.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil semua data pemasok dan mengirimkannya ke view
        $pemasok = Pemasok::all();
        return view('admin.pemasok.index', compact('pemasok'));
    }

    /**
     * Menampilkan form untuk membuat pemasok baru.
     * 
     * @return void
     */
    public function create()
    {
        // Tidak ada logika untuk form create pada controller ini
    }

    /**
     * Menyimpan data pemasok yang baru dibuat ke dalam database.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input yang diterima dari user
        $request->validate([
            'nama_pemasok' => 'required|string',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            
            // Membuat pemasok baru berdasarkan data yang diterima
            $pemasok = Pemasok::create([
                'nama_pemasok' => $request->nama_pemasok,
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp,
                'email' => $request->email,
            ]);
            LogHelper::save('store_pemasok' ,'Pemasok baru ditambahkan: ' . $pemasok->nama_pemasok . ' oleh ' . Auth::user()->name);
            // Mengalihkan kembali ke halaman daftar pemasok dengan pesan sukses
            return redirect()->route('admin.pemasok', compact('pemasok'))->with('success', 'Pemasok berhasil ditambahkan');
        }catch (\Exception $e) {
            // Jika terjadi kesalahan, mengalihkan kembali dengan pesan error
            return redirect()->back()->with('error', 'Gagal menambahkan pemasok: ' . $e->getMessage());
        }

    }

    /**
     * Menampilkan detail pemasok tertentu.
     * 
     * @param Pemasok $pemasok
     * @return void
     */
    public function show(Pemasok $pemasok)
    {
        // Tidak ada logika untuk menampilkan detail pemasok pada controller ini
    }

    /**
     * Menampilkan form untuk mengedit pemasok tertentu.
     * 
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function edit(Request $request, $id)
    {
        // Validasi input yang diterima dari user untuk update
        $request->validate([
            'nama_pemasok' => 'required|string',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'email' => 'required|email',
        ]);

        try{

            // Mencari pemasok berdasarkan ID yang diberikan
            $pemasok = Pemasok::findOrFail($id);
    
            // Memperbarui data pemasok berdasarkan input yang diterima
            $pemasok->update([
                'nama_pemasok' => $request->nama_pemasok,
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp,
                'email' => $request->email,
            ]);
            LogHelper::save('update_pemasok','Pemasok diubah: ' . $pemasok->nama_pemasok . ' oleh ' . Auth::user()->name);
            // Mengalihkan kembali ke halaman daftar pemasok dengan pesan sukses
            return redirect()->route('admin.pemasok')->with('success', 'Pemasok berhasil diubah');
        }catch (\Exception $e) {
            // Jika terjadi kesalahan, mengalihkan kembali dengan pesan error
            return redirect()->back()->with('error', 'Gagal mengubah pemasok: ' . $e->getMessage());
        }
    }

    /**
     * Mengupdate data pemasok yang sudah ada.
     * 
     * @param UpdatePemasokRequest $request
     * @param Pemasok $pemasok
     * @return void
     */
    public function update(UpdatePemasokRequest $request, Pemasok $pemasok)
    {
        // Tidak ada logika update spesifik yang diperlukan di sini
    }

    public function getDataPemasok()
    {
        // Mengambil semua data pemasok
        return Pemasok::all();
    }

    public function generatePdf() 
    {
        $pemasok = $this->getDataPemasok();
        $pdf = Pdf::loadView('admin.laporan.pdf.pemasok', compact('pemasok'));
        return $pdf->download('laporan_pemasok.pdf');
    }

    public function exportExcel(Request $request)
    {
        $pemasok = $this->getDataPemasok($request);
    
        $response = new StreamedResponse(function() use ($pemasok) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_pemasok.xlsx');
    
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
                'No', 'Nama Pemasok', 'No Telp', 'Alamat', 'Email'
            ], $headerStyle);
            $writer->addRow($headerRow);
    
            // Style untuk data
            $dataStyle = (new StyleBuilder())
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data
            foreach ($pemasok as $index => $item) {
                $dataRow = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->nama_pemasok,
                    $item->no_telp,
                    $item->alamat,
                    $item->email,
                ], $dataStyle);
                
                $writer->addRow($dataRow);
            }
    
            $writer->close();
        });
    
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="laporan_pemasok.xlsx"');
        
        return $response;
    }

    public function import(Request $request)
    {
        dd(request()->all());
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
    
        try {
            $file = $request->file('file');
            
            // Tambahkan debug sementara
            Log::info('File name: ' . $file->getClientOriginalName());
    
            Excel::import(new PemasokImport, $file);
    
            return redirect()->route('admin.pemasok')->with('success', 'Data Pemasok berhasil diimpor!');
        } catch (\Exception $e) {
            Log::error('Error importing Pemasok: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
    

    /**
     * Menghapus pemasok dari database.
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Mencari pemasok berdasarkan ID dan menghapusnya
            $pemasok = Pemasok::findOrFail($id);
            $pemasok->delete();
            LogHelper::save('delete_pemasok' ,'Pemasok dihapus: ' . $pemasok->nama_pemasok . ' oleh ' . Auth::user()->name);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan, mengalihkan kembali dengan pesan error
            return redirect()->back()->with('error', 'Gagal menghapus pemasok: ' . $e->getMessage());
        }
    }
}
