<?php

use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PenerimaanBarangController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes for Toko Keluarga (Receipt System)
Route::group(['middleware' => 'auth:sanctum'], function () {
    // Barang API with permissions
    Route::get('barangs', [BarangController::class, 'index'])->middleware('can:view barang');
    Route::get('barangs/{id}', [BarangController::class, 'show'])->middleware('can:view barang');

    // Supplier API with permissions
    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('can:view supplier');
    Route::get('suppliers/{id}', [SupplierController::class, 'show'])->middleware('can:view supplier');

    // Kategori API with permissions
    Route::get('kategoris', [KategoriController::class, 'index'])->middleware('can:view kategori');
    Route::get('kategoris/{id}', [KategoriController::class, 'show'])->middleware('can:view kategori');

    // Penerimaan Barang API with specific permissions
    Route::get('penerimaan-barang', [PenerimaanBarangController::class, 'index'])->middleware('can:view penerimaan');
    Route::get('penerimaan-barang/{id}', [PenerimaanBarangController::class, 'show'])->middleware('can:view penerimaan');
    Route::post('penerimaan-barang', [PenerimaanBarangController::class, 'store'])->middleware('can:create penerimaan');
});
