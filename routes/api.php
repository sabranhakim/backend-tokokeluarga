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
    Route::apiResource('barangs', BarangController::class)->only(['index', 'show']);
    Route::apiResource('suppliers', SupplierController::class)->only(['index', 'show']);
    Route::apiResource('kategoris', KategoriController::class)->only(['index', 'show']);
    Route::apiResource('penerimaan-barang', PenerimaanBarangController::class)->only(['index', 'show', 'store']);
});
