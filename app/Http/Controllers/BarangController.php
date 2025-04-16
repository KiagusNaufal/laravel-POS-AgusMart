<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Barang;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Kategori;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangController extends Controller
{
    /**
     * Menampilkan daftar produk dengan paginasi.
     */
    public function index()
    {
        // Mengambil data produk dengan paginasi 10 item per halaman
        $barang = Barang::paginate(10);

        // Mengambil semua kategori produk untuk ditampilkan
        $kategori = Kategori::all();

        // Mengembalikan view 'admin.barang.index' dengan data produk dan kategori
        return view('admin.barang.index', compact('barang', 'kategori'));
    }

    /**
     * Menampilkan form untuk membuat produk baru.
     */
    public function create()
    {
        // Form untuk membuat produk baru (belum diimplementasikan)
    }

    /**
     * Menyimpan produk baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Melakukan validasi terhadap data yang diterima dari form
        $request->validate([
            'kode_barang' => 'string',  // Kode barang harus berupa string
            'nama_barang' => 'string',  // Nama barang harus berupa string
            'harga_beli' => 'integer',  // Harga beli harus berupa integer
            'persentase_keuntungan' => 'numeric|between:0,99.99',  // Persentase keuntungan harus berupa angka antara 0 dan 99.99
            'stok' => 'integer',  // Stok harus berupa integer
            'id_kategori' => 'required|integer',  // ID kategori harus ada dan berupa integer
            'gambar_barang' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Gambar barang harus ada dan sesuai format
            'ditarik' => 'integer',  // Ditambahkan untuk menandai apakah barang ditarik atau tidak
        ]);

        // Menyimpan gambar produk dengan nama yang unik berdasarkan timestamp
        $imageName = time().'.'.$request->gambar_barang->extension();  
        $request->gambar_barang->move(public_path('images'), $imageName);

        // Menyimpan data produk baru ke database
        $barang = Barang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'harga_beli' => $request->harga_beli,
            'persentase_keuntungan' => $request->persentase_keuntungan,
            'stok' => $request->stok,
            'id_kategori' => $request->id_kategori,
            'ditarik' => $request->ditarik,
            'gambar_barang' => $imageName,  // Menyimpan nama gambar produk yang diupload
        ]);
        LogHelper::save('create_barang', 'Menambahkan barang baru: ' . $request->nama_barang . ' oleh User: ' . Auth::user()->name);
        // Mengarahkan kembali ke halaman daftar produk dengan pesan sukses
        return redirect()->route('admin.barang', compact('barang'))->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Menampilkan form untuk mengedit produk yang ada.
     */
    public function edit(Request $request, $id)
    {
        // Melakukan validasi terhadap data produk yang akan diperbarui
        $request->validate([
            'kode_barang' => 'required|string',  // Kode barang harus ada dan berupa string
            'nama_barang' => 'required|string',  // Nama barang harus ada dan berupa string
            'harga_beli' => 'required|integer',  // Harga beli harus ada dan berupa integer
            'persentase_keuntungan' => 'required|numeric|between:0,99.99',  // Persentase keuntungan harus ada dan antara 0 dan 99.99
            'stok' => 'required|integer',  // Stok harus ada dan berupa integer
            'id_kategori' => 'required|integer',  // ID kategori harus ada dan berupa integer
            'gambar_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Gambar produk bersifat opsional, jika ada harus sesuai format
        ]);

        // Mengambil data produk berdasarkan ID
        $barang = Barang::findOrFail($id);

        // Jika ada gambar baru yang diunggah, maka ganti gambar produk lama
        if ($request->hasFile('gambar_barang')) {
            // Menghapus gambar lama jika ada
            if ($barang->gambar_barang && file_exists(public_path('images/'.$barang->gambar_barang))) {
                unlink(public_path('images/'.$barang->gambar_barang));
            }

            // Menyimpan gambar baru dengan nama yang unik berdasarkan timestamp
            $imageName = time().'.'.$request->gambar_barang->extension();  
            $request->gambar_barang->move(public_path('images'), $imageName);

            // Menyimpan nama gambar baru
            $barang->gambar_barang = $imageName;
        }

        // Memperbarui data produk dengan data yang baru
        $barang->update([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'harga_beli' => $request->harga_beli,
            'persentase_keuntungan' => $request->persentase_keuntungan,
            'stok' => $request->stok,
            'id_kategori' => $request->id_kategori,
            'gambar_barang' => $barang->gambar_barang,  // Menyimpan nama gambar baru
        ]);
        LogHelper::save('update_barang', 'Update barang : ' . $request->nama_barang, 'User: ' . Auth::user()->name);
        

        // Mengarahkan kembali ke halaman daftar produk dengan pesan sukses
        return redirect()->route('admin.barang', compact('barang'))->with('success', 'Data berhasil diubah');
    }

    /**
     * Menampilkan detail produk tertentu.
     */
    public function getDataBarang()
    {
        // Mengambil data produk berdasarkan ID
        $barang = Barang::with('kategori')->get();
        return $barang;
    }
    /**
     * Export data barang ke dalam format PDF.
     */
    public function generatePdf()
    {
        $barang = $this->getDataBarang();
        $pdf = Pdf::loadView('admin.laporan.pdf.barang1', compact('barang'));
        return $pdf->download('laporan_barang.pdf');
    }
    /**
     * Export data barang ke dalam format Excel. 
     */
    public function exportExcel(Request $request)
    {
        $barang = $this->getDataBarang($request);
    
        $response = new StreamedResponse(function() use ($barang) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_barang.xlsx');
    
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
                'No', 'Nama barang', 'nama kategori', 'kode barang', 'harga beli', 'persentase keuntungan', 'stok', 'ditarik'
            ], $headerStyle);
            $writer->addRow($headerRow);
    
            // Style untuk data
            $dataStyle = (new StyleBuilder())
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Data
            foreach ($barang as $index => $item) {
                $dataRow = WriterEntityFactory::createRowFromArray([
                    $index + 1,
                    $item->kode_barang,
                    $item->nama_barang,
                    $item->kategori->nama_kategori,
                    $item->harga_beli,
                    $item->persentase_keuntungan,
                    $item->stok,
                    $item->ditarik ? 'Ya' : 'Tidak'
                ], $dataStyle);
                
                $writer->addRow($dataRow);
            }
    
            $writer->close();
        });
    
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="laporan_kategori.xlsx"');
        
        return $response;
    }





    /**
     * Menghapus produk dari database.
     */
    public function destroy($id)
    {
        // Mengambil produk berdasarkan ID
        $barang = Barang::findOrFail($id);

        // Menghapus produk dari database
        $barang->delete();
        LogHelper::save('delete_barang', 'Menghapus barang: ' . $barang->nama_barang, 'User: ' . Auth::user()->name);
        // Mengarahkan kembali ke halaman utama dengan pesan sukses
        return redirect()->route('home')->with('success', 'Data berhasil dihapus');
    }
}
