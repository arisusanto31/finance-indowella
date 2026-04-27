<?php

namespace App\Console\Commands;

use App\Models\InvoicePurchaseDetail;
use App\Models\Journal;
use App\Models\KartuStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class searchUnBalancePurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:unbalance-purchase';

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
        //3
        Session::put('book_journal_id', 1);
        $purchases = InvoicePurchaseDetail::from('invoice_purchase_details as p')
            ->leftJoin('journals as j', 'p.journal_id', '=', 'j.id')
            ->leftJoin('kartu_stocks as ks', 'p.kartu_stock_id', '=', 'ks.id')
            ->select(
                'p.id as purchasing_id',
                'j.id as journal_id',
                'j.amount_debet',
                'ks.id as kartu_stock_id',
                'p.total_price',
                'ks.mutasi_rupiah_total as total_kartu',
                DB::raw('sum(coalesce(p.total_price, 0)) as sum_total_price'),
                DB::raw('sum(coalesce(ks.mutasi_rupiah_total, 0)) as sum_mutasi_rupiah')
            )
            ->whereNotNull('j.id')
            ->groupBy('j.id')
            ->havingRaw('sum_total_price!= amount_debet or sum_total_price != sum_mutasi_rupiah')
            ->get();


        tampilkanTableTerminal(
            $purchases,
            [
                'purchasing_id' => 'center',
                'sum_total_price' => 'right',
                'journal_id' => 'center',
                'amount_debet' => 'right',
                'kartu_stock_id' => 'center',
                'sum_mutasi_rupiah' => 'right'
            ],
            $this
        );

        foreach ($purchases as $purchase) {
            $inv = InvoicePurchaseDetail::find($purchase->purchasing_id);
            $inv->fillKartuStockID();
        }

        $this->info('kita lihat secara detail yaa');
        $details = InvoicePurchaseDetail::whereIn('invoice_purchase_details.journal_id', $purchases->pluck('journal_id'))->leftJoin(
            'kartu_stocks as ks',
            'invoice_purchase_details.kartu_stock_id',
            '=',
            'ks.id'
        )
            ->select('invoice_purchase_details.*', 'ks.mutasi_rupiah_total as total_kartu')
            ->get();
        $purchases = collect($purchases)->keyBy('journal_id');
        $details = collect($details)->map(function ($item) use ($purchases) {

            if (isset($purchases[$item['journal_id']])) {
                $item['amount_debet'] = $purchases[$item['journal_id']]['amount_debet'];
            }
            return $item;
        });
        tampilkanTableTerminal(
            $details,
            [
                'id' => 'center',
                'kartu_stock_id' => 'center',
                'journal_id' => 'center',
                'quantity' => 'right',
                'price' => 'right',
                'total_price' => 'right',
                'total_kartu' => 'right',
                'amount_debet' => 'right'
            ],
            $this
        );
        if ($this->confirm('mau fix ?')) {
            foreach ($details as $detail) {
                //cek dulu total invoice, jangan jangan yang benar dari total kartu
                $total = $detail->quantity * $detail->price;
                $this->info('cek invoice total : ' . $total);
                if (abs($total - $detail->total_price) > 0.001) {
                    if (abs($total - floatval($detail->total_kartu)) < 0.001) {
                        $invP= InvoicePurchaseDetail::find($detail->id);
                        $invP->total_price = $total;
                        $invP->save();
                        $this->info('update invoice purchase detail id ' . $detail->id . ' dengan total ' . $total);
                        continue;
                    }
                }
                if (abs($total - $detail->total_price) <0.001 && abs($total - floatval($detail->total_kartu)) > 0.001) {
                    //ini kartu dan jurnal harus diupdate. gimana brani kah ?
                    $kartuStock = KartuStock::find($detail->kartu_stock_id);
                    $saldoBefore =  $kartuStock->saldo_rupiah_total - $kartuStock->mutasi_rupiah_total;
                    $kartuStock->mutasi_rupiah_total = $total;
                    $kartuStock->saldo_rupiah_total = $saldoBefore + $total;
                    $kartuStock->save();
                    $kartuStock->recalculateSaldo();

                    $journals= Journal::where('journal_number', $kartuStock->journal_number)
                        ->get();
                    foreach ($journals as $j) {
                        if ($j->amount_debet > 0) {
                            $j->amount_debet = $total;
                            $j->save();
                            $j->recalculateJournal();
                        } else {
                            $j->amount_kredit = $total;
                            $j->save();
                            $j->recalculateJournal();
                        }
                    }
                    $this->info('update kartu stock id ' . $detail->kartu_stock_id . ' dengan total ' . $total);
                    continue;
                }
                $this->error('tidak bisa fix untuk invoice purchase detail id ' . $detail->id . ' dengan total ' . $total);
            }
        }
    }
}
