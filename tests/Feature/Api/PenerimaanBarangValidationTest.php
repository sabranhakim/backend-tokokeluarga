<?php

namespace Tests\Feature\Api;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PenerimaanBarangValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_add_duplicate_barang_in_one_transaction()
    {
        // Setup
        $user = User::factory()->create();
        Permission::create(['name' => 'create penerimaan']);
        $user->givePermissionTo('create penerimaan');
        Sanctum::actingAs($user);

        $kategori = Kategori::factory()->create();
        $supplier = Supplier::factory()->create();
        $barang = Barang::factory()->create(['kategori_id' => $kategori->id]);

        $payload = [
            'no_terima' => 'RCV-001',
            'supplier_id' => $supplier->id,
            'tgl_terima' => now()->format('Y-m-d'),
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 10,
                ],
                [
                    'barang_id' => $barang->id, // DUPLICATE
                    'jumlah' => 5,
                ],
            ],
        ];

        // Act
        $response = $this->postJson('/api/v1/penerimaan-barang', $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.barang_id', 'items.1.barang_id']);
        $response->assertJsonFragment([
            'Barang yang sama tidak boleh ditambahkan lebih dari satu kali dalam satu transaksi.'
        ]);
    }
}
