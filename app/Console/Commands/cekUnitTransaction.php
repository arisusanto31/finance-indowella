<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\StockUnit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class cekUnitTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:unit-transaction {bookid} {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check unit transactions for a specific book and month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookID= $this->argument('bookid');
        $monthyear = $this->argument('monthyear') . '-01';
        Session::put('book_journal_id', $bookID);
        $startDate = createCarbon($monthyear)->startOfMonth();
        $endDate = createCarbon($monthyear)->endOfMonth();
        $sales= SalesOrderDetail::where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->where('unit','like','-%')
            ->get();
        $allStockID= $sales->pluck('stock_id')->unique();
        $hpp= KartuStock::whereIn('index_date',function($q) use($allStockID){
            $q->selectRaw('max(index_date)')
                ->from('kartu_stocks')
                ->whereIn('stock_id',$allStockID)
                ->groupBy('stock_id');
        })->select('stock_id',DB::raw('saldo_rupiah_total/saldo_qty_backend as hppbackend'))->get()->pluck('hppbackend','stock_id');
        $datakonversi= StockUnit::whereIn('stock_id',$allStockID)->get()->groupBy('stock_id')->map(function($item){
            return $item->pluck('konversi','unit')->all();
        })->all();
       
        foreach($sales as $sale){
            //kita cari yang hpp nya terdekat dari semua unit, dibandingkan dengan harga price nya
            $this->info("Stock ID: {$sale->stock_id}, Unit: {$sale->unit}, Price: {$sale->price}");
            $price= $sale->price;
            $choosenMargin=null;
            if(!array_key_exists($sale->stock_id,$datakonversi)){
                $this->info("No unit conversion found for Stock ID: {$sale->stock_id}");
                $this->info("--------------------------------------------------");
                continue;
            }
            foreach($datakonversi[$sale->stock_id] as $unit =>$konversi){
                $hppUnit= ($hpp[$sale->stock_id]??0) * $konversi;
                $margin= abs($price - $hppUnit) / ($price == 0 ? 1 : $price) * 100;
                $this->info("Stock ID: {$sale->stock_id}, Unit: {$unit}, Price: {$price}, HPP Unit: {$hppUnit}\n, Margin:".$margin);
                if($margin > 3 && $margin < 80){
                    $choosenMargin= $unit;
                  
                }
            }
            if($choosenMargin){
                $this->info("Chosen Unit: {$choosenMargin}");
                $sale->unit= $choosenMargin;
                $sale->unitjadi= $choosenMargin;
                $sale->save();

            }else{
                $this->info("No suitable unit found for Stock ID: {$sale->stock_id}");
            }
            $this->info("--------------------------------------------------");
        }
        $this->info('process '.$sales->count().' data completed');
    }
}
