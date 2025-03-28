<?php

use App\Http\Controllers\DaftarAtController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\KartuKasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KartuHutangController;
use App\Http\Controllers\KartuPiutangController;
use App\Http\Controllers\KartuStockController;
use App\Http\Controllers\BDDController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::prefix('book')->middleware(['auth', 'role:admin,web'])->group(function () {
    Route::get('/pilih-jurnal', [JournalController::class, 'pilihJurnal'])->name('pilih.jurnal');
    Route::get('/login-jurnal/{id}', [JournalController::class, 'loginJurnal'])->name('login.jurnal');
    Route::post('/logout-jurnal', [JournalController::class, 'logoutJurnal'])->name('logout.jurnal');
});
Route::prefix('admin')->middleware(['auth', 'role:admin,web','ensure.journal'])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('admin.index');
    Route::get('/random', [IndexController::class, 'random']);
    
    Route::get('/dashboard', [IndexController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/login-dashboard', [IndexController::class, 'loginDashboard']);
    Route::get('/neraca', [JournalController::class, 'neraca']);
    Route::get('/neraca-lajur', [JournalController::class, 'neracalajur']);
    Route::get('/get-mutasi-neraca-lajur', [JournalController::class, 'getMutasiNeracaLajur']);
    Route::get('/laba-rugi', [JournalController::class, 'labarugi']);
  
    Route::prefix('jurnal')->group(function () {
        Route::get('/buku-besar', [JournalController::class, 'bukuBesar'])->name('main.buku-besar');
        Route::get('/mutasi', [JournalController::class, 'mutasi'])->name('main.mutasi');
        Route::get('/get-list-mutasi', [JournalController::class, 'getListMutasiJurnal']);
        Route::post('/submit-manual', [JournalController::class, 'createBaseJournal']);
    });


    Route::prefix('daftar')->group(function () {
        Route::get('/daftar-at', [DaftarAtController::class, 'DaftarAt'])->name('daftar.daftar-at');
        Route::get('/daftar-bdd', [BDDController::class, 'DaftarBDD'])->name('daftar.daftar-bdd');
        Route::get('/daftar-karyawan', [KaryawanController::class, 'DaftarKaryawan'])->name('daftar.daftar-karyawan');
    });

    Route::prefix('kartu')->group(function () {
            Route::get('/kartu-kas', [KartuKasController::class, 'KartuKas'])->name('kartu.kartu-kas');
            Route::get('/kartu-hutang', [KartuHutangController::class, 'KartuHutang'])->name('kartu.kartu-hutang');
            Route::get('/kartu-piutang', [KartuPiutangController::class, 'KartuPiutang'])->name('kartu.kartu-piutang');
            Route::get('/kartu-stock', [KartuStockController::class, 'KartuStock'])->name('kartu.kartu-stock');

    });

    Route::prefix('chart-account')->group(function () {
        Route::resource('/', ChartAccountController::class);
        Route::get('get-item', [ChartAccountController::class, 'getItemChartAccount']);
        
        Route::get('get-chart-accounts', [ChartAccountController::class, 'getChartAccounts']);
        Route::get('get-chart-account/{id}', [ChartAccountController::class, 'getChartAccount']);
        // Route::get('get-item-chart-account', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccount']);
        // Route::get('get-item-chart-account-all', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountAll']);
        Route::get('get-code-group-account/{id}', [ChartAccountController::class, 'getCodeGroupAccount']);
        // Route::get('chart-account-update-level', [App\Http\Controllers\Backend\ChartAccountController::class, 'updateAllLevel']);
        // Route::get('get-item-chart-account-keuangan-manual', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountKeuanganManual']);
        // Route::get('get-item-chart-account-aset-tetap', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountAsetTetap']);
        // Route::get('get-item-chart-account-bdd', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountBDD']);
    
    });
});
require __DIR__ . '/auth.php';

