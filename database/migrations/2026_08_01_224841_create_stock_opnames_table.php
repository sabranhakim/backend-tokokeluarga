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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_opname');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tgl_opname');
            $table->string('keterangan')->nullable();
            $table->enum('status', ['draft', 'selesai'])->default('draft');
            $table->integer('total_selisih')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('no_opname');
            $table->index('tgl_opname');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
