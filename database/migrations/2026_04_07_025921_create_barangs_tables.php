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
        Schema::create('barangs', function (Blueprint $table) {
            $table->unsignedInteger('id_barang', false, 5)->autoIncrement()->primary();
            $table->string('kode_barang', 10)->unique();
            $table->string('nama_barang', 30);
            $table->unsignedInteger('kategori_id', false, 5)->nullable();
            $table->unsignedInteger('supplier_id', false, 5)->nullable();
            $table->string('satuan', 30);
            $table->integer('isi', false, false, 5)->default(1);
            $table->integer('harga_beli', false, false, 5)->default(0);
            $table->integer('harga_jual', false, false, 5)->default(0);
            $table->integer('stok', false, false, 5)->default(0);
            $table->integer('stok_minimal', false, false, 5)->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kategori_id')->references('id_kategori')->on('kategoris')->onDelete('set null');
            $table->foreign('supplier_id')->references('id_supplier')->on('suppliers')->onDelete('set null');

            $table->index('nama_barang');
            $table->index('stok');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
