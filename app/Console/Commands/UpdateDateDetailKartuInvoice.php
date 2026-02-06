<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateDateDetailKartuInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-date-detail-kartu-invoice {id}';

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

        $so= DB::table('detail_kartu_invoices')->where('sales_order_id',$this->argument('id'))->whereNull('date_journal')->get();
        foreach($so as $item){
            $journal = DB::table('journals')->where('id',$item->journal_id)->first();
            if($journal){
                DB::table('detail_kartu_invoices')->where('id',$item->id)->update([
                    'date_journal'=>$journal->created_at
                ]);
                $this->info("Updated Detail Kartu Invoice ID: ".$item->id);
            }
        }
        $this->info("Done.");
    }
}
