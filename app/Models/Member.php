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
}
