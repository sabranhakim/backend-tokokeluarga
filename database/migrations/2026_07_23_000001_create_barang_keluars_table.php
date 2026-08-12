<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluars', function (Blueprint $table) {
            $table->unsignedInteger('id_barang_keluar')->autoIncrement()->primary();
            $table->string('no_keluar', 20)->unique();
            $table->foreignId('user_id');
            $table->date('tgl_keluar');
            $table->enum('jenis_keluar', ['penjualan', 'kerusakan', 'kadaluarsa', 'pemakaian_internal'])
                ->default('penjualan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};
