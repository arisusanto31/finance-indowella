<?php

namespace App\Console\Commands;

use App\Http\Controllers\ExcelExportController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class AnalyzeExportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:export-data {bookid} {month} {year} {forcestr?} ';

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
        $bookid = $this->argument('bookid');
        $month = $this->argument('month');
        $year = $this->argument('year');
        $forceStr = $this->argument('forcestr') ?? '';
        $allforces = explode(',', $forceStr);
        $timeout = 36000;

        Session::put('book_journal_id', $bookid);
        $this->info('process data neraca..');

        $neraca = unserialize(Redis::get('export_data_neraca_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$neraca || in_array('neraca', $allforces)) {
            $neraca = ExcelExportController::getDataNeraca($month, $year);
        }
        Redis::setex('export_data_neraca_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($neraca));
        $this->info('process data neraca finished.');


        $this->info('process data NL..');
        $nl = unserialize(Redis::get('export_data_nl_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$nl || in_array('nl', $allforces)) {
            $nl = ExcelExportController::getDataNL($month, $year);
        }
        Redis::setex('export_data_nl_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($nl));

        $this->info('process data NL finished.');

        $this->info('process data laporan rugi laba..');
        $lr = unserialize(Redis::get('export_data_lr_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$lr || in_array('lr', $allforces)) {
            $lr = ExcelExportController::getDataLR($month, $year);
        }
        Redis::setex('export_data_lr_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($lr));
        $this->info('process data laporan rugi laba finished.');

        $this->info('process data kas..');
        $kas = unserialize(Redis::get('export_data_kas_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kas || in_array('kas', $allforces)) {
            $kas = ExcelExportController::getBukuKas($month, $year);
        }
        Redis::setex('export_data_kas_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kas));
        $this->info('process data kas finished.');

        $this->info('process data memo..');
        $memo = unserialize(Redis::get('export_data_memo_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$memo || in_array('memo', $allforces)) {
            $memo = ExcelExportController::getBukuMemo($month, $year, 1);
        }
        Redis::setex('export_data_memo_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($memo));
        $this->info('process data memo finished.');

        $this->info('process data pembelian..');
        $pembelian = unserialize(Redis::get('export_data_pembelian_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$pembelian || in_array('pembelian', $allforces)) {
            $pembelian = ExcelExportController::getPembelian($month, $year);
        }
        Redis::setex('export_data_pembelian_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($pembelian));
        $this->info('process data pembelian finished.');

        $this->info('process data penjualan..');
        $penjualan = unserialize(Redis::get('export_data_penjualan_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$penjualan || in_array('penjualan', $allforces)) {
            $penjualan = ExcelExportController::getPenjualan($month, $year);
        }
        Redis::setex('export_data_penjualan_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($penjualan));
        $this->info('process data penjualan finished.');


        $this->info('process data kartu piutang..');

        $kartuPiutang = unserialize(Redis::get('export_data_kartu_piutang_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuPiutang || in_array('kartu_piutang', $allforces)) {
            $kartuPiutang = ExcelExportController::getKartuPiutang($month, $year);
        }
        Redis::setex('export_data_kartu_piutang_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuPiutang));
        $this->info('process data kartu piutang finished.');

        $this->info('process data kartu hutang..');
        $kartuHutang = unserialize(Redis::get('export_data_kartu_hutang_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuHutang || in_array('kartu_hutang', $allforces)) {
            $kartuHutang = ExcelExportController::getKartuHutang($month, $year);
        }
        Redis::setex('export_data_kartu_hutang_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuHutang));
        $this->info('process data kartu hutang finished.');

        $this->info('process data kartu dp sales..');

        $kartuDPSales = unserialize(Redis::get('export_data_kartu_dp_sales_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuDPSales || in_array('kartu_dp_sales', $allforces)) {
            $kartuDPSales = ExcelExportController::getKartuDPSales($month, $year);
        }
        Redis::setex('export_data_kartu_dp_sales_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuDPSales));
        $this->info('process data kartu dp sales finished.');

        $this->info('process data kartu inventory..');

        $kartuInventory = unserialize(Redis::get('export_data_kartu_inventory_' . $bookid . '_' . $year)) ?? null;
        if (!$kartuInventory || in_array('kartu_inventory', $allforces)) {
            $kartuInventory = ExcelExportController::getKartuInventory($month,$year);
        }
        Redis::setex('export_data_kartu_inventory_' . $bookid . '_' . $year, $timeout, serialize($kartuInventory));
        $this->info('process data kartu inventory finished.');

        $this->info('process data kartu bdd..');

        $kartuBDD = unserialize(Redis::get('export_data_kartu_bdd_' . $bookid . '_' . $year)) ?? null;
        if (!$kartuBDD || in_array('kartu_bdd', $allforces)) {
            $kartuBDD = ExcelExportController::getKartuBDD($month,$year);
        }
        Redis::setex('export_data_kartu_bdd_' . $bookid . '_' . $year, $timeout, serialize($kartuBDD));
        $this->info('process data kartu bdd finished.');

        $this->info('process data kartu stock..');
        $kartuStock = unserialize(Redis::get('export_data_kartu_stock_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuStock || in_array('kartu_stock', $allforces)) {
            $kartuStock = ExcelExportController::getKartuStock($month, $year);
        }
        Redis::setex('export_data_kartu_stock_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuStock));

        $this->info('process data kartu stock finished.');

        $this->info('process data kartu bdp..');

        $kartuBDP = unserialize(Redis::get('export_data_kartu_bdp_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuBDP || in_array('kartu_bdp', $allforces)) {
            $kartuBDP = ExcelExportController::getKartuBDP($month, $year);
        }
        Redis::setex('export_data_kartu_bdp_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuBDP));
        $this->info('process data kartu bdp finished.');

        $this->info('process data kartu bahan jadi..');

        $kartuBahanJadi = unserialize(Redis::get('export_data_kartu_bahan_jadi_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuBahanJadi || in_array('kartu_bahan_jadi', $allforces)) {
            $kartuBahanJadi = ExcelExportController::getKartuBahanJadi($month, $year);
        }
        Redis::setex('export_data_kartu_bahan_jadi_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuBahanJadi));
        $this->info('process data kartu bahan jadi finished.');

        $this->info('process data kartu in transit..');

        $kartuInTransit = unserialize(Redis::get('export_data_kartu_in_transit_' . $bookid . '_' . $year . '_' . $month)) ?? null;
        if (!$kartuInTransit || in_array('kartu_in_transit', $allforces)) {
            $this->info('menghitung data kartu in transit..');
            $kartuInTransit = ExcelExportController::getKartuInTransit($month, $year);
        }
        Redis::setex('export_data_kartu_in_transit_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuInTransit));
        $this->info('process data kartu in transit finished.');

        $st = ExcelExportController::analyze(new Request(
            [
                'month' => $month,
                'year' => $year,
                'neraca' => $neraca,
                'neraca_lajur' => $nl,
                'laba_rugi' => $lr,
                'kas' => $kas,
                'memo' => $memo,
                'pembelian' => $pembelian,
                'penjualan' => $penjualan,
                'kartu_piutang' => $kartuPiutang,
                'kartu_hutang' => $kartuHutang,
                'kartu_dpsales' => $kartuDPSales,
                'kartu_inventory' => $kartuInventory,
                'kartu_bdd' => $kartuBDD,
                'kartu_stock' => $kartuStock,
                'kartu_bdp' => $kartuBDP,
                'kartu_bahan_jadi' => $kartuBahanJadi,
                'kartu_in_transit' => $kartuInTransit
            ]
        ));
        $this->info(json_encode($st));
        if ($st['status'] == 1) {
            foreach ($st['msg'] as $val) {
                $this->info($val['keterangan'] . ' => ' . $val['hasil']);
                $this->info($val['data1'] . ' vs ' . $val['data2']);
                $this->info('-----------------------------------');
            }
        }else{
            $this->error('Error: ' . $st['msg']);
        }
    }
}
