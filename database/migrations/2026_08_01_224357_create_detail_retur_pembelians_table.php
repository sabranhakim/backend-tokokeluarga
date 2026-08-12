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
            $table->unsignedInteger('id_detail_retur_pembelian', false, 5)->autoIncrement()->primary();
            $table->unsignedInteger('retur_pembelian_id', false, 5);
            $table->unsignedInteger('barang_id', false, 5);
            $table->unsignedInteger('barang_stok_id', false, 5)->nullable();
            $table->integer('jumlah', false, false, 5);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('retur_pembelian_id')->references('id_retur_pembelian')->on('retur_pembelians')->onDelete('cascade');
            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->foreign('barang_stok_id')->references('id_barang_stok')->on('barang_stoks')->onDelete('set null');

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
