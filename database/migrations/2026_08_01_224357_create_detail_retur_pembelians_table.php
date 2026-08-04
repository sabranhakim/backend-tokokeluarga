<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_retur_pembelians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('retur_pembelian_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('barang_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('barang_stok_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('jumlah');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['retur_pembelian_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_retur_pembelians');
    }
};
