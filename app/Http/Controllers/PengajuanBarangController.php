<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBarang;
use App\Http\Requests\StorePengajuanBarangRequest;
use App\Http\Requests\UpdatePengajuanBarangRequest;
use App\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengajuanBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $pengajuan = PengajuanBarang::all();
            Log::info('PengajuanBarang fetched successfully', ['pengajuan' => $pengajuan]);
        } catch (\Exception $e) {
            Log::error('Failed to get PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Mengambil Data Pengajuan: ' . $e->getMessage()]);
        }
        return view('admin.pengajuan.index', compact('pengajuan'));
    }


    public function search(Request $request)
    {
        $search = $request->input('search');
    
        $members = Member::where('nama_pelanggan', 'like', "%{$search}%")
            ->select('id', 'nama_pelanggan')
            ->limit(10)
            ->get();
    
        return response()->json($members);
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
            'id_member' => 'required|integer',
            'nama_barang' => 'required|string|max:100',
            'tanggal_pengajuan' => 'required|date',
            'jumlah' => 'required|integer',
        ]);

        try {
            $pengajuan = PengajuanBarang::create([
                'id_member' => $request->id_member,
                'nama_barang' => $request->nama_barang,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jumlah' => $request->jumlah,
                'terpenuhi' => 0,
            ]);
            Log::info('PengajuanBarang created successfully', ['pengajuan' => $pengajuan]);
        } catch (\Exception $e) {
            Log::error('Failed to create PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Membuat Data Pengajuan: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.pengajuan', compact('pengajuan'))->with('success', 'Berhasil Menambahkan Data Pengajuan');
    }

    /**
     * Display the specified resource.
     */
    public function show(PengajuanBarang $pengajuanBarang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'terpenuhi' => 'sometimes|integer',
        ]);
        try {
            $pengajuan = PengajuanBarang::find($id);
            $pengajuan->update([
                'terpenuhi' => $request->terpenuhi,
            ]);
            Log::info('PengajuanBarang updated successfully', ['pengajuan' => $pengajuan]);
        } catch (\Exception $e) {
            Log::error('Failed to update PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Mengubah Status Pengajuan: ' . $e->getMessage()]);
        }
        return redirect()->route('admin.pengajuan')->with('success', 'Berhasil Mengubah Status Pengajuan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'sometimes|string|max:100',
            'tanggal_pengajuan' => 'sometimes|date',
            'jumlah' => 'sometimes|integer',
        ]);
        try {
            $pengajuan = PengajuanBarang::find($id);
            $pengajuan->update([
                'nama_barang' => $request->nama_barang,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jumlah' => $request->jumlah,
            ]);
            Log::info('PengajuanBarang updated successfully', ['pengajuan' => $pengajuan]);
        } catch (\Exception $e) {
            Log::error('Failed to update PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Mengubah Data Pengajuan: ' . $e->getMessage()]);
        }
        return redirect()->route('admin.pengajuan')->with('success', 'Berhasil Mengubah Data Pengajuan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $pengajuan = PengajuanBarang::find($id);
            $pengajuan->delete();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal Menghapus Data Pengajuan: ' . $e->getMessage()]);
        }
        return redirect()->route('admin.pengajuan')->with('success', 'Berhasil Menghapus Data Pengajuan');
    }

    public function getDataPengajuan() 
    {
        return PengajuanBarang::all();
    }

    public function generatePdf() 
    {
        $pengajuan = $this->getDataPengajuan();
        $pdf = Pdf::loadView('admin.laporan.pdf.pengajuan', compact('pengajuan'));
        return $pdf->download('laporan_pengajuan.pdf');
    }

    public function exportExcel(Request $request)
    {
        $pengajuan = $this->getDataPengajuan($request);
    
        $response = new StreamedResponse(function () use ($pengajuan) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_pengajuan.xlsx');
    
            // Style Header
            $headerStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setBackgroundColor('8fd3fe') // Warna background
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER) // Rata tengah
                ->setShouldWrapText(true)

                ->build();
    
            // Header Excel
            $header = WriterEntityFactory::createRowFromArray([
                'No', 'Nama Pelanggan', 'Nama Barang', 'Tanggal Pengajuan', 'Jumlah', 'Terpenuhi'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Tambahkan baris kosong
           
    
            // Style Data
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data Excel
            foreach ($pengajuan as $index => $item) {
                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->member->nama_pelanggan,
                    $item->nama_barang,
                    $item->tanggal_pengajuan,
                    $item->jumlah,
                    $item->terpenuhi ? 'Terpenuhi' : 'Tidak',
                ], $dataStyle);
    
                $writer->addRow($row);
            }
    
            $writer->close();
        });
    
        return $response;
    }
}

