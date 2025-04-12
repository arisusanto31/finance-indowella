<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    IndexController,
    ProfileController,
    JournalController,
    DaftarAtController,
    BDDController,
    KaryawanController,
    KartuKasController,
    KartuStockController,
    KartuHutangController,
    KartuPiutangController,
    ChartAccountController,
    CustomerController,
    OtherPersonController,
    StockController,
    SupplierController,
    InvoiceSaleController,
     InvoicePurchaseController,

};

Route::get('/', fn() => redirect('/login'));
Route::get('/phpinfo', fn() => phpinfo());

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
        Route::resource('kartu-kas', KartuKasController::class);

        Route::prefix('kartu-stock')->name('kartu-stock.')->group(function () {
            Route::resource('main', KartuStockController::class);
            Route::post('mutasi-store', [KartuStockController::class, 'mutasiStore'])->name('mutasi-store');
            Route::get('create-mutasi-masuk', [KartuStockController::class, 'createMutasiMasuk'])->name('create-mutasi-masuk');
            Route::get('create-mutasi-keluar', [KartuStockController::class, 'createMutasiKeluar'])->name('create-mutasi-keluar');
            Route::get('get-summary', [KartuStockController::class, 'getSummary'])->name('get-summary');
            Route::get('get-mutasi-masuk', [KartuStockController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('get-mutasi-keluar', [KartuStockController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
        });

        Route::prefix('kartu-hutang')->name('kartu-hutang.')->group(function () {
            Route::resource('main', KartuHutangController::class);
            Route::post('create-mutation', [KartuHutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuHutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuHutangController::class, 'getSummary'])->name('get-summary');
        });

        Route::prefix('kartu-piutang')->name('kartu-piutang.')->group(function () {
            Route::resource('main', KartuPiutangController::class);
            Route::post('create-mutation', [KartuPiutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuPiutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuPiutangController::class, 'getSummary'])->name('get-summary');
        });
    });

    Route::prefix('master')->group(function () {
        Route::prefix('chart-account')->name('chart-account.')->group(function () {
            Route::resource('main', ChartAccountController::class);
            Route::get('/get-item', [ChartAccountController::class, 'getItemChartAccount'])->name('get-item');
            Route::get('/get-item-keuangan', [ChartAccountController::class, 'getItemChartAccountKeuanganManual'])->name('get-item-keuangan');
            Route::get('/get-chart-accounts', [ChartAccountController::class, 'getChartAccounts']);
            Route::get('/master-suplier', [SupplierController::class, 'master.supplier']);
            Route::get('/category-json', function () {
                return \App\Models\StockCategory::select('id', 'name as text')->get();
            });
            
        });

        Route::prefix('supplier')->name('supplier.')->group(function () {
            Route::get('main/deleted', [SupplierController::class, 'showDeleted'])->name('main.deleted');
            Route::resource('main', SupplierController::class)->except(['show']);
            Route::get('/get-item', [SupplierController::class, 'getItem'])->name('get-item');
            Route::post('main/{id}/soft-delete', [SupplierController::class, 'softDeleteSupplier'])->name('main.soft-delete');
            Route::post('main/{id}/restore', [SupplierController::class, 'restore'])->name('main.restore');
            Route::post('/invoice-sales', [InvoiceSaleController::class, 'store'])->name('invoice-sale.store');
        });

        Route::prefix('other-person')->name('other-person.')->group(function () {
            Route::resource('main', OtherPersonController::class);
            Route::get('/get-item', [OtherPersonController::class, 'getItem'])->name('get-item');
            Route::get('/trashed', [OtherPersonController::class, 'trashed'])->name('other-persons.trashed');
            Route::post('{id}/restore', [OtherPersonController::class, 'restore'])->name('other-persons.restore');
        });

        Route::prefix('customer')->name('customer.')->group(function () {
            Route::resource('main', CustomerController::class);
            Route::get('/get-item', [CustomerController::class, 'getItem'])->name('get-item');
            Route::get('/trashed', [CustomerController::class, 'trashed'])->name('trashed');
            Route::post('/{id}/restore', [CustomerController::class, 'restore'])->name('restore');
        });

        Route::prefix('stock')->name('stock.')->group(function () {
            Route::resource('main', StockController::class);
            Route::get('trashed', [StockController::class, 'trashed'])->name('trashed');
            Route::post('unit-store', [StockController::class, 'unitStore'])->name('unit-store');
            Route::post('category-store', [StockController::class, 'categoryStore'])->name('category-store');
            Route::get('category-get-item', [StockController::class, 'categoryGetItem'])->name('category-get-item');
            Route::get('get-item', [StockController::class, 'getItem'])->name('get-item');
            Route::get('get-info/{id}', [StockController::class, 'getInfo'])->name('get-info');
           
            // Route::get('/produk/get-item', [StockController::class, 'getItem'])->name('stock.produk-get-item');
 
        });
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/produk/get-item', [StockController::class, 'getItem'])->name('stock.produk-get-item');
    });
    

    Route::prefix('invoice')->name('invoice.')->group(function () {
        Route::get('invoice-sales', [InvoiceSaleController::class, 'ShowSales'])->name('sales.index');
        Route::get('invoice-purchase', [InvoicePurchaseController::class, 'ShowPurchase'])->name('purchase.index');
    });
});

require __DIR__.'/auth.php';
