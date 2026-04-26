<?php

namespace App\Console\Commands;

use App\Models\ChartAccount;
use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session as FacadesSession;

class MoveJournalCodeAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:journal-code-account {booksession} {from_code} {to_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'memindah kode akun di jurnal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $booksession = $this->argument('booksession');
        $fromCode = $this->argument('from_code');
        $toCode = $this->argument('to_code');
        FacadesSession::put('book_journal_id', $booksession);
        $js= Journal::where('code_group',$fromCode)->get();
        $chartID= ChartAccount::pluck('id','code_group')->all();
        foreach($js as $j){
            $j->code_group= $toCode;
            $j->chart_account_id = $chartID[$toCode] ?? null;
            $j->save();
        }
        $ljs= Journal::where('lawan_code_group',$fromCode)->get();
        foreach($ljs as $lj){
            $lj->lawan_code_group= $toCode;
            $lj->save();
        }
        $this->info('Selesai memindahkan kode akun dari '.$fromCode.' ke '.$toCode.' sebanyak '.$js->count().' jurnal dan '.$ljs->count().' lawan jurnal');
    }
}
