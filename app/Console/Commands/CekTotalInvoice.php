<?php

namespace App\Console\Commands;

use App\Models\InvoiceSaleDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CekTotalInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:total-invoice';

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

        $data = DB::SELECT('select i.id,i.quantity,i.price,i.total_price,s.total_price as s_total_price,i.invoice_pack_number,i.is_ppn,i.created_at from invoice_sale_details as i left join sales_order_details as s on s.id= i.sales_order_id  where i.price*i.quantity != i.total_price AND i.total_price !=s.total_price ;');
        tampilkanTableTerminal(
            $data,
            [
                'id' => 'center',
                'quantity' => 'center',
                'price' => 'right',
                'total_price' => 'right',
                's_total_price' => 'right',
                'invoice_pack_number' => 'center',
                'is_ppn' => 'center',
                'created_at' => 'center',
            ],
            $this
        );
        $allupdate = [];
        foreach ($data as $d) {
            $allupdate[] = [
                'id' => $d->id,
                'total_price' => $d->s_total_price
            ];
        }
        upsertInChunks(InvoiceSaleDetail::class, $allupdate, 'id', ['total_price']);
        $this->info("Total " . count($data) . " invoice berhasil diupdate");

        //butuh update semua invoice pack ynag tidak sesuai total sum price
        $invpacks = DB::SELECT('select inv.invoice_number,inv.id as invoice_id, sum(d.total_price) as sum_total_price,
             inv.total_price from invoice_packs as inv join invoice_sale_details as d on d.invoice_pack_number = inv.invoice_number
             group by inv.invoice_number having sum_total_price != inv.total_price');

        tampilkanTableTerminal(
            $invpacks,
            [
                'invoice_id' => 'center',
                'invoice_number' => 'center',
                'sum_total_price' => 'right',
                'total_price' => 'right',
            ],
            $this
        );

        $allUpdate = [];
        foreach ($invpacks as $inv) {
            $allUpdate[] = [
                'id' => $inv->invoice_id,
                'total_price' => $inv->sum_total_price
            ];
        }
        upsertInChunks(\App\Models\InvoicePack::class, $allUpdate, 'id', ['total_price']);
        $this->info("Total " . count($invpacks) . " invoice pack yang total price nya berhasil diupdate");
    }
}
