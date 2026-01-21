<?php

namespace App\Console\Commands;

use App\Models\InvoiceSaleDetail;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuDPSales;
use App\Models\KartuHutang;
use App\Models\KartuInventory;
use App\Models\KartuPiutang;
use App\Models\SalesOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RelinkKartu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:relink-kartu {bookid}';

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
        $bukuId = $this->argument('bookid');
        Session::put('book_journal_id', $bukuId);
        $kartuPiutang = DB::table('kartu_piutangs')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'kartu_piutangs.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\KartuPiutang'"));
        })->select('kartu_piutangs.id')->get();

        $kartuDp = DB::table('kartu_dp_sales')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'kartu_dp_sales.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\KartuDPSales'"));
        })->select('kartu_dp_sales.id')->get();
        $kartuUtang = DB::table('kartu_hutangs')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'kartu_hutangs.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\KartuHutang'"));
        })->select('id')->get();
        $kartuBahanJadi = DB::table('kartu_bahan_jadis')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'kartu_bahan_jadis.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\KartuBahanJadi'"));
        })->select('id')->get();
        $kartuBDP = DB::table('kartu_bdps')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'kartu_bdps.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\KartuBDP'"));
        })->select('id')->get();
        $kartuInventory = DB::table('kartu_inventories')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'kartu_inventories.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\KartuInventory'"));
        })->select('id')->get();
        $invoiceSaleDetail = DB::table('invoice_sale_details')->whereNotExists(function ($q) {
            $q->selectRaw('1')->from('detail_kartu_invoices')
                ->whereColumn('detail_kartu_invoices.kartu_id', 'invoice_sale_details.id')
                ->whereColumn('detail_kartu_invoices.kartu_type', DB::raw("'App\\\Models\\\InvoiceSaleDetail'"));
        })->select('id')->get();

        $this->info('ditemukan Kartu Piutang : ' . $kartuPiutang->count());
        if (!$this->confirm('Yakin melanjutkan proses relink Kartu Piutang ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }
        foreach ($kartuPiutang as $kp) {
            $this->info('Relink Kartu Piutang ID ' . $kp->id);
            $kp = KartuPiutang::find($kp->id);

            $res = $kp->createDetailKartuInvoice();
            if ($res['status'] == 1) {
                $this->info('Relink Kartu Piutang ID ' . $kp->id . ' Berhasil');
            }
        }
        $this->info('ditemukan Kartu DP Sales : ' . $kartuDp->count());
        if (!$this->confirm('Yakin melanjutkan proses relink Kartu DP ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }

        foreach ($kartuDp as $kp) {
            $kp = KartuDPSales::find($kp->id);
            $res = $kp->createDetailKartuInvoice();
            if ($res['status'] == 1) {
                $this->info('Relink Kartu DP Sales ID ' . $kp->id . ' Berhasil');
            }
        }

        $this->info('ditemukan Kartu Hutang : ' . $kartuUtang->count());
        if (!$this->confirm('Yakin melanjutkan proses relink Kartu Hutang ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }
        foreach ($kartuUtang as $kh) {
            $kh = KartuHutang::find($kh->id);
            $res = $kh->createDetailKartuInvoice();
            if ($res['status'] == 1) {
                $this->info('Relink Kartu Hutang ID ' . $kh->id . ' Berhasil');
            }
        }

        $this->info('ditemukan Kartu Bahan Jadi : ' . $kartuBahanJadi->count());
        if (!$this->confirm('Yakin melanjutkan proses relink Kartu Bahan Jadi ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }
        foreach ($kartuBahanJadi as $kbj) {
            $kbj = KartuBahanJadi::find($kbj->id);
            $res = $kbj->createDetailKartuInvoice();
            if ($res['status'] == 1) {
                $this->info('Relink Kartu Bahan Jadi ID ' . $kbj->id . ' Berhasil');
            }
        }

        $this->info('ditemukan Kartu BDP : ' . $kartuBDP->count());
        if (!$this->confirm('Yakin melanjutkan proses relink Kartu BDP ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }
        foreach ($kartuBDP as $kbdp) {
            $kbdp = KartuBDP::find($kbdp->id);
            $res = $kbdp->createDetailKartuInvoice();
            if ($res['status'] == 1) {
                $this->info('Relink Kartu BDP ID ' . $kbdp->id . ' Berhasil');
            }
        }

        $this->info('ditemukan Invoice Sale Detail : ' . $invoiceSaleDetail->count());
        if (!$this->confirm('Yakin melanjutkan proses relink Invoice Sale Detail ? ')) {
            $this->info('Proses dibatalkan');
            return;
        }
        foreach ($invoiceSaleDetail as $isd) {
            $isd = InvoiceSaleDetail::find($isd->id);
            if ($isd) {
                $res = $isd->createDetailKartuInvoice();
                if ($res['status'] == 1) {
                    $this->info('Relink Invoice Sale Detail ID ' . $isd->id . ' Berhasil');
                }
            }
        }


        $so= SalesOrder::where(function($q){
            $q->where('status_payment','<>','LUNAS 100%')->orWhere('status_delivery','<>','terkirim 100%');
        })->get();
        foreach($so as $s){
            $s->updateStatus();
            $this->info('Update status Sales Order ID '.$s->id.' Berhasil, status sekarang: '.$s->status.', payment: '.$s->status_payment.', delivery: '.$s->status_delivery);
        }
        // $this->info('ditemukan Kartu Inventory : ' . $kartuInventory->count());
        // if (!$this->confirm('Yakin melanjutkan proses relink Kartu Inventory ? ')) {
        //     $this->info('Proses dibatalkan');
        //     return;
        // }
        // foreach ($kartuInventory as $ki) {
        //     $ki = KartuInventory::find($ki->id);
        //     $res = $ki->createDetailKartuInvoice();
        //     if ($res['status'] == 1) {
        //         $this->info('Relink Kartu Inventory ID ' . $ki->id . ' Berhasil');
        //     }
        // }
    }
}
