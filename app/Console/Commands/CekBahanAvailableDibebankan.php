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
        $allstockid = Stock::whereIn('parent_category_id', $catid)->pluck('id')->toArray();
        $lasthpp = KartuStock::whereIn('index_date', function ($q) use ($allstockid) {
            $q->selectRaw('max(index_date)')->from('kartu_stocks')
                ->whereIn('stock_id', $allstockid)->groupBy('stock_id');
        })->where('saldo_qty_backend', '>', 0)->select('stock_id', 'saldo_qty_backend', DB::raw('saldo_rupiah_total/saldo_qty_backend as hpp'))->get()->keyBy('stock_id');
        $stockname= Stock::whereIn('id', $allstockid)->pluck('name', 'id')->all();
        for ($month = 12; $month > 0; $month--) {
            $indexDateAwal = createCarbon($year . '-' . toDigit($month, 2) . '-01')->format('ymdHis000');
            $indexDateAkhir = createCarbon($year . '-' . toDigit($month, 2) . '-01')->endOfMonth()->format('ymdHis999');
            // $this->info('cari mutasi dari '.$indexDateAwal.' sampai '.$indexDateAkhir);
            $mutasi = KartuStock::where('index_date', '>', $indexDateAwal)->where('index_date', '<', $indexDateAkhir)
            
                ->whereIn('stock_id', $allstockid)
                ->where('mutasi_qty_backend', '>', 0)->select(DB::raw('sum(mutasi_qty_backend) as total_pembelian'), 'stock_id')->groupBy('stock_id')
                ->get();
            // tampilkanTableTerminal($mutasi,[
            //     'stock_id' => 'center',
            //     'total_pembelian' => 'right'
            // ], $this);
            $mutasi=$mutasi->pluck('total_pembelian', 'stock_id')->all();
            $datas = [];
            foreach ($lasthpp as $stockid => $data) {
                $datas[] = [
                    'month' => $year.'-'.$month,
                    'stock_id' => $stockid,
                    'stock_name' => $stockname[$stockid] ?? $stockid,
                    'available' => $data->saldo_qty_backend,
                    'hpp' => $data->hpp,
                    'total_nilai' => $data->saldo_qty_backend * $data->hpp,
                    'total_pembelian' => isset($mutasi[$stockid]) ? $mutasi[$stockid] : 0
                ];

                $lasthpp[$stockid]->saldo_qty_backend -= isset($mutasi[$stockid]) ? $mutasi[$stockid] : 0;
                if ($lasthpp[$stockid]->saldo_qty_backend <= 0) {
                    // $lasthpp[$stockid]->saldo_qty_backend = 0;
                    //buang array stock id dalam lasthpp
                    unset($lasthpp[$stockid]);
                }
            }
            tampilkanTableTerminal($datas, [
                'month' => 'center',
                'stock_id' => 'center',
                'stock_name'=> 'center',
                'available' => 'right',
                'hpp' => 'right',
                'total_nilai' => 'right',
                'total_pembelian' => 'right'
            ], $this);
        }
    }
}
