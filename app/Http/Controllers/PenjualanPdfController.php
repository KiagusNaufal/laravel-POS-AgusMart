<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjualanPdfController extends Controller
{
    /**
     * Mengambil data penjualan berdasarkan filter yang diberikan (no_faktur, start_date, end_date).
     *
     * @param Request $request Objek request yang berisi parameter query.
     * @return \Illuminate\Database\Eloquent\Collection Data penjualan yang telah difilter.
     */
    public function getDataPenjualan(Request $request)
    {
        $noFaktur = $request->input('no_faktur');  // Mengambil filter no_faktur dari request
        $startDate = $request->input('start_date'); // Mengambil filter start_date dari request
        $endDate = $request->input('end_date'); // Mengambil filter end_date dari request

        return Penjualan::with('user', 'detail_penjualan.barang')  // Memuat data terkait user dan barang
            ->when($noFaktur, function ($query) use ($noFaktur) {
                return $query->where('no_faktur', 'like', '%' . $noFaktur . '%');  // Terapkan filter jika no_faktur ada
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('tanggal_faktur', [$startDate, $endDate]);  // Terapkan filter tanggal jika kedua tanggal diberikan
            })
            ->get();  // Mengembalikan data yang telah difilter
    }

    /**
     * Menghasilkan laporan PDF berdasarkan data penjualan yang telah difilter.
     *
     * @param Request $request Objek request yang berisi parameter query.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse File PDF untuk diunduh.
     */
    public function generatePdf(Request $request)
    {
        $penjualan = $this->getDataPenjualan($request);  // Mengambil data penjualan yang telah difilter
        $pdf = Pdf::loadView('admin.laporan.pdf.penjualan', compact('penjualan'));  // Membuat PDF menggunakan data penjualan

        return $pdf->download('laporan_penjualan.pdf');  // Mengembalikan file PDF untuk diunduh
    }

    /**
     * Mengekspor data penjualan ke dalam file Excel.
     *
     * @param Request $request Objek request yang berisi parameter query.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse Respon file Excel yang dipancarkan (stream).
     */
    public function exportExcel(Request $request)
    {
        $penjualan = $this->getDataPenjualan($request);  // Mengambil data penjualan yang telah difilter

        $response = new StreamedResponse(function () use ($penjualan) {
            $writer = WriterEntityFactory::createXLSXWriter();  // Membuat writer XLSX baru
            $writer->openToBrowser('laporan_penjualan.xlsx');  // Membuka stream untuk diunduh oleh browser

            // Gaya untuk Header
            $headerStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setFontBold()  // Menggunakan font tebal untuk header
                ->setFontSize(10)  // Mengatur ukuran font untuk header
                ->setBackgroundColor('8fd3fe')  // Mengatur warna latar belakang untuk header
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)  // Menyelaraskan sel header ke tengah
                ->setShouldWrapText(true)  // Mengaktifkan pembungkusan teks di header
                ->build();

            // Baris header di Excel
            $header = WriterEntityFactory::createRowFromArray([
                'No',
                'No Faktur',
                'Tanggal Faktur',
                'Total',
                'Nama Pelanggan',
                'Input'
            ], $headerStyle);
            $writer->addRow($header);  // Menambahkan baris header ke file Excel

            // Gaya untuk Data
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)  // Menyelaraskan data ke tengah
                ->setFontSize(10)  // Mengatur ukuran font untuk data
                ->build();

            // Menambahkan data penjualan ke dalam Excel
            foreach ($penjualan as $index => $item) {
                $row = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->no_faktur,
                    $item->tanggal_faktur,
                    number_format($item->total, 0, ',', '.'),
                    $item->member->nama_pelanggan ?? 'Tidak ada member',
                    $item->user->name
                ], $dataStyle);

                $writer->addRow($row);  // Menambahkan baris data ke file Excel
            }

            $writer->close();  // Menutup writer setelah semua data ditambahkan
        });

        return $response;  // Mengembalikan respons berupa file Excel yang dipancarkan
    }
}
