<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'user_id', 'id');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'user_id', 'id');
    }
    public function absensiKerja()
    {
        return $this->hasMany(AbsensiKerja::class, 'user_id', 'id');
    }
}
