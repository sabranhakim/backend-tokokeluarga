<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_stoks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->foreignUuid('detail_penerimaan_id')->nullable()->constrained('detail_penerimaans')->onDelete('set null');
            $table->foreignUuid('penerimaan_barang_id')->nullable()->constrained('penerimaan_barangs')->onDelete('set null');
            $table->string('batch_number', 100)->nullable();
            $table->integer('stok')->default(0);
            $table->date('tgl_kadaluarsa')->nullable();
            $table->date('tgl_masuk');
            $table->integer('harga_beli')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['barang_id', 'tgl_kadaluarsa']);
            $table->index('batch_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_stoks');
    }
};
