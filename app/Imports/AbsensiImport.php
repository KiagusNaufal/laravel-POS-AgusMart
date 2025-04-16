<?php

namespace App\Imports;

use App\Models\AbsensiKerja;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AbsensiImport implements ToModel, WithHeadingRow
{
    /**
     * Mengubah setiap baris Excel menjadi model AbsensiKerja.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cari user berdasarkan nama
        $user = User::where('name', $row['name'])->first(); // Pastikan kolom 'name' ada di Excel

        // Jika user ditemukan, buat model AbsensiKerja
        if ($user) {
            return new AbsensiKerja([
                'user_id' => $user->id, // Gunakan user_id yang ditemukan
                'tanggal_masuk' => $row['tanggal_masuk'],
                'waktu_masuk' => $row['waktu_masuk'],
                'status' => $row['status'],
                'waktu_akhir_kerja' => $row['waktu_akhir_kerja'] ? $row['waktu_akhir_kerja'] : null,
            ]);
        }

        // Jika user tidak ditemukan, bisa mengembalikan null atau menangani error sesuai kebutuhan
        return null;
    }
}
