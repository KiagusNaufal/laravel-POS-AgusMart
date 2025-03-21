<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    /** @use HasFactory<\Database\Factories\PembelianFactory> */
    use HasFactory;
    protected $table = 'pembelian';
    protected $fillable = [
        'kode_masuk',
        'tanggal_masuk',
        'id_pemasok',
        'user_id'
    ];

    public function detail_pembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'id_pembelian', 'id');
    }

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class, 'id_pemasok', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
