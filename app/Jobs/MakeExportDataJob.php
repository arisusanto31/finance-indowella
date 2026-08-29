<?php

namespace App\Jobs;

use App\Exports\MultiSheetReportExport;
use App\Http\Controllers\ExcelExportController;
use App\Models\BackgroundProcess;
use App\Models\BookJournal;
use App\Models\FinalReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class MakeExportDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $month, $year, $bookid, $singkat, $force;
    public function __construct($month, $year, $bookid, $singkat, $force)
    {
        $this->month = $month;
        $this->year = $year;
        $this->bookid = $bookid;
        $this->singkat = $singkat;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //

        try {
            $month = $this->month;
            $year = $this->year;
            $bookid = $this->bookid;
            $singkat = $this->singkat;
            $force = $this->force;
            Session::put('book_journal_id', $bookid);
            $thebook = BookJournal::find($bookid);
            $bookname = $thebook ? str_replace('buku ', '', $thebook->name) : 'unknown';
            $timeout = 3600; // 1 hour

            $this->info('start make export data for bookid: ' . $bookid . ' month: ' . $month . ' year: ' . $year);
            // $memo = ExcelExportController::getBukuMemo($month, $year, $singkat);
            // foreach ($memo['msg'] as $code => $data) {
            //     $this->info('code: ' . $code . '| '.$memo['chart_accounts'][$code].' | total data: ' . count($data));
            //     tampilkanTableTerminal($data, [
            //         'created_at' => 'center',
            //         'lawan_code_name' => 'left',
            //         'description' => 'left',
            //         'amount_debet' => 'right',
            //         'amount_kredit' => 'right',
            //         'amount_saldo' => 'right',
            //     ], $this);
            // }


            $bgprocess = BackgroundProcess::make($bookid, 'admin/jurnal/mutasi', 'Export buku ' . $bookname . ' periode ' . $year . '-' . $month, 17);
            $bgprocess->stage_process = 'process data neraca..';
            $bgprocess->save();
            $this->info('process data neraca..');


            $neraca = $force ? null : (unserialize(Redis::get('export_data_neraca_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$neraca || $force) {
                $neraca = ExcelExportController::getDataNeraca($month, $year);
            }
            Redis::setex('export_data_neraca_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($neraca));
            $bgprocess->success();
            $this->info('process data neraca finished.');


            $this->info('process data NL..');
            $bgprocess->stage_process = 'process data nl..';
            $bgprocess->save();
            $nl = $force ? null : (unserialize(Redis::get('export_data_nl_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$nl || $force) {
                $nl = ExcelExportController::getDataNL($month, $year);
            }
            Redis::setex('export_data_nl_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($nl));

            $bgprocess->success();
            $this->info('process data NL finished.');

            $this->info('process data laporan rugi laba..');
            $bgprocess->stage_process = 'process data lr..';
            $bgprocess->save();
            $lr = $force ? null : (unserialize(Redis::get('export_data_lr_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$lr || $force) {
                $lr = ExcelExportController::getDataLR($month, $year);
            }
            Redis::setex('export_data_lr_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($lr));
            $bgprocess->success();
            $this->info('process data laporan rugi laba finished.');

            $this->info('process data kas..');
            $bgprocess->stage_process = 'process data kas..';
            $bgprocess->save();
            $kas = $force ? null : (unserialize(Redis::get('export_data_kas_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kas || $force) {
                $kas = ExcelExportController::getBukuKas($month, $year);
            }
            Redis::setex('export_data_kas_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kas));
            $bgprocess->success();
            $this->info('process data kas finished.');

            $this->info('process data memo..');
            $bgprocess->stage_process = 'process data memo..';
            $bgprocess->save();
            $memo = $force ? null : (unserialize(Redis::get('export_data_memo_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$memo || $force) {
                $memo = ExcelExportController::getBukuMemo($month, $year, $singkat);
            }
            Redis::setex('export_data_memo_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($memo));
            $bgprocess->success();
            $this->info('process data memo finished.');

            $this->info('process data pembelian..');
            $bgprocess->stage_process = 'process data pembelian..';
            $bgprocess->save();
            $pembelian = $force ? null : (unserialize(Redis::get('export_data_pembelian_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$pembelian || $force) {
                $pembelian = ExcelExportController::getPembelian($month, $year);
            }
            Redis::setex('export_data_pembelian_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($pembelian));
            $bgprocess->success();
            $this->info('process data pembelian finished.');

            $this->info('process data penjualan..');
            $bgprocess->stage_process = 'process data penjualan..';
            $bgprocess->save();
            $penjualan = $force ? null : (unserialize(Redis::get('export_data_penjualan_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$penjualan || $force) {
                $penjualan = ExcelExportController::getPenjualan($month, $year);
            }
            Redis::setex('export_data_penjualan_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($penjualan));
            $bgprocess->success();
            $this->info('process data penjualan finished.');


            $this->info('process data kartu piutang..');
            $bgprocess->stage_process = 'process data kartu piutang..';
            $bgprocess->save();

            $kartuPiutang = $force ? null : (unserialize(Redis::get('export_data_kartu_piutang_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuPiutang || $force) {
                $kartuPiutang = ExcelExportController::getKartuPiutang($month, $year);
            }
            Redis::setex('export_data_kartu_piutang_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuPiutang));

            $bgprocess->success();
            $this->info('process data kartu piutang finished.');

            $this->info('process data kartu hutang..');
            $bgprocess->stage_process = 'process data kartu hutang..';
            $bgprocess->save();
            $kartuHutang = $force ? null : (unserialize(Redis::get('export_data_kartu_hutang_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuHutang || $force) {
                $kartuHutang = ExcelExportController::getKartuHutang($month, $year);
            }
            Redis::setex('export_data_kartu_hutang_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuHutang));
            $bgprocess->success();
            $this->info('process data kartu hutang finished.');

            $this->info('process data kartu dp sales..');
            $bgprocess->stage_process = 'process data kartu dp sales..';
            $bgprocess->save();

            $kartuDPSales = $force ? null : (unserialize(Redis::get('export_data_kartu_dp_sales_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuDPSales || $force) {
                $kartuDPSales = ExcelExportController::getKartuDPSales($month, $year);
            }
            Redis::setex('export_data_kartu_dp_sales_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuDPSales));
            $bgprocess->success();
            $this->info('process data kartu dp sales finished.');

            $this->info('process data kartu inventory..');
            $bgprocess->stage_process = 'process data kartu inventory..';
            $bgprocess->save();

            $kartuInventory = $force ? null : (unserialize(Redis::get('export_data_kartu_inventory_' . $bookid . '_' . $year)) ?? null);
            if (!$kartuInventory || $force) {
                $kartuInventory = ExcelExportController::getKartuInventory($month, $year);
            }
            Redis::setex('export_data_kartu_inventory_' . $bookid . '_' . $year, $timeout, serialize($kartuInventory));
            $bgprocess->success();
            $this->info('process data kartu inventory finished.');

            $this->info('process data kartu bdd..');
            $bgprocess->stage_process = 'process data kartu bdd..';
            $bgprocess->save();

            $kartuBDD = $force ? null : (unserialize(Redis::get('export_data_kartu_bdd_' . $bookid . '_' . $year)) ?? null);
            if (!$kartuBDD || $force) {
                $kartuBDD = ExcelExportController::getKartuBDD($month, $year);
            }
            Redis::setex('export_data_kartu_bdd_' . $bookid . '_' . $year, $timeout, serialize($kartuBDD));
            $bgprocess->success();
            $this->info('process data kartu bdd finished.');

            $this->info('process data kartu stock..');
            $bgprocess->stage_process = 'process data kartu stock..';
            $bgprocess->save();
            $kartuStock = $force ? null : (unserialize(Redis::get('export_data_kartu_stock_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuStock || $force) {
                $kartuStock = ExcelExportController::getKartuStock($month, $year);
            }
            Redis::setex('export_data_kartu_stock_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuStock));

            $bgprocess->success();
            $this->info('process data kartu stock finished.');

            $this->info('process data kartu bdp..');
            $bgprocess->stage_process = 'process data kartu bdp..';
            $bgprocess->save();

            $kartuBDP = $force ? null : (unserialize(Redis::get('export_data_kartu_bdp_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuBDP || $force) {
                $kartuBDP = ExcelExportController::getKartuBDP($month, $year);
            }
            Redis::setex('export_data_kartu_bdp_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuBDP));
            $bgprocess->success();
            $this->info('process data kartu bdp finished.');

            $this->info('process data kartu bahan jadi..');
            $bgprocess->stage_process = 'process data kartu bahan jadi..';
            $bgprocess->save();

            $kartuBahanJadi = $force ? null : (unserialize(Redis::get('export_data_kartu_bahan_jadi_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuBahanJadi || $force) {
                $kartuBahanJadi = ExcelExportController::getKartuBahanJadi($month, $year);
            }
            Redis::setex('export_data_kartu_bahan_jadi_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuBahanJadi));
            $bgprocess->success();
            $this->info('process data kartu bahan jadi finished.');

            $this->info('process data kartu in transit..');
            $bgprocess->stage_process = 'process data kartu in transit..';
            $bgprocess->save();

            $kartuInTransit = $force ? null : (unserialize(Redis::get('export_data_kartu_in_transit_' . $bookid . '_' . $year . '_' . $month)) ?? null);
            if (!$kartuInTransit || $force) {
                $kartuInTransit = ExcelExportController::getKartuInTransit($month, $year);
            }
            Redis::setex('export_data_kartu_in_transit_' . $bookid . '_' . $year . '_' . $month, $timeout, serialize($kartuInTransit));

            $bgprocess->success();
            $this->info('process data kartu in transit finished.');

            $filename = $bookname . '_' . $year . '-' . $month . '.xlsx';
            $path = 'final_report/' . $filename;
            $bgprocess->stage_process = 'rendering excel file..';
            $bgprocess->save();
            $this->info('rendering excel file..');


            Excel::store(
                new MultiSheetReportExport(
                    $month,
                    $year,
                    $neraca,
                    $nl,
                    $lr,
                    $kas,
                    $memo,
                    $pembelian,
                    $penjualan,
                    $kartuPiutang,
                    $kartuHutang,
                    $kartuDPSales,
                    $kartuInventory,
                    $kartuBDD,
                    $kartuStock,
                    $kartuBDP,
                    $kartuBahanJadi,
                    $kartuInTransit
                ),
                $path,
                'public'
            );
            $final = FinalReport::createData(new Request([
                'book_journal_id' => $bookid,
                'month' => $month,
                'year' => $year,
                'file_path' => $path
            ]));
            $bgprocess->success();
            $bgprocess->stage_process = 'final report saved..';
            $bgprocess->status = 'finished';
            $bgprocess->save();
            $this->info('final report successfully created..');
        } catch (Throwable $e) {
            $this->info('error make export data: ' . $e->getMessage());
            if ($bgprocess) {
                $bgprocess->stage_process = 'error make export data: ' . $e->getMessage();
                $bgprocess->save();
                $bgprocess->failure();
            }
        }
    }

    function info($msg)
    {
        info('[' . date('Y-m-d H:i:s') . '] ' . $msg);
    }
}
