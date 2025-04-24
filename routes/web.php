<?php

use App\Http\Controllers\InvoicePackController;
use App\Models\ChartAccount;
use App\Models\KartuStock;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    IndexController,
    ProfileController,
    JournalController,
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
    InventoryController
};
use App\Models\Journal;

Route::get('/', fn() => redirect('/login'));
Route::get('/phpinfo', fn() => phpinfo());

Route::middleware('auth')->group(function () {

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::post('/create-permission', [ProfileController::class, 'createPermission'])->name('create-permission');
        Route::post('/create-role', [ProfileController::class, 'createRole'])->name('create-role');
        Route::post('/create-user', [ProfileController::class, 'createUser'])->name('create-user');
        Route::post('/add-permission-role', [ProfileController::class, 'addPermissionRole'])->name('add-permission-role');
        Route::post('/add-role-user', [ProfileController::class, 'addRoleUser'])->name('add-role-user');
        Route::get('/get-role', [ProfileController::class, 'getRole'])->name('get-role');
        Route::get('/get-permission', [ProfileController::class, 'getPermission'])->name('get-permission');
        Route::get('/get-user', [ProfileController::class, 'getUser'])->name('get-user');
        Route::get('/get-item-permission', [ProfileController::class, 'getItemPermission'])->name('get-item-permission');
        Route::get('/get-item-role', [ProfileController::class, 'getItemRole'])->name('get-item-role');
        Route::get('/get-item-user', [ProfileController::class, 'getItemUser'])->name('get-item-user');
    });

    Route::prefix('permission')->group(function () {
        Route::get('/give-to-role', [ProfileController::class, 'getGivePermissionRole'])->name('give-to-role');
    });
});

Route::prefix('book')->middleware(['auth', 'role:admin,web'])->group(function () {
    Route::get('/pilih-jurnal', [JournalController::class, 'pilihJurnal'])->name('pilih.jurnal');
    Route::get('/login-jurnal/{id}', [JournalController::class, 'loginJurnal'])->name('login.jurnal');
    Route::post('/logout-jurnal', [JournalController::class, 'logoutJurnal'])->name('logout.jurnal');
});

Route::prefix('admin')->middleware(['auth', 'role:admin,web', 'ensure.journal'])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('admin.index');
    Route::get('/random', [IndexController::class, 'random']);

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [IndexController::class, 'dashboard'])->name('index');
        Route::get('/inspect-jurnal', [IndexController::class, 'inspectJurnal'])->name('inspect-jurnal');
    });


    Route::get('/login-dashboard', [IndexController::class, 'loginDashboard']);
    Route::get('/neraca', [JournalController::class, 'neraca']);
    Route::get('/neraca-lajur', [JournalController::class, 'neracalajur']);
    Route::get('/get-mutasi-neraca-lajur', [JournalController::class, 'getMutasiNeracaLajur']);
    Route::get('/laba-rugi', [JournalController::class, 'labarugi']);

    Route::prefix('jurnal')->name('jurnal.')->group(function () {
        Route::get('/buku-besar', [JournalController::class, 'bukuBesar'])->name('main.buku-besar');
        Route::get('/mutasi', [JournalController::class, 'mutasi'])->name('main.mutasi');
        Route::get('/get-list-mutasi', [JournalController::class, 'getListMutasiJurnal']);
        Route::get('/get-buku-besar', [JournalController::class, 'getListBukuBesar']);
        Route::post('/submit-manual', [JournalController::class, 'createBaseJournal']);
        Route::get('/search-error', [JournalController::class, 'searchError'])->name('search-error');
        Route::post('link-journal', [JournalController::class, 'linkJournal'])->name('link-journal');
        Route::get('verify/{id}', [JournalController::class, 'verify'])->name('verify');
        Route::get('/recalculate/{id}', [JournalController::class, 'recalculate'])->name('recalculate');
        Route::delete('delete/{id}', [JournalController::class, 'destroy'])->name('delete');
        Route::get('/get-saldo-highlight', [JournalController::class, 'getSaldoHighlight'])->name('get-saldo-highlight');
        Route::get('/get-saldo-custom/{id}', [JournalController::class, 'getSaldoCustom'])->name('get-saldo-custom');
    });

    Route::prefix('daftar')->group(function () {
        Route::prefix('aset-tetap')->name('aset-tetap.')->group(function () {
            Route::get('/', [InventoryController::class, 'index']);
            Route::get('/create_', [InventoryController::class, 'createInventory'])->name('create');
            Route::get('/create-kartu', [InventoryController::class, 'createKartuInventory'])->name('create-kartu');
            Route::post('/store-inventory', [InventoryController::class, 'storeInventory'])->name('store-inventory');
            Route::post('/store-kartu-inventory', [InventoryController::class, 'storeKartuInventory'])->name('store-kartu-inventory');
            Route::get('/get-item', [InventoryController::class, 'getItem'])->name('get-item');
            Route::get('/get-summary', [InventoryController::class, 'getSummary'])->name('get-summary');
            Route::get('/get-mutasi-masuk', [InventoryController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('/get-mutasi-keluar', [InventoryController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
        });
        Route::prefix('bdd')->name('bdd.')->group(function () {
            Route::get('/', [BDDController::class, 'index']);
            Route::get('/create_', [BDDController::class, 'createPrepaid'])->name('create');
            Route::get('/create-kartu', [BDDController::class, 'createKartuPrepaid'])->name('create-kartu');
            Route::post('/store-prepaid', [BDDController::class, 'storePrepaid'])->name('store-prepaid');
            Route::post('/store-kartu-prepaid', [BDDController::class, 'storeKartuPrepaid'])->name('store-kartu-prepaid');
            Route::get('/get-item', [BDDController::class, 'getItem'])->name('get-item');
            Route::get('/get-summary', [BDDController::class, 'getSummary'])->name('get-summary');
            Route::get('/get-mutasi-masuk', [BDDController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('/get-mutasi-keluar', [BDDController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
        });

        Route::get('/daftar-karyawan', [KaryawanController::class, 'DaftarKaryawan'])->name('daftar.daftar-karyawan');
        Route::get('/karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('/daftar-karyawan', [KaryawanController::class, 'index']);
        Route::get('/daftar-karyawan', [KaryawanController::class, 'index'])->name('daftar.daftar-karyawan');
        Route::put('/karyawans/{id}/resign', [KaryawanController::class, 'resign'])->name('karyawans.resign');
    });
    Route::prefix('kartu')->group(function () {
        Route::resource('/kartu-kas', KartuKasController::class);

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
            Route::get('show-detail/{id}', [KartuHutangController::class, 'showDetail'])->name('show-detail');
            Route::get('search-link-journal', [KartuHutangController::class, 'searchLinkJournal'])->name('search-link-journal');
        });

        Route::prefix('kartu-piutang')->name('kartu-piutang.')->group(function () {
            Route::resource('main', KartuPiutangController::class);
            Route::post('create-mutation', [KartuPiutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuPiutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuPiutangController::class, 'getSummary'])->name('get-summary');
            Route::get('show-detail/{id}', [KartuPiutangController::class, 'showDetail'])->name('show-detail');
            Route::get('search-link-journal', [KartuPiutangController::class, 'searchLinkJournal'])->name('search-link-journal');
        });
    });

    Route::prefix('master')->group(function () {
        Route::prefix('chart-account')->name('chart-account.')->group(function () {
            Route::resource('main', ChartAccountController::class);
            Route::get('/get-item', [ChartAccountController::class, 'getItemChartAccount'])->name('get-item');
            Route::get('/get-item-all', [ChartAccountController::class, 'getItemChartAccountAll'])->name('get-item-all');
            Route::get('/get-item-keuangan', [ChartAccountController::class, 'getItemChartAccountKeuanganManual'])->name('get-item-keuangan');
            Route::get('/get-chart-accounts', [ChartAccountController::class, 'getChartAccounts']);
            Route::get('/master-suplier', [SupplierController::class, 'master.supplier']);
            Route::get('/category-json', function () {
                return \App\Models\StockCategory::select('id', 'name as text')->get();
            });
        });

        Route::prefix('supplier')->name('supplier.')->group(function () {
            Route::get('deleted', [SupplierController::class, 'showDeleted'])->name('deleted');
            Route::resource('main', SupplierController::class)->except(['show']);
            Route::get('/get-item', [SupplierController::class, 'getItem'])->name('get-item');
            Route::post('{id}/restore', [SupplierController::class, 'restore'])->name('restore');
        });

        Route::prefix('toko')->name('toko.')->group(function () {
            Route::get('deleted', [SupplierController::class, 'showDeleted'])->name('deleted');
            Route::resource('main', SupplierController::class)->except(['show']);
            Route::get('/get-item', [SupplierController::class, 'getItem'])->name('get-item');
            Route::post('{id}/restore', [SupplierController::class, 'restore'])->name('restore');
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
            Route::get('get-unit/{id}', [StockController::class, 'getUnit'])->name('get-unit');
            // Route::get('/produk/get-item', [StockController::class, 'getItem'])->name('stock.produk-get-item');

        });
    });

    Route::prefix('invoice')->name('invoice.')->group(function () {
        Route::get('invoice-sales', [InvoiceSaleController::class, 'ShowSales'])->name('sales.index');
        Route::get('invoice-purchase', [InvoicePurchaseController::class, 'ShowPurchase'])->name('purchase.index');
        Route::post('invoice-sales', [InvoiceSaleController::class, 'store'])->name('sales.store');
        Route::post('invoice-purchase', [InvoicePurchaseController::class, 'store'])->name('purchase.store');
        Route::get('show-detail/{id}', [InvoicePackController::class, 'showDetail'])->name('detail');
        Route::post('create-claim-pembelian', [InvoicePackController::class, 'createClaimPembelian'])->name('create-claim-pembelian');
        Route::post('create-claim-penjualan', [InvoicePackController::class, 'createClaimPenjualan'])->name('create-claim-penjualan');
    });
    Route::post('sales/store', [InvoiceSaleController::class, 'store'])->name('sales.store');
    Route::get('{id}', [InvoicePackController::class, 'show'])->name('show');
});


require __DIR__ . '/auth.php';
