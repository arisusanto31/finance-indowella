<?php

namespace App\Imports\excel_saldo_awal_stock_jurnal;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelSaldoAwalImport implements WithMultipleSheets
{
    public $data = [];

    public function sheets(): array
    {
        return [
            'saldo_jurnal' => new _saldo_jurnal_awal_import($this),
            'saldo_stock' => new _saldo_stock_awal_import($this),
        ];
    }
}
