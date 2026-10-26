<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class BatalkanProsesSOBulan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batalkan:proses-so-bulan {monthyear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan proses SO untuk bulan tertentu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $monthyear = $this->argument('monthyear');
        $dateAwal = createCarbon($monthyear . '-01')->startOfMonth();
        $dateAkhir = createCarbon($monthyear . '-01')->endOfMonth();
        Session::put('book_journal_id', 2);
        $sales = SalesOrder::whereBetween('created_at', [$dateAwal, $dateAkhir])->get();
        tampilkanTableTerminal(
            $sales,
            [
                'id' => 'center',
                'sales_order_number' => 'center',
                'created_at' => 'center',
            ],
            $this
        );
        if (!$this->confirm('lanjut ?')) {
            return;
        }
        foreach ($sales as $sale) {
            $sale->removeAllProcess();
            $sale->updateStatus();
            $this->info('batalkan proses SO ' . $sale->sales_order_number . ' : ' . $sale->created_at);
        }
    }
}
