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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->unsignedInteger('id_supplier')->autoIncrement()->primary();
            $table->boolean('is_active')->default(true);
            $table->string('nama_supplier', 30);
            $table->text('alamat')->nullable();
            $table->string('no_telp', 15)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_supplier');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
