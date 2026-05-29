<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use App\Models\SalesOrderDetail;
use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CekDoubleStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:double-stock {bookid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for duplicate stock entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookId = $this->argument('bookid');
        Session::put('book_journal_id', $bookId);
        $stocks = Stock::where('book_journal_id', $bookId)
            ->select(DB::raw('count(id) as total_count'), 'id', 'name', 'reference_stock_id', DB::raw('GROUP_CONCAT(id) as stock_ids'))
            ->groupBy('reference_stock_id')
            ->havingRaw('total_count > 1')
            ->get();
        tampilkanTableTerminal(
            $stocks,
            [
                'name' => 'left',
                'total_count' => 'center',
                'reference_stock_id' => 'left',
                'stock_ids' => 'left',
            ],
            $this
        );

        foreach ($stocks as $stock) {
            $this->info("Mengecek stock: " . $stock->name . " (Reference Stock ID: " . $stock->reference_stock_id . ")");
            $stockIds = explode(',', $stock->stock_ids);
            $stockIdReadys = [];
            $stockIdNotReadys = [];
            $qtyReady = 0;
            foreach ($stockIds as $id) {
                $ks = KartuStock::where('stock_id', $id)
                    ->orderBy('index_date', 'desc')
                    ->first();
                $saldo = 0;
                if ($ks) {
                    $saldo = $ks->saldo_qty_backend;
                }
                if ($saldo == 0) {
                    $stockIdNotReadys[] = $id;
                } else {
                    $qtyReady = $saldo;
                    $stockIdReadys[] = $id;
                }
            }
            foreach ($stockIdReadys as $stockIdReady) {
                $this->info("Stock ID ready: $stockIdReady ($qtyReady)");
            }
            foreach ($stockIdNotReadys as $stockIdNotReady) {
                $this->info("Stock ID not ready: $stockIdNotReady");
            }
            $sales = SalesOrderDetail::whereIn('stock_id', $stockIds)
                ->select(DB::raw('count(id) as total_count'), 'stock_id')
                ->groupBy('stock_id')
                ->get()->pluck('total_count', 'stock_id');

            $firstStockIdReady = $stockIdReadys[0] ?? null;
            foreach ($stockIdNotReadys as $stockIdNotReady) {
                if ($sales->has($stockIdNotReady)) {
                    $this->info("Stock ID $stockIdNotReady  terjual sebanyak " . $sales->get($stockIdNotReady) . " kali, padahal ga ada stock");
                    $this->info("kita alihkan penjualan ke stock $firstStockIdReady sebanyak $qtyReady");
                    SalesOrderDetail::where('stock_id', $stockIdNotReady)->update(['stock_id' => $firstStockIdReady]);
                }
                if (!$sales->has($stockIdNotReady)) {
                    $this->info("Stock ID $stockIdNotReady tidak terjual sama sekali, jadi aman untuk dihapus");
                    $this->info("kita hapus stock id $stockIdNotReady");
                    Stock::where('id', $stockIdNotReady)->delete();
                }
            }

            foreach ($stockIdReadys as $stockIdReady) {
                if ($sales->has($stockIdReady)) {
                    $this->info("Stock ID $stockIdReady terjual sebanyak " . $sales->get($stockIdReady) . " kali");
                } else {
                    //ini berati ga ada penjualannya ini bisa kita hapus aja stocknya, tapi kita pastikan kalo sama berati bener .
                    $this->info("Stock ID $stockIdReady tidak terjual sama sekali, jadi aman untuk dihapus");
                    $kartuStocks = KartuStock::where('stock_id', $stockIdReady)->get();
                    if (count($kartuStocks) == 1) {
                        $firstKartuStock = $kartuStocks->first();
                        if ($firstKartuStock->sales_order_number == 'INITAWAL') {
                            $firstKartuStock->delete();
                            Stock::where('id', $stockIdReady)->delete();
                            $this->info("Stock ID $stockIdReady dan kartu stock terkait berhasil dihapus");
                        }
                    }
                }
            }
            $this->info("====================================================");
        }
    }
}
