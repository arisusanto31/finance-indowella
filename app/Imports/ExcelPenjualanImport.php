<?php

namespace App\Imports;

use App\Traits\ExcelHeaderDetect;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExcelPenjualanImport implements ToArray
{
    use ExcelHeaderDetect;

    public $result;
    protected $array;
    public function array(array $array)
    {
        //
        $this->array = $array;
        $headers = [
            'Tanggal',
            'Kode Barang',
            'Nama Barang',
            'Quantity',
            'Satuan',
            'Harga/Pcs',
            'Sub Total',
            'Total Nota',
            'No Transaksi',
            'Payment',
            'Nama Toko',
            'Nama Customer'
        ];

        $fillHeaders=[
            'No Transaksi',
            'Total Nota',
            'Nama Toko',
            'Nama Customer'
        ];
        $array = $this->extractData($headers,false,$fillHeaders);

        $this->result = $array;
    }
}
