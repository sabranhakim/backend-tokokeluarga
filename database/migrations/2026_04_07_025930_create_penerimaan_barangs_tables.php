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
        Schema::create('penerimaan_barangs', function (Blueprint $table) {
            $table->unsignedInteger('id_penerimaan_barang', false, 5)->autoIncrement()->primary();
            $table->string('no_terima', 20)->unique();
            $table->unsignedInteger('supplier_id', false, 5);
            $table->foreignId('user_id');
            $table->date('tgl_terima');
            $table->string('foto_bon', 255)->nullable();
            $table->enum('status_verifikasi', ['pending', 'verified'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_id')->references('id_supplier')->on('suppliers');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index('tgl_terima');
            $table->index('status_verifikasi');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barangs');
    }
};
