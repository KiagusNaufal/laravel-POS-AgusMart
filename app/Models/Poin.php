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

    public function member()
    {
        return $this->belongsTo(Member::class, 'id_member
        ', 'id');
        }
        public function penjualan()
        {
            return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id');
        }

        
}
