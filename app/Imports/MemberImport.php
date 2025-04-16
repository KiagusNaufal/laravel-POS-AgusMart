<?php

namespace App\Imports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MemberImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if the required columns exist in the row
        if (!isset($row['kode_pelanggan'], $row['nama_pelanggan'], $row['no_telp'], $row['email'])) {
            return null; // Skip this row if any required column doesn't exist
        }

        return new Member([
            'kode_pelanggan' => $row['kode_pelanggan'],
            'nama_pelanggan' => $row['nama_pelanggan'],
            'no_telp' => $row['no_telp'],
            'email' => $row['email'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}