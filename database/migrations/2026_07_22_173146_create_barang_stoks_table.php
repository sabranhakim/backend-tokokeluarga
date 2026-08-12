<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_stoks', function (Blueprint $table) {
            $table->unsignedInteger('id_barang_stok', false, 5)->autoIncrement()->primary();
            $table->unsignedInteger('barang_id', false, 5);
            $table->unsignedInteger('detail_penerimaan_id', false, 5)->nullable();
            $table->unsignedInteger('penerimaan_barang_id', false, 5)->nullable();
            $table->string('batch_number', 20)->nullable();
            $table->integer('stok', false, false, 5)->default(0);
            $table->date('tgl_kadaluarsa')->nullable();
            $table->date('tgl_masuk');
            $table->integer('harga_beli', false, false, 5)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->foreign('detail_penerimaan_id')->references('id_detail_penerimaan')->on('detail_penerimaans')->onDelete('set null');
            $table->foreign('penerimaan_barang_id')->references('id_penerimaan_barang')->on('penerimaan_barangs')->onDelete('set null');

            $table->index(['barang_id', 'tgl_kadaluarsa']);
            $table->index('batch_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_stoks');
    }
};
