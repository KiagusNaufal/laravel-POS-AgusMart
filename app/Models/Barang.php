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
        'gambar_barang',
        'harga_jual',
        'stok',
        'ditarik',
    ];
    

}
