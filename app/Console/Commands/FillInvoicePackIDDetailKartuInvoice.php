<?php

namespace App\Console\Commands;

use App\Models\DetailKartuInvoice;
use App\Models\InvoicePack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FillInvoicePackIDDetailKartuInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:invoice-pack-id-detail-kartu-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill invoice pack ID on detail kartu invoice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $details = DB::table('detail_kartu_invoices')->whereNull('invoice_pack_id')->get();
        foreach ($details as $detail) {
            Session::put('book_journal_id', $detail->book_journal_id);
            $thed = DetailKartuInvoice::find($detail->id);
            if ($thed) {
                $thed->invoice_pack_id = $thed->getInvoicePackID();
                if ($thed->invoice_pack_id) {
                    $thed->save();
                    $inv = InvoicePack::find($thed->invoice_pack_id);
                    if ($inv) {
                        $inv->updateStatus();
                    }
                    $this->info('Updated Detail Kartu Invoice ID: ' . $detail->id . ' with Invoice Pack ID: ' . $thed->invoice_pack_id);
                } else {
                    $this->error('Invoice Pack ID not found for Detail Kartu Invoice ID: ' . $detail->id);
                }
            }
        }
    }
}
