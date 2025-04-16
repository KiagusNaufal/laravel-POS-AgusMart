<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KategoriControllerTest extends TestCase
{


    public function testStoreSuccesfully(): void
    {
        /** @var \App\Models\User $user */
        $user = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($user);

        $data = [
            'nama_kategori' => 'Test Kategori',
        ];

        $response = $this->post(route('kategori.store'), $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kategori', $data);
    }

    public function testUpdateSuccessfully(): void
    {
        /** @var \App\Models\User $user */
        $user = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($user);

        $kategori = \App\Models\Kategori::factory()->create();

        $data = [
            'nama_kategori' => 'Updated Kategori',
        ];

        $response = $this->put(route('kategori.update', $kategori->id), $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kategori', $data);
    }

    public function testDeleteSuccessfully(): void
    {
        /** @var \App\Models\User $user */
        $user = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($user);

        $kategori = \App\Models\Kategori::factory()->create();

        $response = $this->delete(route('kategori.delete', $kategori->id));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }
}
