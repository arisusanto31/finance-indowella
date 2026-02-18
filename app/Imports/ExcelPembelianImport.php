<?php

namespace App\Imports;

use App\Traits\ExcelHeaderDetect;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExcelPembelianImport implements ToArray
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
            'Diskon',
            'Harga/Pcs',        
            'No Invoice',
            'Payment',
            'Kode Toko',
            'Supplier'
        ];

        $fillHeaders = [
            'No Invoice',
            'Supplier'
        ];
        $array = $this->extractData($headers, false, $fillHeaders);

        $this->result = $array;
    }
}
