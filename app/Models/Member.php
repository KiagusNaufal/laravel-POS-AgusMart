<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory;
    protected $table = 'member';
    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'no_telp',
        'alamat',
    ];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'id_member', 'id');
    }

    public function poin()
    {
        return $this->hasMany(Poin::class, 'id_member
        ', 'id');
    }

}
