<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Pemasok;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Kategori::truncate();
        Schema::enableForeignKeyConstraints();
        $file = FIle::get('database/data/kategori.json');
        $data = json_decode($file);
        foreach ($data as $obj) {
            Kategori::create([
                'nama_kategori' => $obj->nama_kategori,
            ]);
        }

    }
}
