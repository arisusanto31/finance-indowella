<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class FillLinkKartuStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-link-kartu-stock {bookid}';

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
        Session::put('book_journal_id', $this->argument('bookid'));
        $ks= KartuStock::from('kartu_stocks as ks')
        ->leftJoin('detail_kartu_invoices as dki',function($join){
            $join->on('dki.kartu_id','=','ks.id')
            ->where('dki.kartu_type','App\\Models\\KartuStock');
        })->whereNull('dki.id')
        ->select('ks.id','ks.stock_id','ks.journal_number','ks.journal_id','ks.index_date')->get();

        // tampilkanTableTerminal($ks,[
        //     'id'=>'center',
        //     'stock_id'=>'center',
        //     'journal_number'=>'center',
        //     'journal_id'=>'center',
        //     'index_date'=>'center'
        // ],$this);
        // $this->info("Total Kartu Stock: ".$ks->count());
        // if(!$this->confirm('Apakah anda yakin ingin mengisi link kartu stock ke detail kartu invoice?')){
        //     $this->info("Proses dibatalkan");
        //     return;
        // }
        foreach($ks as $k){
            $kartuStock= KartuStock::find($k->id);
            try {
                $kartuStock->createDetailKartuInvoice();
                $this->info("Berhasil mengisi link kartu stock id: " . $k->id);
            } catch (\Exception $e) {
                $this->error("Gagal mengisi link kartu stock id: " . $k->id . ". Error: " . $e->getMessage());
            }
        }
    }
}
