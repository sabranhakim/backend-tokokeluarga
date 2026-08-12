<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_barang_keluars', function (Blueprint $table) {
            $table->unsignedInteger('id_detail_barang_keluar', false, 5)->autoIncrement()->primary();
            $table->unsignedInteger('barang_keluar_id', false, 5);
            $table->unsignedInteger('barang_id', false, 5);
            $table->unsignedInteger('barang_stok_id', false, 5)->nullable();
            $table->integer('jumlah', false, false, 5);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('barang_keluar_id')->references('id_barang_keluar')->on('barang_keluars')->onDelete('cascade');
            $table->foreign('barang_id')->references('id_barang')->on('barangs');
            $table->foreign('barang_stok_id')->references('id_barang_stok')->on('barang_stoks')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_barang_keluars');
    }
};
