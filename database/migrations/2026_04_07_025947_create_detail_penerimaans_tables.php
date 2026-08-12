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
        Schema::create('detail_penerimaans', function (Blueprint $table) {
            $table->unsignedInteger('id_detail_penerimaan', false, 5)->autoIncrement()->primary();
            $table->unsignedInteger('penerimaan_barang_id', false, 5);
            $table->unsignedInteger('barang_id', false, 5);
            $table->integer('jumlah', false, false, 5);
            $table->string('batch_number', 20)->nullable();
            $table->date('tgl_kadaluarsa')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('penerimaan_barang_id')->references('id_penerimaan_barang')->on('penerimaan_barangs')->onDelete('cascade');
            $table->foreign('barang_id')->references('id_barang')->on('barangs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penerimaans');
    }
};
