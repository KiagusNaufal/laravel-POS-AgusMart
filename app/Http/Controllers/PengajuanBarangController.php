<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\PengajuanBarang;
use App\Http\Requests\StorePengajuanBarangRequest;
use App\Http\Requests\UpdatePengajuanBarangRequest;
use App\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengajuanBarangController extends Controller
{
    /**
     * Menampilkan daftar pengajuan barang.
     *
     * @param Request $request Objek request yang berisi parameter pencarian.
     * @return \Illuminate\View\View Halaman daftar pengajuan barang.
     */
    public function index(Request $request)
    {
        try {
            // Mengambil semua data pengajuan barang
            $pengajuan = PengajuanBarang::all();

        } catch (\Exception $e) {
            // Menangani error jika gagal mengambil data
            Log::error('Failed to get PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Mengambil Data Pengajuan: ' . $e->getMessage()]);
        }
        // Mengembalikan tampilan dengan data pengajuan barang
        return view('admin.pengajuan.index', compact('pengajuan'));
    }

    /**
     * Menyaring dan mencari data member berdasarkan nama pelanggan.
     *
     * @param Request $request Objek request yang berisi parameter pencarian.
     * @return \Illuminate\Http\JsonResponse Data member yang ditemukan.
     */
    public function search(Request $request)
    {
        $search = $request->input('search');
    
        // Mencari member berdasarkan nama pelanggan
        $members = Member::where('nama_pelanggan', 'like', "%{$search}%")
            ->select('id', 'nama_pelanggan')
            ->limit(10)
            ->get();
    
        // Mengembalikan hasil pencarian dalam format JSON
        return response()->json($members);
    }

    /**
     * Menampilkan form untuk membuat pengajuan barang baru.
     *
     * @return \Illuminate\View\View Form untuk membuat pengajuan barang baru.
     */
    public function create()
    {
        //
    }

    /**
     * Menyimpan pengajuan barang baru ke dalam penyimpanan.
     *
     * @param Request $request Objek request yang berisi data pengajuan barang.
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman pengajuan barang dengan pesan sukses.
     */
    public function store(Request $request)
    {
        // Validasi data yang dikirimkan melalui request
        $request->validate([
            'id_member' => 'required|integer',
            'nama_barang' => 'required|string|max:100',
            'tanggal_pengajuan' => 'required|date',
            'jumlah' => 'required|integer',
        ]);

        try {
            // Membuat data pengajuan barang baru
            $pengajuan = PengajuanBarang::create([
                'id_member' => $request->id_member,
                'nama_barang' => $request->nama_barang,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jumlah' => $request->jumlah,
                'terpenuhi' => 0,
            ]);
            // Log informasi pengajuan yang berhasil
            LogHelper::save('create_pengajuan_barang', 'Menambahkan pengajuan barang baru: ' . $request->nama_barang . ' oleh Member: ' . $request->id_member);
        } catch (\Exception $e) {
            // Menangani error jika gagal membuat pengajuan barang
            Log::error('Failed to create PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Membuat Data Pengajuan: ' . $e->getMessage()]);
        }

        // Redirect ke halaman pengajuan barang dengan pesan sukses
        return redirect()->route('admin.pengajuan', compact('pengajuan'))->with('success', 'Berhasil Menambahkan Data Pengajuan');
    }

    /**
     * Menampilkan data pengajuan barang yang sudah ada.
     *
     * @param PengajuanBarang $pengajuanBarang Pengajuan barang yang ingin ditampilkan.
     * @return void
     */
    public function show(PengajuanBarang $pengajuanBarang)
    {
        //
    }

    /**
     * Menampilkan form untuk mengedit pengajuan barang.
     *
     * @param Request $request Objek request yang berisi data pengajuan.
     * @param int $id ID dari pengajuan yang akan diedit.
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman pengajuan barang dengan pesan sukses.
     */
    public function edit(Request $request, $id)
    {
        // Validasi input request untuk status terpenuhi
        $request->validate([
            'terpenuhi' => 'sometimes|integer',
        ]);

        try {
            // Mencari pengajuan barang berdasarkan ID
            $pengajuan = PengajuanBarang::find($id);
            // Update status terpenuhi
            $pengajuan->update([
                'terpenuhi' => $request->terpenuhi,
            ]);
            // Log informasi update pengajuan barang
            LogHelper::save('update_pengajuan_barang', 'Mengubah status pengajuan barang: ' . $pengajuan->nama_barang . ' oleh User: ' . Auth::user()->name);
        } catch (\Exception $e) {
            // Menangani error jika gagal mengubah status pengajuan
            Log::error('Failed to update PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Mengubah Status Pengajuan: ' . $e->getMessage()]);
        }
        // Redirect ke halaman pengajuan barang dengan pesan sukses
        return redirect()->route('admin.pengajuan')->with('success', 'Berhasil Mengubah Status Pengajuan');
    }

    /**
     * Memperbarui data pengajuan barang yang sudah ada.
     *
     * @param Request $request Objek request yang berisi data pengajuan yang ingin diperbarui.
     * @param int $id ID dari pengajuan barang yang akan diperbarui.
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman pengajuan barang dengan pesan sukses.
     */
    public function update(Request $request, $id)
    {
        // Validasi data yang dikirimkan melalui request
        $request->validate([
            'nama_barang' => 'sometimes|string|max:100',
            'tanggal_pengajuan' => 'sometimes|date',
            'jumlah' => 'sometimes|integer',
        ]);

        try {
            // Mencari pengajuan barang berdasarkan ID
            $pengajuan = PengajuanBarang::find($id);
            // Update data pengajuan barang
            $pengajuan->update([
                'nama_barang' => $request->nama_barang,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'jumlah' => $request->jumlah,
            ]);
            // Log informasi update pengajuan barang
            LogHelper::save('update_pengajuan_barang', 'Mengubah pengajuan barang: ' . $pengajuan->nama_barang . ' oleh User: ' . Auth::user()->name);
        } catch (\Exception $e) {
            // Menangani error jika gagal memperbarui pengajuan barang
            Log::error('Failed to update PengajuanBarang', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Gagal Mengubah Data Pengajuan: ' . $e->getMessage()]);
        }

        // Redirect ke halaman pengajuan barang dengan pesan sukses
        return redirect()->route('admin.pengajuan')->with('success', 'Berhasil Mengubah Data Pengajuan');
    }

    /**
     * Menghapus pengajuan barang berdasarkan ID.
     *
     * @param int $id ID dari pengajuan yang akan dihapus.
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman pengajuan barang dengan pesan sukses.
     */
    public function destroy($id)
    {
        try {
            // Mencari dan menghapus pengajuan barang berdasarkan ID
            $pengajuan = PengajuanBarang::find($id);
            $pengajuan->delete();
            // Log informasi penghapusan pengajuan barang
            LogHelper::save('delete_pengajuan_barang', 'Menghapus pengajuan barang: ' . $pengajuan->nama_barang . ' oleh User: ' . Auth::user()->name);
        } catch (\Exception $e) {
            // Menangani error jika gagal menghapus pengajuan
            return redirect()->back()->withErrors(['error' => 'Gagal Menghapus Data Pengajuan: ' . $e->getMessage()]);
        }
        // Redirect ke halaman pengajuan barang dengan pesan sukses
        return redirect()->route('admin.pengajuan')->with('success', 'Berhasil Menghapus Data Pengajuan');
    }

    /**
     * Mengambil data pengajuan barang untuk laporan.
     *
     * @return \Illuminate\Database\Eloquent\Collection Data pengajuan barang.
     */
    public function getDataPengajuan() 
    {
        // Mengambil semua data pengajuan barang
        return PengajuanBarang::all();
    }

    /**
     * Menghasilkan laporan PDF untuk pengajuan barang.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse File PDF untuk diunduh.
     */
    public function generatePdf() 
    {
        // Mengambil data pengajuan barang
        $pengajuan = $this->getDataPengajuan();
        // Menghasilkan PDF dan mengunduhnya
        $pdf = Pdf::loadView('admin.laporan.pdf.pengajuan', compact('pengajuan'));
        return $pdf->download('laporan_pengajuan.pdf');
    }

    /**
     * Mengekspor data pengajuan barang ke dalam format Excel.
     *
     * @param Request $request Objek request yang berisi parameter query.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse File Excel yang dipancarkan.
     */
    public function exportExcel(Request $request)
    {
        // Mengambil data pengajuan barang
        $pengajuan = $this->getDataPengajuan($request);
    
        $response = new StreamedResponse(function () use ($pengajuan) {
            // Membuat writer untuk file Excel
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser('laporan_pengajuan.xlsx');
    
            // Style untuk header
            $headerStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setBackgroundColor('8fd3fe') // Warna background header
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER) // Rata tengah
                ->setShouldWrapText(true)
                ->build();
    
            // Header Excel
            $header = WriterEntityFactory::createRowFromArray([
                'No', 'Nama Pelanggan', 'Nama Barang', 'Tanggal Pengajuan', 'Jumlah', 'Terpenuhi'
            ], $headerStyle);
            $writer->addRow($header);
    
            // Style untuk data
            $dataStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                ->setCellAlignment(\Box\Spout\Common\Entity\Style\CellAlignment::CENTER)
                ->setFontSize(10)
                ->build();
    
            // Menambahkan data pengajuan ke dalam Excel
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
