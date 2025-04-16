<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Kategori;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Imports\KategoriImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource (kategori).
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Fetch categories with pagination
            $kategori = Kategori::paginate(10);
        } catch (\Exception $e) {
            // Handle error and redirect back with the error message
            return redirect()->back()->with('error', $e->getMessage());
        }
        return view('admin.kategori.index', compact('kategori'));
    }

    /**
     * Show the form for creating a new resource (kategori).
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Fetch all categories (if needed for context in the create form)
        $kategori = Kategori::all();
        return view('kategori.create', compact('kategori'));
    }

    /**
     * Store a newly created resource (kategori) in storage.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'nama_kategori' => 'required|string',
        ]);

        try {
            // Create a new category
            $kategori = new Kategori();
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->save();
            LogHelper::save('create', 'Kategori baru ditambahkan: ' . $kategori->nama_kategori. ' Oleh User ' . Auth::user()->name);
            return redirect()->route('kategori')->with('success', 'Kategori berhasil ditambahkan');
        } catch (\Exception $e) {
            // Handle error and redirect back with the error message
            return redirect()->back()->with('error', $e->getMessage());
        }

        // Redirect back to the category listing with a success message
    }

    /**
     * Display the specified resource (kategori).
     * 
     * @param Kategori $kategori
     * @return void
     */
    public function show(Kategori $kategori)
    {
        // Typically would show the details of a specific category
    }

    /**
     * Show the form for editing the specified resource (kategori).
     * 
     * @param UpdateKategoriRequest $request
     * @param Kategori $kategori
     * @return void
     */
    public function edit(UpdateKategoriRequest $request, Kategori $kategori)
    {
        // Typically would show the edit form for the specified category
    }

    /**
     * Update the specified resource (kategori) in storage.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'nama_kategori' => 'required|string',
        ]);

        try {
            $kategori = Kategori::findOrFail($id);
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->save();
            LogHelper::save('update', 'Kategori diubah: ' . $kategori->nama_kategori . ' oleh User: ' . Auth::user()->name);
            return redirect()->route('kategori')->with('success', 'Kategori berhasil diubah');
        } catch (\Exception $e) {
            // Handle error and redirect back with the error message
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getDataKategori()
    {
        // Fetch categories with pagination
        $kategori = Kategori::all();
        return $kategori;
    }

    public function generatePDF(Request $request)
    {
        // Generate PDF for categories
        $kategori = $this->getDataKategori();
        $pdf = Pdf::loadView('admin.laporan.pdf.kategori', compact('kategori'));
        return $pdf->download('laporan_kategori.pdf');
    }
    public function exportExcel(Request $request)
    {
        $kategori = $this->getDataKategori($request);
    
        $response = new StreamedResponse(function() use ($kategori) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_kategori.xlsx');
    
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
                'No', 'Nama Kategori',
            ], $headerStyle);
            $writer->addRow($headerRow);
    
            // Style untuk data
            $dataStyle = (new StyleBuilder())
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data
            foreach ($kategori as $index => $item) {
                $dataRow = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->nama_kategori,
                ], $dataStyle);
                
                $writer->addRow($dataRow);
            }
    
            $writer->close();
        });
    
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="laporan_kategori.xlsx"');
        
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
        Excel::import(new KategoriImport, $file);

        // Mengembalikan response jika berhasil
        return redirect()->route('kategori')->with('success', 'Data Pemasok berhasil diimpor!');
    } catch (\Exception $e) {
        // Menangani error jika terjadi kesalahan dalam proses import
        Log::error('Error importing Kategori: ' . $e->getMessage());
        return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
    }
}
    /**
     * Remove the specified resource (kategori) from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Find the category by its ID and delete it
        try {
            $kategori = Kategori::findOrFail($id);
            $kategori->delete();
            LogHelper::save('delete', 'Kategori dihapus: ' . $kategori->nama_kategori . 'Oleh User' . Auth::user()->name);
            // Redirect back to the category listing with a success message
            return redirect()->route('kategori')->with('success', 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            // Handle error and redirect back with the error message
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
