<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CekBahanAvailableDibebankan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:bahan-available-dibebankan {bookid} {catid} {year=2025}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek bahan available dibebankan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Session::put('book_journal_id', $this->argument('bookid', 1));
        $catid = $this->argument('catid');
        $year = $this->argument('year', 2025);
        $catid = explode(',', $catid);
        $stocknames = Stock::whereIn('category_id', $catid)->pluck('name', 'id')->all();
        $start = createCarbon($year . '-01-01 00:00:00')->format('ymdHis000');
        $allstockid = Stock::whereIn('category_id', $catid)->pluck('id')->toArray();
        $allMutasi = KartuStock::where('index_date', '>', $start)
            ->whereIn('stock_id', $allstockid)->select('saldo_qty_backend', 'saldo_rupiah_total', 'stock_id')->get()->groupBy('stock_id')
            ->map(function ($item) use ($stocknames) {
                $min = collect($item)->min('saldo_qty_backend');
                return [
                    'stock_id' => $item[0]->stock_id,
                    'stock_name' => $stocknames[$item[0]->stock_id] ?? '',
                    'available' => $min,

                ];
            })->values();

        tampilkanTableTerminal($allMutasi, [
            'stock_id' => 'center',
            'stock_name' => 'center',
            'available' => 'right',
        ], $this);
    }
}
