<?php

namespace App\Jobs;

use App\Http\Controllers\StockController;
use App\Models\KartuStock;
use App\Models\ManufStock;
use App\Models\RetailStock;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use App\Models\TaskImportDetail;
use App\Services\ContextService;
use CustomLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportKartuStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected $taskID;
    public function __construct($taskID)
    {
        //
        $this->taskID = $taskID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        CustomLogger::log('journal', 'info', 'ImportKartuStockJob-' . $this->taskID);

        $task = TaskImportDetail::find($this->taskID);
        ContextService::setBookJournalID($task->book_journal_id);

        if ($task->status == 'success') {
            return;
        }
        $data = json_decode($task->payload, true);
        $qty = $data['quantity'];
        $unit = $data['unit'];
        info('data :' . json_encode($data));
        $lawanCode = 301000;
        $bookModel = $task->book_journal_id == 1 ? ManufStock::class : RetailStock::class;
        info('ref_id:' . intval($data['ref_id']));
        info('book model:' . $bookModel);
        $stock = Stock::where('reference_stock_id', intval($data['ref_id']))
            ->where('reference_stock_type', $bookModel)
            ->first();
        if (!$stock)
            $stock = Stock::where('name', $data['name'])->first();
        info('stock terdaftar:' . json_encode($stock));

        try {


            if (!$stock) {
                if ($task->book_journal_id == 1) {
                    $manufStock = ManufStock::where('name', $data['name'])->with(['parentCategory', 'category', 'units'])->first();
                    if ($manufStock) {
                        $manufStock['units_manual'] = $manufStock->getUnits();
                        $st = StockController::sync(new Request([
                            'book_journal_id' => 1,
                            'data' => $manufStock,
                            'stock_id' => $manufStock->id,
                        ]));
                        if ($st['status'] == 0) {
                            throw new \Exception($st['msg']);
                        }
                        $stock = $st['msg'];
                    }
                } else if ($task->book_journal_id == 2) {
                    $retailStock = RetailStock::where('name', $data['name'])->with(['parentCategory', 'category', 'units'])->first();
                    if ($retailStock) {
                        $retailStock['units_manual'] = $retailStock->getUnits();
                        $st = StockController::sync(new Request([
                            'book_journal_id' => 2,
                            'data' => $retailStock,
                            'stock_id' => $retailStock->id,
                        ]));
                        if ($st['status'] == 0) {
                            throw new \Exception($st['msg']);
                        }
                        $stock = $st['msg'];
                    }
                }

                if (!$stock) {

                    //nah disini buat stock nih
                    $unitBackend = 'Pcs';
                    if ($unit == 'Kg' || $unit == 'Gram') {
                        $unitBackend = 'Gram';
                    }
                    if ($unit == 'Meter' || $unit == 'Cm' || $unit == 'm') {
                        $unitBackend = 'Meter';
                    }

                    $unknownCat = StockCategory::where('name', 'unknown')->first();
                    if (!$unknownCat) {
                        $unknownCat = StockCategory::create([
                            'name' => 'unknown',
                            'parent_id' => null
                        ]);
                    }
                    $stockNameFirst = explode(' ', $data['name'])[0];
                    $category = StockCategory::where('name', 'like', '%' . $stockNameFirst . '%')->first();
                    if (!$category) {
                        $category = $unknownCat;
                    }
                    $stock = Stock::create([
                        'name' => $data['name'],
                        'unit_backend' => $unitBackend,
                        'parent_category_id' => $category->parent_id,
                        'category_id' => $category->id,
                        'unit_default' => $data['unit'],
                        'book_journal_id' => $task->book_journal_id,
                    ]);
                    StockUnit::create([
                        'stock_id' => $stock->id,
                        'unit' => $data['unit'],
                        'konversi' => 1,
                    ]);
                }
            }
            DB::beginTransaction();
            if ($task->processed_at == null)
                $task->processed_at = now();

            $journal = TaskImportDetail::where('task_import_id', $task->task_import_id)->whereNotNull('journal_number')->first();
            $journalNumber = $journal ? $journal->journal_number : null;
            if ($data['amount'] > 0) {
                info('try to create kartu stock');
                $stStock = KartuStock::mutationStore(new Request([
                    'stock_id' => $stock->id,
                    'mutasi_qty_backend' => floatval($qty),
                    'unit_backend' => $unit,
                    'mutasi_quantity' => floatval($qty),
                    'unit' => $unit,
                    'flow' => 0,
                    'sales_order_number' => 'INITAWAL',
                    'code_group' => 140001,
                    'lawan_code_group' => $lawanCode,
                    'is_otomatis_jurnal' => 0,
                    'journal_number' => $journalNumber,
                    'is_custom_rupiah' => 1,
                    'mutasi_rupiah_total' => floatval($data['amount']),

                ]), false);
                info('hasil dari kartu stcok:' . json_encode($stStock));
                if ($stStock['status'] == 0) {
                    throw new \Exception($stStock['msg']);
                }
            }

            $task->status = 'success';
            $task->error_message = "";
            $task->journal_number = $journalNumber;
            $task->finished_at = now();
            $task->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($task->processed_at == null)
                $task->processed_at = now();
            $task->error_message = $e->getMessage();
            $task->status = 'failed';
            $task->save();
        }
    }
}
