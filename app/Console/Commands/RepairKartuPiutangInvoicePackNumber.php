<?php

namespace App\Console\Commands;

use App\Models\BackgroundProcess;
use App\Models\DetailKartuInvoice;
use App\Models\InvoicePack;
use App\Models\InvoiceSaleDetail;
use App\Models\KartuPiutang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class RepairKartuPiutangInvoicePackNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:kartu-piutang-invoice-pack-number {bookid} {mothyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'invoice pack numbernya ada yang salah, sudah ga update';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        //
        $bookid = $this->argument('bookid');
        Session::put('book_journal_id', $bookid);
        $monthyear = $this->argument('mothyear');
        $indexStart =  createCarbon($monthyear . '-01')->startOfMonth()->format('ymdHis000');
        $indexEnd =  createCarbon($monthyear . '-01')->endOfMonth()->format('ymdHis999');

        $kp = KartuPiutang::where('book_journal_id', $bookid)
            ->whereBetween('index_date', [$indexStart, $indexEnd])
            ->get();

        $bg = BackgroundProcess::make($bookid, 'admin/kartu/kartu-piutang/main', 'repair kartu piutang invoice pack number', count($kp));
        foreach ($kp as $item) {
            //kita cari dulu inv yang punya sales nomer
            if (!$item->sales_order_id) {
                //kita akan cari dari link
                $dk = DetailKartuInvoice::where('kartu_id', $item->id)
                    ->where('kartu_type', KartuPiutang::class)
                    ->first();
                $item->sales_order_id = $dk->sales_order_id ?? null;
                $item->sales_order_number = $dk->sales_order_number ?? null;
                $item->save();
            }
            $invsale = InvoicePack::where('sales_order_id', $item->sales_order_id)->first();
            if ($invsale) {
                //sudah jelas mana invoicepackid dan invoicenumbernya 
                $item->invoice_pack_id = $invsale->id;
                $item->invoice_pack_number = $invsale->invoice_number;
                $item->save();
                $this->info('update kartu piutang ' . $item->id . ' invoice pack number ' . $item->invoice_pack_number);
                $bg->success();
            } else {

                $bg->failed();
                $this->error('tidak ada invoice sale detail untuk sales order id ' . $item->id);
            }
        }
    }
}
