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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\OtherPersonController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
Route::get('/customers/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');

Route::resource('other-persons', OtherPersonController::class);

// Soft delete related
Route::get('other-persons-trashed',[OtherPersonController::class, 'trashed'])->name('other-persons.trashed');
Route::post('other-persons/{id}/restore',[OtherPersonController::class,'restore'])->name('other-persons.restore');

Route::prefix('supplier')->name('supplier.')->group(function () {
    Route::resource('/', SupplierController::class);
    Route::get('/get-item', [SupplierController::class, 'getItem'])->name('get-item');
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

        Route::resource('/kartu-stock', KartuStockController::class);


        Route::prefix('kartu-hutang')->name('kartu-hutang.')->group(function () {
            Route::resource('/', KartuHutangController::class);
            Route::post('create-mutation', [KartuHutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuHutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuHutangController::class, 'getSummary'])->name('get-summary');
        });

        Route::prefix('kartu-piutang')->name('kartu-piutang.')->group(function () {
            Route::resource('/', KartuPiutangController::class);
            Route::post('create-mutation', [KartuPiutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuPiutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuPiutangController::class, 'getSummary'])->name('get-summary');
        });
    });
    Route::prefix('master')->group(function () {
        Route::prefix('chart-account')->group(function () {
            Route::resource('/', ChartAccountController::class);
    
            
            Route::get('/customer/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
    
           
            Route::get('/customer', [CustomerController::class, 'index'])->name('master.customer');
            Route::post('/admin/master/customer/store', [CustomerController::class, 'store'])->name('admin.master.customer.store');
    
            Route::post('/customer/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
            Route::post('/customer/restore-all', [CustomerController::class, 'restoreAll'])->name('customers.restoreAll');
            
            Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');

  
        });

    

        Route::prefix('supplier')->name('supplier.')->group(function(){
            Route::resource('/',SupplierController::class);
            Route::get('/get-item',[SupplierController::class,'getItem'])->name('get-item');
        });
        Route::prefix('supplier')->name('supplier.')->group(function () {
            Route::resource('/', SupplierController::class);
            Route::get('/get-item', [SupplierController::class, 'getItem'])->name('get-item');
        });

        Route::prefix('other-person')->name('other-person.')->group(function () {
            Route::resource('/', OtherPersonController::class);
            Route::get('/get-item', [OtherPersonController::class, 'getItem'])->name('get-item');
        });

        Route::prefix('customer')->name('customer.')->group(function () {
            Route::resource('/', CustomerController::class);
            Route::get('/get-item', [CustomerController::class, 'getItem'])->name('get-item');
        });
    });
});
require __DIR__ . '/auth.php';
