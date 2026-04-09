<?php

namespace App\Console\Commands;

use App\Models\ChartAccount;
use App\Models\DetailKartuInvoice;
use App\Models\InvoicePack;
use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FillAmountOnDetailKartuInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:amount-on-detail-kartu-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill amount on detail kartu invoice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $charts = ChartAccount::whereNull('is_deleted')->pluck('name', 'code_group')->all();
        $details = DB::table('detail_kartu_invoices')->where('amount_debet', 0)->where('amount_kredit', 0)->get();
        foreach ($details as $detail) {
            $journal = DB::table('journals')->where('id', $detail->journal_id)->first();
            if (!$journal) {
                $this->error('Journal not found for Detail Kartu Invoice ID: ' . $detail->id);
                continue;
            } else {
                $detail->amount_debet = $journal->amount_debet;
                $detail->amount_kredit = $journal->amount_kredit;
                $detail->amount_journal = $journal->amount_debet - $journal->amount_kredit;
                $detail->account_name = $charts[$journal->code_group];
                $detail->account_code_group = $journal->code_group;
                DB::table('detail_kartu_invoices')->where('id', $detail->id)->update([
                    'amount_debet' => $detail->amount_debet,
                    'amount_kredit' => $detail->amount_kredit,
                    'amount_journal' => $detail->amount_journal,
                    'account_name' => $detail->account_name,
                    'account_code_group' => $detail->account_code_group,
                ]);

                if (!$detail->invoice_pack_id) {
                    Session::put('book_journal_id', $detail->book_journal_id);
                    $thed = DetailKartuInvoice::find($detail->id);
                    $thed->invoice_pack_id = $thed->getInvoicePackID();
                    $thed->save();
                    $detail->invoice_pack_id = $thed->invoice_pack_id;
                }

                if ($detail->invoice_pack_id) {
                    Session::put('book_journal_id', $detail->book_journal_id);
                    $invpack = InvoicePack::find($detail->invoice_pack_id);
                    if ($invpack) {
                        $invpack->updateStatus();
                    }
                }
                $this->info('Updated Detail Kartu Invoice ID: ' . $detail->id . ' with Amount Debet: ' . $detail->amount_debet . ', Amount Kredit: ' . $detail->amount_kredit);
            }
        }
        $this->info('Finished updating Detail Kartu Invoice records.');
    }
}
