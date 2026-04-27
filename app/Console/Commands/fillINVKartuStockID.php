<?php

namespace App\Console\Commands;

use App\Models\InvoicePurchaseDetail;
use App\Models\KartuStock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class fillINVKartuStockID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:inv-kartu-stock-id {bookid} {invid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Session::put('book_journal_id', $this->argument('bookid', 1));
        $invid = intval($this->argument('invid'));
        $inv = InvoicePurchaseDetail::find($invid);
        $ks = KartuStock::where('purchase_order_id', $inv->id)->first();
        if ($ks) {
            $ks->kartu_stock_id = $ks->id;
        }
        if (!$ks) {
            $ks = KartuStock::leftJoin('journals', 'journals.id', 'kartu_stocks.journal_id')
                ->where('journals.book_journal_id', bookID())
                ->where('kartu_stocks.stock_id', $inv->stock_id)
                ->where('journals.description', 'like', '%' . $inv->invoice_pack_number . '%')
                ->select('kartu_stocks.id as kartu_stock_id', 'kartu_stocks.mutasi_rupiah_total', 'journals.id as journal_id', 'journals.journal_number', 'journals.index_date_group')
                ->get();
            tampilkanTableTerminal($ks,[
                'kartu_stock_id' => 'center',
                'mutasi_rupiah_total' => 'right',
                'journal_id' => 'center',
                'journal_number' => 'center',
                'index_date_group' => 'center'
            ],$this);
            if (count($ks) == 1) {
                $ks = $ks->first();
            } else {
                $ks = collect($ks)->where('mutasi_rupiah_total', $inv->total_price)->first();
            }
        }
        if ($ks) {
            $this->info('ketemu kartu stock id ' . $ks->kartu_stock_id . ' dengan total ' . $ks->mutasi_rupiah_total);
            $inv->kartu_stock_id = $ks->kartu_stock_id;
            $inv->index_date = InvoicePurchaseDetail::getNextIndexDate(Carbon::createFromFormat('ymdHis', $ks->index_date_group));
            $inv->index_date_group = $ks->index_date_group;
            $inv->journal_id = $ks->journal_id;
            $inv->journal_number = $ks->journal_number;
            $inv->save();
        }
    }
}
