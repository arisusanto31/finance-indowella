<?php

namespace App\Imports\excel_kartu_stock;

use App\Traits\ExcelHeaderDetect;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class _kartu_stock_import implements ToArray
    {

        use ExcelHeaderDetect;
    /**
    * @param Collection $collection
    */
    protected $parent;
    protected $array;
    public function __construct($parent)
    {
        $this->parent = $parent;
    }
    public function array(array $array)
    {
        //
        $this->array = $array;
        $headers =[
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Mutasi Masuk',
            'Mutasi Keluar',
            'Saldo Akhir'
        ];
        $array= $this->extractData($headers,true);
        
        $this->parent->data['kartu_stock']= $array;
    }
}
