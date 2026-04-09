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

            Route::post('/users/{id}/restore', function ($id) {
                \App\Models\User::withTrashed()->findOrFail($id)->restore();
                return redirect()->back()->with('success', 'User berhasil dipulihkan');
            })->name('user.restore');
            Route::delete('/users/{id}/force-delete', function ($id) {
                \App\Models\User::withTrashed()->findOrFail($id)->forceDelete();
                return redirect()->back()->with('success', 'User berhasil dihapus permanen');
            })->name('user.force-delete');
        });
    });

    Route::resource('barang', BarangController::class);
    Route::resource('supplier', SupplierController::class);
    Route::resource('kategori', KategoriController::class);

    Route::get('penerimaan', [PenerimaanBarangController::class, 'index'])->name('penerimaan.index');
    Route::get('penerimaan/create', [PenerimaanBarangController::class, 'create'])->name('penerimaan.create');
    Route::get('penerimaan/{penerimaanBarang}', [PenerimaanBarangController::class, 'show'])->name('penerimaan.show');
    Route::patch('penerimaan/{penerimaanBarang}/verify', [PenerimaanBarangController::class, 'verify'])->name('penerimaan.verify');
    Route::delete('penerimaan/{penerimaanBarang}', [PenerimaanBarangController::class, 'destroy'])->name('penerimaan.destroy');

    Route::get('activity', function () {
        return view('activity.index');
    })->name('activity.index');
});
