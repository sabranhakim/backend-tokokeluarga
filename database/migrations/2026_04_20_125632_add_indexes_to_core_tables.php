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
        Schema::table('penerimaan_barangs', function (Blueprint $table) {
            $table->index('tgl_terima');
            $table->index('status_verifikasi');
            // Index untuk soft deletes jika kolomnya ada
            if (Schema::hasColumn('penerimaan_barangs', 'deleted_at')) {
                $table->index('deleted_at');
            }
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->index('nama_barang');
            $table->index('stok'); // Sering digunakan untuk filter stok menipis
            if (Schema::hasColumn('barangs', 'deleted_at')) {
                $table->index('deleted_at');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('nama_supplier');
            if (Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->index('deleted_at');
            }
        });

        Schema::table('kategoris', function (Blueprint $table) {
            $table->index('nama_kategori');
            if (Schema::hasColumn('kategoris', 'deleted_at')) {
                $table->index('deleted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_barangs', function (Blueprint $table) {
            $table->dropIndex(['tgl_terima']);
            $table->dropIndex(['status_verifikasi']);
            if (Schema::hasColumn('penerimaan_barangs', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
            }
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->dropIndex(['nama_barang']);
            $table->dropIndex(['stok']);
            if (Schema::hasColumn('barangs', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['nama_supplier']);
            if (Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
            }
        });

        Schema::table('kategoris', function (Blueprint $table) {
            $table->dropIndex(['nama_kategori']);
            if (Schema::hasColumn('kategoris', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
            }
        });
    }
};
