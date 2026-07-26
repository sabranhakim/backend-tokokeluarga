<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_barang_keluars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('barang_keluar_id')->constrained('barang_keluars')->cascadeOnDelete();
            $table->foreignUuid('barang_id')->constrained('barangs');
            $table->foreignUuid('barang_stok_id')->nullable()->constrained('barang_stoks')->nullOnDelete();
            $table->integer('jumlah');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_barang_keluars');
    }
};
