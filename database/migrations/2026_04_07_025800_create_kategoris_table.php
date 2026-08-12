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
        Schema::create('kategoris', function (Blueprint $table) {
            $table->unsignedInteger('id_kategori')->autoIncrement()->primary();
            $table->boolean('is_active')->default(true);
            $table->string('nama_kategori', 30);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_kategori');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};
