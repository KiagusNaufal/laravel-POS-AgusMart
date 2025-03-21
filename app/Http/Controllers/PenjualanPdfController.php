<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;


class PenjualanPdfController extends Controller
{
    public function getDataPenjualan(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        return Penjualan::with('user', 'detail_penjualan.barang')
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('no_faktur', 'like', '%' . $noFaktur . '%');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_faktur', [$startDate, $endDate]);
            })
            ->get();
    }
    public function generatePdf(Request $request)
    {
        $penjualan = $this->getDataPenjualan($request);
        $pdf = Pdf::loadView('admin.laporan.pdf.penjualan', compact('penjualan'));
    
        return $pdf->download('laporan_penjualan.pdf');
    }

    public function exportExcel(Request $request)
    {
        $penjualan = $this->getDataPenjualan($request);
    
        $response = new StreamedResponse(function () use ($penjualan) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_penjualan.xlsx');
    
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
                'No', 'No Faktur', 'Tanggal Faktur', 'Total', 'Nama Pelanggan', 'Input'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Tambahkan baris kosong
           
    
            // Style Data
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data Excel
            foreach ($penjualan as $index => $item) {
                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->no_faktur,
                    $item->tanggal_faktur,
                    number_format($item->total, 0, ',', '.'),
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
