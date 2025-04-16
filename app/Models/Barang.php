<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    /** @use HasFactory<\Database\Factories\BarangFactory> */
    use HasFactory;

    protected $table = 'barang';
    protected $fillable = [
        'kode_barang',
        'id_kategori',
        'nama_barang',
        'harga_beli',
        'persentase_keuntungan',
        'gambar_barang',
        'stok',
        'ditarik',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }


    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'id_barang', 'id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'id_barang', 'id');
    }

    


    

}
