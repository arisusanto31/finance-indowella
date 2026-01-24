<?php

namespace App\Imports\excel_saldo_awal_stock_jurnal;

use App\Traits\ExcelHeaderDetect;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class _saldo_jurnal_awal_import implements ToArray
{
    /**
     * @param Collection $collection
     */
    use ExcelHeaderDetect;
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
        $headers = [
            'account_code',
            'account_name',
            'amount_saldo'
        ];
        $array = $this->extractData($headers);

        $this->parent->data['jurnal'] = $array;
    }
}
