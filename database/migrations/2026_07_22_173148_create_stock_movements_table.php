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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->unsignedInteger('id_stock_movement', false, 5)->autoIncrement()->primary();
            $table->unsignedInteger('barang_id', false, 5);
            $table->unsignedInteger('barang_stok_id', false, 5)->nullable();
            $table->foreignId('user_id')->nullable();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity', false, false, 5);
            $table->integer('before_quantity', false, false, 5);
            $table->integer('after_quantity', false, false, 5);
            $table->string('reason', 50)->nullable();
            $table->unsignedInteger('reference_id', false, 5)->nullable();
            $table->string('reference_type', 255)->nullable();
            $table->timestamps();

            $table->foreign('barang_id')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->foreign('barang_stok_id')->references('id_barang_stok')->on('barang_stoks')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['reference_id', 'reference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
