<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBarang extends Model
{
    /** @use HasFactory<\Database\Factories\PengajuanBarangFactory> */
    use HasFactory;

    protected $table = 'pengajuan_barang';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_member',
        'nama_barang',
        'tanggal_pengajuan',
        'jumlah',
        'terpenuhi',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'id_member');
    }
    public function getNamaBarang(): string
    {
        return $this->nama_barang;
    }
    public function getJumlah(): int
    {
        return $this->jumlah;
    }
    
}
