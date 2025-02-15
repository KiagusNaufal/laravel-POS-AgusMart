<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\map;

class Penjualan extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanFactory> */
    use HasFactory;
    protected $table = 'penjualan';
    protected $fillable = [
        'no_faktur',
        'tanggal_faktur',
        'total',
        'id_member',
        'user_id'
    ];
}
