<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembelianPdfController extends Controller
{
    /**
     * Mengambil data pembelian dengan filter berdasarkan no_faktur dan rentang tanggal.
     * 
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDataPembelian(Request $request)
    {
        $noFaktur = $request->input('no_faktur');  // Mengambil input no_faktur dari request
        $startDate = $request->input('start_date'); // Mengambil input start_date dari request
        $endDate = $request->input('end_date');     // Mengambil input end_date dari request
    
        // Mengambil data pembelian dengan filter berdasarkan no_faktur dan rentang tanggal
        return Pembelian::with('user', 'detail_pembelian.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('kode_masuk', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_masuk', [$startDate, $endDate]);
            })
            ->get();  // Mengembalikan hasil query
    }

    /**
     * Menghasilkan laporan pembelian dalam format PDF.
     * 
     * @param Request $request
     * @return \Barryvdh\DomPDF\Facade\Pdf
     */
    public function generatePdf(Request $request)
    {
        // Mengambil data pembelian yang sudah difilter
        $pembelian = $this->getDataPembelian($request);
        
        // Membuat PDF dengan data pembelian dan menampilkan view 'admin.laporan.pdf.pembelian'
        $pdf = Pdf::loadView('admin.laporan.pdf.pembelian', compact('pembelian'));
    
        // Mengunduh file PDF dengan nama 'laporan_pembelian.pdf'
        return $pdf->download('laporan_pembelian.pdf');
    }

    /**
     * Mengekspor data pembelian ke dalam file Excel.
     * 
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportExcel(Request $request)
    {
        // Mengambil data pembelian yang sudah difilter
        $pembelian = $this->getDataPembelian($request);
    
        // Membuat response yang akan menghasilkan file Excel
        $response = new StreamedResponse(function () use ($pembelian) {
            // Membuat penulis untuk file Excel
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_pembelian.xlsx');
    
            // Style untuk header Excel
            $headerStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setFontBold() // Menebalkan font
                ->setFontSize(10) // Ukuran font 10
                ->setBackgroundColor('8fd3fe') // Warna latar belakang header
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER) // Rata tengah
                ->setShouldWrapText(true) // Membungkus teks jika diperlukan
                ->build();
    
            // Menambahkan baris header ke file Excel
            $header = WriterEntityFactory::createRowFromArray([
                'No', 'Kode Masuk', 'Tanggal Masuk', 'Member', 'Pemasok', 'Input'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Style untuk data Excel
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER) // Rata tengah
                ->setFontSize(10) // Ukuran font 10
                ->build();
    
            // Menambahkan data pembelian ke Excel
            foreach ($pembelian as $index => $item) {
                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->kode_masuk, // Kode Masuk
                    $item->tanggal_masuk, // Tanggal Masuk
                    $item->pemasok->nama_pemasok, // Nama Pemasok
                    $item->member->nama_pelanggan ?? 'Tidak ada member', // Nama Pelanggan atau 'Tidak ada member'
                    $item->user->name // Nama User yang menginput data
                ], $dataStyle);
    
                // Menambahkan baris data ke file Excel
                $writer->addRow($row);
            }
    
            // Menutup penulis Excel
            $writer->close();
        });
    
        // Mengembalikan response dengan file Excel untuk diunduh
        return $response;
    }
}
