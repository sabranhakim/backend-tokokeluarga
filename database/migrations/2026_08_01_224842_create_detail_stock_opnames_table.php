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
        Schema::create('detail_stock_opnames', function (Blueprint $table) {
            $table->unsignedInteger('id_detail_stock_opname', false, 5)->autoIncrement()->primary();
            $table->unsignedInteger('stock_opname_id', false, 5);
            $table->unsignedInteger('barang_id', false, 5);
            $table->integer('stok_sistem', false, false, 5);
            $table->integer('stok_fisik', false, false, 5);
            $table->integer('selisih', false, false, 5);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stock_opname_id')->references('id_stock_opname')->on('stock_opnames')->onDelete('cascade');
            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');

            $table->index(['stock_opname_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_stock_opnames');
    }
};
