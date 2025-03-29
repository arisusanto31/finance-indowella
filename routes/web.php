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
use App\Http\Controllers\SupplierController;
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
Route::prefix('admin')->middleware(['auth', 'role:admin,web', 'ensure.journal'])->group(function () {
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
        Route::get('/get-buku-besar', [JournalController::class, 'getListBukuBesar']);
        Route::post('/submit-manual', [JournalController::class, 'createBaseJournal']);
    });


    Route::prefix('daftar')->group(function () {
        Route::get('/daftar-at', [DaftarAtController::class, 'DaftarAt'])->name('daftar.daftar-at');
        Route::get('/daftar-bdd', [BDDController::class, 'DaftarBDD'])->name('daftar.daftar-bdd');
        Route::get('/daftar-karyawan', [KaryawanController::class, 'DaftarKaryawan'])->name('daftar.daftar-karyawan');
    });

    Route::prefix('kartu')->group(function () {
        Route::resource('/kartu-kas', KartuKasController::class);
        Route::resource('/kartu-hutang', KartuHutangController::class);
        Route::resource('/kartu-piutang', KartuPiutangController::class);
        Route::resource('/kartu-stock', KartuStockController::class);
        

        Route::post('create-mutasi-hutang',[KartuHutangController::class,'createMutationHutang']);
   
    });
    Route::prefix('master')->group(function () {
        Route::prefix('chart-account')->group(function () {
            Route::resource('/', ChartAccountController::class);
            Route::get('get-item', [ChartAccountController::class, 'getItemChartAccount'])->name('chart-account.get-item');
            Route::get('get-chart-accounts', [ChartAccountController::class, 'getChartAccounts']);
            Route::get('get-chart-account/{id}', [ChartAccountController::class, 'getChartAccount']);
            // Route::get('get-item-chart-account', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccount']);
            // Route::get('get-item-chart-account-all', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountAll']);
            Route::get('get-code-group-account/{id}', [ChartAccountController::class, 'getCodeGroupAccount']);
            Route::get('get-item-keuangan', [ChartAccountController::class, 'getItemChartAccountKeuanganManual'])->name('chart-account.get-item-keuangan');
            // Route::get('get-item-chart-account-aset-tetap', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountAsetTetap']);
            // Route::get('get-item-chart-account-bdd', [App\Http\Controllers\Backend\ChartAccountController::class, 'getItemChartAccountBDD']);
            // Route::get('chart-account-update-level', [App\Http\Controllers\Backend\ChartAccountController::class, 'updateAllLevel']);

        });

        Route::prefix('supplier')->name('supplier.')->group(function(){
            Route::resource('/',SupplierController::class);
            Route::get('/get-item',[SupplierController::class,'getItem'])->name('get-item');
        });
    });
});
require __DIR__ . '/auth.php';

