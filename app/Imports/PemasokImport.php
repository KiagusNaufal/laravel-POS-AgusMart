<?php

namespace App\Imports;

use App\Models\Pemasok;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PemasokImport implements ToModel, WithHeadingRow, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

     public function startRow(): int
     {
         return 2; // Assuming the data starts from the second row
     }
    public function model(array $row)
    {
        
        Log::info('Import row:', $row);
        dd($row); // HARUS tampil pas proses import
    
        if (
            empty($row['nama_pemasok']) ||
            empty($row['alamat']) ||
            empty($row['no_telp']) ||
            empty($row['email'])
        ) {
            Log::warning('Skipped row due to missing fields:', $row);
            return null;
            /**
             * Specify the starting row for the import.
             *
             * @return int
             */

        }
    
        return new Pemasok([
            'nama_pemasok' => $row['nama_pemasok'],
            'alamat' => $row['alamat'],
            'no_telp' => $row['no_telp'],
            'email' => $row['email'],
        ]);
    }
    
}
