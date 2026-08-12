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
        Schema::create('retur_pembelians', function (Blueprint $table) {
            $table->unsignedInteger('id_retur_pembelian', false, 5)->autoIncrement()->primary();
            $table->string('no_retur', 20);
            $table->unsignedInteger('supplier_id', false, 5)->nullable();
            $table->foreignId('user_id')->nullable();
            $table->date('tgl_retur');
            $table->string('keterangan', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_id')->references('id_supplier')->on('suppliers')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('no_retur');
            $table->index('tgl_retur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelians');
    }
};
