<?php

namespace Database\Factories;

use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB as FacadesDB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barang>
 */
class BarangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $data = FacadesDB::table('kategori')
                ->inRandomOrder()
                ->select('id')
                ->first();

                $images = [
                    '1740468253.jpg',
                    '1741351786.jpg',
                ];
        
        return [
            'kode_barang' => $this->faker->unique()->randomNumber(9),
            'id_kategori' => $data->id,
            'nama_barang' => $this->faker->name(),
            'harga_beli' => $this->faker->randomNumber(6),
            'persentase_keuntungan' => $this->faker->randomNumber(2),
            'gambar_barang' => $images[array_rand($images)],
            'stok' => $this->faker->randomNumber(2),
            'ditarik' => $this->faker->randomElement([0, 1]),
        ];
    }
}
