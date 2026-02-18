<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KartuHutangFacturSupplierNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dummy-kartu-hutang-factur-supplier-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dummy command for kartu hutang factur supplier number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $khs = DB::table('kartu_hutangs')->where('factur_supplier_number', null)->get();
        foreach ($khs as $kh) {
            DB::table('kartu_hutangs')->where('id', $kh->id)->update([
                'factur_supplier_number' => $kh->invoice_pack_number
            ]);
            $this->info('Updated Kartu Hutang ID: ' . $kh->id . ' with Factur Supplier Number: ' . $kh->invoice_pack_number);
        }
    }
}
