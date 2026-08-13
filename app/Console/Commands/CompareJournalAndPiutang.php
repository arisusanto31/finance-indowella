<?php

namespace App\Console\Commands;

use App\Http\Controllers\JournalController;
use App\Models\ChartAccountAlias;
use App\Models\Journal;
use App\Models\KartuPiutang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CompareJournalAndPiutang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:journal-and-piutang {monthyear}';

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
        Session::put('book_journal_id', 2);
        $monthyear = $this->argument('monthyear');
        $startIndex = createCarbon($monthyear . '-01')->startOfMonth()->format('ymdHis00');
        $endIndex = createCarbon($monthyear . '-01')->endOfMonth()->format('ymdHis99');
        $codePiutang = ChartAccountAlias::aktif()->child()->where('reference_model', KartuPiutang::class)->pluck('code_group')->all();
        $this->info('codePiutang :' . json_encode($codePiutang));
        $journals = Journal::whereBetween('index_date', [$startIndex, $endIndex])->whereIn('code_group', $codePiutang);
        $journals = Journal::fromSub($journals, 'journals')->leftJoin('detail_kartu_invoices as dk', function ($join) {
            $join->on('dk.journal_id', '=', 'journals.id');
        })->leftJoin('kartu_piutangs as kp', function ($join) {
            $join->on('kp.id', '=', 'dk.kartu_id')->where('dk.kartu_type', KartuPiutang::class);
        })->select(
            'journals.id',
            'journals.tag',
            'journals.journal_number',
            'journals.index_date',
            'journals.code_group',
            DB::raw('journals.amount_debet-journals.amount_kredit as amount_journal'),
            'kp.id as kartu_piutang_id',
            DB::raw('kp.amount_debet-kp.amount_kredit as amount_kartu_piutang'),
            'kp.index_date as kartu_piutang_index_date',
        )->whereNull('journals.tag')->get();

        $keys = ['journal_id', 'journal_index_date', 'code_group', 'amount_journal', 'kartu_piutang_id', 'amount_kartu_piutang', 'kartu_piutang_index_date'];
        $datas = [];
        $datas[] = $keys;
        foreach ($journals as $j) {
            $datas[] = [
                $j->id,
                $j->index_date,
                $j->code_group,
                $j->amount_journal,
                $j->kartu_piutang_id,
                $j->amount_kartu_piutang,
                $j->kartu_piutang_index_date,
            ];
        }


        $problems = collect($journals)->filter(function ($item) {
            return !$item->amount_kartu_piutang;
        });

        tampilkanTableTerminal($problems, [
            'id' => 'center',
            'tag' => 'center',
            'index_date' => 'center',
            'journal_number' => 'center',
            'code_group' => 'center',
            'amount_journal' => 'right',
            'kartu_piutang_id' => 'center',
            'amount_kartu_piutang' => 'right',
            'kartu_piutang_index_date' => 'center',

        ], $this);

        // if ($this->confirm('apa mau delete yang ga ada kartu_piutangnya')) {
            // foreach ($problems as $p) {
            //     $st = JournalController::destroy($p->id,1);
            //     if ($st['status'] == 1) {
            //         $this->info('deleted journal id: ' . $p->id);
            //     } else {
            //         $this->error('failed to delete journal id: ' . $p->id.':'.$st['msg']);
            //     }
            // }
        // }


        if (count($journals) > 0) {
            $file = public_path('files/journal-piutang.csv');

            $handle = fopen($file, 'w');
            foreach ($datas as $d) {
                fputcsv($handle, $d);
            }
            fclose($handle);
            $this->info('File CSV has been created at: ' . $file);
        }
    }
}
