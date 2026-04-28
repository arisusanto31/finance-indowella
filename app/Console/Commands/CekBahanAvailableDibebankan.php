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
    protected $signature = 'cek:bahan-available-dibebankan {bookid} {catid} {month} {year=2025}';

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
        $month = $this->argument('month');
        $year = $this->argument('year', 2025);
        $catid = explode(',', $catid);
        $stocknames = Stock::whereIn('category_id', $catid)->pluck('name', 'id')->all();
        $start = createCarbon($year . '-' . $month . '-01 00:00:00')->format('ymdHis000');
        $allstockid = Stock::whereIn('category_id', $catid)->pluck('id')->toArray();

        $lastMutasi = KartuStock::whereIn('index_date', function ($q) use ($start, $allstockid) {
            $q->selectRaw('max(index_date)')->from('kartu_stocks')
                ->where('index_date', '<', $start)->whereIn('stock_id', $allstockid)
                ->groupBy('stock_id');
        })->pluck('index_date')->all();
        $allMutasi = KartuStock::where(function ($q) use ($start, $lastMutasi) {
            $q->where('index_date', '>', $start)->orWhereIn('index_date', $lastMutasi);
        })
            ->whereIn('stock_id', $allstockid)->select('saldo_qty_backend', 'saldo_rupiah_total', 'stock_id')->get()->groupBy('stock_id')
            ->map(function ($item, $stockid) use ($stocknames, $lastMutasi) {
                $item = collect($item)->merge(collect($lastMutasi[$stockid] ?? ['saldo_qty_backend' => 0, 'stock_id' => $stockid, 'saldo_rupiah_total' => 0]))->values();
                $min = collect($item)->min('saldo_qty_backend');
                if ($min > 0) {
                    return [
                        'stock_id' => $item[0]->stock_id,
                        'stock_name' => $stocknames[$item[0]->stock_id] ?? '',
                        'available' => $min,
                    ];
                }
            })->filter(function ($val) {
                if ($val) return true;
            })->values();

        tampilkanTableTerminal($allMutasi, [
            'stock_id' => 'center',
            'stock_name' => 'left',
            'available' => 'right',
        ], $this);
    }
}
