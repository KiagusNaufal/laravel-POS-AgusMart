<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiKerja extends Model
{
    /** @use HasFactory<\Database\Factories\AbsensiKerjaFactory> */
    use HasFactory;
    protected $table = 'absensi_kerja';
    protected $fillable = [
        'user_id',
        'tanggal_masuk',
        'waktu_masuk',
        'status',
        'waktu_akhir_kerja',
    ];
    protected $casts = [
        'tanggal_masuk' => 'date',
        'waktu_masuk' => 'datetime',
        'waktu_akhir_kerja' => 'datetime',
    ];
    protected $attributes = [
        'status' => 'Masuk',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
