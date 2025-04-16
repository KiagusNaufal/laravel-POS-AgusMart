<?php

namespace App\Imports;

use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KategoriImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if the nama_kategori exists in the row
        if (!isset($row['nama_kategori'])) {
            return null; // Skip this row if the column doesn't exist
        }

        return new Kategori([
            'nama_kategori' => $row['nama_kategori'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}