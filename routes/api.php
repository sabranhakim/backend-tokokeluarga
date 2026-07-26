<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BarangController;
use App\Http\Controllers\Api\V1\BarangKeluarController;
use App\Http\Controllers\Api\V1\KategoriController;
use App\Http\Controllers\Api\V1\PenerimaanBarangController;
use App\Http\Controllers\Api\V1\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Auth Routes
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Debug Route (Temporary)
    Route::post('/debug-request', function (Request $request) {
        return response()->json([
            'headers' => $request->headers->all(),
            'all_data' => $request->all(),
            'raw_content' => $request->getContent(),
            'content_type' => $request->header('Content-Type'),
        ]);
    })->middleware('throttle:api');

    // Server time endpoint (public — for time sync with mobile)
    Route::get('/server-time', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'server_time' => now()->toIso8601String(),
                'timezone' => config('app.timezone'),
            ],
        ]);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware(['auth:sanctum', 'throttle:api']);

    // API Routes for Toko Keluarga (Receipt System)
    Route::group(['middleware' => ['auth:sanctum', 'throttle:api']], function () {
        // Logout route
        Route::post('/logout', [AuthController::class, 'logout']);

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
        Route::post('penerimaan-barang/{id}/verify', [PenerimaanBarangController::class, 'verify'])->middleware('can:verify penerimaan');
        Route::post('penerimaan-barang/{id}/reject', [PenerimaanBarangController::class, 'reject'])->middleware('can:verify penerimaan');

        // Barang Keluar API with permissions
        Route::get('barang-keluar', [BarangKeluarController::class, 'index'])->middleware('can:view barang_keluar');
        Route::get('barang-keluar/{id}', [BarangKeluarController::class, 'show'])->middleware('can:view barang_keluar');
        Route::post('barang-keluar', [BarangKeluarController::class, 'store'])->middleware('can:create barang_keluar');
    });
});
