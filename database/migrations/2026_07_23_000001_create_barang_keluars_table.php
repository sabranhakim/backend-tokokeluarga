<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_keluar')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->date('tgl_keluar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};
