<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_penerimaans', function (Blueprint $table) {
            $table->string('batch_number', 100)->nullable()->after('jumlah');
            $table->date('tgl_kadaluarsa')->nullable()->after('batch_number');
        });
    }

    public function down(): void
    {
        Schema::table('detail_penerimaans', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'tgl_kadaluarsa']);
        });
    }
};
