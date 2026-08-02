<?php

namespace App\Console\Commands;

use App\Models\InvoiceSaleDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SplitNotaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'split:nota-command {salesid}';

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

        try {
            DB::beginTransaction();

            $sale = DB::table('sales_orders')->where('id', $this->argument('salesid'))->first();

            //kita cari invice detail nya yaa 
            Session::put('book_journal_id', $sale->book_journal_id);
            $sale = SalesOrder::find($sale->id);
            $details = SalesOrderDetail::where('sales_order_id', $sale->id)->get();

            $this->info('DATA awal sales order');
            $this->info('nomer nota : ' . $sale->sales_order_number);
            $this->info('jumlah detail : ' . count($details));
            $this->info('total price : ' . $sale->total_price);
            $this->info('total ppn : ' . $sale->total_ppn_k);
            $this->info('------------------------------------------');
            foreach ($details as $detail) {
                $this->info(' > detail : ' . $detail->custom_stock_name . ' | qty : ' . $detail->quantity . ' ' . $detail->unit . ' | total price : ' . $detail->total_price);
            }

            $this->info('------------------------------------------');
            $this->info('checking price = ' . $sale->total_price . ' vs sum detail = ' . collect($details)->sum('total_price'));
            $this->info('checking ppn = ' . $sale->total_ppn_k . ' vs sum detail = ' . collect($details)->sum('total_ppn_k'));
            if (!$this->confirm('Apakah kamu yakin ingin membagi nota ini menjadi beberapa nota?')) {
                $this->info('Proses dibatalkan');
                return;
            }
            $totalPriceAwall = $sale->total_price;
            $totalPPNAwal = $sale->total_ppn_k;
            $countNota = $this->ask('mau jadi berapa nota lur ? (masukkan angka, misal 2,3,4 dst)');
            $countNota = (int)$countNota;
            //kita bagi jadi 5 item aja 
            $newsales = [];
            $newsales[] = $sale;
            for ($i = 0; $i < $countNota - 1; $i++) {
                $thesale = new SalesOrder();
                $thesale->toko_id = $sale->toko_id;
                $thesale->book_journal_id = bookID();
                $thesale->is_ppn = $sale->is_ppn;
                $thesale->total_ppn_k = $sale->total_ppn_k;
                $thesale->total_price = $sale->total_price;
                $thesale->ref_akun_cash_kind_name = $sale->ref_akun_cash_kind_name;
                $thesale->draft_number = $sale->draft_number . '-' . ($i + 2);
                $thesale->sales_order_number = $thesale->draft_number;
                $thesale->customer_id = $sale->customer_id;
                $thesale->save();
                $newsales[] = $thesale;
            }
            $this->info('Berhasil ada ' . count($newsales) . ' nota baru');

            $count = 0;
            $stage = 0;
            $maxCount = ceil(count($details) / $countNota);
            foreach ($details as $detail) {
                $detail->sales_order_id = $newsales[$stage];
                $detail->sales_order_number = $newsales[$stage]->sales_order_number;
                $detail->save();
                $count++;
                if ($count >= $maxCount) {
                    $count = 0;
                    $stage++;
                }
            }

            $this->info('inilah hasil pembagian nota nya');
            foreach ($newsales as $sale) {
                $sale->updateTotalPrice();
                $sale->refresh();
                $this->info('nomer nota : ' . $sale->sales_order_number);
                $this->info('jumlah detail : ' . count($sale->details));
                $this->info('total price : ' . $sale->total_price);
                $this->info('total ppn : ' . $sale->total_ppn_k);
                $this->info('------------------------------------------');
                foreach ($sale->details as $detail) {
                    $this->info(' > detail : ' . $detail->custom_stock_name . ' | qty : ' . $detail->quantity . ' ' . $detail->unit . ' | total price : ' . $detail->total_price);
                }
                $this->info('------------------------------------------');
                $this->info('checking price = ' . $sale->total_price . ' vs sum detail = ' . collect($sale->details)->sum('total_price'));
                $this->info('checking ppn = ' . $sale->total_ppn_k . ' vs sum detail = ' . collect($sale->details)->sum('total_ppn_k'));
                $this->info('------------------------------------------');
                $this->info(' ');
            }

            $totalPriceAkhir = collect($newsales)->sum('total_price');
            $totalPPNAkhir = collect($newsales)->sum('total_ppn_k');
            $this->info('Proses selesai, coba cek total price awal dibanding akhir');
            $this->info('total price awal = ' . $totalPriceAwall . ' vs total price akhir = ' . $totalPriceAkhir);
            $this->info('total ppn awal = ' . $totalPPNAwal . ' vs total ppn akhir = ' . $totalPPNAkhir);
            if ($totalPriceAwall != $totalPriceAkhir || $totalPPNAwal != $totalPPNAkhir) {
                $this->info('WARNING : total price atau total ppn tidak sama, mohon cek lagi');
            } else {
                $this->info('total price dan total ppn sama, proses BERHASIL');
            }
            DB::commit(); // hanya untuk checker dulu aja hehe..
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
