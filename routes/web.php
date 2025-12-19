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
    ExcelExportController,
    OtherPersonController,
    StockController,
    SupplierController,
    InvoiceSaleController,
    InvoicePurchaseController,
    InventoryController,
    KartuBahanJadiController,
    KartuBDPController,
    KartuDPSalesController,
    SalesOrderController,
    TokoController
};
use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\SalesOrder;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

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
        Route::post('update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
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
    Route::get('area-developer', [IndexController::class, 'areaDeveloper'])->name('admin.area-developer');
    Route::get('get-summary-balance', [IndexController::class, 'getSummaryBalance'])->name('admin.get-summary');
    Route::get('/random', [IndexController::class, 'random']);
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [IndexController::class, 'dashboard'])->name('index');;
        Route::get('/inspect-jurnal', [IndexController::class, 'inspectJurnal'])->name('inspect-jurnal');
    });
    Route::get('/login-dashboard', [IndexController::class, 'loginDashboard']);
    Route::get('/neraca', [JournalController::class, 'neraca']);
    Route::get('/neraca-lajur', [JournalController::class, 'neracalajur']);
    Route::get('/get-mutasi-neraca-lajur', [JournalController::class, 'getMutasiNeracaLajur']);
    Route::get('/laba-rugi', [JournalController::class, 'labarugi']);
    Route::get('/export-data', [ExcelExportController::class, 'exportData'])->name('export-data');

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
        Route::get('/get-laba-rugi/{id}', [JournalController::class, 'getLabaRugi'])->name('get-laba-rugi');
        Route::get('/get-closing-journal', [JournalController::class, 'getClosingJournal'])->name('get-closing-journal');
        Route::get('/update-not-valid', [JournalController::class, 'updateNotValid'])->name('update-not-valid');

        Route::post('get-import-saldo', [JournalController::class, 'getImportSaldo'])->name('get-import-saldo');
        Route::post('import-saldo', [JournalController::class, 'importSaldo'])->name('import-saldo');
        Route::get('get-import-saldo-followup/{id}', [JournalController::class, 'getImportSaldoFollowup']);
        Route::get('get-task-import-aktif', [JournalController::class, 'getTaskImportAktif']);
        Route::get('resend-import-task/{id}', [JournalController::class, 'resendImportTask']);
        Route::get('resend-import-task-all/{id}', [JournalController::class, 'resendImportTaskAll']);

        Route::get('get-import-data', [JournalController::class, 'getImportData'])->name('get-import-data');
        Route::post('import-data', [JournalController::class, 'importData'])->name('import-data');
        Route::post('tutup-jurnal', [JournalController::class, 'tutupJurnal']);
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

        Route::prefix('karyawan')->name('karyawan.')->group(function () {
            Route::get('/', [KaryawanController::class, 'index'])->name('index');
            Route::get('/create', [KaryawanController::class, 'create'])->name('create');
            Route::post('/store', [KaryawanController::class, 'store'])->name('store');
            Route::get('/{id}', [KaryawanController::class, 'show'])->name('show');
            Route::get('edit/{id}', [KaryawanController::class, 'edit'])->name('edit');
            Route::put('/{id}/resign', [KaryawanController::class, 'resign'])->name('resign');
            Route::put('/update/{id}', [KaryawanController::class, 'update'])->name('update');
            Route::delete('/{id}', [KaryawanController::class, 'destroy'])->name('destroy');
            Route::put('karyawans/{id}', [KaryawanController::class, 'update'])->name('karyawans.update');
        });
    });

    Route::prefix('kartu')->group(function () {
        Route::prefix('kartu-kas')->name('kartu-kas.')->group(function () {
            Route::resource('main', KartuKasController::class);
            Route::get('get-buku-kas', [KartuKasController::class, 'getBukuKas'])->name('get-buku-kas');
            Route::post('add-kas', [KartuKasController::class, 'addKas'])->name('add-kas');
        });

        Route::prefix('kartu-stock')->name('kartu-stock.')->group(function () {
            Route::resource('main', KartuStockController::class);
            Route::post('mutasi-store', [KartuStockController::class, 'mutasiStore'])->name('mutasi-store');
            Route::get('create-mutasi-masuk', [KartuStockController::class, 'createMutasiMasuk'])->name('create-mutasi-masuk');
            Route::get('create-mutasi-keluar', [KartuStockController::class, 'createMutasiKeluar'])->name('create-mutasi-keluar');
            Route::get('get-summary', [KartuStockController::class, 'getSummary'])->name('get-summary');
            Route::get('get-mutasi-masuk', [KartuStockController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('get-mutasi-keluar', [KartuStockController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
            Route::post('refresh-kartu', [KartuStockController::class, 'refreshKartu'])->name('refresh-kartu');
            Route::get('get-hpp', [KartuStockController::class, 'getHPP'])->name('get-hpp');
            Route::get('kartu-mutasi/{id}', [KartuStockController::class, 'kartuMutasi'])->name('kartu-mutasi');
            Route::get('show-history-stock/{id}', [KartuStockController::class, 'showHistoryStock'])->name('show-history-stock');
            Route::post('recalculate', [KartuStockController::class, 'recalculate'])->name('recalculate');
        });

        Route::prefix('kartu-bdp')->name('kartu-bdp.')->group(function () {
            Route::resource('main', KartuBDPController::class);
            Route::post('mutasi-store', [KartuBDPController::class, 'mutasiStore'])->name('mutasi-store');
            Route::get('create-mutasi-masuk', [KartuBDPController::class, 'createMutasiMasuk'])->name('create-mutasi-masuk');
            Route::get('create-mutasi-keluar', [KartuBDPController::class, 'createMutasiKeluar'])->name('create-mutasi-keluar');
            Route::get('get-summary', [KartuBDPController::class, 'getSummary'])->name('get-summary');
            Route::get('get-mutasi-masuk', [KartuBDPController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('get-mutasi-keluar', [KartuBDPController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
            Route::post('create-mutations', [KartuBDPController::class, 'createMutations'])->name('create-mutations');
            Route::post('refresh-kartu', [KartuBDPController::class, 'refreshKartu'])->name('refresh-kartu');
            Route::get('show-history-stock/{id}', [KartuBDPController::class, 'showHistoryStock'])->name('show-history-stock');
            Route::post('recalculate', [KartuBDPController::class, 'recalculate'])->name('recalculate');
            Route::post('delete-mutation', [KartuBDPController::class, 'deleteMutation'])->name('delete-mutation');
        });

        Route::prefix('kartu-bahan-jadi')->name('kartu-bahan-jadi.')->group(function () {
            Route::resource('main', KartuBahanJadiController::class);
            Route::post('mutasi-store', [KartuBahanJadiController::class, 'mutasiStore'])->name('mutasi-store');
            Route::get('create-mutasi-masuk', [KartuBahanJadiController::class, 'createMutasiMasuk'])->name('create-mutasi-masuk');
            Route::get('create-mutasi-keluar', [KartuBahanJadiController::class, 'createMutasiKeluar'])->name('create-mutasi-keluar');
            Route::get('get-summary', [KartuBahanJadiController::class, 'getSummary'])->name('get-summary');
            Route::get('get-mutasi-masuk', [KartuBahanJadiController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('get-mutasi-keluar', [KartuBahanJadiController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
            Route::post('create-mutations', [KartuBahanJadiController::class, 'createMutations'])->name('create-mutations');
            Route::post('refresh-kartu', [KartuBahanJadiController::class, 'refreshKartu'])->name('refresh-kartu');
            Route::get('show-history-stock/{id}', [KartuBahanJadiController::class, 'showHistoryStock'])->name('show-history-stock');
            Route::post('recalculate', [KartuBahanJadiController::class, 'recalculate'])->name('recalculate');
            Route::post('delete-mutation', [KartuBahanJadiController::class, 'deleteMutation'])->name('delete-mutation');
        });
        Route::prefix('kartu-hutang')->name('kartu-hutang.')->group(function () {
            Route::resource('main', KartuHutangController::class);
            Route::post('create-mutation', [KartuHutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuHutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuHutangController::class, 'getSummary'])->name('get-summary');
            Route::get('show-detail/{id}', [KartuHutangController::class, 'showDetail'])->name('show-detail');
            Route::get('search-link-journal', [KartuHutangController::class, 'searchLinkJournal'])->name('search-link-journal');
            Route::get('get-mutasi-masuk', [KartuHutangController::class, 'getMutasiMasuk'])->name('get-mutasi-masuk');
            Route::get('get-mutasi-keluar', [KartuHutangController::class, 'getMutasiKeluar'])->name('get-mutasi-keluar');
            Route::get('refresh/{id}', [KartuHutangController::class, 'refresh'])->name('refresh');
        });

        Route::prefix('kartu-piutang')->name('kartu-piutang.')->group(function () {
            Route::resource('main', KartuPiutangController::class);
            Route::post('create-mutation', [KartuPiutangController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuPiutangController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuPiutangController::class, 'getSummary'])->name('get-summary');
            Route::get('show-detail/{id}', [KartuPiutangController::class, 'showDetail'])->name('show-detail');
            Route::get('search-link-journal', [KartuPiutangController::class, 'searchLinkJournal'])->name('search-link-journal');
            Route::get('refresh/{id}', [KartuPiutangController::class, 'refresh'])->name('refresh');
            Route::delete('delete-mutation/{id}', [KartuPiutangController::class, 'deleteMutation'])->name('delete-mutasi');
        });
        Route::prefix('kartu-dp-sales')->name('kartu-dp-sales.')->group(function () {
            Route::resource('main', KartuDPSalesController::class);
            Route::post('create-mutation', [KartuDPSalesController::class, 'createMutation'])->name('create-mutation');
            Route::post('create-pelunasan', [KartuDPSalesController::class, 'createPelunasan'])->name('create-pelunasan');
            Route::get('get-summary', [KartuDPSalesController::class, 'getSummary'])->name('get-summary');
            Route::get('show-detail/{id}', [KartuDPSalesController::class, 'showDetail'])->name('show-detail');
            Route::get('search-link-journal', [KartuDPSalesController::class, 'searchLinkJournal'])->name('search-link-journal');
            Route::get('refresh/{id}', [KartuDPSalesController::class, 'refresh'])->name('refresh');
            Route::get('recalculate/{id}', [KartuDPSalesController::class, 'recalculateKartuDP'])->name('recalculate');
        });
    });

    Route::prefix('master')->group(function () {
        Route::prefix('chart-account')->name('chart-account.')->group(function () {
            Route::resource('main', ChartAccountController::class);
            Route::get('/get-item', [ChartAccountController::class, 'getItemChartAccount'])->name('get-item');
            Route::get('/get-item-all', [ChartAccountController::class, 'getItemChartAccountAll'])->name('get-item-all');
            Route::get('/get-item-keuangan', [ChartAccountController::class, 'getItemChartAccountKeuanganManual'])->name('get-item-keuangan');
            Route::get('/get-chart-accounts', [ChartAccountController::class, 'getChartAccounts']);
            Route::get('/get-chart-account/{id}', [ChartAccountController::class, 'getChartAccount']);
            Route::get('/get-code-group/{id}', [ChartAccountController::class, 'getCodeGroupAccount']);
            Route::get('/master-suplier', [SupplierController::class, 'master.supplier']);
            Route::post('/make-alias', [ChartAccountController::class, 'makeAlias']);
            Route::delete('/delete-account/{id}', [ChartAccountController::class, 'deleteAccount'])->name('delete');
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
            Route::get('deleted', [TokoController::class, 'showDeleted'])->name('deleted');
            Route::resource('main', TokoController::class)->except(['show']);
            Route::get('/get-item', [TokoController::class, 'getItem'])->name('get-item');
            Route::post('{id}/restore', [TokoController::class, 'restore'])->name('restore');
            Route::get('/get-parent-option', [TokoController::class, 'getParentOption'])->name('get-parent-option');
            Route::post('/make-link-parent', [TokoController::class, 'makeLinkParent'])->name('make-link-parent');
            Route::post('/change-code-kas', [TokoController::class, 'changeCodeKas'])->name('change-code-kas');
        });


        Route::prefix('other-person')->name('other-person.')->group(function () {
            Route::resource('main', OtherPersonController::class);
            Route::get('/get-item', [OtherPersonController::class, 'getItem'])->name('get-item');
            Route::get('/trashed', [OtherPersonController::class, 'trashed'])->name('other-persons.trashed');
            Route::post('{id}/restore', [OtherPersonController::class, 'restore'])->name('other-persons.restore');
            Route::get('/get-item-other-person', [OtherPersonController::class, 'getItemOtherPerson'])->name('get-item-other-person');
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
            Route::get('get-stock', [StockController::class, 'getStock'])->name('get-stock');
            Route::get('open-sinkron-stock/{id}', [StockController::class, 'openSinkron'])->name('open-sinkron');
            Route::post('sync', [StockController::class, 'sync'])->name('sync');
            // Route::get('/produk/get-item', [StockController::class, 'getItem'])->name('stock.produk-get-item');
        });
    });

    Route::prefix('invoice')->name('invoice.')->group(function () {

        Route::get('invoice-sales', [InvoiceSaleController::class, 'ShowSales'])->name('sales.index');
        Route::get('invoice-purchase', [InvoicePurchaseController::class, 'showPurchase'])->name('purchase');
        Route::post('invoice-sales', [InvoiceSaleController::class, 'store'])->name('sales.store');
        Route::post('invoice-purchase', [InvoicePurchaseController::class, 'store'])->name('purchase.store');
        Route::post('purchase-create-mutations', [InvoicePurchaseController::class, 'createMutations'])->name('purchase.create-mutations');

        Route::get('show-detail/{id}', [InvoicePackController::class, 'showDetail'])->name('detail');
        Route::post('create-claim-pembelian', [InvoicePackController::class, 'createClaimPembelian'])->name('create-claim-pembelian');
        Route::post('create-claim-penjualan', [InvoicePackController::class, 'createClaimPenjualan'])->name('create-claim-penjualan');
        Route::get('get-item-invoice-aktif/{id}', [InvoicePackController::class, 'getItemInvoiceAktif'])->name('get-item-invoice-aktif');
        Route::get('open-import/{id}', [InvoiceSaleController::class, 'openImport'])->name('open-import');
        Route::post('create-invoices', [InvoiceSaleController::class, 'createInvoices'])->name('create-invoices');
        Route::get('edit-sales-order/{id}', [SalesOrderController::class, 'editInvoice']);
        Route::post('invoice/update-detail', [SalesOrderController::class, 'updateDetail']);

        Route::post('invoice-sales/update/{invoiceNumber}', [InvoiceSaleController::class, 'updateInvoiceSales'])->name('invoice.sales.update');
        Route::post('submit-bayar-sales-invoice', [InvoiceSaleController::class, 'submitBayarSalesInvoice']);
        Route::get('invoice-get-data-import/{id}', [InvoiceSaleController::class, 'getDataImport']);
        Route::post('invoice-make-final', [InvoiceSaleController::class, 'makeFinal'])->name('invoice-make-final');
        Route::post('invoice-cancel-final', [InvoiceSaleController::class, 'cancelFinal'])->name('invoice-cancel-final');
        Route::post('invoice-mark', [InvoicePackController::class, 'mark'])->name('invoice-mark');

        Route::get('sales-order', [SalesOrderController::class, 'index'])->name('sales-order.index');
        Route::post('sales-order-store', [SalesOrderController::class, 'store'])->name('sales-order.store');
        Route::get('sales-open-import/{id}', [SalesOrderController::class, 'openImport'])->name('sales-open-import');
        Route::get('sales-get-data-import/{id}', [SalesOrderController::class, 'getDataImport'])->name('sales-get-data-import');
        Route::post('sales-get-data-import-excel', [SalesOrderController::class, 'getDataImportExcel'])->name('sales-get-data-import-excel');
        Route::get('sales-open-import-excel/{id}',[SalesOrderController::class, 'openImportExcel'])->name('sales-open-import-excel');
        Route::get('show-sales-detail/{id}', [SalesOrderController::class, 'showDetail'])->name('sale-order-detail');
        Route::get('update-input-invoice/{id}', [SalesOrderController::class, 'updateInputInvoice'])->name('update-input-invoice');
        Route::post('sales-make-final', [SalesOrderController::class, 'makeFinal'])->name('sales-make-final');
        Route::post('sales-cancel-final', [SalesOrderController::class, 'cancelFinal'])->name('sales-cancel-final');
        Route::post('sales-mark', [SalesOrderController::class, 'mark'])->name('sales-mark');
        Route::post('sales-process-dagang', [SalesOrderController::class, 'processDagang'])->name('sales-process-dagang');

        Route::get('purchase-open-import-excel/{id}',[InvoicePurchaseController::class, 'openImportExcel'])->name('purchase-open-import-excel');
        Route::post('purchase-get-data-import-excel', [InvoicePurchaseController::class, 'getDataImportExcel'])->name('purchase-get-data-import-excel');
        

        Route::get('invoice-sales-refresh/{id}', [InvoiceSaleController::class, 'refresh'])->name('invoice-sales-refresh');
        Route::get('invoice-sales/edit/{id}', [InvoiceSaleController::class, 'editInvoiceSales']);
        Route::get('invoice-purchase/edit/{id}', [InvoicePurchaseController::class, 'editInvoicePurchase']);
        Route::post('invoice-purchase-update', [InvoicePurchaseController::class, 'updateInvoicePurchase']);
        Route::delete('delete-invoice-purchase/{id}', [InvoicePurchaseController::class, 'destroy']);
        Route::post('invoice/sales-get-info-reference-finish', [SalesOrderController::class, 'getInfoReferenceFinish'])->name('sales-get-info-reference-finish');

        Route::get('kebutuhan-produksi-marked/{data}', [SalesOrderController::class, 'kebutuhanProduksiMarked'])->name('kebutuhan-produksi-marked');
        Route::post('hitung-reference-biaya', [SalesOrderController::class, 'hitungReferenceBiaya'])->name('hitung-reference-biaya');
        Route::post('hitung-kisaran-biaya', [SalesOrderController::class, 'hitungKisaranBiaya'])->name('hitung-kisaran-biaya');
        Route::delete('sales-order-delete/{id}', [SalesOrderController::class, 'destroy']);
        Route::get('sales-update-status/{id}', [SalesOrderController::class, 'updateStatus']);
        Route::get('get-data-kartu/{number}', [SalesOrderController::class, 'getDataKartu'])->name('get-data-kartu');
    });
});


require __DIR__ . '/auth.php';
