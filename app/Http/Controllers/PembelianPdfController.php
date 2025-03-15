<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembelianPdfController extends Controller
{
    public function getDataPembelian(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        return Pembelian::with('user', 'detail_pembelian.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('kode_masuk', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_masuk', [$startDate, $endDate]);
            })
            ->get();
    }

    public function generatePdf(Request $request)
    {
        $pembelian = $this->getDataPembelian($request);
        $pdf = Pdf::loadView('admin.laporan.pdf.pembelian', compact('pembelian'));
    
        return $pdf->download('laporan_pembelian.pdf');
    }
    public function exportExcel(Request $request)
    {
        $pembelian = $this->getDataPembelian($request);
    
        $response = new StreamedResponse(function () use ($pembelian) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_pembelian.xlsx');
    
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
                'No', 'Kode Masuk', 'Tanggal Masuk', 'Member', 'Pemasok', 'Input'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Tambahkan baris kosong
           
    
            // Style Data
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data Excel
            foreach ($pembelian as $index => $item) {
                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->kode_masuk,
                    $item->tangga_masuk,
                    $item->pemasok->nama_pemasok,
                    $item->member->nama_pelanggan ?? 'Tidak ada member',
                    $item->user->name
                ], $dataStyle);
    
                $writer->addRow($row);
            }
    
            $writer->close();
        });
    
        return $response;
    }


}
