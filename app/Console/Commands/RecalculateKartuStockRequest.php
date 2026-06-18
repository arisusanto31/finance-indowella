<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

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
        for ($bookid = 1; $bookid <= 2; $bookid++) {
            Session::put('book_journal_id', $bookid);

            $requestcalculate = Redis::get('request_kartu_stock' . $bookid) ?? '[]';
            $arrrequestcalculate = json_decode($requestcalculate, true);

            info('request recalculate kartu stock '.$bookid.' count ' . count($arrrequestcalculate) . ' ' . $requestcalculate);
            $this->info('request recalculate kartu stock '.$bookid.' count ' . count($arrrequestcalculate) . ' ' . $requestcalculate);

            $kartuStocks = KartuStock::whereIn('id', $arrrequestcalculate)->get()->groupBy('stock_id')->map(function ($q) {
                return collect($q)->sortBy('index_date')->first();
            });
            $this->info(json_encode($kartuStocks));
            Redis::set('request_kartu_stock' . $bookid, '[]');
            foreach ($kartuStocks as $stockid => $kartuStock) {
                $kartuStock->recalculateSaldo();
                info('request recalculate kartu stock '.$bookid.' stock ' . $kartuStock->stock_id . ' at ' . $kartuStock->index_date);
                $this->info('request recalculate kartu stock '.$bookid.' stock ' . $kartuStock->stock_id . ' at ' . $kartuStock->index_date);
            }
        }
    }
}
