<?php

use App\Http\Controllers\Web\BarangController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\KategoriController;
use App\Http\Controllers\Web\PenerimaanBarangController;
use App\Http\Controllers\Web\SupplierController;
use Illuminate\Support\Facades\Route;

// Login Route
Route::get('/login', function () {
    return view('login');
})->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', function () {
        return view('users.index');
    })->name('users.index');

    Route::get('/roles', function () {
        return view('roles.index');
    })->name('roles.index');

    // Trash Routes
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::middleware(['can:view trash'])->group(function () {
            Route::get('/barang', [BarangController::class, 'trash'])->name('barang.index');
            Route::get('/supplier', [SupplierController::class, 'trash'])->name('supplier.index');
            Route::get('/kategori', [KategoriController::class, 'trash'])->name('kategori.index');
            Route::get('/penerimaan', [PenerimaanBarangController::class, 'trash'])->name('penerimaan.index');
            Route::get('/users', function () {
                return view('users.trash');
            })->name('user.index');
        });

        Route::middleware(['can:manage trash'])->group(function () {
            Route::post('/barang/{id}/restore', [BarangController::class, 'restore'])->name('barang.restore');
            Route::delete('/barang/{id}/force-delete', [BarangController::class, 'forceDelete'])->name('barang.force-delete');

            Route::post('/supplier/{id}/restore', [SupplierController::class, 'restore'])->name('supplier.restore');
            Route::delete('/supplier/{id}/force-delete', [SupplierController::class, 'forceDelete'])->name('supplier.force-delete');

            Route::post('/kategori/{id}/restore', [KategoriController::class, 'restore'])->name('kategori.restore');
            Route::delete('/kategori/{id}/force-delete', [KategoriController::class, 'forceDelete'])->name('kategori.force-delete');

            Route::post('/penerimaan/{id}/restore', [PenerimaanBarangController::class, 'restore'])->name('penerimaan.restore');
            Route::delete('/penerimaan/{id}/force-delete', [PenerimaanBarangController::class, 'forceDelete'])->name('penerimaan.force-delete');
        });
    });

    // Resource Routes with Permissions
    Route::middleware(['can:view barang'])->resource('barang', BarangController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::middleware(['can:manage barang'])->resource('barang', BarangController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::middleware(['can:view supplier'])->resource('supplier', SupplierController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::middleware(['can:manage supplier'])->resource('supplier', SupplierController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::middleware(['can:view kategori'])->resource('kategori', KategoriController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::middleware(['can:manage kategori'])->resource('kategori', KategoriController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    // Penerimaan Barang Routes with Specific Permissions
    Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
        Route::get('/', [PenerimaanBarangController::class, 'index'])->name('index')->middleware('can:view penerimaan');
        Route::get('/create', [PenerimaanBarangController::class, 'create'])->name('create')->middleware('can:create penerimaan');
        Route::get('/{penerimaanBarang}', [PenerimaanBarangController::class, 'show'])->name('show')->middleware('can:view penerimaan');
        Route::patch('/{penerimaanBarang}/verify', [PenerimaanBarangController::class, 'verify'])->name('verify')->middleware('can:verify penerimaan');
        Route::delete('/{penerimaanBarang}', [PenerimaanBarangController::class, 'destroy'])->name('destroy')->middleware('can:delete penerimaan');
    });

    Route::get('activity', function () {
        return view('activity.index');
    })->name('activity.index');
});
