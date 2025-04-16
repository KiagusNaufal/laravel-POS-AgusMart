<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransaksiControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_admin_can_store_penjualan_successfully()
    {
        // Buat user dengan role kasir/admin
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        // Buat barang
        $barang = Barang::factory()->create([
            'stok' => 10,
            'harga_beli' => 10000
        ]);

        // Login sebagai user tersebut
        $this->actingAs($user);

        // Kirim data transaksi
        $response = $this->post(route('admin.penjualan.store'), [
            'id_barang' => [$barang->id],
            'jumlah' => [2],
            'harga_jual' => [15000],
            'cash' => 50000
        ]);

        $response->assertRedirect(); // redirect ke route yang sesuai role
        $response->assertSessionHas('success');

        // Cek apakah data penjualan tersimpan
        $this->assertDatabaseHas('penjualan', [
            'total' => 30000,
            'user_id' => $user->id
        ]);

        // Cek detail penjualan tersimpan
        $this->assertDatabaseHas('detail_penjualan', [
            'id_barang' => $barang->id,
            'jumlah' => 2,
            'harga_jual' => 15000,
            'sub_total' => 30000,
        ]);

        // Cek apakah stok barang berkurang
        $this->assertEquals(8, $barang->fresh()->stok);
    }
}
