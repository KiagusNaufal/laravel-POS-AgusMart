<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poin extends Model
{
    /** @use HasFactory<\Database\Factories\PoinFactory> */
    use HasFactory;
    protected $table = 'poin';
    protected $fillable = [
        'id_member',
        'id_penjualan',
        'poin_didapat',
        'total_poin',
    ];
}
