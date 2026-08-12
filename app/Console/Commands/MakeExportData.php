<?php

namespace App\Console\Commands;

use App\Exports\MultiSheetReportExport;
use App\Http\Controllers\ExcelExportController;
use App\Models\BackgroundProcess;
use App\Models\BookJournal;
use App\Models\FinalReport;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class MakeExportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:export-data {month?} {year?} {bookid?} {singkat=0} {force=0}';

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
        try {
            $month = $this->argument('month') ?? now()->format('m');
            $year = $this->argument('year') ?? now()->format('Y');
            $bookid = $this->argument('bookid');
            $singkat = $this->argument('singkat') == 1 ? true : false;
            $force = $this->argument('force') == 1 ? true : false;
            $bgprocess = null;
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

            $neraca = Cache::get('export_data_neraca_' . $bookid . '_' . $year . '_' . $month);
            if (!$neraca || $force) {
                $neraca = ExcelExportController::getDataNeraca($month, $year);
            }
            Cache::set('export_data_neraca_' . $bookid . '_' . $year . '_' . $month, $neraca, $timeout);
            $bgprocess->success();
            $this->info('process data neraca finished.');


            $this->info('process data NL..');
            $bgprocess->stage_process = 'process data nl..';
            $bgprocess->save();
            $nl = Cache::get('export_data_nl_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$nl || $force) {
                $nl = ExcelExportController::getDataNL($month, $year);
            }
            Cache::set('export_data_nl_' . $bookid . '_' . $year . '_' . $month, $nl, $timeout);

            $bgprocess->success();
            $this->info('process data NL finished.');

            $this->info('process data laporan rugi laba..');
            $bgprocess->stage_process = 'process data lr..';
            $bgprocess->save();
            $lr = Cache::get('export_data_lr_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$lr || $force) {
                $lr = ExcelExportController::getDataLR($month, $year);
            }
            Cache::set('export_data_lr_' . $bookid . '_' . $year . '_' . $month, $lr, $timeout);
            $bgprocess->success();
            $this->info('process data laporan rugi laba finished.');

            $this->info('process data kas..');
            $bgprocess->stage_process = 'process data kas..';
            $bgprocess->save();
            $kas = Cache::get('export_data_kas_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kas || $force) {
                $kas = ExcelExportController::getBukuKas($month, $year);
            }
            Cache::set('export_data_kas_' . $bookid . '_' . $year . '_' . $month, $kas, $timeout);
            $bgprocess->success();
            $this->info('process data kas finished.');

            $this->info('process data memo..');
            $bgprocess->stage_process = 'process data memo..';
            $bgprocess->save();
            $memo = Cache::get('export_data_memo_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$memo || $force) {
                $memo = ExcelExportController::getBukuMemo($month, $year, $singkat);
            }
            Cache::set('export_data_memo_' . $bookid . '_' . $year . '_' . $month, $memo, $timeout);
            $bgprocess->success();
            $this->info('process data memo finished.');

            $this->info('process data pembelian..');
            $bgprocess->stage_process = 'process data pembelian..';
            $bgprocess->save();
            $pembelian = Cache::get('export_data_pembelian_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$pembelian || $force) {
                $pembelian = ExcelExportController::getPembelian($month, $year);
            }
            Cache::set('export_data_pembelian_' . $bookid . '_' . $year . '_' . $month, $pembelian, $timeout);
            $bgprocess->success();
            $this->info('process data pembelian finished.');

            $this->info('process data penjualan..');
            $bgprocess->stage_process = 'process data penjualan..';
            $bgprocess->save();
            $penjualan = Cache::get('export_data_penjualan_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$penjualan || $force) {
                $penjualan = ExcelExportController::getPenjualan($month, $year);
            }
            Cache::set('export_data_penjualan_' . $bookid . '_' . $year . '_' . $month, $penjualan, $timeout);
            $bgprocess->success();
            $this->info('process data penjualan finished.');


            $this->info('process data kartu piutang..');
            $bgprocess->stage_process = 'process data kartu piutang..';
            $bgprocess->save();

            $kartuPiutang = Cache::get('export_data_kartu_piutang_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuPiutang || $force) {
                $kartuPiutang = ExcelExportController::getKartuPiutang($month, $year);
            }
            Cache::set('export_data_kartu_piutang_' . $bookid . '_' . $year . '_' . $month, $kartuPiutang, $timeout);
            $bgprocess->success();
            $this->info('process data kartu piutang finished.');

            $this->info('process data kartu hutang..');
            $bgprocess->stage_process = 'process data kartu hutang..';
            $bgprocess->save();
            $kartuHutang = Cache::get('export_data_kartu_hutang_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuHutang || $force) {
                $kartuHutang = ExcelExportController::getKartuHutang($month, $year);
            }
            Cache::set('export_data_kartu_hutang_' . $bookid . '_' . $year . '_' . $month, $kartuHutang, $timeout);
            $bgprocess->success();
            $this->info('process data kartu hutang finished.');

            $this->info('process data kartu dp sales..');
            $bgprocess->stage_process = 'process data kartu dp sales..';
            $bgprocess->save();

            $kartuDPSales = Cache::get('export_data_kartu_dp_sales_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuDPSales || $force) {
                $kartuDPSales = ExcelExportController::getKartuDPSales($month, $year);
            }
            Cache::set('export_data_kartu_dp_sales_' . $bookid . '_' . $year . '_' . $month, $kartuDPSales, $timeout);
            $bgprocess->success();
            $this->info('process data kartu dp sales finished.');

            $this->info('process data kartu inventory..');
            $bgprocess->stage_process = 'process data kartu inventory..';
            $bgprocess->save();

            $kartuInventory = Cache::get('export_data_kartu_inventory_' . $bookid . '_' . $year) ?? null;
            if (!$kartuInventory || $force) {
                $kartuInventory = ExcelExportController::getKartuInventory($year);
            }
            Cache::set('export_data_kartu_inventory_' . $bookid . '_' . $year, $kartuInventory, $timeout);
            $bgprocess->success();
            $this->info('process data kartu inventory finished.');

            $this->info('process data kartu bdd..');
            $bgprocess->stage_process = 'process data kartu bdd..';
            $bgprocess->save();

            $kartuBDD = Cache::get('export_data_kartu_bdd_' . $bookid . '_' . $year) ?? null;
            if (!$kartuBDD || $force) {
                $kartuBDD = ExcelExportController::getKartuBDD($year);
            }
            Cache::set('export_data_kartu_bdd_' . $bookid . '_' . $year, $kartuBDD, $timeout);
            $bgprocess->success();
            $this->info('process data kartu bdd finished.');

            $this->info('process data kartu stock..');
            $bgprocess->stage_process = 'process data kartu stock..';
            $bgprocess->save();
            $kartuStock = Cache::get('export_data_kartu_stock_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuStock || $force) {
                $kartuStock = ExcelExportController::getKartuStock($month, $year);
            }
            Cache::set('export_data_kartu_stock_' . $bookid . '_' . $year . '_' . $month, $kartuStock, $timeout);

            $bgprocess->success();
            $this->info('process data kartu stock finished.');

            $this->info('process data kartu bdp..');
            $bgprocess->stage_process = 'process data kartu bdp..';
            $bgprocess->save();

            $kartuBDP = Cache::get('export_data_kartu_bdp_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuBDP || $force) {
                $kartuBDP = ExcelExportController::getKartuBDP($month, $year);
            }
            Cache::set('export_data_kartu_bdp_' . $bookid . '_' . $year . '_' . $month, $kartuBDP, $timeout);
            $bgprocess->success();
            $this->info('process data kartu bdp finished.');

            $this->info('process data kartu bahan jadi..');
            $bgprocess->stage_process = 'process data kartu bahan jadi..';
            $bgprocess->save();

            $kartuBahanJadi = Cache::get('export_data_kartu_bahan_jadi_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuBahanJadi || $force) {
                $kartuBahanJadi = ExcelExportController::getKartuBahanJadi($month, $year);
            }
            Cache::set('export_data_kartu_bahan_jadi_' . $bookid . '_' . $year . '_' . $month, $kartuBahanJadi, $timeout);
            $bgprocess->success();
            $this->info('process data kartu bahan jadi finished.');

            $this->info('process data kartu in transit..');
            $bgprocess->stage_process = 'process data kartu in transit..';
            $bgprocess->save();

            $kartuInTransit = Cache::get('export_data_kartu_in_transit_' . $bookid . '_' . $year . '_' . $month) ?? null;
            if (!$kartuInTransit || $force) {
                $kartuInTransit = ExcelExportController::getKartuInTransit($month, $year);
            }
            Cache::set('export_data_kartu_in_transit_' . $bookid . '_' . $year . '_' . $month, $kartuInTransit, $timeout);
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
        } catch (\Exception $e) {
            $this->info('error make export data: ' . $e->getMessage());
            if ($bgprocess) {
                $bgprocess->stage_process = 'error make export data: ' . $e->getMessage();
                $bgprocess->save();
                $bgprocess->failure();
            }
        }
    }
}
