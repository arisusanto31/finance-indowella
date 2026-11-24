<?php

namespace App\Console\Commands;

use App\Models\DetailKartuInvoice;
use App\Models\Journal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UpdateValueJournalKartu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:value-journal-kartu {journalid}';

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
        $journalid = $this->argument('journalid');
        $journal = DB::table('journals')->find($journalid);
        if (!$journal) {
            $this->info('tidak ditemukan jurnal nya');
            return;
        } else {
            Session::put('book_journal_id', $journal->book_journal_id);
            $this->info('jurnal ditemukan. AMOUNT JOURNAL ' . ($journal->amount_debet - $journal->amount_kredit));
        }
        $dk = DetailKartuInvoice::where('journal_id', $journalid)->get();
        $this->info('ditemukan link sebanyak ' . $dk->count());
        foreach ($dk as $d) {
            $this->info('kartu type: ' . $d->kartu_type . ' - kartu id:' . $d->kartu_id . ' amount :' . $d->amount_journal);
        }

        $this->info('\ncoba cari kartu dari jurnal ini');
        $dkfirst = collect($dk)->first();
        $kartu = $dkfirst->kartu_type::where('journal_id', $journalid)->get();
        $this->info('ditemukan kartu sebanyak ' . $kartu->count());
        foreach ($kartu as $k) {
            $this->info('kartu id: ' . $k->id . ' AMOUNT : ' . $k->mutasi_rupiah_total);
            $thek = (new \ReflectionClass($k))->newInstanceWithoutConstructor();
            $thek = $thek::find($k->id);
            $thek->createDetailKartuInvoice();
        }
        $this->info('nilai amount kartu=' . $kartu->sum('mutasi_rupiah_total'));
        if ($kartu->count() == 1 && $kartu->first()->mutasi_rupiah_total != ($journal->amount_debet - $journal->amount_kredit)) {
            if ($this->confirm('DATA KARTU GA BALANCE? MAU EDIT?')) {
                $k = $kartu->first();
                $k->mutasi_rupiah_total = $journal->amount_debet - $journal->amount_kredit;
                $k->mutasi_rupiah_on_unit = ($journal->amount_debet - $journal->amount_kredit) / ($k->mutasi_quantity);
                $k->save();
                $this->info('data kartu berhasil diupdate');
            }
        }
    }
}
