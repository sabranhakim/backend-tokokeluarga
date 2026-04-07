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

    Route::resource('barang', BarangController::class);
    Route::resource('supplier', SupplierController::class);
    Route::resource('kategori', KategoriController::class);

    Route::get('penerimaan', [PenerimaanBarangController::class, 'index'])->name('penerimaan.index');
    Route::get('penerimaan/{penerimaanBarang}', [PenerimaanBarangController::class, 'show'])->name('penerimaan.show');
    Route::patch('penerimaan/{penerimaanBarang}/verify', [PenerimaanBarangController::class, 'verify'])->name('penerimaan.verify');
    Route::delete('penerimaan/{penerimaanBarang}', [PenerimaanBarangController::class, 'destroy'])->name('penerimaan.destroy');
});
