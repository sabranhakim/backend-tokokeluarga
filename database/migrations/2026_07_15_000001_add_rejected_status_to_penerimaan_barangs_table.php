<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE penerimaan_barangs MODIFY COLUMN status_verifikasi ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE penerimaan_barangs MODIFY COLUMN status_verifikasi ENUM('pending', 'verified') NOT NULL DEFAULT 'pending'");
    }
};
