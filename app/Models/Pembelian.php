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
        'id_pemasok',
        'user_id'
    ];
}
