<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\DetailPenjualan;
use App\Models\Member;
use App\Models\Pemasok;
use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class TransaksiController extends Controller
{
    // Menampilkan halaman penjualan berdasarkan peran pengguna (admin/ kasir)
    public function index()
    {
        $user = FacadesAuth::user(); // Mendapatkan data pengguna yang sedang login
        if ($user->role == 'admin') {
            return view('admin.penjualan.index'); // Tampilkan halaman admin penjualan
        } elseif ($user->role == 'kasir') {
            return view('kasir.penjualan.index'); // Tampilkan halaman kasir penjualan
        } else {
            return redirect('/'); // Redirect ke halaman utama jika peran tidak dikenali
        }
    }

    // Fungsi pencarian barang berdasarkan nama atau kode barang
    public function search(Request $request)
    {
        $query = trim($request->input('query')); // Mendapatkan parameter pencarian
    
        if ($query === '') {
            return response()->json([]); // Kembalikan hasil kosong jika query kosong
        }
    
        // Mencari barang yang sesuai dengan query dan stok > 0
        $results = Barang::where(function ($q) use ($query) {
                $q->where('nama_barang', 'like', "%$query%")
                  ->orWhere('kode_barang', 'like', "%$query%");
            })
            ->where('stok', '>', 0)
            ->get();
    
        return response()->json($results); // Mengembalikan hasil pencarian dalam format JSON
    }

    // Fungsi untuk menyimpan transaksi penjualan
    public function store(Request $request)
    {
        $user = FacadesAuth::user(); // Mendapatkan data pengguna yang sedang login

        // Cek apakah pengguna memiliki peran yang sesuai untuk melakukan transaksi
        if (!in_array($user->role, ['admin', 'kasir'])) {
            return redirect('/')->withErrors(['error' => 'Anda tidak memiliki izin untuk melakukan transaksi']);
        }

        // Validasi input dari form
        $request->validate([
            'id_barang' => 'required|array', // ID barang harus berupa array
            'id_barang.*' => 'required', // Setiap elemen dalam array ID barang harus ada
            'jumlah' => 'required|array', // Jumlah barang harus berupa array
            'jumlah.*' => 'required|integer|min:1', // Setiap elemen jumlah harus integer dan lebih dari 0
            'harga_jual' => 'required|array', // Harga jual harus berupa array
            'harga_jual.*' => 'required|numeric|min:0', // Setiap harga jual harus berupa angka dan lebih dari atau sama dengan 0
        ]);

        $userId = $user->id; // Menyimpan ID pengguna yang sedang login
        $total = 0; // Variabel untuk menghitung total transaksi

        // Menghitung total transaksi berdasarkan barang yang dibeli
        foreach ($request->id_barang as $index => $id_barang) {
            $sub_total = $request->harga_jual[$index] * $request->jumlah[$index];
            $total += $sub_total; // Menambahkan subtotal ke total transaksi
        }

        // Menyimpan data penjualan ke dalam database
        Penjualan::create([
            'no_faktur' => 'F' . date('YmdHis'), // Membuat nomor faktur yang unik 
            'tanggal_faktur' => now(), // Menyimpan tanggal faktur
            'total' => $total, // Menyimpan total transaksi
            'id_member' => $request->id_member, // ID member
            'user_id' => $userId, // Menyimpan ID pengguna yang melakukan transaksi
        ]);

        // Mengambil penjualan terakhir yang baru saja disimpan
        $penjualan = Penjualan::latest('id')->with('detail_penjualan.barang')->first();

        try {
            // Menyimpan detail penjualan dan mengurangi stok barang
            foreach ($request->id_barang as $index => $id_barang) {
                $sub_total = $request->harga_jual[$index] * $request->jumlah[$index];

                // Menyimpan detail penjualan
                DetailPenjualan::create([
                    'id_penjualan' => $penjualan->id,
                    'id_barang' => $id_barang,
                    'harga_jual' => $request->harga_jual[$index],
                    'jumlah' => $request->jumlah[$index],
                    'sub_total' => $sub_total,
                ]);

                // Mengurangi stok barang yang terjual
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->stok -= $request->jumlah[$index]; // Kurangi stok sesuai jumlah barang yang terjual
                    $barang->save(); // Simpan perubahan stok
                }
            }
            $penjualan1 = Penjualan::latest('id')->with('detail_penjualan.barang')->first();

            // Menghitung kembalian jika ada pembayaran tunai
            $cash = $request->cash ?? 0; // Ambil jumlah uang yang dibayar
            $kembalian = $cash - $penjualan->total; // Hitung kembalian

            LogHelper::save('Transaksi Penjualan', 'User ID ' . $userId . ' melakukan transaksi penjualan dengan total: Rp.' . number_format($total) . ' dan kembalian: Rp.' . number_format($kembalian)); // Log transaksi penjualan
            try {
                // Mencetak struk penjualan jika printer tersedia
                if (class_exists('Mike42\Escpos\Printer')) {
                    $connector = new WindowsPrintConnector("POS-58"); // Koneksi ke printer POS
                    
                    $printer = new Printer($connector);

                    // Menyiapkan format struk
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("STRUK PENJUALAN\n");
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("==============================\n");
                    $printer->text("No Faktur : " . $penjualan1->no_faktur . "\n");
                    $printer->text("Tanggal   : " . $penjualan1->tanggal_faktur . "\n\n");

                    // Menambahkan detail barang yang terjual ke struk
                    foreach ($penjualan1->detail_penjualan as $item) {
                        $barang = Barang::find($item->id_barang);
                        $printer->text($barang->nama_barang . "\n");
                        $printer->text($item->jumlah . " x Rp." . number_format($item->harga_jual) . " = Rp." . number_format($item->sub_total) . "\n");
                    }

                    // Menambahkan total dan kembalian ke struk
                    $printer->text("------------------------\n");
                    $printer->text("Total     : Rp." . number_format($penjualan1->total) . "\n");
                    $printer->text("Cash      : Rp." . number_format($cash) . "\n");
                    $printer->text("Kembalian : Rp." . number_format($kembalian) . "\n");
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("==============================\n");
                    $printer->text("Terima Kasih!\n");
                    $printer->pulse();
                    $printer->cut(); // Memotong struk
                    $printer->close(); // Menutup koneksi printer
                } else {
                    Log::warning("Printer tidak tersedia. Struk tidak dicetak."); // Log jika printer tidak tersedia
                }
            } catch (\Exception $e) {
                Log::error("Gagal mencetak struk: " . $e->getMessage()); // Log jika ada kesalahan saat mencetak struk
            }

            return redirect()->route($user->role == 'admin' ? 'admin.penjualan' : 'kasir.penjualan')
                ->with('success', 'Transaksi berhasil disimpan' . (class_exists('Mike42\Escpos\Printer') ? ' dan struk berhasil dicetak.' : '.')); // Mengarahkan kembali ke halaman penjualan dengan pesan sukses
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan transaksi: ' . $e->getMessage()); // Log kesalahan jika transaksi gagal disimpan
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage()]); // Mengarahkan kembali dengan pesan error
        }
    }

    // Menampilkan halaman pembelian dengan data yang terhubung
    public function pembelian()
    {
        $pembelian = Pembelian::with('user', 'detail_pembelian.barang')->get(); // Mengambil semua data pembelian

        return view('admin.pembelian.index', compact('pembelian')); // Menampilkan halaman pembelian
    }

    // Menampilkan form untuk membuat pembelian baru
    public function createPembelian()
    {
        return view('admin.pembelian.create'); // Menampilkan form pembelian baru
    }

    // Fungsi pencarian pembelian berdasarkan nama barang
    public function searchPembelian(Request $request)
    {
        $query = trim($request->input('q')); // Mendapatkan parameter pencarian

        Log::info("Query Barang: " . ($query ?: 'NULL')); // Debugging log

        if ($query === '') {
            return response()->json([]); // Kembalikan hasil kosong jika query kosong
        }
        $items = Barang::where('nama_barang', 'LIKE', "%{$query}%") // Mencari barang berdasarkan nama
            ->orWhere('kode_barang', 'LIKE', "%{$query}%") // Atau kode barang
            ->get();

        return response()->json($items); // Mengembalikan hasil pencarian dalam format JSON
    }

    // Fungsi pencarian vendor berdasarkan nama pemasok
    public function searchVendor(Request $request)
    {
        $query = trim($request->input('q')); // Mendapatkan parameter pencarian
        $results = Pemasok::where('nama_pemasok', 'like', "%{$query}%") // Mencari pemasok berdasarkan nama
            ->get();
        return response()->json($results); // Mengembalikan hasil pencarian dalam format JSON
    }

    // Fungsi untuk menyimpan transaksi pembelian
    public function storePembelian(Request $request)
    {
        $request->validate([ // Validasi input untuk transaksi pembelian
            'id_pemasok' => 'required|exists:pemasok,id', // ID pemasok harus ada di database
            'id_barang' => 'required|array', // ID barang harus berupa array
            'id_barang.*' => 'required', // Setiap elemen dalam array ID barang harus ada
            'jumlah' => 'required|array', // Jumlah barang harus berupa array
            'jumlah.*' => 'required|integer|min:1', // Setiap elemen jumlah harus integer dan lebih dari 0
            'harga_beli' => 'required|array', // Harga beli harus berupa array
            'harga_beli.*' => 'required|numeric|min:0', // Setiap harga beli harus berupa angka dan lebih dari atau sama dengan 0
        ]);

        $user = FacadesAuth::user(); // Mendapatkan data pengguna yang sedang login
        $userId = $user->id; // Menyimpan ID pengguna yang melakukan transaksi
        Log::info("User ID {$userId} Melakukan Transaksi Pembelian"); // Log transaksi pembelian

        // Menyimpan transaksi pembelian
        Pembelian::create([
            'kode_masuk' => 'M' . strtoupper(Str::random(8)), // Membuat kode transaksi pembelian
            'tanggal_masuk' => now(), // Menyimpan tanggal transaksi
            'id_pemasok' => $request->id_pemasok, // Menyimpan ID pemasok
            'user_id' => $userId, // Menyimpan ID pengguna
        ]);

        $pembelian = Pembelian::latest('id')->first(); // Mengambil pembelian terakhir yang baru saja disimpan

        try {
            // Menyimpan detail pembelian dan memperbarui stok barang
            foreach ($request->id_barang as $index => $id_barang) {
                $sub_total = $request->harga_beli[$index] * $request->jumlah[$index]; // Menghitung subtotal pembelian
                DetailPembelian::create([ // Menyimpan detail pembelian
                    'id_pembelian' => $pembelian->id,
                    'id_barang' => $id_barang,
                    'harga_beli' => $request->harga_beli[$index],
                    'jumlah' => $request->jumlah[$index],
                    'sub_total' => $sub_total,
                ]);

                // Memperbarui stok barang setelah pembelian
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->stok += $request->jumlah[$index]; // Menambah stok barang
                    $barang->save(); // Menyimpan perubahan stok
                }

                // Memperbarui harga beli barang di tabel barang
                $barang = Barang::find($id_barang);
                if ($barang) {
                    $barang->harga_beli = $request->harga_beli[$index];
                    $barang->save();
                }
            }

            LogHelper::save('Transaksi Pembelian', 'User ID ' . $userId . FacadesAuth::user()->name); // Log transaksi pembelian

            return redirect()->route('admin.pembelian')->with('success', 'Pembelian berhasil disimpan.'); // Redirect ke halaman pembelian dengan pesan sukses
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan pembelian: ' . $e->getMessage()]); // Redirect kembali dengan pesan error
        }
    }
}
