<?php

namespace Tests\Feature\Api;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PenerimaanBarangDuplicateNotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_submit_duplicate_no_terima()
    {
        // Setup
        $user = User::factory()->create();
        Permission::create(['name' => 'create penerimaan']);
        $user->givePermissionTo('create penerimaan');
        Sanctum::actingAs($user);

        $kategori = Kategori::factory()->create();
        $supplier = Supplier::factory()->create();
        $barang = Barang::factory()->create(['kategori_id' => $kategori->id]);

        // Pre-insert a penerimaan with a specific no_terima
        PenerimaanBarang::factory()->create([
            'no_terima' => 'RCV-999',
            'supplier_id' => $supplier->id,
        ]);

        $payload = [
            'no_terima' => 'RCV-999', // DUPLICATE no_terima
            'supplier_id' => $supplier->id,
            'tgl_terima' => now()->format('Y-m-d'),
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 10,
                ],
            ],
        ];

        // Act
        $response = $this->postJson('/api/v1/penerimaan-barang', $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['no_terima']);
        $response->assertJsonFragment([
            'Nomor nota/terima sudah pernah dimasukkan.'
        ]);
    }

    public function test_can_submit_unique_no_terima()
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
            'no_terima' => 'RCV-NEW-001',
            'supplier_id' => $supplier->id,
            'tgl_terima' => now()->format('Y-m-d'),
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 10,
                ],
            ],
        ];

        // Act
        $response = $this->postJson('/api/v1/penerimaan-barang', $payload);

        // Assert
        $response->assertStatus(201);
    }
}
