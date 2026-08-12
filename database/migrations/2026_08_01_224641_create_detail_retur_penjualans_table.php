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
        Schema::create('detail_retur_penjualans', function (Blueprint $table) {
            $table->unsignedInteger('id_detail_retur_penjualan')->autoIncrement()->primary();
            $table->unsignedInteger('retur_penjualan_id');
            $table->unsignedInteger('barang_id');
            $table->integer('jumlah');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('retur_penjualan_id')->references('id_retur_penjualan')->on('retur_penjualans')->onDelete('cascade');
            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');

            $table->index(['retur_penjualan_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_retur_penjualans');
    }
};
