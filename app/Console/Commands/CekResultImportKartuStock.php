<?php

namespace App\Console\Commands;

use App\Models\KartuStock;
use App\Models\TaskImportDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class CekResultImportKartuStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:result-import-kartu-stock {bookid} {importid}';

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
        $importid = $this->argument('importid');
        Session::put('book_journal_id', $bookid);
        $taskimports = TaskImportDetail::where('task_import_id', $importid)->where('type', 'kartu_stock')
            ->select('payload', 'request_date','id')
            ->get();

        $diff = [];
        $all=[];
        foreach ($taskimports as $task) {
            $payload = json_decode($task->payload, true);
            $ks = KartuStock::join('stocks', 'stocks.id', 'kartu_stocks.stock_id')
                ->where('stocks.reference_stock_id', $payload['ref_id'])
                ->whereDate('kartu_stocks.created_at', $task->request_date)
                ->where('kartu_stocks.tag', 'init_import2025-12-31T23:59')
                ->select('kartu_stocks.mutasi_rupiah_total', 'stocks.name', 'stocks.reference_stock_id')->first();
            $ksname = $ks ? $ks->name : 'not found';
            $ksamount = $ks ? $ks->mutasi_rupiah_total : 0;
            $payloadAmount= $payload['amount'] ?? 0;
            $payloadAmount= round($payloadAmount,2);
            if (round($payloadAmount, 2) != round($ksamount, 2)) {
                $diff[] = [
                    'ref_id' => $payload['ref_id'],
                    'payload_name' => $payload['name'],
                    'ks_name' => $ksname,
                    'amount_import' => round($payloadAmount,2),
                    'amount_ks' => round($ksamount,2),
                    'selisih' => round($payloadAmount,2) - round($ksamount,2)
                ];
                $thetask= TaskImportDetail::find($task->id);
                $thetask->status='queue';
                $thetask->save();

            }
            $all[] = [
                'ref_id' => $payload['ref_id'],
                'payload_name' => $payload['name'],
                'ks_name' => $ksname,
                'amount_import' => round($payloadAmount,2),
                'amount_ks' => round($ksamount,2),
                'selisih' => round($payloadAmount,2) - round($ksamount,2)
            ];
        }
        tampilkanTableTerminal(
            $diff,
            [
                'ref_id' => 'center',
                'payload_name' => 'left',
                'ks_name' => 'left',
                'amount_import' => 'right',
                'amount_ks' => 'right'
            ],
            $this
        );
        $this->info('total amount task '.array_sum(array_column($all,'amount_import')));
        $this->info('total amount kartu stock '.array_sum(array_column($all,'amount_ks')));
        $this->info('total amount diff '. array_sum(array_column($diff,'selisih')));
    }
}
