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
            $table->uuid('id')->primary();
            $table->string('no_terima')->unique();
            $table->foreignUuid('supplier_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->date('tgl_terima');
            $table->string('foto_bon')->nullable();
            $table->enum('status_verifikasi', ['pending', 'verified'])->default('pending');
            $table->timestamps();
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
