<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RecalculateKartuStockRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-kartu-stock-request';

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
        $requestcalculate = Redis::get('request_kartu_stock') ?? '[]';
        $arrrequestcalculate = json_decode($requestcalculate, true);

        info('request recalculate kartu stock count ' .count($arrrequestcalculate).' '.$requestcalculate);
        $this->info('request recalculate kartu stock count ' .count($arrrequestcalculate).' '.$requestcalculate);
        
        $kartuStocks = KartuStock::whereIn('id',$arrrequestcalculate)->get()->groupBy('stock_id')->map(function($q){
            return collect($q)->sortBy('index_date')->first();
        });
        Redis::set('request_kartu_stock', '[]');
        foreach($kartuStocks as $kartuStock){
            $kartuStock->recalculateSaldo();
            info('request recalculate kartu stock '.$kartuStock->stock_id. ' at '.$kartuStock->index_date);
            $this->info('request recalculate kartu stock '.$kartuStock->stock_id. ' at '.$kartuStock->index_date);
        }
    }
}
