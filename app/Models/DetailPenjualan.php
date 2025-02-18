<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    /** @use HasFactory<\Database\Factories\DetailPenjualanFactory> */
    use HasFactory;

    protected $table = 'detail_penjualan';
    protected $fillable = [
        'id_penjualan',
        'id_barang',
        'harga_jual',
        'jumlah',
        'sub_total',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang

        ', 'id');
        }
        public function penjualan()
        {
            return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id');
        }
        
}
