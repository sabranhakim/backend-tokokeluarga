<?php

use App\Http\Controllers\Web\BarangController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\KategoriController;
use App\Http\Controllers\Web\PenerimaanBarangController;
use App\Http\Controllers\Web\ReportController;
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
        Route::get('/barang', [BarangController::class, 'trash'])->name('barang.index');
        Route::get('/supplier', [SupplierController::class, 'trash'])->name('supplier.index');
        Route::get('/kategori', [KategoriController::class, 'trash'])->name('kategori.index');
        Route::get('/penerimaan', [PenerimaanBarangController::class, 'trash'])->name('penerimaan.index');
        Route::get('/users', function () {
            return view('users.trash');
        })->name('user.index');

        Route::post('/barang/{id}/restore', [BarangController::class, 'restore'])->name('barang.restore');
        Route::delete('/barang/{id}/force-delete', [BarangController::class, 'forceDelete'])->name('barang.force-delete');

        Route::post('/supplier/{id}/restore', [SupplierController::class, 'restore'])->name('supplier.restore');
        Route::delete('/supplier/{id}/force-delete', [SupplierController::class, 'forceDelete'])->name('supplier.force-delete');

        Route::post('/kategori/{id}/restore', [KategoriController::class, 'restore'])->name('kategori.restore');
        Route::delete('/kategori/{id}/force-delete', [KategoriController::class, 'forceDelete'])->name('kategori.force-delete');

        Route::post('/penerimaan/{id}/restore', [PenerimaanBarangController::class, 'restore'])->name('penerimaan.restore');
        Route::delete('/penerimaan/{id}/force-delete', [PenerimaanBarangController::class, 'forceDelete'])->name('penerimaan.force-delete');
    });

    // Resource Routes
    Route::get('/barang/history', function () {
        return view('barang.history-all');
    })->name('barang.history-all');
    Route::get('/barang/{barang}/history', [BarangController::class, 'history'])->name('barang.history');
    Route::resource('barang', BarangController::class);
    Route::resource('supplier', SupplierController::class);
    Route::resource('kategori', KategoriController::class);

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/stok', [ReportController::class, 'exportStok'])->name('export.stok');
        Route::get('/export/kadaluarsa', [ReportController::class, 'exportKadaluarsa'])->name('export.kadaluarsa');
        Route::get('/export/penerimaan-periode', [ReportController::class, 'exportPenerimaanPeriode'])->name('export.penerimaan-periode');
        Route::get('/export/penerimaan-supplier', [ReportController::class, 'exportPenerimaanSupplier'])->name('export.penerimaan-supplier');
    });

    // Penerimaan Barang Routes
    Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
        Route::get('/', [PenerimaanBarangController::class, 'index'])->name('index');
        Route::get('/create', [PenerimaanBarangController::class, 'create'])->name('create');
        Route::get('/{penerimaanBarang}', [PenerimaanBarangController::class, 'show'])->name('show');
        Route::patch('/{penerimaanBarang}/verify', [PenerimaanBarangController::class, 'verify'])->name('verify');
        Route::post('/{penerimaanBarang}/reject', [PenerimaanBarangController::class, 'reject'])->name('reject');
        Route::delete('/{penerimaanBarang}', [PenerimaanBarangController::class, 'destroy'])->name('destroy');
    });

    Route::get('activity', function () {
        return view('activity.index');
    })->name('activity.index');
});
