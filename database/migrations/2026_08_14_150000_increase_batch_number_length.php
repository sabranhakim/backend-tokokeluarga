<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_penerimaans', function (Blueprint $table) {
            $table->string('batch_number', 100)->nullable()->change();
        });

        Schema::table('barang_stoks', function (Blueprint $table) {
            $table->string('batch_number', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('detail_penerimaans', function (Blueprint $table) {
            $table->string('batch_number', 20)->nullable()->change();
        });

        Schema::table('barang_stoks', function (Blueprint $table) {
            $table->string('batch_number', 20)->nullable()->change();
        });
    }
};