<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Pembelian; // Sesuaikan dengan model pembelian kamu
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class BarangPdfController extends Controller
{
    private function getDataBarang($request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $searchTerm = $request->input('search_term');
    
        $query = DetailPenjualan::select(
                'id_barang',
                DB::raw('SUM(jumlah) as total_terjual'),
                DB::raw('SUM(detail_penjualan.harga_jual * jumlah) as total_penjualan')
            )
            ->join('penjualan', 'detail_penjualan.id_penjualan', '=', 'penjualan.id')
            ->join('barang', 'detail_penjualan.id_barang', '=', 'barang.id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('penjualan.tanggal_faktur', [$startDate, $endDate]);
            })
            ->when($searchTerm, function ($query) use ($searchTerm) {
                return $query->where(function ($query) use ($searchTerm) {
                    $query->where('barang.nama_barang', 'like', '%' . $searchTerm . '%')
                          ->orWhere('barang.kode_barang', 'like', '%' . $searchTerm . '%');
                });
            })
            ->groupBy('id_barang')
            ->with('barang')
            ->get()
            ->toArray();
    
        return array_map(function ($item) {
            return [
                'nama_barang' => $item['barang']['nama_barang'],
                'total_terjual' => $item['total_terjual'],
                'total_penjualan' => $item['total_penjualan'],
                'kode_barang' => $item['barang']['kode_barang'],
                'keuntungan' => $item['total_penjualan'] - ($item['total_terjual'] * $item['barang']['harga_beli']),
            ];
        }, $query);
    }
    
    public function generatePDF(Request $request)
    {
        $barang = $this->getDataBarang($request);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $pdf = Pdf::loadView('admin.laporan.pdf.barang', compact('barang', 'startDate', 'endDate'));
    
        return $pdf->download('laporan_barang.pdf');
    }
    public function exportExcel(Request $request)
    {
        $barang = $this->getDataBarang($request);
    
        $response = new StreamedResponse(function () use ($barang) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_barang.xlsx');
    
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
                'No', 'Kode Barang', 'Nama Barang', 'Total Terjual', 'Total Penjualan', 'Keuntungan'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Tambahkan baris kosong
           
    
            // Style Data
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data Excel
            $totalPenjualan = 0;
            $totalTerjual = 0;
            $totalKeuntungan = 0;

            foreach ($barang as $index => $item) {
                $totalPenjualan += $item['total_penjualan'];
                $totalTerjual += $item['total_terjual'];
                $totalKeuntungan += $item['keuntungan'];

                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item['kode_barang'],
                    $item['nama_barang'],
                    $item['total_terjual'],
                    number_format($item['total_penjualan'], 0, ',', '.'),
                    number_format($item['keuntungan'], 0, ',', '.')
                ], $dataStyle);

                $writer->addRow($row);
            }

            // Add total row
            $totalRow = WriterEntityFactory::createRowFromArray([
                '',
                '',
                'Total',
                $totalTerjual,
                number_format($totalPenjualan, 0, ',', '.'),
                number_format($totalKeuntungan, 0, ',', '.')
            ], $dataStyle);

            $writer->addRow($totalRow);
            $writer->close();
        });
    
        return $response;
    }

    
    
}
