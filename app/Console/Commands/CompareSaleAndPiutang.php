<?php

namespace App\Console\Commands;

use App\Models\KartuPiutang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CompareSaleAndPiutang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:sale-and-piutang {monthyear}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare Sale and Piutang';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Session::put('book_journal_id', 2);
        $monthyear = $this->argument('monthyear');
        $startIndex = createCarbon($monthyear . '-01')->startOfMonth()->format('ymdHis000');
        $endIndex = createCarbon($monthyear . '-01')->endOfMonth()->format('ymdHis999');
        $kps = KartuPiutang::whereBetween('index_date', [$startIndex, $endIndex])
            ->leftJoin('invoice_packs as inv', 'inv.invoice_number', '=', 'kartu_piutangs.invoice_pack_number')
            ->where('kartu_piutangs.amount_debet','>',0)
            ->select(
                'kartu_piutangs.index_date',
                'kartu_piutangs.invoice_pack_number',
                DB::raw('sum(kartu_piutangs.amount_debet - kartu_piutangs.amount_kredit) as total_kartu'),
                'inv.invoice_number as inv_invoice_number',
                'inv.total_price as inv_total_price',
                'inv.created_at as inv_created_at'
            )
            ->groupBy('kartu_piutangs.invoice_pack_number')
            ->get();

        $keys = ['index_date', 'invoice_pack_number', 'total_kartu', 'inv_invoice_number', 'inv_total_price', 'inv_created_at'];
        $datas = [];
        $datas[] = $keys;
        foreach ($kps as $kp) {
            $datas[] = [
                $kp->index_date,
                $kp->invoice_pack_number,
                $kp->total_kartu,
                $kp->inv_invoice_number,
                $kp->inv_total_price,
                $kp->inv_created_at
            ];
        }
        $file = public_path('files/piutang-sales.csv');
        $handle = fopen($file, 'w');
        foreach ($datas as $d) {
            fputcsv($handle, $d);
        }
        fclose($handle);
        $this->info('File CSV has been created at: ' . $file);
    }
}
