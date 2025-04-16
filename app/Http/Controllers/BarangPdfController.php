<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
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
    /**
     * Retrieves the filtered data for the report.
     *
     * @param Request $request
     * @return array
     */
    private function getDataBarang($request)
    {
        // Retrieve filtering inputs: start and end dates, and search term
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $searchTerm = $request->input('search_term');
    
        // Query the 'DetailPenjualan' model, joining with 'Penjualan' and 'Barang' models
        $query = DetailPenjualan::select(
                'id_barang',
                DB::raw('SUM(jumlah) as total_terjual'), // Total items sold
                DB::raw('SUM(detail_penjualan.harga_jual * jumlah) as total_penjualan') // Total sales amount
            )
            ->join('penjualan', 'detail_penjualan.id_penjualan', '=', 'penjualan.id')
            ->join('barang', 'detail_penjualan.id_barang', '=', 'barang.id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                // Apply date filter if both start and end date are provided
                return $query->whereBetween('penjualan.tanggal_faktur', [$startDate, $endDate]);
            })
            ->when($searchTerm, function ($query) use ($searchTerm) {
                // Apply search term filter for barang name or code
                return $query->where(function ($query) use ($searchTerm) {
                    $query->where('barang.nama_barang', 'like', '%' . $searchTerm . '%')
                          ->orWhere('barang.kode_barang', 'like', '%' . $searchTerm . '%');
                });
            })
            ->groupBy('id_barang') // Group the data by barang ID
            ->with('barang') // Include the related barang data
            ->get()
            ->toArray();
    
        // Format the results by mapping through each item
        return array_map(function ($item) {
            return [
                'nama_barang' => $item['barang']['nama_barang'], // Item name
                'total_terjual' => $item['total_terjual'], // Total sold
                'total_penjualan' => $item['total_penjualan'], // Total sales
                'kode_barang' => $item['barang']['kode_barang'], // Item code
                'keuntungan' => $item['total_penjualan'] - ($item['total_terjual'] * $item['barang']['harga_beli']), // Profit calculation
            ];
        }, $query);
    }
    
    /**
     * Generates a PDF report for the barang data.
     *
     * @param Request $request
     * @return \Barryvdh\DomPDF\Facade\Pdf
     */
    public function generatePDF(Request $request)
    {
        // Get the filtered data
        $barang = $this->getDataBarang($request);
        // Get the start and end date from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // Load the PDF view and pass the data
        $pdf = Pdf::loadView('admin.laporan.pdf.barang', compact('barang', 'startDate', 'endDate'));
        

        // Save the log using LogHelper
        LogHelper::save('generate_pdf', 'Generated PDF for Barang Report', 'User: ' . ($request->user()->name ?? 'Guest'));

        // Download the generated PDF
        return $pdf->download('laporan_barang.pdf');
    }

    /**
     * Exports the barang data to an Excel file.
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportExcel(Request $request)
    {
        // Get the filtered data
        $barang = $this->getDataBarang($request);
    
        // Create a streamed response to export the Excel file
        $response = new StreamedResponse(function () use ($barang) {
            // Create an Excel writer
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_barang.xlsx');
    
            // Define the style for the header
            $headerStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setBackgroundColor('8fd3fe') // Background color for header
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER) // Center alignment
                ->setShouldWrapText(true) // Allow text wrapping
                ->build();
    
            // Add the header row to the Excel file
            $header = WriterEntityFactory::createRowFromArray([
                'No', 'Kode Barang', 'Nama Barang', 'Total Terjual', 'Total Penjualan', 'Keuntungan'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Define the style for data rows
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Initialize total values
            $totalPenjualan = 0;
            $totalTerjual = 0;
            $totalKeuntungan = 0;

            // Loop through each item and add the data row
            foreach ($barang as $index => $item) {
                // Accumulate totals
                $totalPenjualan += $item['total_penjualan'];
                $totalTerjual += $item['total_terjual'];
                $totalKeuntungan += $item['keuntungan'];

                // Create the data row for the item
                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item['kode_barang'],
                    $item['nama_barang'],
                    $item['total_terjual'],
                    number_format($item['total_penjualan'], 0, ',', '.'), // Format the number
                    number_format($item['keuntungan'], 0, ',', '.')
                ], $dataStyle);

                // Add the row to the Excel file
                $writer->addRow($row);
            }

            // Add a total row at the end
            $totalRow = WriterEntityFactory::createRowFromArray([
                '',
                '',
                'Total',
                $totalTerjual,
                number_format($totalPenjualan, 0, ',', '.'), // Format the total sales
                number_format($totalKeuntungan, 0, ',', '.')
            ], $dataStyle);

            // Add the total row to the Excel file
            $writer->addRow($totalRow);
            // Close the writer
            $writer->close();
        });
    
        // Return the response to download the Excel file
        return $response;
    }
}
