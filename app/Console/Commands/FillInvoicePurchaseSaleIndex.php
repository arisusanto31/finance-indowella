<?php

namespace App\Console\Commands;

use App\Models\InvoicePurchaseDetail;
use App\Models\InvoiceSaleDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class FillInvoicePurchaseSaleIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:invoice-purchase-sale-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill index for invoice purchase and sale details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        try{
        Session::put('book_journal_id', 1);
        $invSales = InvoiceSaleDetail::whereNull('index_date')->get();
        foreach ($invSales as $invSale) {
            $invSale->fillIndexDate();
            $invSale->refresh();
            if($invSale->index_date){
                $this->info('Updated Invoice Sale Detail ID: ' . $invSale->id . ' with index date: ' . $invSale->index_date);
            }else{
                $this->error('Failed to update Invoice Sale Detail ID: ' . $invSale->id . ' - Index date not found');
            }
        }

        $this->info('sekarang untuk yang purchase ya');
        $invPurchases = InvoicePurchaseDetail::whereNull('kartu_stock_id')->get();
        foreach ($invPurchases as $invPurchase) {
            $invPurchase->fillKartuStockID();
            if($invPurchase->kartu_stock_id){
                $this->info('Updated Invoice Purchase Detail ID: ' . $invPurchase->id . ' with Kartu Stock ID: ' . $invPurchase->kartu_stock_id);
            }else{
                $this->error('Failed to update Invoice Purchase Detail ID: ' . $invPurchase->id . ' - Kartu Stock ID not found');
            }
        }
        }catch(\Exception $e){
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
