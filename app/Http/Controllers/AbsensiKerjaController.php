<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\AbsensiKerja;
use App\Http\Requests\StoreAbsensiKerjaRequest;
use App\Http\Requests\UpdateAbsensiKerjaRequest;
use App\Imports\AbsensiImport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsensiKerjaController extends Controller
{
    /**
     * Menampilkan Data
     */
    public function index()
    {
        $absensi = AbsensiKerja::with('user')->orderBy('tanggal_masuk', 'desc')->get();
        $users = User::all();
        return view('admin.absensi.index', compact('absensi', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Memasukkan data absensi baru ke dalam database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal_masuk' => 'required|date',
            'waktu_masuk' => 'required|date_format:H:i',
            'status' => 'required|in:Masuk,Cuti,Sakit',
            'waktu_akhir_kerja' => 'nullable|date_format:H:i',
        ]);
    
        try {
            $absensi = AbsensiKerja::create([
                'user_id' => $request->user_id,
                'tanggal_masuk' => $request->tanggal_masuk,
                'waktu_masuk' => $request->waktu_masuk,
                'status' => $request->status,
                'waktu_akhir_kerja' => $request->status === 'Masuk' ? null : '00:00:00',
            ]);
    
            LogHelper::save('Absensi Kerja', 'Create', $absensi->id, Auth::user()->id);
    
            return redirect()->route('absensi.index')
                ->with('success', 'Absensi Kerja berhasil ditambahkan.');
    
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan absensi: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(AbsensiKerja $absensiKerja)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AbsensiKerja $absensiKerja)
    {
        //
    }

    /**
     * Update data tertentu dalam database.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal_masuk' => 'required|date',
            'waktu_masuk' => 'required|date_format:H:i',
            'status' => 'required|in:Masuk,Cuti,Sakit',
            'waktu_akhir_kerja' => 'nullable|date_format:H:i',
        ]);

        try{
            $absensi = AbsensiKerja::findOrFail($id);
            $absensi->update([
                'user_id' => $request->user_id,
                'tanggal_masuk' => $request->tanggal_masuk,
                'waktu_masuk' => $request->waktu_masuk,
                'status' => $request->status,
                'waktu_akhir_kerja' => $request->waktu_akhir_kerja,
            ]);

            LogHelper::save('Absensi Kerja', 'Update', $absensi->id, Auth::user()->id);

            return redirect()->route('absensi.index')->with('success', 'Absensi Kerja berhasil diperbarui.');

        } catch (\Exception $e) {
            // Handle error and redirect back with the error message
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update status absensi kerja.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Masuk,Cuti,Sakit',
        ]);
    
        try {
            $absensi = AbsensiKerja::with('user')->findOrFail($id);
            $previousStatus = $absensi->status;
            $absensi->status = $request->status;
    
            // Atur waktu_akhir_kerja sesuai kebutuhan
            if ($request->status !== 'Masuk') {
                $absensi->waktu_akhir_kerja = '00:00:00'; // Set ke 00:00:00 untuk non-Masuk
            } elseif ($previousStatus !== 'Masuk' && $request->status === 'Masuk') {
                $absensi->waktu_akhir_kerja = null; // Reset ke null jika berubah ke Masuk
            }
    
            $absensi->save();
    
            LogHelper::save('Absensi Kerja', 'Update Status', $absensi->id, Auth::user()->id);
    
            return response()->json([
                'message' => 'Status berhasil diperbarui',
                'nama' => $absensi->user->name,
                'waktu_akhir_kerja' => $absensi->waktu_akhir_kerja,
                'status' => $absensi->status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menandai absensi sebagai selesai.
     */
public function selesai($id)
{
    $absensi = AbsensiKerja::findOrFail($id);
    $absensi->waktu_akhir_kerja = now(); // Atau waktu custom
    $absensi->save();

    return response()->json([
        'message' => 'Absensi selesai',
        'waktu_akhir_kerja' => $absensi->waktu_akhir_kerja
    ]);
} 

/**
 * Mengambil data absensi dari database.
 */
public function getDataAbsensi()
{
    // Mengambil data absensi barang dari database
    $absensi = AbsensiKerja::with('user')->orderBy('tanggal_masuk', 'desc')->get();
    return $absensi;
}

/**
 * Menghasilkan PDF dari data absensi.
 */
public function generatePdf() 
{
    // Mengambil data a$absensi barang
    $absensi = $this->getDataAbsensi();
    // Menghasilkan PDF dan mengunduhnya
    $pdf = Pdf::loadView('admin.laporan.pdf.absensi', compact('absensi'));
    return $pdf->download('laporan_absensi.pdf');
}

/**
 * Menghasilkan format Excel dari data absensi.
 */
public function exportExcel(Request $request)
{
    $absensi = $this->getDataAbsensi($request);

    $response = new StreamedResponse(function() use ($absensi) {
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('laporan_absensi.xlsx');

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
            'No', 'Nama Karyawan', 'Tanggal Masuk', 'Waktu Masuk', 'Status', 'Waktu Akhir Kerja'
        ], $headerStyle);
        $writer->addRow($headerRow);

        // Style untuk data
        $dataStyle = (new StyleBuilder())
            ->setCellAlignment(CellAlignment::CENTER)
            ->setFontSize(10)
            ->build();

        // Data
        foreach ($absensi as $index => $item) {
            $dataRow = WriterEntityFactory::createRowFromArray([
                $index + 1,
                $item->user->name ?? '-',
                $item->tanggal_masuk->format('Y-m-d'),
                $item->waktu_masuk->format('H:i:s'),
                $item->status,
                $item->waktu_akhir_kerja ? $item->waktu_akhir_kerja->format('H:i:s') : '-'
            ], $dataStyle);
            
            $writer->addRow($dataRow);
        }

        $writer->close();
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="laporan_absensi.xlsx"');
    
    return $response;
}

/**
 * Mengimpor data absensi dari file Excel.
 */
public function import(Request $request)
{
    // Validasi file yang diunggah
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Menentukan tipe file yang diterima
    ]);

    try {
        // Mengimpor file yang diunggah
        $file = $request->file('file');
        FacadesExcel::import(new AbsensiImport, $file);

        // Mengembalikan response jika berhasil
        return redirect()->route('absensi.index')->with('success', 'Data Absensi Kerja berhasil diimpor!');
    } catch (\Exception $e) {
        // Menangani error jika terjadi kesalahan dalam proses import
        return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
    }
}


public function format()
{
    $response = new StreamedResponse(function() {
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('format_import_absensi.xlsx');

        // Style untuk header
        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor('8fd3fe')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setShouldWrapText(true)
            ->build();

        // Style untuk data contoh
        $dataStyle = (new StyleBuilder())
            ->setFontSize(11)
            ->build();

        // Header
        $headerRow = WriterEntityFactory::createRowFromArray([
            'name', 
            'tanggal_masuk',
            'waktu_masuk',
            'status',
            'waktu_akhir_kerja'
        ], $headerStyle);
        $writer->addRow($headerRow);

        // Contoh data
        $sampleData1 = WriterEntityFactory::createRowFromArray([
            'Lemuel Zemlak',
            '2023-06-01',
            '08:00:00',
            'Masuk',
            '17:00:00'
        ], $dataStyle);
        
        
        $writer->addRow($sampleData1);

        // Instruksi
        $instructionStyle = (new StyleBuilder())
            ->setFontColor('FF0000')
            ->setFontItalic()
            ->build();
            
        $instructions = [
            WriterEntityFactory::createRowFromArray(['']),
            WriterEntityFactory::createRowFromArray(['INSTRUKSI:'], $instructionStyle),
            WriterEntityFactory::createRowFromArray(['1. Jangan mengubah urutan kolom']),
            WriterEntityFactory::createRowFromArray(['2. Format tanggal: YYYY-MM-DD']),
            WriterEntityFactory::createRowFromArray(['3. Format waktu: HH:MM:SS (24 jam)']),
            WriterEntityFactory::createRowFromArray(['4. Status hanya boleh: Masuk, Cuti, atau Sakit']),
            WriterEntityFactory::createRowFromArray(['5. Kolom Waktu Pulang boleh dikosongkan']),
        ];
        
        foreach ($instructions as $instruction) {
            $writer->addRow($instruction);
        }

        $writer->close();
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="format_import_absensi.xlsx"');
    
    return $response;
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $absensi = AbsensiKerja::findOrFail($id);
            $absensi->delete();

            LogHelper::save('Absensi Kerja', 'Delete', $absensi->id, Auth::user()->id);

            return redirect()->route('admin.absensi')->with('success', 'Absensi Kerja berhasil dihapus.');
        } catch (\Exception $e) {
            // Handle error and redirect back with the error message
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
