<?php

namespace App\Jobs;

use App\Http\Controllers\JournalController;
use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\TaskImportDetail;
use App\Services\ContextService;
use App\Services\LockManager;
use CustomLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportSaldoNLJob implements ShouldQueue
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
        info('worker jalan ' . $this->taskID);
        CustomLogger::log('journal', 'info', 'ImportSaldoNLJob-' . $this->taskID);

        try {
            $task = TaskImportDetail::find($this->taskID);
            if ($task->status == 'success') {
                return;
            }
            DB::beginTransaction();
            ContextService::setBookJournalID($task->book_journal_id);

            $data = json_decode($task->payload, true);
            $codeGroup = $data['code_group'];
            $amount = $data['amount'];
            $date = $data['date'];
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            $codeName = $chart ? $chart->code_group : null;
            $lawanCode = 301000;
            if ($amount == 0) {
                $task->status = 'success';
                $task->finished_at = now();
                $task->save();
                DB::commit();
            } else {
                if ($amount > 0) {
                    //kalo aset debet, kalo liabilitas kredit
                    if ($codeGroup > 400000) {
                        //liabilitas
                        $codeKredit = $codeGroup;
                        $codeDebet = $lawanCode;
                    } else {
                        $codeDebet = $codeGroup;
                        $codeKredit = $lawanCode;
                    }
                } else {
                    if ($codeGroup > 400000) {
                        //liabilitas
                        $codeKredit = $lawanCode;
                        $codeDebet = $codeGroup;
                    } else {
                        //aset
                        $codeDebet = $lawanCode;
                        $codeKredit = $codeGroup;
                    }
                }
                $kredits = [
                    [
                        'code_group' => $codeKredit,
                        'description' => 'init saldo awal ' . $date . ' akun ' . $codeName,
                        'amount' => abs($amount),
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $codeDebet,
                        'description' => 'init saldo awal ' . $date . ' akun ' . $codeName,
                        'amount' => abs($amount),
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'transaction',
                    'date' => $date,
                    'is_backdate' => 1,
                    'user_backdate_id' => 0,
                    'is_auto_generated' => 1,
                    'title' => 'create penerimaan penjualan',
                    'url_try_again' => null,
                    'book_journal_id' => $task->book_journal_id,


                ]), false);
                if ($task->processed_at == null)
                    $task->processed_at = now();
                if ($st['status'] == 0) {
                    throw new \Exception($st['msg']);
                }
                $task->status = 'success';
                $task->journal_number = $st['journal_number'];
                $journals = Journal::where('journal_number', $task->journal_number)->pluck('id')->all();
                $task->journal_id = json_encode($journals);
                $task->finished_at = now();
                $task->save();
                DB::commit();
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $task->status = "failed";
            $task->error_message = $e->getMessage();
            $task->save();
        }
    }
}
