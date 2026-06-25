<?php

namespace App\Console\Commands;

use App\Imports\ExcelPenjualanImport;
use App\Models\LinkTokoParent;
use App\Models\RetailToko;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class CekDataPenjualanImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cek-data-penjualan-import {namafile} {monthyear}';

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
        $namafile = $this->argument('namafile');
        $importer = new ExcelPenjualanImport;
        Excel::import(
            $importer,
            public_path('dataimport/' . $namafile . '.xlsx')
        );
        Session::put('book_journal_id', 2);
        $refToko = RetailToko::select('name', 'id')->get()->map(function ($item) {
            return [
                'name' => norm_string($item->name),
                'id' => $item->id,
            ];
        })->pluck('id', 'name')->all();
        $linkToko = LinkTokoParent::where('parent_type', 'App\Models\RetailToko')
            ->pluck('toko_id', 'parent_id')->all();
        $data = $importer->result;
        $data = collect($data)->groupBy('no_transaksi')->map(function ($item, $key) use ($refToko, $linkToko) {
            $tokoname = norm_string(collect($item)->first()['nama_toko'] ?? null);
            $tanggal = collect($item)->first()['tanggal'];
            $akunCash = collect($item)->first()['payment'];
            $tokoId = $refToko[$tokoname] ?? null;
            $realTokoId = $linkToko[$tokoId] ?? null;
            return [
                'package_number' => $key . '-draft',
                'thekey' => collect($item)->pluck('kode_barang')->implode('_') . '-' . collect($item)->first()['total_nota'],
                'payment' => collect($item)->first()['payment'],

                'details' => collect($item)->map(function ($val) {
                    return [
                        'created_at' => excelSerialToCarbon($val['tanggal']),
                        'stock_id' => $val['kode_barang'],
                        'stock_name' => $val['nama_barang'],
                        'quantity' => $val['quantity'],
                        'unit' => $val['satuan'],
                        'price' => $val['harga_pcs'],
                        'total_price' => $val['sub_total'],
                        'akun_cash_kind_name' => $val['payment'],
                        'reference_id' => null,
                        'reference_type' => null,
                        'toko' => $val['nama_toko'] ?? null,
                    ];
                }),
                // 'created_at' => excelSerialToCarbon($tanggal),
                // 'akun_cash_kind_name' => $akunCash,
                'total_nota' => collect($item)->first()['total_nota'],
                // 'customer_name' => collect($item)->first()['nama_customer'] ?? 'Anonim',
                'toko_id' => $refToko[$tokoname] ?? null,
                'real_toko_id' => $realTokoId,
                // 'toko' => collect($item)->first()['nama_toko'] ?? null,
                // 'id' => $idBuatan++,
            ];
        });
        $numberKey = collect($data)->pluck('package_number');
        // tampilkanTableTerminal($data, [
        //     'package_number' => 'center',
        //     'key' => 'left',
        //     'total_nota' => 'right',
        // ], $this);

        $monthyear = $this->argument('monthyear');
        $startDate = createCarbon($monthyear . '-01')->startOfMonth();
        $endDate = createCarbon($monthyear . '-01')->endOfMonth();
        $sosKey = SalesOrder::from('sales_orders as so')->whereBetween('so.created_at', [$startDate, $endDate])
            ->pluck('so.draft_number');

        //yang ga ada di database tapi ada di file import
        $diffSO = [];
        foreach ($numberKey as $key) {
            if (!$sosKey->contains($key)) {
                $diffSO[] = $key;
            }
        }


        //yang ga ada di file import tapi ada di database
        $diffNumber = [];
        foreach ($sosKey as $key) {
            if (!$numberKey->contains($key)) {
                $diffNumber[] = $key;
            }
        }
        $awalDiffSO = count($diffSO);
        $awalDiffNumber = count($diffNumber);
        $this->info('count asing difile import' . count($diffSO) . ' count asing di database ' . count($diffNumber));


        // seharusnya semua yang ada di diffnumber nanti akan kita cari mana yang cocok di file importnya. diffNumber -> diffSO
        //cari satu satu ya
        $diffNumberFound = [];
        foreach ($diffNumber as $number) {
            $this->info('cari data untuk ' . $number);
            $salesDetails = SalesOrderDetail::from('sales_order_details as sd')->join('stocks as s', 'sd.stock_id', '=', 's.id')
                ->where('draft_number', $number)->select('toko_id', 's.reference_stock_id', 'quantity', DB::raw('total_ppn_k+total_price as total'))->get();
            if (count($salesDetails) == 0) {
                $this->info('tidak ada data detail untuk ' . $number);
                continue;
            }
            $totalNota = collect($salesDetails)->sum('total');
            $tokoID = collect($salesDetails)->first()->toko_id;
            $dataFilter = collect($data)->filter(function ($item) use ($totalNota, $tokoID, $diffSO) {
                if ($item['total_nota'] == $totalNota && $item['real_toko_id'] == $tokoID && in_array($item['package_number'], $diffSO)) {
                    return true;
                }
            });
            foreach ($dataFilter as $pack) {
                $cocok = 1;
                foreach ($pack['details'] as $detail) {
                    $saleDetail = collect($salesDetails)->firstWhere('reference_stock_id', $detail['stock_id']);
                    if (!$saleDetail) {
                        $cocok = 0;
                        break;
                    }
                    if ($saleDetail->quantity != $detail['quantity']) {
                        $cocok = 0;
                        break;
                    }
                    if ($saleDetail->total != $detail['total_price']) {
                        $cocok = 0;
                        break;
                    }
                }
                if (!$cocok) {
                    continue;
                } else {
                    $this->info('wow ketemu, ternyata ' . $number . ' cocok dengan ' . $pack['package_number'] . ' total nota ' . $totalNota . ' toko id ' . $tokoID);
                    $salesOrder = SalesOrder::where('draft_number', $number)->first();
                    $salesOrder->draft_number = $pack['package_number'];
                    $salesOrder->save();
                    foreach ($salesOrder->details as $detail) {
                        $detail->draft_number = $pack['package_number'];
                        $detail->save();
                    }

                    // $this->info('sementara ini dulu coba di cek dulu ya');
                    //karena cocok, maka package_number itu kita keluarkan ya dari list 
                    $diffSO = array_filter($diffSO, function ($item) use ($pack) {
                        return $item != $pack['package_number'];
                    });
                    $diffNumberFound[] = $number;
                    break;
                }
            }
        }
        $diffNumber = collect($diffNumber)->diff($diffNumberFound)->values()->all();

        $this->info('MULA MULA count asing difile import' . $awalDiffSO . ' count asing di database ' . $awalDiffNumber);
        $this->info('SESUDAH count asing difile import' . count($diffSO) . ' count asing di database ' . count($diffNumber));

        $this->info('LIST ASING DI FILE IMPORT');
        foreach ($diffSO as $item) {
            $this->info($item);
        }
        $this->info('LIST ASING DI DATABASE');
        foreach ($diffNumber as $item) {
            $this->info($item);
        }

        $totalNotaDatabase = SalesOrder::whereIn('draft_number', $diffNumber)->sum(DB::raw('total_price+total_ppn_k'));
        $totalNotaImport = collect($data)->filter(function ($item) use ($diffSO) {
            return in_array($item['package_number'], $diffSO);
        })->sum('total_nota');
        $this->info('TOTAL NOTA DATABASE ' . format_price($totalNotaDatabase));
        $this->info('TOTAL NOTA IMPORT ' . format_price($totalNotaImport));

        if (count($diffNumber) > 0 && $this->confirm('Apakah anda ingin menghapus sales order yang tidak ada di file import?')) {
            $this->info('kita hapus ya sales order yang ga ada number nya');
            foreach ($diffNumber as $number) {
                $sale = SalesOrder::where('draft_number', $number)->first();
                $st = $sale->removeAllProcess();
                if ($st['status'] == 1) {
                    $this->info('berhasil hapus process sales order ' . $number);
                    SalesOrderDetail::where('draft_number', $number)->delete();
                    $sale->delete();
                    $this->info('berhasil hapus sales order ' . $number);
                } else {
                    $this->info('gagal hapus process sales order ' . $number . ' error: ' . $st['msg']);
                }
            }
        }
    }
}
