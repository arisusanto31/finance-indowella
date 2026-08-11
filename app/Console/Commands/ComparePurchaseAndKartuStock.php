<?php

namespace App\Console\Commands;

use App\Models\ChartAccountAlias;
use App\Models\InvoicePurchaseDetail;
use App\Models\Journal;
use App\Models\KartuInTransit;
use App\Models\KartuStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class ComparePurchaseAndKartuStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:purchase-and-kartu-stock {monthyear}';

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
        //kita rapikan dudlu aja data peri
        Session::put('book_journal_id', 2);
        $monthyear = $this->argument('monthyear');
        $startIndex = createCarbon($monthyear . '-01')->startOfMonth()->format('ymdHis00');
        $endIndex = createCarbon($monthyear . '-01')->endOfMonth()->format('ymdHis99');
        //     $coa = [];
        //     $coa = array_merge($coa, ChartAccountAlias::where('reference_model', KartuStock::class)->pluck('code_group')->all());
        //     $coa = array_merge($coa, ChartAccountAlias::where('reference_model', KartuInTransit::class)->pluck('code_group')->all());
        //     $this->info('coa :' . json_encode($coa));
        //     $this->info('startIndex :' . $startIndex);
        //     $this->info('endIndex :' . $endIndex);
        //     $j = Journal::whereBetween('index_date', [$startIndex, $endIndex])
        //         ->whereIn('code_group', $coa);
        //     //ini versi jurnalnya
        //     $kartustock = Journal::fromSub($j, 'journals')->leftJoin('detail_kartu_invoices as dk', function ($join) {
        //         $join->on('dk.journal_id', '=', 'journals.id');
        //     })->leftJoin('kartu_stocks as ks1', function ($join) {
        //         $join->on('ks1.id', '=', 'dk.kartu_id')->where('dk.kartu_type', KartuStock::class);
        //     })->leftJoin('kartu_in_transits as ks2', function ($join) {
        //         $join->on('ks2.id', '=', 'dk.kartu_id')->where('dk.kartu_type', KartuInTransit::class);
        //     })
        //   ->select('dk.kartu_id', 'dk.kartu_type', 'amount_journal', 'journals.id', DB::raw('coalesce(ks2.purchase_order_id,ks1.purchase_order_id) as keydata'))
        //         ->get();
        //     //ini versi invoicepurchase
        //     $inv = InvoicePurchaseDetail::whereBetween('index_date', [$startIndex . '0', $endIndex . '9'])
        //         ->select(
        //             'kartu_stock_id as kartu_id',
        //             'kartu_stock_type as kartu_type',
        //             'total_price',
        //             DB::raw('id as keydata')
        //         )
        //         ->get();
        //     $this->info('list yang ada di jurnal');
        //     // tampilkanTableTerminal($kartustock, [
        //     //     'kartu_id' => 'center',
        //     //     'kartu_type' => 'center',
        //     //     'keydata' => 'center',
        //     //     'amount_journal' => 'right',
        //     // ], $this);
        //     // $this->info('list yang ada di invoice purchase');
        //     // tampilkanTableTerminal($inv, [
        //     //     'kartu_id' => 'center',
        //     //     'kartu_type' => 'center',
        //     //     'keydata' => 'center',
        //     //     'total_price' => 'right',
        //     // ], $this);
        //     $kartustock = collect($kartustock)->keyBy('keydata')->all();
        //     $inv = collect($inv)->keyBy('keydata')->all();
        //     $this->info('Total Kartu Stock in Journal: ' . count($kartustock));
        //     $this->info('Total Kartu Stock in Invoice Purchase: ' . count($inv));
        //     $this->info('yang ada di versi jurnal tapi tidak ada di versi invoice purchase:');
        //     $problemKartu=[];
        //     foreach ($kartustock as $key => $value) {
        //         if (!isset($inv[$key])) {
        //             $this->info('Kartu Stock ID: ' . $value->kartu_id . ' Type: ' . $value->kartu_type . ' Amount: ' . $value->amount_journal);
        //             $problemKartu[] = $value;
        //         }
        //     }
        //     $this->info('total problem kartu '.collect($problemKartu)->sum('amount_journal'));
        //     $this->info(" ");
        //     $this->info('yang ada di versi invoice purchase tapi tidak ada di versi jurnal:');
        //     $problemInv=[];
        //     foreach ($inv as $key => $value) {
        //         if (!isset($kartustock[$key])) {
        //             $this->info('Kartu Stock ID: ' . $value->kartu_id . ' Type: ' . $value->kartu_type . ' Amount: ' . $value->total_price);
        //         }
        //     }
        //     $this->info('total problem inv '.collect($problemInv)->sum('total_price'));
        $startDate = createCarbon($monthyear . '-01')->startOfMonth();
        $endDate = createCarbon($monthyear . '-01')->endOfMonth();
        $inv = InvoicePurchaseDetail::from('invoice_purchase_details as inv')->leftJoin('journals as j','j.id','=','inv.journal_id')->whereBetween('inv.created_at', [$startDate, $endDate])->orderBy('inv.index_date')
        ->select('inv.*',DB::raw('j.amount_debet-j.amount_kredit as amount_journal'),'j.index_date as journal_index_date','j.code_group as journal_code_group')
        ->get();
        $datas = [];
        $keys = [
            'id',
            'created_at',
            'invoice_pack_number',
            'factur_supplier_number',
            'index_date',
            'kartu_stock_id',
            'kartu_stock_type',
            'total_price',
            'journal_id',
            'journal_number',
            'amount_journal',
            'journal_index_date',
            'journal_code_group',


        ];
        $datas[] = $keys;
        foreach ($inv as $i) {
            $datas[] = [
                $i->id,
                $i->created_at,
                $i->invoice_pack_number,
                $i->factur_supplier_number,
                $i->index_date,
                $i->kartu_stock_id,
                $i->kartu_stock_type,
                $i->total_price,
                $i->journal_id,
                $i->journal_number,
                $i->amount_journal,
                $i->journal_index_date,
                $i->journal_code_group,
            ];
        }

        $file = public_path('files/purchase.csv');
        $handle = fopen($file, 'w');
        foreach ($datas as $d) {
            fputcsv($handle, $d);
        }
        fclose($handle);
        $this->info('File CSV has been created at: ' . $file);
    }
}
